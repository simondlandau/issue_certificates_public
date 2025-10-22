<?php // index.php ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>COMPANY Licensing Authority</title>
  <link rel="icon" type="image/x-icon" href="license.ico">
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <div class="page">
    <header class="header">
      <div class="brand">
        <h1 class="title">COMPANY Licensing Authority.</h1>
        <p class="subtitle">We deal with your Licensing Challenges.</p>
      </div>
      <div class="logo-slot">
        <img src="license.jpg" alt="Organization Logo Placeholder" class="logo-img">
      </div>
    </header>

    <main class="content">
      <h2>Welcome</h2>
      <p>This is the demo home page for the COMPANY Licensing Authority website.</p>
      <p><a href="payment.php" class="pay-link">➡ Make a Payment</a></p>
    </main>

    <footer class="footer">
      <button class="to-top" aria-label="Back to top" title="Back to top">▲</button>
      <span class="copyright">© COMPANY Licensing Authority - 
        <span id="year"></span>
      </span>
    </footer>
  </div>

  <script>
    document.getElementById("year").textContent = new Date().getFullYear();
  </script>
</body>
</html>

