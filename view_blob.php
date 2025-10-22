<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/config.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$field = $_GET['field'] ?? '';
$download = isset($_GET['download']) ? (bool)$_GET['download'] : false;

$validFields = ['identification', 'accounts', 'financials'];
if ($id <= 0 || !in_array($field, $validFields)) {
    die("Invalid request.");
}

try {
    $stmt = $pdo->prepare("SELECT $field FROM registration WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $data = $stmt->fetchColumn();

    if (!$data) {
        die("No document available.");
    }

    // Detect MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->buffer($data);

    if (!$mimeType || $mimeType === 'application/octet-stream') {
        $mimeType = 'application/pdf';
    }

    // Headers
    header("Content-Type: $mimeType");
    header("Content-Length: " . strlen($data));

    if ($download) {
        header("Content-Disposition: attachment; filename=\"document_$id.$field\"");
    } else {
        if (in_array($mimeType, ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'])) {
            header("Content-Disposition: inline; filename=\"document_$id.$field\"");
        } else {
            header("Content-Disposition: attachment; filename=\"document_$id.$field\"");
        }
    }

    echo $data;
    exit;

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

