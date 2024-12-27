const body = document.querySelector("body"),
  modeToggle = body.querySelector(".mode-toggle");
sidebar = body.querySelector("nav");
sidebarToggle = body.querySelector(".sidebar-toggle");

let getMode = localStorage.getItem("mode");
if (getMode && getMode === "dark") {
  body.classList.toggle("dark");
}

let getStatus = localStorage.getItem("status");
if (getStatus && getStatus === "close") {
  sidebar.classList.toggle("close");
}

modeToggle.addEventListener("click", () => {
  body.classList.toggle("dark");
  if (body.classList.contains("dark")) {
    localStorage.setItem("mode", "dark");
  } else {
    localStorage.setItem("mode", "light");
  }
});

sidebarToggle.addEventListener("click", () => {
  sidebar.classList.toggle("close");
  if (sidebar.classList.contains("close")) {
    localStorage.setItem("status", "close");
  } else {
    localStorage.setItem("status", "open");
  }
});

// calaculation for purchase total price
function updatePrice() {
    
    const productSelect = document.getElementById('product_id');
    const selectedOption = productSelect.options[productSelect.selectedIndex];

    // Get the price from the data attribute
    const price = selectedOption.getAttribute('data-price');

    document.getElementById('quantity').value = "";
    document.getElementById('total').value = "";
}

function calculateTotal() {
    const productSelect = document.getElementById('product_id');
    const selectedOption = productSelect.options[productSelect.selectedIndex];
    const price = parseFloat(selectedOption.getAttribute('data-price'));

    const quantity = parseFloat(document.getElementById('quantity').value);

    if (!isNaN(price) && !isNaN(quantity)) {
        const total = price * quantity;
        document.getElementById('total').value = total.toFixed(2);
    } else {
        document.getElementById('total').value = "";
    }
}
