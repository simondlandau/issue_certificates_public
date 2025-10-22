<?php
session_start();

// ✅ Only allow access if opened from index.html
if (!isset($_GET['from']) || $_GET['from'] !== 'index') {
    header("Location: index.html");
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/PHPMailer/src/Exception.php";
require __DIR__ . "/PHPMailer/src/PHPMailer.php";
require __DIR__ . "/PHPMailer/src/SMTP.php";
require_once __DIR__ . "/config.php";

// ✅ Database connection
$errors = [];
$success = false;

// ✅ CSRF token generation
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // ✅ CSRF validation
    if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
        $errors[] = "Invalid form submission. Please try again.";
    }

    // Collect and trim input
    $principal_name = trim($_POST['principal_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $broker_name = trim($_POST['broker_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $building_name = trim($_POST['building_name'] ?? '');
    $street_name = trim($_POST['street_name'] ?? '');
    $town = trim($_POST['town'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $postcode = trim($_POST['postcode'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $permit_type = trim($_POST['permit_type'] ?? '');

    // Mandatory fields
    if (empty($principal_name) || empty($email) || empty($broker_name) || empty($address) || empty($building_name) || empty($street_name) || empty($town) || empty($country) || empty($postcode) || empty($contact_number) || empty($permit_type)) {
        $errors[] = "All mandatory fields must be completed.";
    }

    // Email format validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // ✅ File uploads & validation
    $allowedTypes = [
        'application/msword', // doc
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
        'application/vnd.oasis.opendocument.text', // odt
        'application/vnd.ms-excel', // xls
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
        'application/vnd.oasis.opendocument.spreadsheet', // ods
        'application/pdf', // pdf
        'image/jpeg', // jpg/jpeg
        'image/png' // png
    ];

    $maxFileSize = 5 * 1024 * 1024; // 5MB

    $files = ['identification', 'accounts', 'financials'];
    $uploads = [];

    foreach ($files as $file) {
        if (!empty($_FILES[$file]['tmp_name'])) {
            if ($_FILES[$file]['size'] > $maxFileSize) {
                $errors[] = ucfirst($file) . " exceeds the maximum allowed size of 5MB.";
            } elseif (!in_array($_FILES[$file]['type'], $allowedTypes)) {
                $errors[] = ucfirst($file) . " type is not allowed.";
            } else {
                $uploads[$file] = file_get_contents($_FILES[$file]['tmp_name']);
            }
        } else {
            $uploads[$file] = null;
        }
    }

    if (empty($errors)) {
        try {
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            // ✅ Check email and broker uniqueness
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) AS count, 'email' AS type FROM registration WHERE email = ? 
                                        UNION ALL 
                                        SELECT COUNT(*) AS count, 'broker' AS type FROM registration WHERE broker_name = ?");
            $stmtCheck->execute([$email, $broker_name]);
            $results = $stmtCheck->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results as $row) {
                if ($row['count'] > 0) {
                    if ($row['type'] === 'email') $errors[] = "This email has already been submitted with a previous Permit Application.";
                    if ($row['type'] === 'broker') $errors[] = "This Broker has already been submitted with a previous Permit Application.";
                }
            }

            if (empty($errors)) {
                // ✅ Insert new record
                $stmt = $pdo->prepare("INSERT INTO registration 
                    (principal_name, email, broker_name, address, building_name, street_name, town, country, postcode, contact_number, identification, accounts, financials, permit_type)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->execute([
                    $principal_name, $email, $broker_name, $address,
                    $building_name, $street_name, $town, $country, $postcode,
                    $contact_number, $uploads['identification'], $uploads['accounts'], $uploads['financials'], $permit_type
                ]);

                // ✅ Send confirmation email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = '@gmail.com'; // replace
                    $mail->Password   = ''; // app password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('@gmail.com', 'COMPANY');
                    $mail->addAddress($email, $principal_name);
                    $mail->addBCC('@gmail.com');

                    $mail->isHTML(true);
                    $mail->Subject = 'Registration Confirmation - COMPANY';

                    $mailBody = <<<HTML
<div style="text-align:center; color:blue; font-weight:bold; font-style:italic; font-size:16px;">
COMPANY Licensing Authority
<img src="cid:company_logo" style="width:50px; height:50px; vertical-align:middle;">
</div>
<p>Dear {$principal_name}. Representing {$broker_name},</p>
<p>{$address}<br>{$building_name}<br>{$street_name}<br>{$town}<br>{$country}<br>{$postcode}</p>
<p>Your request for a {$permit_type} Permit has been received. We are processing your request and your Permit and invoice will be sent to this email address as soon as possible.</p>
<p>Thank you for your support and we wish you every success in your ventures.</p>
<p>Regards,<br>COMPANY Licensing Authority</p>
HTML;

                    $mail->Body = $mailBody;

                    // Attach the logo as embedded image
                    $mail->addEmbeddedImage(__DIR__ . '/license.jpg', 'company_logo');

                    $mail->send();

                } catch (Exception $e) {
                    error_log("PHPMailer error: " . $e->getMessage());
                    $errors[] = "There was an issue sending your confirmation email. Please try again later.";
                }

                // ✅ Redirect after success
                if (empty($errors)) {
                    header("Location: index.html?registered=1");
                    exit();
                }
            }

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $errors[] = "A database error occurred while processing your request. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registration Form - COMPANY</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="page">
    <header class="header">
      <div class="brand">
        <h1 class="title"> Broker Licensing Authority.</h1>
        <p class="subtitle">We deal with your Licensing Challenges.</p>
      </div>
      <div class="logo-slot">
        <img src="license.jpg" alt="Organization Logo" class="logo-img">
      </div>
    </header>

    <section class="red-divider">
      <img src="sectionsm.ico" alt="Section Icon" class="red-divider-icon">
      <span class="red-divider-title">Registration</span>
    </section>

    <main class="content">
      <h2>Registration Form</h2>
      <div class="section-header">
        <img src="sectionsm.ico" alt="Section Icon" class="section-icon">
        <span class="section-title">Fields marked with <b>*</b> are mandatory. </span>
      </div>

<?php if (!empty($errors)): ?>
  <div class="alert error">
    <span class="close-btn">&times;</span>
    <ul>
      <?php foreach ($errors as $error) echo "<li>$error</li>"; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" id="registrationForm">
    <!-- ✅ CSRF token -->
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['token']); ?>">

    <label>Principal Name*<br>
      <input type="text" name="principal_name" required value="<?php echo htmlspecialchars($_POST['principal_name'] ?? ''); ?>">
    </label><br><br>

    <label>Email*<br>
      <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
    </label><br><br>

    <label>Broker Name*<br>
      <input type="text" name="broker_name" required value="<?php echo htmlspecialchars($_POST['broker_name'] ?? ''); ?>">
    </label><br><br>

    <label>Address*<br>
      <input type="text" name="address" required value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
    </label><br><br>

    <label>Building Name*<br>
      <input type="text" name="building_name" required value="<?php echo htmlspecialchars($_POST['building_name'] ?? ''); ?>">
    </label><br><br>

    <label>Street Name*<br>
      <input type="text" name="street_name" required value="<?php echo htmlspecialchars($_POST['street_name'] ?? ''); ?>">
    </label><br><br>

    <label>Town*<br>
      <input type="text" name="town" required value="<?php echo htmlspecialchars($_POST['town'] ?? ''); ?>">
    </label><br><br>

    <label>Country*<br>
      <input type="text" name="country" required value="<?php echo htmlspecialchars($_POST['country'] ?? ''); ?>">
    </label><br><br>

    <label>Postcode*<br>
      <input type="text" name="postcode" required value="<?php echo htmlspecialchars($_POST['postcode'] ?? ''); ?>">
    </label><br><br>

    <label>Contact Number*<br>
      <input type="text" name="contact_number" required value="<?php echo htmlspecialchars($_POST['contact_number'] ?? ''); ?>">
    </label><br><br>

    <label>Identification Document<br>
      <span class="sublabel">(Principal's ID)</span><br>
      <input type="file" name="identification">
    </label><br><br>

    <label>Accounts Document<br>
      <span class="sublabel">(Company Registration)</span><br>
      <input type="file" name="accounts">
    </label><br><br>

    <label>Financials Document<br>
      <span class="sublabel">(Statement of Accounts or Current Bank Statement)</span><br>
      <input type="file" name="financials">
    </label><br><br>

    <label>Permit Type*<br>
      <span class="sublabel">(Select the type of Permit you require)</span><br>
      <select name="permit_type" required>
        <option value="">-- Select --</option>
        <option value="Representative" <?php if(($_POST['permit_type'] ?? '')==='Representative') echo 'selected'; ?>>Representative</option>
        <option value="Broker" <?php if(($_POST['permit_type'] ?? '')==='Broker') echo 'selected'; ?>>Broker</option>
        <option value="Advisor" <?php if(($_POST['permit_type'] ?? '')==='Advisor') echo 'selected'; ?>>Advisor</option>
      </select>
    </label><br><br>

    <button type="submit" id="submitBtn" class="register-btn" disabled>Submit</button>
</form>

    </main>

    <footer class="footer">
      <button class="to-top" aria-label="Back to top" title="Back to top">▲</button>
      <span class="copyright">© COMPANY Licensing Authority - <span id="year"></span></span>
    </footer>
  </div>

  <script>
    // Dynamic year
    document.getElementById('year').textContent = new Date().getFullYear();

    // Back to top
    document.querySelector('.to-top').addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Enable submit only when all required fields are filled
    const form = document.getElementById('registrationForm');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('input', () => {
      let allFilled = true;
      form.querySelectorAll('[required]').forEach(input => {
        if (!input.value.trim()) allFilled = false;
      });
      submitBtn.disabled = !allFilled;
    });

    // Close alert when "×" clicked
    document.querySelectorAll('.alert .close-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        btn.parentElement.style.display = 'none';
      });
    });
  </script>
</body>
</html>

