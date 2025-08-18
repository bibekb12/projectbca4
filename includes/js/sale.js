function updateItemPrice() {
  const selectedOption = $("#item_select").find("option:selected");
  const price = selectedOption.data("price");
  $("#price").val(price);
}

$(document).ready(function () {
  let billItems = [];

  // Close modal when clicking outside of it
  window.addEventListener("click", function (event) {
    const billViewModal = document.getElementById("billViewModal");
    const billOptionsModal = document.getElementById("billOptionsModal");
    if (event.target == billViewModal) {
      billViewModal.style.display = "none";
    } else if (event.target == billOptionsModal) {
      billOptionsModal.style.display = "none";
    }
  });

  // Close modal when clicking the close button
  document.querySelectorAll(".close-modal").forEach(function (closeButton) {
    closeButton.addEventListener("click", function () {
      document.getElementById("billViewModal").style.display = "none";
      document.getElementById("billOptionsModal").style.display = "none";
    });
  });

  // Handle item selection
  $("#item_select").on("change", function () {
    const selectedOption = $(this).find("option:selected");
    const price = selectedOption.data("price");
    const stock = selectedOption.data("stock");

    $("#price").val(price);
    $("#quantity").attr("max", stock).val("");
    $("#total").val("");
  });

  // Calculate total when quantity changes
  $("#quantity").on("input", function () {
    const quantity = parseInt($(this).val()) || 0;
    const price = parseFloat($("#price").val()) || 0;
    const total = quantity * price;
    $("#total").val(total.toFixed(2));
  });

  // Add item to bill
  $("#addItem").on("click", function () {
    const itemSelect = $("#item_select");
    const selectedOption = itemSelect.find("option:selected");

    if (!itemSelect.val()) {
      alert("Please select an item");
      return;
    }

    const quantity = parseInt($("#quantity").val());
    if (!quantity || quantity <= 0) {
      alert("Please enter a valid quantity");
      return;
    }

    const item = {
      id: parseInt(itemSelect.val()),
      name: selectedOption.text(),
      quantity: quantity,
      price: parseFloat($("#price").val()),
      total: parseFloat(
        $("#total").val() || quantity * parseFloat($("#price").val())
      ),
    };

    // Append item to the bill items table
    $("#billItems").append(`
            <tr>
                <td>${item.name}</td>
                <td>${item.quantity}</td>
                <td>Rs. ${item.price.toFixed(2)}</td>
                <td>Rs. ${item.total.toFixed(2)}</td>
                <td><button type="button" class="remove-item-btn">Remove</button></td>
            </tr>
        `);

    // Add item to billItems array
    billItems.push(item);

    // Reset form fields
    itemSelect.val("");
    $("#quantity").val("");
    $("#price").val("");
    $("#total").val("");

    // Update bill totals
    updateBillTotals();
  });

  // Remove item from bill
  $(document).on("click", ".remove-item-btn", function () {
    const rowIndex = $(this).closest("tr").index();
    billItems.splice(rowIndex, 1);
    $(this).closest("tr").remove();
    updateBillTotals();
  });

  // Function to update bill totals
  function updateBillTotals() {
    let subtotal = 0;
    $("#billItems tr").each(function () {
      const total =
        parseFloat($(this).find("td:eq(3)").text().replace("Rs. ", "")) || 0;
      subtotal += total;
    });

    const discountPercent = parseFloat($("#discount_percent").val()) || 0;
    const discount = (subtotal * discountPercent) / 100;
    const vat = (subtotal - discount) * 0.13;
    const netTotal = subtotal - discount + vat;

    $("#subtotal").text(subtotal.toFixed(2));
    $("#discount").text(discount.toFixed(2));
    $("#vat").text(vat.toFixed(2));
    $("#netTotal").text(netTotal.toFixed(2));
  }

  function refreshRecentTransactions() {
    // Logic to refresh recent transactions
    // This could involve an AJAX call to fetch the latest transactions
    $.ajax({
      url: "get_recent_transactions.php",
      method: "GET",
      success: function (response) {
        // Update the recent transactions table with the new data
        // Assuming response contains the HTML for the updated table
        $(".recent-transactions tbody").html(response);
      },
      error: function () {
        $("#customer_name").val("Cash");
      },
    });
  }

  $("#completeSale").on("click", function () {
    if (billItems.length === 0) {
      alert("Please add items to the bill first");
      return;
    }

    const paymentMethod = $("#payment_method").val();
    const saleData = {
      customer_name: $("#customer_name").val() || "Cash",
      customer_contact: $("#customer_contact").val() || "",
      items: billItems,
      sub_total: parseFloat($("#subtotal").text()),
      discount_percent: parseFloat($("#discount_percent").val()) || 0,
      vat_amount: parseFloat($("#vat").text()),
      net_total: parseFloat($("#netTotal").text()),
      payment_method: paymentMethod,
    };

    $.ajax({
      url: "process_sale.php",
      type: "POST",
      contentType: "application/json",
      data: JSON.stringify(saleData),
      success: function (response) {
        if (response.success) {
          const printFrame = $("<iframe>", {
            name: "print_frame",
            class: "print-frame",
            style: "display: none;",
          }).appendTo("body");

          printFrame.contents().find("body").html(response.bill_html);
          setTimeout(function () {
            printFrame[0].contentWindow.print();
            // Reset form after printing
            billItems = [];
            updateBillTotals();
            $("#saleForm")[0].reset();
            $("#customer_name").val("Cash");
            $("#payment_method").val("cash"); // Reset payment method to default
            $("#billItems").empty(); // Clear the bill items table
            $("#subtotal").text("0.00"); // Reset subtotal
            $("#discount").text("0.00"); // Reset discount
            $("#vat").text("0.00"); // Reset VAT
            $("#netTotal").text("0.00"); // Reset net total
            printFrame.remove();

            // Refresh recent transactions
            refreshRecentTransactions();
          }, 500);
        } else {
          alert("Error: " + response.message);
        }
      },
      error: function () {
        $("#customer_name").val("Cash");
      },
    });
  });

  // AJAX to get the customer name based on contact number
  $("#customer_contact").on("change", function () {
    const contact = $(this).val();
    if (contact.length === 10) {
      $.ajax({
        url: "get_customer.php",
        method: "GET",
        data: { contact: contact },
        success: function (response) {
          if (response.success) {
            $("#customer_name").val(response.customer.name);
          } else {
            $("#customer_name").val("Cash");
          }
        },
        error: function () {
          $("#customer_name").val("Cash");
        },
      });
    } else {
      $("#customer_name").val("Cash");
    }
  });
});

document
  .getElementById("saleForm")
  .addEventListener("submit", function (event) {
    event.preventDefault();

    // Assuming an AJAX call or some form submission logic here
    // On successful save:
    clearForm();
    showSuccessMessage();
  });

function clearForm() {
  document.getElementById("saleForm").reset();
  document.getElementById("billItems").innerHTML = ""; // Clear the bill items table
}

function showSuccessMessage() {
  const successMessage = document.getElementById("successMessage");
  successMessage.style.display = "block";
  setTimeout(() => {
    successMessage.style.display = "none";
  }, 3000);
}

// Global variable to store current sale ID
let currentSaleId = null;

function showBillOptions(saleId) {
  currentSaleId = saleId;
  const billOptionsModal = document.getElementById("billOptionsModal");

  if (billOptionsModal) {
    billOptionsModal.style.display = "block";

    // Attempt to load bill preview in iframe
    const billPreviewIframe = document.getElementById("billPreviewIframe");
    if (billPreviewIframe) {
      billPreviewIframe.src = `get_bill_preview.php?id=${saleId}`;
    }
  }
}

function viewBill() {
  if (!currentSaleId) {
    alert("No sale selected");
    return;
  }

  // AJAX to fetch bill details
  $.ajax({
    url: "get_bill_details.php",
    method: "GET",
    data: { sale_id: currentSaleId },
    success: function (response) {
      if (response.success) {
        // Display bill details in modal
        $("#billViewContainer").html(response.bill_html);
        $("#billOptionsModal").hide();
        $("#billViewModal").show();
      } else {
        alert(response.message || "Failed to retrieve bill details");
      }
    },
    error: function () {
      alert("Error fetching bill details");
    },
  });
}

function reprintBill() {
  if (!currentSaleId) {
    alert("No sale selected");
    return;
  }

  // AJAX to reprint bill
  $.ajax({
    url: "reprint_bill.php",
    method: "GET",
    data: { id: currentSaleId },
    success: function (response) {
      if (response.success) {
        // Trigger print
        const printFrame = $("<iframe>", {
          name: "print_frame",
          class: "print-frame",
          style: "display: none;",
        }).appendTo("body");

        printFrame.contents().find("body").html(response.bill_html);
        printFrame.contents().find("body").append(`
          <script>
            window.onload = function() {
              window.print();
              window.close();
            }
          </script>
        `);

        $("#billOptionsModal").hide();
      } else {
        alert(response.message || "Failed to reprint bill");
      }
    },
    error: function () {
      alert("Error reprinting bill");
    },
  });
}
