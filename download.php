<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("HTTP/1.1 403 Forbidden");
    exit("Access denied.");
}

if (!isset($_GET['file'])) die("No file specified.");

$filename = basename($_GET['file']);
$filepath = __DIR__ . "/permits/" . $filename;

if (!file_exists($filepath)) die("File not found.");

header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
flush();
readfile($filepath);
exit;

