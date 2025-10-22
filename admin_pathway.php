<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ✅ Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$adminUser = $_SESSION['admin_username'] ?? 'system';

// ✅ Handle logout request
if (isset($_POST['logout'])) {sam
    session_unset();
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

require_once __DIR__ . "/config.php";

// ✅ Fetch current cost value
$stmt = $pdo->query("SELECT cost FROM set_company WHERE id = 1 LIMIT 1");
$currentCost = $stmt->fetchColumn();

// ✅ Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cost'])) {
    $newCost = filter_input(INPUT_POST, 'cost', FILTER_VALIDATE_FLOAT);

    if ($newCost !== false && $newCost >= 50 && $newCost <= 500) {
        $updateStmt = $pdo->prepare("
            UPDATE set_company 
            SET cost = :cost, update_by = :update_by, update_when = NOW() 
            WHERE id = 1
        ");
        $updateStmt->execute([
            ':cost' => $newCost,
            ':update_by' => $adminUser
        ]);

        $message = "✔ Permit cost updated to US$ " . number_format($newCost, 2) . 
                   " by " . htmlspecialchars($adminUser);
        $currentCost = $newCost;
    } else {
        $message = "❌ Please enter a valid cost between 50.00 and 500.00";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Administrator Pathway - COMPANY</title>
<link rel="stylesheet" href="styles.css">
<style>
  .form-box { margin: 1rem 0; }
  label { font-weight: bold; display:block; margin-bottom: 0.5rem; }
  input[type=number] { padding: 6px; width: 120px; }
.btn {
  display: inline-block;
  padding: 6px 12px;
  margin-left: 8px;
  border: none;
  border-radius: 4px;
  color: white;
  text-decoration: none;
  cursor: pointer;
  font-size: 14px;
}

/* Save / Update (default green) */
.btn-save {
  background-color: #4CAF50;
}
.btn-save:hover {
  background-color: #45a049;
}

/* View (blue) */
.btn-view {
  background-color: #2196F3;
}
.btn-view:hover {
  background-color: #1976D2;
}

/* Permits (orange) */
.btn-permit {
  background-color: #FF9800;
}
.btn-permit:hover {
  background-color: #F57C00;
}
.action-row {
  display: flex;
  align-items: center;
  margin-top: 10px;
  gap: 10px;
}

.action-row span {
  font-size: 14px;
  color: #333;
}
  .msg { margin-top: 0.5rem; font-weight: bold; }
  .action-links { margin-top: 1rem; }
  .action-links p { margin: 0.5rem 0; }
</style>
</head>
<body>
<div class="page">
  <!-- Header -->
  <header class="header">
    <div class="brand">
      <h1 class="title">Southern Africa Medical Intermediary and Broker Licensing Authority.</h1>
      <p class="subtitle">Administrator Pathway</p>
    </div>
    <div class="logo-slot">
      <img src="license.jpg" alt="Organization Logo" class="logo-img">
    </div>
  </header>

  <section class="red-divider">
    <img src="sectionsm.ico" alt="Section Icon" class="red-divider-icon">
    <span class="red-divider-title">
      Select Action Path
      <form method="post" style="display:inline;">
        <button type="submit" name="logout">Logout</button>
      </form>
    </span>
  </section>

  <main class="content">
    <!-- Editable Cost Form -->
   <!-- Editable Cost Form -->
    <div class="form-box">
      <form method="post">
        <label for="cost">Enter the cost in US$ for a Permit</label>
        <input type="number" step="0.01" min="50" max="500" name="cost" id="cost" 
               value="<?php echo htmlspecialchars(number_format($currentCost, 2)); ?>" required>
        <button type="submit" name="update_cost" class="btn">Save / Update</button>
      </form>
      <?php if ($message): ?>
        <div class="msg"><?php echo htmlspecialchars($message); ?></div>
      <?php endif; ?>
    </div>

<!-- View button -->
<div class="action-row">
  <a href="admin_view.php" class="btn btn-view">View</a>
  <span>View supporting documentation for Registered Clients</span>
</div>
<!-- Permits button -->
<div class="action-row">
  <a href="admin_dashboard.php" class="btn btn-permit">Permits</a>
  <span>Create Invoices & Permits for Registered Clients</span>
</div>
<!-- Payments Recvd button -->
<div class="action-row"> 
  <a href="admin_transactions.php" class="btn">💳 View </a>
  <span>View Stripe/PayPal Receipts</span> 
</div>
  </main>

  <!-- Footer -->
  <footer class="footer">
    <button class="to-top" aria-label="Back to top" title="Back to top">▲</button>
    <span class="copyright">© Southern Africa Medical Intermediary &amp; Broker Licensing Authority - 
      <span id="year"></span>
    </span>
  </footer>
</div>

<script>
  document.getElementById('year').textContent = new Date().getFullYear();
  document.querySelector('.to-top').addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
</script>
</body>
</html>

