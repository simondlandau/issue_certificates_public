<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ✅ Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// ✅ Handle logout request
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

// ✅ Database connection
require_once __DIR__ . "/config.php";

$search = trim($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? 'created_at';
$order = strtoupper($_GET['order'] ?? 'DESC');
$order = ($order === 'ASC') ? 'ASC' : 'DESC'; // sanitize

$recordsPerPage = 20;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $recordsPerPage;

try {
 //   $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // ✅ Handle CSV export
    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        $stmtExport = $pdo->prepare("SELECT * FROM registration WHERE principal_name LIKE :search OR email LIKE :search OR broker_name LIKE :search ORDER BY $sort $order");
        $stmtExport->execute(['search' => "%$search%"]);
        $exportRecords = $stmtExport->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="company_records.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, array_keys($exportRecords[0] ?? [])); // header row
        foreach ($exportRecords as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    // ✅ Count total records for pagination
    $countQuery = "SELECT COUNT(*) FROM registration WHERE principal_name LIKE :search OR email LIKE :search OR broker_name LIKE :search";
    $stmtCount = $pdo->prepare($countQuery);
    $stmtCount->execute(['search' => "%$search%"]);
    $totalRecords = $stmtCount->fetchColumn();
    $totalPages = ceil($totalRecords / $recordsPerPage);

    // ✅ Fetch records with search, sort, and pagination
    $query = "SELECT * FROM registration 
              WHERE principal_name LIKE :search OR email LIKE :search OR broker_name LIKE :search
              ORDER BY $sort $order
              LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    $stmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// ✅ Toggle sorting order
function toggleOrder($currentOrder) {
    return ($currentOrder === 'ASC') ? 'DESC' : 'ASC';
}

// ✅ Highlight search matches
function highlight($text, $search) {
    if (!$search) return htmlspecialchars($text);
    return preg_replace('/('.preg_quote($search,'/').')/i','<mark>$1</mark>',htmlspecialchars($text));
}
?>
<?php if (!empty($flashMsg)): ?>
    <div id="flashMsg" class="alert-success"><?php echo htmlspecialchars($flashMsg); ?></div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Administrator Dashboard - COMPANY</title>
<link rel="stylesheet" href="styles.css">
<style>
  table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
  th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
  th { background-color: #f44336; color: white; cursor: pointer; }
  tr:nth-child(even) {background-color: #f2f2f2;}
  .create-btn { background-color: #4CAF50; color: white; border: none; padding: 6px 12px; cursor: pointer; border-radius: 4px; text-decoration: none; }
  .create-btn:hover { background-color: #45a049; }
  .pagination { margin-top: 1rem; }
  .pagination a { margin: 0 5px; text-decoration: none; padding: 5px 10px; border: 1px solid #ccc; border-radius: 4px; }
  .pagination a.active { background-color: #f44336; color: white; }
  .search-box { margin-top: 1rem; display:flex; gap:0.5rem; align-items:center; }
  .export-btn { background-color:#2196F3; color:white; border:none; padding:5px 10px; border-radius:4px; text-decoration:none; }
  .export-btn:hover { background-color:#1976D2; }
  mark { background-color: yellow; font-weight:bold; }
    .doc-btn { background-color:#2196F3; color:white; border:none; padding:6px 12px; border-radius:4px; text-decoration:none; margin-right:5px; }
  .doc-btn.disabled { background-color:#ccc; cursor:not-allowed; }
</style>
</head>
<body>
<div class="page">
  <!-- Header -->
  <header class="header">
    <div class="brand">
      <h1 class="title">Southern Africa Medical Intermediary and Broker Licensing Authority.</h1>
      <p class="subtitle">Administrator Invoice & Issue</p>
    </div>
    <div class="logo-slot">
      <img src="license.jpg" alt="Organization Logo" class="logo-img">
    </div>
  </header>

  <section class="red-divider">
    <img src="sectionsm.ico" alt="Section Icon" class="red-divider-icon">
    <span class="red-divider-title">All Registration Records<form method="post" style="display:inline;">
    <button type="submit" name="logout">Logout</button>
</form>
      <a href="admin_view.php" class="doc-btn">View</a>
</span>
  </section>

  <main class="content">
    <!-- Search & Export -->
    <div class="search-box">
      <form method="get" action="" style="flex-grow:1; display:flex; gap:0.5rem;">
        <input type="text" name="search" placeholder="Search by name, email, broker" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
      </form>
      <a class="export-btn" href="?search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&export=csv">Download CSV</a>
    </div>

    <?php if (empty($records)): ?>
        <p>No records found.</p>
    <?php else: ?>
<table>
    <thead>
        <tr>
            <?php 
            $columns = [
                'id'=>'ID',
                'principal_name'=>'Principal Name',
                'email'=>'Email',
                'broker_name'=>'Broker Name',
                'permit_type'=>'Permit Type',
                'contact_number'=>'Contact Number',
                'created_at'=>'Date Requested'
            ];
            foreach($columns as $col => $label):
                $newOrder = ($sort === $col) ? toggleOrder($order) : 'ASC';
                $sortLink = "?search=".urlencode($search)."&sort=$col&order=$newOrder";
            ?>
                <th><a href="<?php echo $sortLink; ?>"><?php echo $label; ?></a></th>
            <?php endforeach; ?>
            <th>Actions<br><small>(Permit / Invoice)</small></th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($records as $row): ?>
        <tr>
            <?php foreach(array_keys($columns) as $col): ?>
                <td><?php echo highlight($row[$col], $search); ?></td>
            <?php endforeach; ?>
<td>
    <?php
$isCreated  = !is_null($row['permit_created']);
$isPaid     = !is_null($row['payment_received']);
$isInvoiced = !is_null($row['invoiced']);
    
// Permit button class
$permitClass = (!$isCreated && $isPaid) ? 'create-btn' : 'create-btn disabled';

// Invoice button state
if (!$isInvoiced && !$isPaid) {
    // Invoice not yet generated and not paid
    $invoiceClass = 'create-btn btn-invoice'; 
    $invoiceLabel = 'Invoice';
    $invoiceHref  = 'invoice.php?id=' . urlencode($row['id']);
} elseif ($isInvoiced && !$isPaid) {
    // Invoice exists, not paid
    $invoiceClass = 'create-btn btn-invoiced disabled';
    $invoiceLabel = 'Invoiced';
    $invoiceHref  = '#';
} elseif ($isInvoiced && $isPaid) {
    // Invoice exists and payment received
    $invoiceClass = 'create-btn btn-paid disabled';
    $invoiceLabel = 'Paid';
    $invoiceHref  = '#';
} else {
    // Fallback
    $invoiceClass = 'create-btn disabled';
    $invoiceLabel = 'Disabled';
    $invoiceHref  = '#';
}
?>
<div class="action-buttons">

    <!-- Invoice Button -->
    <a href="<?php echo $invoiceHref; ?>" class="<?php echo $invoiceClass; ?>">
       <?php echo $invoiceLabel; ?>
    </a>

    <!-- Permit Button -->
    <a href="create.php?id=<?php echo urlencode($row['id']); ?>"
       class="<?php echo $permitClass; ?>"
       title="Permit Type: <?php echo htmlspecialchars($row['permit_type']); ?>">
       <?php echo (!$isCreated && $isPaid) ? 'Create Permit' : 'Permit Disabled'; ?>
    </a>

</div></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

        <!-- Pagination -->
        <div class="pagination">
            <?php for($i=1;$i<=$totalPages;$i++): ?>
                <a href="?search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&page=<?php echo $i; ?>" class="<?php echo ($i==$page)?'active':''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
  </main>

  <!-- Footer -->
  <footer class="footer">
    <button class="to-top" aria-label="Back to top" title="Back to top">▲</button>
    <span class="copyright">© Southern Africa Medical Intermediary &amp; Broker Licensing Authority - <span id="year"></span></span>
  </footer>
</div>

<script>
  document.getElementById('year').textContent = new Date().getFullYear();
  document.querySelector('.to-top').addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
</script>
<script>
  // Fade out flash message after 5 seconds
  const flash = document.getElementById('flashMsg');
  if (flash) {
      setTimeout(() => {
          flash.style.transition = "opacity 1s ease";
          flash.style.opacity = 0;
          setTimeout(() => flash.remove(), 1000); // remove from DOM after fade
      }, 3000); // 3 seconds
  }
</script>
</body>
</html>

