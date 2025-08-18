const qrButton = document.getElementById("dynamic-qr");

qrButton.addEventListener("click", function () {
  // Call your backend to initiate eSewa payment
  $.post(
    "/initial-esewa-payment",
    {
      amount: netTotal, // Make sure netTotal is defined in the scope
      orderId: orderId, // Same here
    },
    function (response) {
      if (response.success && response.paymentUrl) {
        // Load digitalpayment.js if not already loaded
        if (!window.showDynamicQR) {
          const script = document.createElement("script");
          script.src = "digitalpayment.js";
          script.onload = function () {
            callShowDynamicQR(response.paymentUrl);
          };
          document.head.appendChild(script);
        } else {
          callShowDynamicQR(response.paymentUrl);
        }
      } else {
        alert("Payment initiation failed.");
      }
    }
  );
});

function callShowDynamicQR(paymentUrl) {
  const paymentData = {
    url: paymentUrl, // URL returned from the server
  };

  showDynamicQR(paymentData); // This should be defined inside digitalpayment.js
}
