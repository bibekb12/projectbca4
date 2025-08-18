const qrButton = document.getElementById("dynamic-qr");
const paymentMethodSelect = document.getElementById("payment_method");
const completeSaleButton = document.getElementById("completeSale");

paymentMethodSelect.addEventListener("change", function () {
  if (this.value === "qrpay") {
    qrButton.style.display = "inline-block";
    completeSaleButton.style.display = "none";
  } else {
    qrButton.style.display = "none";
  }
});


if (qrButton) qrButton.addEventListener("click", function (event) {
  event.preventDefault(); // Prevent form submit since this button sits inside a form

  // Read amount from UI instead of relying on an undefined variable
  // NOTE: we extract from #netTotal which is updated by sale.js
  const netTotalEl = document.getElementById("netTotal");
  const amount = parseFloat(netTotalEl ? netTotalEl.textContent : "0") || 0;
  if (!amount || amount <= 0) {
    alert("Invalid amount for payment.");
    return;
  }

  // Generate a client-side temporary order id (pid). Ideally, create this on the server
  // after creating a pending sale record, then return it to the client.
  const orderId = "INV-" + Date.now();

  // Show modal early with a loading state so user sees feedback immediately
  openQrPaymentModal(null, orderId, amount, true);

  // Call backend to get eSewa payment URL
  $.ajax({
    url: "esewa/initial-esewa-payment.php", 
    method: "POST",
    data: { amount: amount, orderId: orderId },
    dataType: "json",
    success: function (response) {
      if (response && response.success && response.paymentUrl) {
        openQrPaymentModal(response.paymentUrl, orderId, amount);
      } else {
        alert(response && response.message ? response.message : "Payment initiation failed.");
      }
    },
    error: function () {
      alert("Failed to reach payment server. Check endpoint path and server logs.");
    },
  });
});

function renderEsewaQR(paymentUrl) {
  const container = document.getElementById("qr-container");
  if (!container) {
    window.open(paymentUrl, "_blank"); // Fallback: open eSewa page in a new tab
    return;
  }

  // Lazy-load a tiny QR library from CDN and render the QR
  if (!window.QRCode) {
    const script = document.createElement("script");
    script.src = "https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js";
    script.onload = function () {
      drawQR(container, paymentUrl);
    };
    document.head.appendChild(script);
  } else {
    drawQR(container, paymentUrl);
  }
}

function drawQR(container, paymentUrl) {
  container.innerHTML = "";
  const note = document.createElement("div");
  note.textContent = "Scan to pay with eSewa";
  note.style.marginBottom = "8px";
  container.appendChild(note);


  new QRCode(container, {
    text: paymentUrl,
    width: 180,
    height: 180,
  });

  // Also keep a normal link as a fallback
  const link = document.createElement("a");
  link.href = paymentUrl;
  link.target = "_blank";
  link.rel = "noopener";
  link.textContent = "Open eSewa checkout";
  link.style.display = "block";
  link.style.marginTop = "8px";
  container.appendChild(link);
}

// Open QR modal with QR and controls
function openQrPaymentModal(paymentUrl, orderId, amount, isLoading) {
  const modal = document.getElementById("qrPaymentModal");
  const closeBtn = document.getElementById("closeQrModal");
  // Prefer modal container; fall back to inline #qr-container if modal not present
  const qrContainer = document.getElementById("qrModalContainer") || document.getElementById("qr-container");
  const info = document.getElementById("qrPaymentInfo");
  const checkBtn = document.getElementById("checkPaymentBtn");
  const saveBtn = document.getElementById("saveAfterPaymentBtn");

  // Reset state
  if (info) {
    info.textContent = `Invoice: ${orderId} | Amount: Rs. ${amount.toFixed(2)}`;
  }
  if (qrContainer) {
    qrContainer.innerHTML = "";
    // Loading state
    if (isLoading) {
      const loading = document.createElement("div");
      loading.textContent = "Generating QR...";
      loading.style.padding = "12px 0";
      qrContainer.appendChild(loading);
      if (saveBtn) saveBtn.disabled = true; // keep disabled while loading
    } else if (paymentUrl) {
      // Render QR inside modal container
      const fallbackLink = document.createElement("a");
      fallbackLink.href = paymentUrl;
      fallbackLink.target = "_blank";
      fallbackLink.rel = "noopener";
      fallbackLink.textContent = "Open eSewa checkout";
      fallbackLink.style.display = "block";
      fallbackLink.style.marginTop = "10px";

      if (!window.QRCode) {
        const script = document.createElement("script");
        script.src = "https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js";
        script.onload = function () {
          qrContainer.innerHTML = "";
          // eslint-disable-next-line no-undef
          new QRCode(qrContainer, { text: paymentUrl, width: 220, height: 220 });
          qrContainer.appendChild(fallbackLink);
          if (saveBtn) saveBtn.disabled = false; // enable save after QR is ready
        };
        document.head.appendChild(script);
      } else {
        qrContainer.innerHTML = "";
        // eslint-disable-next-line no-undef
        new QRCode(qrContainer, { text: paymentUrl, width: 220, height: 220 });
        qrContainer.appendChild(fallbackLink);
        if (saveBtn) saveBtn.disabled = false; // enable save when QR rendered
      }
    }
  }

  // Wire close
  if (closeBtn && modal) {
    closeBtn.onclick = function () {
      modal.style.display = "none";
    };
  }

  // Check Payment: call server to verify if success.php recorded the payment
  if (checkBtn) {
    checkBtn.onclick = function () {
      const refInput = document.getElementById("refIdInput");
      const refId = refInput && refInput.value ? refInput.value.trim() : "";

      // If refId provided, verify directly with eSewa; else, check local status from callback
      if (refId) {
        $.ajax({
          url: "esewa/verify.php",
          method: "POST",
          data: { pid: orderId, amt: amount, rid: refId },
          dataType: "json",
          success: function (res) {
            if (res && res.success) {
              alert("Payment verified. You can now Save the sale.");
              if (saveBtn) saveBtn.disabled = false;
            } else {
              alert("Verification failed: " + (res && res.message ? res.message : ""));
            }
          },
          error: function () {
            alert("Verification request failed");
          },
        });
      } else {
        $.get(
          "esewa/status.php",
          { pid: orderId },
          function (res) {
            try {
              if (typeof res === "string") res = JSON.parse(res);
            } catch (e) {}
            if (res && res.status === "paid") {
              alert("Payment verified. You can now Save the sale.");
              if (saveBtn) saveBtn.disabled = false;
            } else {
              alert("Not paid yet. Please complete payment or paste Ref ID.");
            }
          }
        ).fail(function () {
          alert("Could not check payment status. Paste Ref ID and verify.");
        });
      }
    };
  }

  // Save: trigger existing Complete Sale flow
  if (saveBtn) {
    // Enable Save once QR is rendered (above). It stays disabled only during loading.
    saveBtn.onclick = function () {
      // Reuse the existing Complete Sale button handler
      const completeBtn = document.getElementById("completeSale");
      if (completeBtn) {
        // Set payment method to qrpay to store it on server
        const methodSel = document.getElementById("payment_method");
        if (methodSel) methodSel.value = "qrpay";
        completeBtn.click();
        modal.style.display = "none";
      }
    };
  }

  // Show modal
  if (modal) modal.style.display = "block";
}
