<?php
session_start();

// ✅ Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// ✅ Check selected IDs
if (!isset($_POST['selected']) || !is_array($_POST['selected']) || empty($_POST['selected'])) {
    die("No records selected.");
}

$selectedIds = array_map('intval', $_POST['selected']); // sanitize

// ✅ Database connection
$dsn = "mysql:host=localhost;dbname=company;charset=utf8mb4";
$user = "company";
$pass = "company";

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // ✅ Filter out records where permit is already created
    $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
    $stmt = $pdo->prepare("SELECT id, permit_type FROM registration WHERE id IN ($placeholders) AND permit_created IS NULL");
    $stmt->execute($selectedIds);
    $pendingRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($pendingRecords)) {
        die("All selected records already have permits created.");
    }

    $successCount = 0;
    $failed = [];

    // ✅ Loop through pending records and process automatically
    foreach ($pendingRecords as $record) {
        $_GET['id'] = $record['id'];
        $_GET['bulk'] = 1;

        try {
            include 'create.php'; // create.php handles PDF/email generation and updates permit_created
            $successCount++;
        } catch (Exception $e) {
            $failed[] = $record['id'];
        }
    }

    // ✅ Summary
    echo "<!DOCTYPE html><html><head><title>Bulk Permit Creation</title></head><body>";
    echo "<h2>Bulk Permit Creation Summary</h2>";
    echo "<p>Successfully created permits for $successCount record(s).</p>";

    if (!empty($failed)) {
        echo "<p>Failed to create permits for IDs: " . implode(', ', $failed) . "</p>";
    }

    echo '<p><a href="admin_dashboard.php">Back to Dashboard</a></p>';
    echo "</body></html>";

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

