<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Panel</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <nav>
        <div class="logo-name">
            <div class="logo-image">
               <img src="images/logo.png" alt="">
            </div>
            <span class="logo_name">SIMPLE IMS</span>
        </div>
        <div class="menu-items">
            <ul class="nav-links">
                <li><a href="dashboard.php">
                    <i class="uil uil-estate"></i>
                    <span class="link-name">Dashboard</span>
                </a></li>
                <li><a href="sale.php">
                    <i class="fa fa-money" aria-hidden="true"></i>
                    <span class="link-name">Sale</span>
                </a></li>
                <li><a href="purchase.php">
                    <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                    <span class="link-name">Purchase</span>
                </a></li>
                <li><a href="report.php">
                    <i class="uil uil-chart"></i>
                    <span class="link-name">Report</span>
                </a></li>
                <li><a href="setup.php">
                    <i class="uil uil-setting"></i>
                    <span class="link-name">Setup</span>
                </a></li>
            </ul>
            
            <ul class="logout-mode">
                <li><a href="logout.php">
                    <i class="uil uil-signout"></i>
                    <span class="link-name">Logout</span>
                </a></li>
            </ul>
        </div>
    </nav>

    <section class="dashboard">
        <div class="top">
            <i class="uil uil-bars sidebar-toggle"></i>
            <div class="user-greeting">
                <i class="uil uil-user-circle"></i>
                <span>Welcome, <span class="username"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?></span></span>
            </div>
        </div>

        <div class="dash-content">
            <div class="overview">
                <div class="title">
                    <i class="fa fa-money"></i>
                    <span class="text">Sales Entry</span>
                </div>

                <!-- Customer Details Form -->
                <div class="form-container">
                    <div class="activity-title">
                        <i class="fa fa-user"></i>
                        <span>Customer Details</span>
                    </div>
                    <form id="customerForm" class="form-grid">
                        <div class="form-group">
                            <label for="customer_contact">Contact:</label>
                            <input type="tel" 
                                   id="customer_contact" 
                                   name="customer_contact" 
                                   pattern="[0-9]{10}" 
                                   maxlength="10" 
                                   title="Please enter a valid 10-digit contact number"
                                   placeholder="Enter 10-digit number"
                                   required>
                        </div>
                        <div class="form-group">
                            <label for="customer_name">Customer Name:</label>
                            <input type="text" id="customer_name" name="customer_name" value="Cash" required>
                        </div>
                        <input type="hidden" id="customer_id" name="customer_id">
                    </form>
                </div>

                <!-- Sales Form -->
                <div class="form-container">
                    <div class="activity-title">
                        <i class="fa fa-shopping-cart"></i>
                        <span>Sales Bill</span>
                    </div>
                    <form id="saleForm" method="POST" action="process_sale.php" class="form-grid">
                        <div class="form-group">
                            <label for="item">Item:</label>
                            <select name="item_id" id="item_select" required>
                                <option value="">Select Item</option>
                                <?php
                                include('db.php');
                                $result = $conn->query("SELECT id, name, sell_price, stock_quantity FROM items WHERE stock_quantity > 0");
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='{$row['id']}' 
                                            data-price='{$row['sell_price']}' 
                                            data-stock='{$row['stock_quantity']}'>
                                            {$row['name']} - Stock: {$row['stock_quantity']}
                                          </option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="quantity">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" required min="1">
                        </div>
                        <div class="form-group">
                            <label for="price">Sell Price:</label>
                            <input type="number" id="sellprice" name="sellprice" step="0.01" required readonly>
                        </div>
                        <div class="form-group">
                            <label for="total">Total:</label>
                            <input type="number" id="total" name="total" step="0.01" readonly>
                        </div>
                        <div class="form-group">
                            <button type="button" id="addItem" class="btn-primary">
                                <i class="fa fa-plus"></i> Add Item
                            </button>
                        </div>
                    </form>

                    <!-- Bill Preview -->
                    <div class="bill-preview">
                        <h3>Bill Preview</h3>
                        <div class="customer-info-preview">
                            <p><strong>Customer Name:</strong> ${$('#customer_name').val() || 'Cash'}</p>
                            <p><strong>Contact:</strong> ${$('#customer_contact').val() || 'N/A'}</p>
                        </div>
                        <table id="billTable">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="billItems">
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3"><strong>Grand Total:</strong></td>
                                    <td id="grandTotal">0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                        <button type="button" id="generateBill" class="btn-primary">
                            <i class="fa fa-file-text"></i> Generate Bill
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="script.js"></script>
    
    <script>
    $(document).ready(function() {
        // Handle item selection
        $('#item_select').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const sellPrice = selectedOption.data('price');
            const stockQuantity = selectedOption.data('stock');
            
            // Update sell price field
            $('#sellprice').val(sellPrice);
            
            // Reset quantity and total
            $('#quantity').val('');
            $('#total').val('');
            
            // Set max quantity based on stock
            $('#quantity').attr('max', stockQuantity);
        });

        // Calculate total when quantity changes
        $('#quantity').on('input', function() {
            const quantity = $(this).val();
            const sellPrice = $('#sellprice').val();
            if (quantity && sellPrice) {
                const total = (quantity * sellPrice).toFixed(2);
                $('#total').val(total);
            }
        });

        // Customer lookup code (keep your existing AJAX code)
        let typingTimer;
        const doneTypingInterval = 500;

        // Validate contact number input to allow only numbers
        $('#customer_contact').on('input', function() {
            // Remove any non-numeric characters
            $(this).val($(this).val().replace(/[^0-9]/g, ''));
            
            // Limit to 10 digits
            if ($(this).val().length > 10) {
                $(this).val($(this).val().slice(0, 10));
            }
        });

        // Validate contact before searching
        $('#customer_contact').on('keyup', function() {
            clearTimeout(typingTimer);
            const contact = $(this).val();
            
            // Only search if contact has at least 3 digits and contains only numbers
            if (contact.length >= 3 && /^\d+$/.test(contact)) {
                typingTimer = setTimeout(function() {
                    searchCustomer(contact);
                }, doneTypingInterval);
            }
        });

        function searchCustomer(contact) {
            $.ajax({
                url: 'get_customer.php',
                type: 'GET',
                data: { contact: contact },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#customer_name').val(response.customer.name);
                        $('#customer_id').val(response.customer.id);
                    } else {
                        $('#customer_name').val('Cash');
                        $('#customer_id').val('');
                    }
                },
                error: function() {
                    console.error('Error fetching customer data');
                    $('#customer_name').val('Cash');
                    $('#customer_id').val('');
                }
            });
        }

        // Initialize array to store bill items
        let billItems = [];
        let grandTotal = 0;

        // Handle Add Item button click
        $('#addItem').on('click', function() {
            const itemSelect = $('#item_select');
            const selectedOption = itemSelect.find('option:selected');
            const itemId = itemSelect.val();
            const itemName = selectedOption.text().split(' - ')[0]; // Get item name without stock info
            const quantity = $('#quantity').val();
            const price = $('#sellprice').val();
            const total = $('#total').val();

            // Validate inputs
            if (!itemId || !quantity || quantity <= 0) {
                alert('Please select an item and enter a valid quantity');
                return;
            }

            // Add item to bill items array
            const item = {
                id: itemId,
                name: itemName,
                quantity: quantity,
                price: price,
                total: total
            };

            // Add to bill items array
            billItems.push(item);

            // Update bill preview
            updateBillPreview();

            // Reset form fields
            itemSelect.val('');
            $('#quantity').val('');
            $('#sellprice').val('');
            $('#total').val('');
        });

        // Function to update bill preview
        function updateBillPreview() {
            // Calculate totals
            let subTotal = 0;
            billItems.forEach(item => {
                subTotal += parseFloat(item.total);
            });

            const discountPercent = parseFloat($('#discount_percent').val()) || 0;
            const discountAmount = (subTotal * discountPercent / 100);
            const vatPercent = 13; // Fixed 13% VAT
            const vatableAmount = subTotal - discountAmount;
            const vatAmount = (vatableAmount * vatPercent / 100);
            const netTotal = vatableAmount + vatAmount;

            let html = `
                <h3>Bill Preview</h3>
                <div class="customer-info-preview">
                    <p><strong>Customer Name:</strong> ${$('#customer_name').val() || 'Cash'}</p>
                    <p><strong>Contact:</strong> ${$('#customer_contact').val() || 'N/A'}</p>
                </div>
                <table id="billTable">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>`;
            
            billItems.forEach((item, index) => {
                html += `
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.quantity}</td>
                        <td>${parseFloat(item.price).toFixed(2)}</td>
                        <td>${parseFloat(item.total).toFixed(2)}</td>
                        <td>
                            <button type="button" class="btn-delete" onclick="removeItem(${index})">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>`;
            });
            
            html += `
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: right;"><strong>Sub Total:</strong></td>
                            <td><strong>${subTotal.toFixed(2)}</strong></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="3" style="text-align: right;">
                                <strong>Discount (%):</strong>
                                <input type="number" id="discount_percent" min="0" max="100" step="0.01" 
                                       style="width: 70px;" value="${discountPercent}">
                            </td>
                            <td>${discountAmount.toFixed(2)}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="3" style="text-align: right;"><strong>VAT (13%):</strong></td>
                            <td>${vatAmount.toFixed(2)}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="3" style="text-align: right;"><strong>Net Total:</strong></td>
                            <td><strong>${netTotal.toFixed(2)}</strong></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="3" style="text-align: right;"><strong>Payment Method:</strong></td>
                            <td>
                                <select id="payment_method">
                                    <option value="cash">Cash</option>
                                    <option value="credit">Credit</option>
                                    <option value="bank">Bank</option>
                                </select>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>`;

            if (billItems.length > 0) {
                html += '<button type="button" id="generateBill" class="btn">Generate Bill</button>';
            }
            
            $('.bill-preview').html(html);

            // Update discount and totals when discount changes
            $('#discount_percent').on('input', function() {
                updateBillPreview();
            });
        }

        // Handle delete item
        $(document).on('click', '.btn-delete', function() {
            const index = $(this).data('index');
            billItems.splice(index, 1);
            updateBillPreview();
        });

        // Handle Generate Bill button
        $(document).on('click', '#generateBill', function() {
            if (billItems.length === 0) {
                alert('Please add items to the bill first');
                return;
            }

            // Calculate all totals
            const subTotal = billItems.reduce((sum, item) => sum + parseFloat(item.total), 0);
            const discountPercent = parseFloat($('#discount_percent').val()) || 0;
            const discountAmount = (subTotal * discountPercent / 100);
            const vatPercent = 13;
            const vatableAmount = subTotal - discountAmount;
            const vatAmount = (vatableAmount * vatPercent / 100);
            const netTotal = vatableAmount + vatAmount;

            const billData = {
                customer_id: $('#customer_id').val(),
                customer_name: $('#customer_name').val(),
                customer_contact: $('#customer_contact').val(),
                items: billItems,
                sub_total: subTotal,
                discount_percent: discountPercent,
                discount_amount: discountAmount,
                vat_percent: vatPercent,
                vat_amount: vatAmount,
                net_total: netTotal,
                payment_method: $('#payment_method').val()
            };

            // Send bill data to server
            $.ajax({
                url: 'process_sale.php',
                type: 'POST',
                data: JSON.stringify(billData),
                contentType: 'application/json',
                success: function(response) {
                    if (response.success) {
                        // Create a new window for printing
                        const printWindow = window.open('', '_blank');
                        printWindow.document.write(`
                            <html>
                                <head>
                                    <title>Sale Invoice</title>
                                    <style>
                                        @page {
                                            size: A5;
                                            margin: 10mm;
                                        }
                                        body {
                                            margin: 0;
                                            font-family: Arial, sans-serif;
                                            font-size: 12px;
                                        }
                                        .bill-print {
                                            width: 100%;
                                            max-width: 148mm; /* A5 width */
                                            margin: 0 auto;
                                            padding: 10mm;
                                        }
                                        .bill-header {
                                            text-align: center;
                                            margin-bottom: 5mm;
                                            padding-bottom: 2mm;
                                            border-bottom: 1px solid #1e3c72;
                                        }
                                        .bill-header h2 {
                                            color: #1e3c72;
                                            margin: 0;
                                            font-size: 16px;
                                        }
                                        .customer-info {
                                            margin-bottom: 5mm;
                                            padding: 2mm;
                                            border: 1px solid #ddd;
                                        }
                                        .customer-info p {
                                            margin: 1mm 0;
                                        }
                                        .bill-items {
                                            width: 100%;
                                            border-collapse: collapse;
                                            margin-bottom: 5mm;
                                            font-size: 11px;
                                        }
                                        .bill-items th, .bill-items td {
                                            padding: 2mm;
                                            border: 1px solid #ddd;
                                            text-align: left;
                                        }
                                        .bill-items th {
                                            background: #1e3c72;
                                            color: white;
                                        }
                                        .bill-totals {
                                            margin-top: 5mm;
                                            border-top: 1px solid #ddd;
                                            padding-top: 2mm;
                                        }
                                        .bill-totals table {
                                            width: 100%;
                                            max-width: 80mm;
                                            margin-left: auto;
                                        }
                                        .bill-totals td {
                                            padding: 1mm 2mm;
                                        }
                                        .bill-totals td:first-child {
                                            text-align: right;
                                            color: #666;
                                        }
                                        .bill-totals td:last-child {
                                            text-align: right;
                                            font-weight: 500;
                                        }
                                        .bill-footer {
                                            text-align: center;
                                            margin-top: 5mm;
                                            padding-top: 2mm;
                                            border-top: 1px solid #ddd;
                                            font-size: 11px;
                                        }
                                        @media print {
                                            .no-print { display: none; }
                                            @page { size: A5; margin: 10mm; }
                                            html, body {
                                                width: 148mm;
                                                height: 210mm;
                                            }
                                        }
                                    </style>
                                </head>
                                <body>
                                    ${response.bill_html}
                                    <div class="no-print" style="text-align: center; margin-top: 10mm;">
                                        <button onclick="window.print()">Print Bill</button>
                                    </div>
                                </body>
                            </html>
                        `);
                        printWindow.document.close();

                        // Clear the form and bill preview
                        billItems = [];
                        updateBillPreview();
                        $('#customerForm')[0].reset();
                        $('#saleForm')[0].reset();
                        $('#customer_name').val('Cash');
                    } else {
                        alert('Error generating bill: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.error('Response:', xhr.responseText);
                    alert('Error generating bill. Please try again.');
                }
            });
        });
    });
    </script>

    <style>
    /* Add these styles to your CSS */
    .btn-delete {
        background: #dc3545;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-delete:hover {
        background: #c82333;
    }

    #billTable {
        width: 100%;
        margin-top: 20px;
        border-collapse: collapse;
    }

    #billTable th,
    #billTable td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    #billTable th {
        background-color: #1e3c72;
        color: white;
    }

    #billTable tbody tr:hover {
        background-color: #f5f5f5;
    }

    .bill-preview {
        margin-top: 30px;
    }

    .bill-preview h3 {
        color: #1e3c72;
        margin-bottom: 15px;
    }

    #generateBill {
        margin-top: 20px;
    }
    </style>
</body>
</html>
