<?php
require_once 'config.php';
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

$currentDate = date('d-m-Y');
$createdDate = date('d-m-Y', strtotime($record['created_at']));

ob_start(); // start output buffering
?>

<!-- Begin HTML Invoice -->
<div style="max-width:800px; margin:0 auto; font-family:Arial,sans-serif; font-size:12px; color:#000; line-height:1.4;">

    <!-- Logo -->
    <div style="text-align:center; margin-bottom:20px;">
        <img src="cid:logo" alt="COMPANY Logo" style="height:60px;">
        <h2 style="margin:5px 0;">COMPANY Licensing Authority</h2>
    </div>

    <!-- Header -->
    <div style="display:flex; flex-wrap:wrap; justify-content:space-between; margin-bottom:20px;">
        <div style="flex:1 1 300px; text-align:right; margin-bottom:10px;">
            <strong>Invoice Date:</strong> <?php echo $currentDate; ?>
        </div>
        <div style="flex:1 1 300px; text-align:left; margin-bottom:10px;">
            <strong>Invoice To:</strong> <?php echo htmlspecialchars($record['principal_name']); ?><br>
            <?php echo htmlspecialchars($record['broker_name']); ?><br>
            <?php echo htmlspecialchars($record['address'] . ' ' . $record['building_name']); ?><br>
            <?php echo htmlspecialchars($record['street_name']); ?><br>
            <?php echo htmlspecialchars($record['town']); ?><br>
            <?php echo htmlspecialchars($record['country'] . ' ' . $record['postcode']); ?>
        </div>
    </div>

    <!-- Invoice Table -->
    <table cellpadding="0" cellspacing="0" border="0" style="width:100%; border-collapse:collapse; margin-bottom:20px;">
        <thead>
            <tr>
                <th style="border:1px solid #ddd; padding:8px; background-color:#f2f2f2; text-align:left; width:70%;">Description</th>
                <th style="border:1px solid #ddd; padding:8px; background-color:#f2f2f2; text-align:center; width:15%;">Qty</th>
                <th style="border:1px solid #ddd; padding:8px; background-color:#f2f2f2; text-align:right; width:15%;">Cost US$</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="border:1px solid #ddd; padding:8px;">
                    Permit as an Insurance <?php echo htmlspecialchars($record['permit_type']); ?> requested on <?php echo $createdDate; ?>
                </td>
                <td style="border:1px solid #ddd; padding:8px; text-align:center;">One</td>
                <td style="border:1px solid #ddd; padding:8px; text-align:right;">$<?php echo number_format($record['cost'], 2); ?></td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="border:1px solid #ddd; padding:8px; text-align:right;"><strong>Total:</strong></td>
                <td style="border:1px solid #ddd; padding:8px; text-align:right;"><strong>$<?php echo number_format($record['cost'], 2); ?></strong></td>
            </tr>
        </tfoot>
    </table>

    <!-- Make Payment Button -->
    <div style="margin-top:15px;">
        <a href="https://yourdomain.com/payment.php?id=<?php echo urlencode($record['id']); ?>"
           style="display:inline-block; background-color:#4CAF50; color:white; padding:10px 20px; border-radius:4px; text-decoration:none; font-weight:bold;">
           Make Payment
        </a>
    </div>

    <!-- Footer -->
    <div style="margin-top:30px; font-size:10px; color:#555; text-align:center;">
        &copy; <?php echo date('Y'); ?> COMPANY Licensing Authority
    </div>
</div>
<!-- End HTML Invoice -->

<?php
$htmlInvoice = ob_get_clean();

// Send Email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;

    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress($record['email'], $record['principal_name']);
    $mail->addBCC(SMTP_BCC);

    // Embed logo
    $mail->addEmbeddedImage(__DIR__ . '/license.jpg', 'logo');

    $mail->isHTML(true);
    $mail->Subject = "Invoice - {$record['permit_type']} Permit - COMPANY";
    $mail->Body = $htmlInvoice;

    $mail->send();

    // Update invoiced timestamp
    $stmtUpdate = $pdo->prepare("UPDATE registration SET invoiced = NOW() WHERE id = ?");
    $stmtUpdate->execute([$id]);

 // ✅ Set flash message
    $_SESSION['flash_msg'] = "Invoice emailed and database updated successfully.";

    // Redirect to dashboard
    header("Location: admin_dashboard.php");
    exit();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $mail->ErrorInfo]);
}

