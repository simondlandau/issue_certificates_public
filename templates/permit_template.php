<?php
require_once __DIR__ . "/../config.php";
require __DIR__ . "/../PHPMailer/src/PHPMailer.php";
require __DIR__ . "/../PHPMailer/src/SMTP.php";
require __DIR__ . "/../PHPMailer/src/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Generate Permit PDF and save to /permits/
 */
function generatePermitPDF($record) {
    require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';

    // Create TCPDF instance
    $pdf = new TCPDF('L', PDF_UNIT, 'A5', true, 'UTF-8', false);
    $pdf->SetCreator('COMPANY');
    $pdf->SetAuthor('COMPANY Licensing Authority');
    $pdf->SetTitle("Permit - {$record['principal_name']}");
    $pdf->SetMargins(5, 5, 5);
    $pdf->SetAutoPageBreak(true, 10); // automatic page breaks with 10mm margin
    $pdf->AddPage();
    $pdf->SetFont('dejavusans', '', 10);

    // Load HTML template
    $templatePath = __DIR__ . '/all_templates.html';
    $html = file_get_contents($templatePath);

    // Replace logo path
    $html = str_replace('{{LOGO_PATH}}', realpath(__DIR__ . '/../license.jpg'), $html);

    // Prepare placeholders
    $placeholders = [
        'principal_name' => htmlspecialchars($record['principal_name']),
        'broker_name'    => htmlspecialchars($record['broker_name']),
        'permit_type'    => htmlspecialchars($record['permit_type']),
        'address1'       => htmlspecialchars(trim($record['address'] . ' ' . $record['building_name'])),
        'address2'       => htmlspecialchars($record['street_name']),
        'address3'       => htmlspecialchars($record['town']),
        'address4'       => htmlspecialchars(trim($record['country'] . ' ' . $record['postcode'])),
        'grant_date'     => date('Y-m-d')
    ];

    // Replace placeholders using regex
    foreach ($placeholders as $key => $value) {
        $html = preg_replace(
            '/<span\s+data-placeholder="'.preg_quote($key, '/').'">.*?<\/span>/s',
            $value,
            $html
        );
    }

    // Remove unsupported tags
    $html = preg_replace('/<link[^>]+>/', '', $html);
    $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);

    // Ensure permits directory exists
    $permitsDir = __DIR__ . '/../permits/';
    if (!is_dir($permitsDir)) mkdir($permitsDir, 0755, true);

    // Output file path
    $pdfFile = $permitsDir . "permit_{$record['id']}.pdf";

    // Write HTML to PDF
    $pdf->writeHTML($html, true, false, true, false, '');

    // Save PDF to file
    $pdf->Output($pdfFile, 'F');

    return $pdfFile;
}

/**
 * Send Permit Email with attached generated PDF
 */
function sendPermitEmail($record) {
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

        // Attach the already-generated PDF
        $pdfFile = generatePermitPDF($record);
        $mail->addAttachment($pdfFile);

        // Email body
        $mail->isHTML(true);
        $mail->Subject = "Your {$record['permit_type']} Permit - SAMIBLA";
        $mail->Body = '
        <div style="text-align:center; color:blue; font-weight:bold; font-style:italic;">
            Southern Africa Medical Intermediary and Broker Licensing Authority
            <br>
            <img src="cid:logo" style="height:50px; margin-top:5px;" />
        </div>
        <p>Dear ' . htmlspecialchars($record['principal_name']) . ', representing ' . htmlspecialchars($record['broker_name']) . ',</p>
        <p>Your request for a ' . htmlspecialchars($record['permit_type']) . ' Permit has been processed. Please find your permit attached.</p>
        <p>Regards,<br>COMPANY Licensing Authority</p>
        ';

        // Embedded logo for email
        $mail->addEmbeddedImage(__DIR__ . '/../license.jpg', 'logo');

        $mail->send();
    } catch (Exception $e) {
        throw new Exception("Email could not be sent: " . $mail->ErrorInfo);
    }
}

