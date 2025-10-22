<?php
require_once 'config.php';
require_once 'header.php';

// PHPMailer
require __DIR__ . "/PHPMailer/src/PHPMailer.php";
require __DIR__ . "/PHPMailer/src/SMTP.php";
require __DIR__ . "/PHPMailer/src/Exception.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_GET['id'])) die("No registration ID provided.");
$id = (int)$_GET['id'];

// Fetch record
$stmt = $pdo->prepare("SELECT * FROM registration WHERE id = ?");
$stmt->execute([$id]);
$record = $stmt->fetch();
if (!$record) die("Record not found.");

$success = false;

// Handle form submission
if (isset($_POST['generate'])) {
    $permitHTML = '
    <div style="font-family: Arial, sans-serif; max-width:700px; margin:20px auto; border:1px solid #ddd; padding:20px;">
        <h2 style="text-align:center;">Permit</h2>
        <p><strong>Permit Type:</strong> ' . htmlspecialchars($record['permit_type']) . '</p>
        <p><strong>Principal:</strong> ' . htmlspecialchars($record['principal_name']) . '</p>
        <p><strong>Broker:</strong> ' . htmlspecialchars($record['broker_name']) . '</p>
        <p><strong>Requested Date:</strong> ' . date('d-m-Y', strtotime($record['created_at'])) . '</p>
        <p><strong>Address:</strong><br>' .
            htmlspecialchars($record['address'] . ' ' . $record['building_name']) . '<br>' .
            htmlspecialchars($record['street_name']) . '<br>' .
            htmlspecialchars($record['town']) . '<br>' .
            htmlspecialchars($record['country'] . ' ' . $record['postcode']) .
        '</p>
        <p style="text-align:center; margin-top:30px;">This permit is issued by COMPANY Licensing Authority.</p>
    </div>';

    // Send Email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($record['email'], $record['principal_name']);
        $mail->addBCC(SMTP_BCC);

        $mail->isHTML(true);
        $mail->Subject = "Your {$record['permit_type']} Permit - COMPANY";
        $mail->Body = $permitHTML;

        $mail->send();
        $success = true;

        // Update DB
        $stmt = $pdo->prepare("UPDATE registration SET permit_created = NOW() WHERE id = ?");
        $stmt->execute([$id]);

    } catch (Exception $e) {
        die("Permit email could not be sent: " . $mail->ErrorInfo);
    }
}
?>

<main class="content">
  <?php if (!$success): ?>
    <!-- Styled Form -->
    <section class="form-box">
      <h2>Generate Permit</h2>
      <p>Permit Type: <strong><?php echo htmlspecialchars($record['permit_type']); ?></strong></p>
      <form method="post">
        <button type="submit" name="generate" class="btn">Generate</button>
      </form>
    </section>
  <?php else: ?>
    <!-- Styled Success Message -->
    <section class="form-box" style="text-align:center;">
      <h2>Permit Generated &amp; Email Sent Successfully!</h2>
      <a href="admin_dashboard.php" class="btn">Return to Dashboard</a>
    </section>
  <?php endif; ?>
</main>

<?php require_once 'footer.php'; ?>

