<?php
// header.php
session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>COMPANY Invoice</title>
<link rel="stylesheet" href="styles.css">
<style>
/* Optional: small inline styles for header if needed */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    border-bottom: 2px solid #f44336;
}
.header .brand h1 {
    margin: 0;
    font-size: 1.2rem;
}
.header .brand p {
    margin: 0;
    font-size: 0.9rem;
    color: #555;
}
.header .logo-slot img {
    height: 50px;
}
</style> 
</head>
<body>
<div class="page">
<header class="header">
    <div class="brand">
        <h1>COMPANY Licensing Authority</h1>
        <p>Invoice</p>
    </div>
    <div class="logo-slot">
        <img src="license.jpg" alt="Organization Logo">
    </div>
</header>

