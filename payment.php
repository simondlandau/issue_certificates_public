<?php
require_once "config.php";
$id = (int)($_GET['id'] ?? 0);

// fetch cost & principal name for invoice
require_once "config.php";
$stmt = $pdo->prepare("SELECT principal_name, cost FROM registration WHERE id = ?");
$stmt->execute([$id]);
$record = $stmt->fetch();
if (!$record) die("Invalid registration ID.");

$amount = $record['cost'];
$principal = htmlspecialchars($record['principal_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Payment - COMPANY</title>
  <link rel="stylesheet" href="styles.css" />
  <script src="https://js.stripe.com/v3/"></script>
  <script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=USD"></script>
</head>
<body>
<div class="page">
  <header class="header">
    <h1>Payment Portal</h1>
  </header>

  <main class="content">
    <h2>Hello <?php echo $principal; ?>,</h2>
    <p>Please pay <strong>$<?php echo number_format($amount,2); ?></strong> to receive your permit.</p>

    <div class="payment-options">
      <button id="choose-stripe">💳 Pay with Stripe</button>
      <button id="choose-paypal">🅿️ Pay with PayPal</button>
    </div>

    <!-- Stripe Form -->
    <div id="stripe-container" style="display:none; margin-top:20px;">
      <form id="stripe-form">
        <div id="card-element"></div>
        <button id="stripe-submit">Pay $<?php echo number_format($amount,2); ?> with Stripe</button>
      </form>
      <div id="stripe-message"></div>
    </div>

    <!-- PayPal Button -->
    <div id="paypal-button-container" style="display:none; margin-top:20px;"></div>

  </main>
</div>

<script>
document.getElementById("choose-stripe").addEventListener("click", () => {
  document.getElementById("stripe-container").style.display = "block";
  document.getElementById("paypal-button-container").style.display = "none";
});
document.getElementById("choose-paypal").addEventListener("click", () => {
  document.getElementById("paypal-button-container").style.display = "block";
  document.getElementById("stripe-container").style.display = "none";
});

// Container to show success
const paymentMessage = document.createElement('div');
paymentMessage.id = 'payment-success';
paymentMessage.style.marginTop = '20px';
paymentMessage.style.fontWeight = 'bold';
paymentMessage.style.color = 'green';
document.querySelector('main.content').appendChild(paymentMessage);

function showSuccess(msg) {
    paymentMessage.innerHTML = msg + '<br><button id="return-email">Return to Email</button>';
    document.getElementById('return-email').addEventListener('click', () => {
        // Try to close the window
        window.close();
        // If window.close() is blocked, show a message
        alert("You may now exit this page manually.");
    });
}

// ===== Stripe =====
document.getElementById("stripe-form").addEventListener("submit", async (e) => {
  e.preventDefault();
  const { paymentMethod, error } = await stripe.createPaymentMethod({
    type: "card",
    card: cardElement,
  });
  const stripeMsg = document.getElementById("stripe-message");

  if (error) {
    stripeMsg.textContent = error.message;
  } else {
    const response = await fetch("process_payment.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        provider: "stripe",
        amount: <?php echo $amount * 100; ?>, // cents
        currency: "usd",
        payment_method: paymentMethod.id,
        id: <?php echo $id; ?>
      }),
    });
    const result = await response.json();
    if (result.success) {
        showSuccess("Payment successful! You may now exit this page.");
        stripeMsg.style.display = 'none';
    } else {
        stripeMsg.textContent = result.message;
    }
  }
});

// ===== PayPal =====
paypal.Buttons({
  createOrder: function(data, actions) {
    return actions.order.create({
      purchase_units: [{
        amount: {
          value: "<?php echo number_format($amount,2,'.',''); ?>"
        },
        description: "Permit payment for <?php echo $principal; ?>"
      }]
    });
  },
  onApprove: function(data, actions) {
    return actions.order.capture().then(async function(details) {
      const response = await fetch("process_payment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          provider: "paypal",
          order_id: data.orderID,
          id: <?php echo $id; ?>
        }),
      });
      const result = await response.json();
      if (result.success) {
          showSuccess("Payment successful! You may now exit this page.");
          document.getElementById("paypal-button-container").style.display = 'none';
      } else {
          paymentMessage.style.color = 'red';
          paymentMessage.textContent = "Payment failed: " + result.message;
      }
    });
  }
}).render('#paypal-button-container');
</script>
</body>
</html>

