const body = document.querySelector("body");
const itemSelect = document.getElementById("item_select");
const quantityInput = document.getElementById("quantity");
const priceInput = document.getElementById("price");
const totalInput = document.getElementById("total");
const grandTotalDisplay = document.getElementById("grandTotal");
const billItemsTableBody = document.querySelector("#billItems tbody");
const addItemButton = document.getElementById("addItem");
const generateBillButton = document.getElementById("generateBill");
const sidebarToggle = body.querySelector(".sidebar-toggle");

let grandTotal = 0;

// Function to update total price
function updateTotal() {
  const quantity = parseInt(quantityInput.value) || 0;
  const price = parseFloat(priceInput.value) || 0;
  const total = quantity * price;
  totalInput.value = total.toFixed(2);
}

// Event listener for item selection
itemSelect.addEventListener("change", function () {
  const selectedItem = this.value;
  if (selectedItem) {
    // Fetch item price from the server (get_item.php)
    fetch(`get_item.php?id=${selectedItem}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.price) {
          priceInput.value = data.price;
          updateTotal();
        }
      });
  } else {
    priceInput.value = "";
    totalInput.value = "";
  }
});

// Event listener for quantity change
quantityInput.addEventListener("input", updateTotal);

// Event listener for adding items to the bill
addItemButton.addEventListener("click", function () {
  const itemName = itemSelect.options[itemSelect.selectedIndex].text;
  const quantity = parseInt(quantityInput.value);
  const price = parseFloat(priceInput.value);
  const total = parseFloat(totalInput.value);

  if (itemName && quantity > 0 && price > 0) {
    const row = document.createElement("tr");
    row.innerHTML = `
            <td>${itemName}</td>
            <td>${quantity}</td>
            <td>${price.toFixed(2)}</td>
            <td>${total.toFixed(2)}</td>
            <td><button class="removeItem">Remove</button></td>
        `;
    billItemsTableBody.appendChild(row);
    grandTotal += total;
    grandTotalDisplay.textContent = `Total: Rs. ${grandTotal.toFixed(2)}`;

    // Clear inputs
    itemSelect.value = "";
    quantityInput.value = 1;
    priceInput.value = "";
    totalInput.value = "";

    // Add event listener for removing items
    row.querySelector(".removeItem").addEventListener("click", function () {
      grandTotal -= total;
      grandTotalDisplay.textContent = `Total: Rs. ${grandTotal.toFixed(2)}`;
      row.remove();
    });
  } else {
    alert("Please select an item and enter a valid quantity.");
  }
});

// Event listener for generating the bill
generateBillButton.addEventListener("click", function () {
  // Logic to generate the bill (e.g., send data to process_sale.php)
  alert("Bill generated successfully!");
});

// Prevent sidebar from closing when navigating to sale
sidebarToggle.addEventListener("click", () => {
  const sidebar = body.querySelector("nav");
  sidebar.classList.toggle("close");
  const isClose = sidebar.classList.contains("close");
  sidebarToggle.setAttribute("data-tooltip", isClose ? "Expand" : "Minimize");
  localStorage.setItem("status", isClose ? "close" : "open");
});
