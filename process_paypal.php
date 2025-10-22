<?php
// process_paypal.php
require_once __DIR__ . "/config.php";

header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);

if (!$input || empty($input['id']) || empty($input['orderId']) || empty($input['amount'])) {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$invoiceId = (int)$input['id'];
$orderId   = $input['orderId'];
$amount    = (float)$input['amount'];

// ✅ Update DB
try {
// ✅ Update registration
$stmt = $pdo->prepare("UPDATE registration 
                       SET payment_received = NOW() 
                       WHERE id = :id");
$stmt->execute(['id' => $invoiceId]);

// ✅ Insert into transactions
$stmtLog = $pdo->prepare("INSERT INTO transactions 
    (registration_id, provider, transaction_id, amount, currency, status) 
    VALUES (:reg_id, 'paypal', :txid, :amount, :currency, 'COMPLETED')");
$stmtLog->execute([
    'reg_id'   => $invoiceId,
    'txid'     => $orderId,
    'amount'   => $amount,
    'currency' => 'USD' // adjust if you support multi-currency in PayPal
]);

    echo json_encode(["success" => true, "message" => "Payment successful via PayPal!"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "DB error: " . $e->getMessage()]);
}

