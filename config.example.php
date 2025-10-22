<?php
// config.php — Centralized DB + Email settings

// Debug mode toggle
define('DEBUG_MODE', false); // set to false in production

// Database
define('DB_HOST', '');
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

// SMTP / Email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '@gmail.com');       // Gmail address
define('SMTP_PASS', '');       // Google App Password
define('SMTP_FROM_EMAIL', '@gmail.com');
define('SMTP_FROM_NAME', 'SAMIBLA');
define('SMTP_BCC', '@gmail.com');


// Stripe
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_xxxxxxxxxxxxxxxxxx'); 
define('STRIPE_SECRET_KEY', 'sk_test_xxxxxxxxxxxxxxxxxx'); 

// PayPal Sandbox Credentials
define('PAYPAL_CLIENT_ID', '');
define('PAYPAL_SECRET', '');
define('PAYPAL_BASE_URL', 'https://api-m.sandbox.paypal.com'); // sandbox endpoint

