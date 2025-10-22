<?php
 session_start();
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL); 

// Replace these with your actual admin credentials
$ADMIN_USERNAME = "admin";
$ADMIN_PASSWORD = "samibla";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username === $ADMIN_USERNAME && $password === $ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin_pathway.php");
        exit();
    } else {
        $errors[] = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - SAMIBLA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-gray-100">

<div class="page">
<!--   <?php include "header.php"; ?> -->

    <main class="content flex justify-center items-center min-h-[70vh]">
        <div class="bg-white shadow-2xl rounded-2xl p-8 w-full max-w-md text-center">
            
            <!-- Logo -->
            <img src="license.jpg" alt="SAMIBLA Logo" class="mx-auto mb-4 h-20">

            <h2 class="text-2xl font-bold mb-6 text-blue-700">Admin Login</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert error mb-4 p-3 rounded border border-red-500 bg-red-100 text-red-800 relative">
                    <ul class="list-disc list-inside">
                        <?php foreach($errors as $err) echo "<li>$err</li>"; ?>
                    </ul>
                    <span class="close-btn absolute top-1 right-2 cursor-pointer font-bold">&times;</span>
                </div>
            <?php endif; ?>

            <form method="post" class="space-y-4">
                <div>
                    <label class="block font-semibold mb-1">Username*</label>
                    <input type="text" name="username" required
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block font-semibold mb-1">Password*</label>
                    <input type="password" name="password" required
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">
                    Login
                </button>
            </form>
        </div>
    </main>
</div>

<script>
    // Close alert messages
    document.querySelectorAll('.alert .close-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.parentElement.style.display = 'none';
        });
    });
</script>
</body>
</html>

