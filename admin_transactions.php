<?php
require_once "config.php";
require_once "header.php";

// Export to CSV if requested
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=transactions_' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');
    // Column headers
    fputcsv($output, ['ID','Principal','Email','Broker','Provider','Transaction ID','Amount','Currency','Status','Date']);

    $stmt = $pdo->query("SELECT t.id, r.principal_name, r.email, r.broker_name,
                                t.provider, t.transaction_id, t.amount, t.currency,
                                t.status, t.created_at
                         FROM transactions t
                         JOIN registration r ON t.registration_id = r.id
                         ORDER BY t.created_at DESC");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}

// Normal page view
$sql = "SELECT t.id, t.registration_id, t.provider, t.transaction_id, 
               t.amount, t.currency, t.status, t.created_at,
               r.principal_name, r.email, r.broker_name
        FROM transactions t
        JOIN registration r ON t.registration_id = r.id
        ORDER BY t.created_at DESC";

$stmt = $pdo->query($sql);
$transactions = $stmt->fetchAll();
?>

<div class="container">
  <h2 class="page-title">💳 Transactions Log</h2>
  <p>Below is a record of all Stripe & PayPal transactions.</p>

  <div class="actions" style="margin-bottom:15px;">
    <a href="admin_dashboard.php" class="btn">⬅ Back to Dashboard</a>
    <a href="admin_transactions.php?export=csv" class="btn" style="background:#007bff;color:#fff;">⬇ Export CSV</a>
  </div>

  <?php if (count($transactions) > 0): ?>
    <table class="styled-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Principal</th>
          <th>Email</th>
          <th>Broker</th>
          <th>Provider</th>
          <th>Transaction ID</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($transactions as $t): ?>
          <tr>
            <td><?php echo htmlspecialchars($t['id']); ?></td>
            <td><?php echo htmlspecialchars($t['principal_name']); ?></td>
            <td><?php echo htmlspecialchars($t['email']); ?></td>
            <td><?php echo htmlspecialchars($t['broker_name']); ?></td>
            <td>
              <?php if ($t['provider'] === 'stripe'): ?>
                <span style="color:#635BFF;font-weight:bold;">Stripe</span>
              <?php else: ?>
                <span style="color:#003087;font-weight:bold;">PayPal</span>
              <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($t['transaction_id']); ?></td>
            <td><?php echo htmlspecialchars($t['currency']).' '.number_format($t['amount'], 2); ?></td>
            <td>
              <?php if ($t['status'] === 'succeeded' || $t['status'] === 'COMPLETED'): ?>
                <span style="color:green;font-weight:bold;"><?php echo htmlspecialchars($t['status']); ?></span>
              <?php else: ?>
                <span style="color:red;"><?php echo htmlspecialchars($t['status']); ?></span>
              <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($t['created_at']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p class="msg">No transactions found yet.</p>
  <?php endif; ?>
</div>

<?php require_once "footer.php"; ?>

