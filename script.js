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

document.addEventListener('DOMContentLoaded', function() {
    const customerContact = document.getElementById('customer_contact');
    const customerName = document.getElementById('customer_name');
    const itemSelect = document.getElementById('item_select');
    const quantityInput = document.getElementById('quantity');
    const priceInput = document.getElementById('price');
    const totalInput = document.getElementById('total');
    const addItemBtn = document.getElementById('addItem');
    const billItems = document.getElementById('billItems');
    let items = [];
    let existingCustomer = false;

    // Set focus to contact input when page loads
    customerContact.focus();

    // Function to lookup customer
    function lookupCustomer() {
        const contact = customerContact.value.trim();
        if (contact) {
            fetch('get_customer.php?contact=' + encodeURIComponent(contact))
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.customer) {
                        customerName.value = data.customer.name;
                        existingCustomer = true;
                        // Move focus to item select after finding customer
                        itemSelect.focus();
                    } else {
                        customerName.value = 'Cash';
                        existingCustomer = false;
                        // Keep focus on customer name for editing
                        customerName.focus();
                        customerName.select();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    customerName.value = 'Cash';
                    existingCustomer = false;
                    customerName.focus();
                    customerName.select();
                });
        } else {
            customerName.value = 'Cash';
            existingCustomer = false;
            customerName.focus();
            customerName.select();
        }
    }

    // Handle Enter key press on contact input
    customerContact.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            lookupCustomer();
        }
    });

    // Allow editing customer name
    customerName.addEventListener('change', function() {
        if (!existingCustomer && this.value.trim() !== 'Cash') {
            // New customer name entered
            console.log('New customer name entered:', this.value);
        }
    });

    // Update price when item is selected
    itemSelect.addEventListener('change', function() {
        if (this.value) {
            const selectedOption = this.options[this.selectedIndex];
            const sellPrice = parseFloat(selectedOption.dataset.price);
            priceInput.value = sellPrice.toFixed(2);
            quantityInput.value = '1'; // Set default quantity
            updateTotal();
            quantityInput.focus();
            quantityInput.select(); // Select the default quantity for easy changing
        } else {
            priceInput.value = '';
            quantityInput.value = '';
            totalInput.value = '';
        }
    });

    // Update total when quantity changes
    quantityInput.addEventListener('input', updateTotal);

    function updateTotal() {
        const quantity = parseFloat(quantityInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        totalInput.value = (quantity * price).toFixed(2);
    }

    // Add item to bill
    addItemBtn.addEventListener('click', function() {
        if (!itemSelect.value || !quantityInput.value) {
            alert('Please select an item and enter quantity');
            return;
        }

        const selectedOption = itemSelect.options[itemSelect.selectedIndex];
        const maxStock = parseInt(selectedOption.dataset.stock);
        const quantity = parseInt(quantityInput.value);

        if (quantity > maxStock) {
            alert(`Only ${maxStock} items available in stock`);
            return;
        }

        const item = {
            id: itemSelect.value,
            name: selectedOption.text.split(' - ')[0],
            quantity: quantity,
            price: parseFloat(priceInput.value),
            total: parseFloat(totalInput.value)
        };

        items.push(item);
        updateBillTable();
        resetForm();
        itemSelect.focus(); // Focus back to item select after adding
    });

    function updateBillTable() {
        billItems.innerHTML = '';
        let grandTotal = 0;

        items.forEach((item, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.name}</td>
                <td>${item.quantity}</td>
                <td>$${item.price.toFixed(2)}</td>
                <td>$${item.total.toFixed(2)}</td>
                <td>
                    <button type="button" onclick="removeItem(${index})" class="btn-delete">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            `;
            billItems.appendChild(row);
            grandTotal += item.total;
        });

        document.getElementById('grandTotal').textContent = '$' + grandTotal.toFixed(2);
    }

    function resetForm() {
        itemSelect.value = '';
        quantityInput.value = '';
        priceInput.value = '';
        totalInput.value = '';
    }

    // Make removeItem function available globally
    window.removeItem = function(index) {
        items.splice(index, 1);
        updateBillTable();
    };

    // Generate Bill button functionality
    document.getElementById('generateBill').addEventListener('click', function() {
        if (items.length === 0) {
            alert('Please add items to the bill');
            return;
        }

        const billData = {
            customer: {
                name: customerName.value.trim(),
                contact: customerContact.value.trim(),
                isExisting: existingCustomer
            },
            items: items,
            total: parseFloat(document.getElementById('grandTotal').textContent.replace('$', ''))
        };

        fetch('process_bill.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(billData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Bill generated successfully!');
                window.location.href = 'print_bill.php?bill_no=' + data.bill_no;
            } else {
                alert('Error generating bill: ' + data.message);
            }
        });
    });
});
