<?php
require_once "config.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if ($data['provider'] === 'stripe') {
    require 'vendor/autoload.php';
    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

    try {
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'payment_method' => $data['payment_method'],
            'confirm' => true,
        ]);

        // update DB
        $stmt = $pdo->prepare("UPDATE registration SET payment_received = NOW() WHERE id = ?");
        $stmt->execute([$data['id']]);

        echo json_encode(['success' => true, 'message' => "Stripe payment successful."]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

} elseif ($data['provider'] === 'paypal') {
    // Exchange credentials for token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PAYPAL_BASE_URL . "/v1/oauth2/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ":" . PAYPAL_SECRET);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    $result = curl_exec($ch);
    $token = json_decode($result, true)['access_token'];
    curl_close($ch);

    // Verify order
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PAYPAL_BASE_URL . "/v2/checkout/orders/" . $data['order_id']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    $orderDetails = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if ($orderDetails['status'] === 'COMPLETED') {
        $stmt = $pdo->prepare("UPDATE registration SET payment_received = NOW() WHERE id = ?");
        $stmt->execute([$data['id']]);

        echo json_encode(['success' => true, 'message' => "PayPal payment successful."]);
    } else {
        echo json_encode(['success' => false, 'message' => "PayPal payment not completed."]);
    }
} else {
    echo json_encode(['success' => false, 'message' => "Invalid payment provider."]);
}

