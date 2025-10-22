// payment.js

// STRIPE
const stripe = Stripe("pk_test_xxxxxxxxxxxxxxxxxxxxxxxxx"); // replace with your key
const elements = stripe.elements();
const cardElement = elements.create("card");
cardElement.mount("#card-element");

const form = document.getElementById("payment-form");
if (form) {
  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const { paymentMethod, error } = await stripe.createPaymentMethod({
      type: "card",
      card: cardElement,
      billing_details: { name: principal },
    });

    if (error) {
      document.getElementById("payment-message").textContent = error.message;
    } else {
      const response = await fetch("process_payment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          id: invoiceId,
          amount: amount,
          currency: "usd",
          payment_method: paymentMethod.id,
        }),
      });

      const result = await response.json();
      document.getElementById("payment-message").textContent = result.message;
    }
  });
}

// PAYPAL
if (document.getElementById("paypal-button-container")) {
  paypal.Buttons({
    createOrder: (data, actions) => {
      return actions.order.create({
        purchase_units: [{
          amount: { value: amount },
          description: `Payment for Permit - ${principal}`
        }]
      });
    },
    onApprove: async (data, actions) => {
      const order = await actions.order.capture();

      // Send confirmation to server (optional)
      await fetch("process_paypal.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          id: invoiceId,
          amount: amount,
          orderId: order.id
        }),
      });

      document.getElementById("payment-message").textContent =
        "Payment successful via PayPal!";
    },
    onError: (err) => {
      document.getElementById("payment-message").textContent = "PayPal error: " + err;
    }
  }).render("#paypal-button-container");
}

// GENERAL UI
document.getElementById("year").textContent = new Date().getFullYear();

function showStripe() {
  document.getElementById("stripe-form").style.display = "block";
  document.getElementById("paypal-form").style.display = "none";
  document.getElementById("payment-choice").style.display = "none";
}

function showPayPal() {
  document.getElementById("paypal-form").style.display = "block";
  document.getElementById("stripe-form").style.display = "none";
  document.getElementById("payment-choice").style.display = "none";
}

