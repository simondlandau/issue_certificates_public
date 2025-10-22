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
$order = ($order === 'ASC') ? 'ASC' : 'DESC';

$recordsPerPage = 20;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $recordsPerPage;

try {
    // ✅ Count total records
    $countQuery = "SELECT COUNT(*) FROM registration 
                   WHERE principal_name LIKE :search 
                      OR email LIKE :search 
                      OR broker_name LIKE :search";
    $stmtCount = $pdo->prepare($countQuery);
    $stmtCount->execute(['search' => "%$search%"]);
    $totalRecords = $stmtCount->fetchColumn();
    $totalPages = ceil($totalRecords / $recordsPerPage);

    // ✅ Fetch records
    $query = "SELECT * FROM registration 
              WHERE principal_name LIKE :search 
                 OR email LIKE :search 
                 OR broker_name LIKE :search
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

// ✅ Toggle sorting
function toggleOrder($currentOrder) {
    return ($currentOrder === 'ASC') ? 'DESC' : 'ASC';
}

// ✅ Highlight search
function highlight($text, $search) {
    if (!$search) return htmlspecialchars($text);
    return preg_replace('/(' . preg_quote($search, '/') . ')/i', '<mark>$1</mark>', htmlspecialchars($text));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Administrator Document View - COMPANY</title>
<link rel="stylesheet" href="styles.css">
<style>
  table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
  th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
  th { background-color: #f44336; color: white; cursor: pointer; }
  tr:nth-child(even) {background-color: #f2f2f2;}
  .doc-btn { background-color:#2196F3; color:white; border:none; padding:6px 12px; border-radius:4px; text-decoration:none; margin-right:5px; }
  .doc-btn.disabled { background-color:#ccc; cursor:not-allowed; }
.doc-group {
  display: flex;
  gap: 4px;
  margin-bottom: 6px;
}

.create-btn {
  background-color: #4CAF50;
  color: white;
  border: none;
  padding: 6px 10px;
  cursor: pointer;
  border-radius: 4px;
  text-decoration: none;
  font-size: 0.85rem;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.create-btn:hover { background-color: #45a049; }
.create-btn.disabled {
  background-color: #ccc;
  cursor: not-allowed;
}
</style>
</head>
<body>
<div class="page">
  <!-- Header -->
  <header class="header">
    <div class="brand">
      <h1 class="title">COMPANY Licensing Authority.</h1>
      <p class="subtitle">Administrator Document View</p>
    </div>
    <div class="logo-slot">
      <img src="license.jpg" alt="Organization Logo" class="logo-img">
    </div>
  </header>

  <section class="red-divider">
    <img src="sectionsm.ico" alt="Section Icon" class="red-divider-icon">
    <span class="red-divider-title">
      View Submitted Documents
      <form method="post" style="display:inline;">
        <button type="submit" name="logout">Logout</button>
      </form>
      <a href="admin_dashboard.php" class="doc-btn">Permits</a>
    </span>
  </section>

  <main class="content">
    <!-- Search -->
    <div class="search-box">
      <form method="get" action="" style="flex-grow:1; display:flex; gap:0.5rem;">
        <input type="text" name="search" placeholder="Search by name, email, broker" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
      </form>
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
            <th>Documents</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($records as $row): ?>
        <tr>
            <?php foreach(array_keys($columns) as $col): ?>
                <td><?php echo highlight($row[$col], $search); ?></td>
            <?php endforeach; ?>
<td>
  <!-- ID Document -->
  <div class="doc-group">
    <?php if (!empty($row['identification'])): ?>
      <a href="view_blob.php?id=<?php echo $row['id']; ?>&field=identification" 
         class="create-btn" title="View ID">👁 View</a>
      <a href="view_blob.php?id=<?php echo $row['id']; ?>&field=identification&download=1" 
         class="create-btn" title="Download ID">⬇ Download</a>
    <?php else: ?>
      <button class="create-btn disabled">ID N/A</button>
    <?php endif; ?>
  </div>

  <!-- Accounts Document -->
  <div class="doc-group">
    <?php if (!empty($row['accounts'])): ?>
      <a href="view_blob.php?id=<?php echo $row['id']; ?>&field=accounts" 
         class="create-btn" title="View Accounts">👁 View</a>
      <a href="view_blob.php?id=<?php echo $row['id']; ?>&field=accounts&download=1" 
         class="create-btn" title="Download Accounts">⬇ Download</a>
    <?php else: ?>
      <button class="create-btn disabled">A/C's N/A</button>
    <?php endif; ?>
  </div>

  <!-- Financials Document -->
  <div class="doc-group">
    <?php if (!empty($row['financials'])): ?>
      <a href="view_blob.php?id=<?php echo $row['id']; ?>&field=financials" 
         class="create-btn" title="View Financials">👁 View</a>
      <a href="view_blob.php?id=<?php echo $row['id']; ?>&field=financials&download=1" 
         class="create-btn" title="Download Financials">⬇ Download</a>
    <?php else: ?>
      <button class="create-btn disabled">Finance N/A</button>
    <?php endif; ?>
  </div>
</td>
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
    <span class="copyright">© COMPANY Licensing Authority - <span id="year"></span></span>
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

