<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}
include('db.php');


$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Panel</title>
    <link rel="icon" href="images/inv-logo.png" type="image/icon type">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        .main-content.full-width {
            margin-left: 88px;
        }

        .sale-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-group label {
            font-weight: 500;
            color: #333;
        }

        .form-group input, 
        .form-group select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .bill-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .bill-table th {
            background: #1e3c72;
            color: white;
            padding: 10px;
            text-align: left;
        }

        .bill-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .totals-section {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .btn-primary {
            background: #1e3c72;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: #2a5298;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 10px;
            }
        }

        /* Form styling to match paper bill layout */
        .bill-form {
            width: 148mm; /* A5 width */
            margin: 20px auto;
            padding: 20px;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            font-family: 'Courier New', monospace; /* Traditional bill font */
        }

        .bill-header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 5px;
            margin-top: -15px;
        }

        .bill-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 14px;
        }
        

        .customer-details {
            display: flex;
            gap: 15px;
        }

        .item-entry {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 10px;
            width: 100%;
        }

        .item-entry .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        .item-entry .form-group input,
        .item-entry .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .item-entry .add-item-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px 15px;
            background-color: #1e3c72;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .item-entry .add-item-btn:hover {
            background-color: #2c5aa0;
        }

        .bill-items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .bill-items-table th {
            
            border-bottom: 1px solid #000;
            text-align: left;
            padding: 5px;
        }

        .bill-items-table td {
            padding: 5px;
            border-bottom: 1px dotted #ccc;
        }

        .bill-totals {
            margin-top: 15px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }

        .bill-totals div {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }

        .bill-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        @media print {
            body * {
                visibility: hidden;
            }
            .bill-print, .bill-print * {
                visibility: visible;
            }
            .bill-print {
                position: absolute;
                left: 0;
                top: 0;
                width: 148mm; /* A5 width */
                height: 210mm; /* A5 height */
            }
        }

        .print-frame {
            display: none;
            position: absolute;
            width: 0;
            height: 0;
            border: none;
        }

        .bill-form {
            width: 148mm;
            margin: 20px auto;
            padding: 20px;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            font-family: 'Courier New', monospace;
        }

        /* Add styles for the Add and Complete buttons */
        #addItem, #completeSale {
            background: #1e3c72;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        #addItem:hover, #completeSale:hover {
            background: #2a5298;
        }

        #addItem:disabled, #completeSale:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 10px;
        }

        .section-header h2 {
            margin: 0;
            color: #1e3c72;
        }

        .section-header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .date-filter {
            background-color: #f4f4f4;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9em;
            color: #666;
        }

        .recent-transactions {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .recent-transactions th {
            background-color: #1e3c72;
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 500;
        }

        .recent-transactions td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: middle;
        }

        .recent-transactions tr:hover {
            background-color: #f9f9f9;
            transition: background-color 0.3s ease;
        }

        .customer-cell {
            display: flex;
            flex-direction: column;
        }

        .customer-name {
            font-weight: 600;
            color: #333;
        }

        .customer-contact {
            font-size: 0.8em;
            color: #666;
        }

        .invoice-number {
            font-family: monospace;
            color: #1e3c72;
            font-weight: bold;
        }

        .amount {
            font-weight: 600;
            color: #28a745;
        }

        .payment-method {
            text-transform: capitalize;
            font-style: italic;
            color: #6c757d;
        }

        .time {
            color: #6c757d;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8em;
        }

        .actions {
            text-align: center;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            padding-top: 50px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-width: 800px;
            position: relative;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .close-modal {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-modal:hover,
        .close-modal:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        #billViewContainer iframe {
            width: 100%;
            height: 600px;
            border: none;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 0.75em;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            margin-left: 5px;
        }

        .badge-success {
            color: #fff;
            background-color: #28a745;
        }

        .badge-warning {
            color: #212529;
            background-color: #ffc107;
        }

        .btn-group {
            display: flex;
            gap: 5px;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <?php include('includes/sidebar.php'); ?>
    <div class="top">
        <div class="user-greeting">
            <i class="uil uil-user-circle"></i>
            <span>Welcome, <span class="username"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?></span></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
        <!-- Sales Entry Form -->
        <div class="sale-section">
            <form id="saleForm" class="bill-form">
                <div class="bill-header">
                    <h2>S M I S</h2>
                    <p>Sale Invoice</p>
                    <p>Date: <?php echo date('Y-m-d'); ?> <span id="currentTime"></span></p>
                </div>

                <div class="bill-info">
                    <div class="customer-details">
                        <div class="form-group">
                            <label>Customer:</label>
                            <input type="text" id="customer_name" value="Cash" placeholder="Cash">
                        </div>
                        <div class="form-group">
                            <label>Contact:</label>
                            <input type="text" id="customer_contact" placeholder="Contact">
                        </div>
                    </div>
                </div>

                <div class="item-entry">
                    <div class="form-group">
                        <select id="item_select" onchange="updateItemPrice()">
                            <option value="">Select Item</option>
                            <?php
                            $query = "SELECT * FROM items WHERE status = 'Y' AND stock_quantity > 0 ORDER BY itemname ASC";
                            $result = mysqli_query($conn, $query);
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='{$row['id']}' 
                                      data-price='{$row['sell_price']}' 
                                      data-stock='{$row['stock_quantity']}'>
                                      {$row['itemname']} (Stock: {$row['stock_quantity']})
                                      </option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="number" id="quantity" min="1" placeholder="Qty">
                    </div>
                    <div class="form-group">
                        <input type="number" id="price" readonly placeholder="Total">
                    </div>
                    <button type="button" class="add-item-btn" id="addItem">Add</button>
                </div>

                <table class="bill-items-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="billItems"></tbody>
                </table>

                <div class="bill-totals">
                    <div>
                        <span>Sub Total:</span>
                        <span>Rs.<span id="subtotal">0.00</span></span>
                    </div>
                    <div>
                        <span>Discount:</span>
                        <input type="number" id="discount_percent" min="0" max="100" value="0" style="width: 50px">%
                        <span>Rs.<span id="discount">0.00</span></span>
                    </div>
                    <div>
                        <span>VAT (13%):</span>
                        <span>Rs.<span id="vat">0.00</span></span>
                    </div>
                    <div>
                        <strong>Net Total:</strong>
                        <strong>Rs.<span id="netTotal">0.00</span></strong>
                    </div>
                    <div>
                        <span>Payment Method:</span>
                        <select id="payment_method">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="qrpay">QR Payment</option>
                        </select>
                    </div>
                </div>

                <div class="bill-footer">
                    <button type="button" id="completeSale">Complete Sale</button>
                </div>
            </form>
        </div>

        <!-- Recent Transactions -->
        <div class="sale-section">
            <div class="section-header">
                <h2>Recent Transactions</h2>
                <div class="section-header-actions">
                    <span class="date-filter">Today: <?php echo date('Y-m-d'); ?></span>
                </div>
            </div>
            <table class="bill-table recent-transactions">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Total Amount</th>
                        <th>Payment Method</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch recent sales with more comprehensive data
                    $recent_sales = $conn->query("SELECT s.*, 
                        c.contact AS customer_contact, 
                        u.username AS sale_user 
                        FROM sales s 
                        LEFT JOIN customers c ON s.customer_id = c.id 
                        LEFT JOIN users u ON s.user_id = u.id
                        WHERE DATE(s.sale_date) = '$today' 
                        ORDER BY s.sale_date DESC 
                        LIMIT 10");
                    
                    // Check if query was successful
                    if ($recent_sales === false) {
                        echo "<tr><td colspan='6'>Error fetching transactions: " . $conn->error . "</td></tr>";
                    } elseif ($recent_sales->num_rows === 0) {
                        echo "<tr><td colspan='6'>No recent transactions found.</td></tr>";
                    } else {
                        while ($sale = $recent_sales->fetch_assoc()) {
                            // Determine payment method display
                            $payment_method = !empty($sale['payment_method']) ? 
                                ucfirst(strtolower($sale['payment_method'])) : 
                                'Cash';

                            // Determine bill file status
                            $bill_status = !empty($sale['bill_file']) ? 
                                '<span class="badge badge-success">Generated</span>' : 
                                '<span class="badge badge-warning">Pending</span>';

                            echo "<tr data-sale-id='{$sale['id']}'>
                                <td class='invoice-number'>" . str_pad($sale['id'], 6, '0', STR_PAD_LEFT) . "</td>
                                <td>
                                    <div class='customer-cell'>
                                        <span class='customer-name'>" . htmlspecialchars($sale['customer_name']) . "</span>
                                        <span class='customer-contact'>" . 
                                            (!empty($sale['customer_contact']) ? 
                                            htmlspecialchars($sale['customer_contact']) : 
                                            'N/A') . 
                                        "</span>
                                    </div>
                                </td>
                                <td class='amount'>Rs. " . number_format($sale['net_total'], 2) . "</td>
                                <td class='payment-method'>" . htmlspecialchars($payment_method) . "</td>
                                <td class='time'>" . date('h:i A', strtotime($sale['sale_date'])) . "</td>
                                <td class='actions'>
                                    <div class='btn-group'>
                                        <button onclick='reprintBill(" . $sale['id'] . ")' class='btn-primary btn-sm'>
                                            <i class='fa fa-print'></i> Reprint
                                        </button>
                                        " . ($sale['bill_file'] ? 
                                        "<button onclick='viewBill(\"" . htmlspecialchars($sale['bill_file']) . "\")' class='btn-secondary btn-sm'>
                                            <i class='fa fa-file-text'></i> View Bill
                                        </button>" : '') . "
                                    </div>
                                    $bill_status
                                </td>
                            </tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Bill View Modal -->
        <div id="billViewModal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <div id="billViewContainer">
                    <!-- Saved bill will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        let billItems = [];
        const VAT_RATE = 0.13; // 13% VAT

        // Handle item selection
        $('#item_select').on('change', function() {
            updateItemPrice();
        });

        // Quantity input validation
        $('#quantity').on('input', function() {
            updateItemPrice();
        });

        // Discount percentage change
        $('#discount_percent').on('input', function() {
            updateBillPreview();
        });

        function updateItemPrice() {
            const selectedOption = $('#item_select option:selected');
            const price = parseFloat(selectedOption.data('price')) || 0;
            const maxStock = parseInt(selectedOption.data('stock')) || 0;
            const quantity = parseInt($('#quantity').val()) || 0;

            // Update price input
            const totalPrice = (price * quantity).toFixed(2);
            $('#price').val(totalPrice);

            // Validate stock
            if (quantity > maxStock) {
                alert(`Insufficient stock! Maximum available: ${maxStock}`);
                $('#quantity').val(maxStock);
                $('#price').val((price * maxStock).toFixed(2));
            }
        }

        // Add item to bill
        $('#addItem').on('click', function() {
            const itemSelect = $('#item_select');
            const quantityInput = $('#quantity');
            const priceInput = $('#price');

            const itemId = itemSelect.val();
            const itemName = itemSelect.find('option:selected').text();
            const quantity = parseInt(quantityInput.val()) || 0;
            const unitPrice = parseFloat(itemSelect.find('option:selected').data('price')) || 0;
            const price = parseFloat(priceInput.val()) || 0;
            const maxStock = parseInt(itemSelect.find('option:selected').data('stock')) || 0;

            // Validation checks
            if (!itemId) {
                alert('Please select an item');
                return;
            }

            if (quantity <= 0) {
                alert('Please enter a valid quantity');
                return;
            }

            if (quantity > maxStock) {
                alert(`Insufficient stock! Maximum available: ${maxStock}`);
                quantityInput.val(maxStock);
                priceInput.val((unitPrice * maxStock).toFixed(2));
                return;
            }

            // Add item to bill
            const existingItemIndex = billItems.findIndex(item => item.itemId === itemId);
            if (existingItemIndex !== -1) {
                // Update existing item
                billItems[existingItemIndex].quantity += quantity;
                billItems[existingItemIndex].total = (billItems[existingItemIndex].quantity * unitPrice).toFixed(2);
            } else {
                // Add new item
                billItems.push({
                    itemId: itemId,
                    itemName: itemName,
                    quantity: quantity,
                    unitPrice: unitPrice,
                    total: (unitPrice * quantity).toFixed(2)
                });
            }

            // Update bill preview
            updateBillPreview();

            // Reset inputs
            itemSelect.val('');
            quantityInput.val('');
            priceInput.val('');
        });

        function updateBillPreview() {
            const tbody = $('#billItems');
            tbody.empty();
            
            let subtotal = 0;
            billItems.forEach((item, index) => {
                const row = `
                    <tr>
                        <td>${item.itemName}</td>
                        <td>${item.quantity}</td>
                        <td>${item.unitPrice.toFixed(2)}</td>
                        <td>${item.total}</td>
                        <td>
                            <button onclick="removeItem(${index})" class="btn-remove">Remove</button>
                        </td>
                    </tr>
                `;
                tbody.append(row);
                subtotal += parseFloat(item.total);
            });

            // Calculate discount
            const discountPercent = parseFloat($('#discount_percent').val()) || 0;
            const discountAmount = subtotal * (discountPercent / 100);
            const subtotalAfterDiscount = subtotal - discountAmount;

            // Calculate VAT
            const vatAmount = subtotalAfterDiscount * VAT_RATE;
            const netTotal = subtotalAfterDiscount + vatAmount;

            // Update bill summary
            $('#subtotal').text(subtotal.toFixed(2));
            $('#discount').text(discountAmount.toFixed(2));
            $('#vat').text(vatAmount.toFixed(2));
            $('#netTotal').text(netTotal.toFixed(2));
        }

        // Remove item from bill
        window.removeItem = function(index) {
            billItems.splice(index, 1);
            updateBillPreview();
        };

        // Complete sale
        $('#completeSale').on('click', function() {
            if (billItems.length === 0) {
                alert('Please add items to the bill first');
                return;
            }

            const saleData = {
                customer_name: $('#customer_name').val() || 'Cash Customer',
                customer_contact: $('#customer_contact').val() || '',
                items: billItems.map(item => ({
                    itemId: item.itemId,
                    quantity: item.quantity,
                    price: item.unitPrice,
                    total: parseFloat(item.total)
                })),
                subtotal: parseFloat($('#subtotal').text()),
                discount_percent: parseFloat($('#discount_percent').val()) || 0,
                discount_amount: parseFloat($('#discount').text()),
                vat_amount: parseFloat($('#vat').text()),
                net_total: parseFloat($('#netTotal').text()),
                payment_method: $('#payment_method').val() || 'Cash'
            };

            // AJAX call to save sale
            $.ajax({
                url: 'process_sale.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(saleData),
                success: function(response) {
                    if (response.success) {
                        // Create print iframe
                        const printFrame = $('<iframe>', {
                            name: 'print_frame',
                            class: 'print-frame',
                            style: 'display: none;'
                        }).appendTo('body');

                        // Write bill to iframe and print
                        printFrame.contents().find('body').html(response.bill_html);
                        setTimeout(function() {
                            printFrame[0].contentWindow.print();
                            
                            // Reset form after printing
                            billItems = [];
                            updateBillPreview();
                            $('#saleForm')[0].reset();
                            $('#customer_name').val('Cash Customer');
                            printFrame.remove();

                            // Show success message
                            alert('Sale completed successfully!');
                        }, 500);
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ajax error:', {xhr, status, error});
                    alert('Error processing sale. Please try again.');
                }
            });
        });
    });
    </script>

    <script>
    function reprintBill(saleId) {
        // Create print iframe
        const printFrame = $('<iframe>', {
            name: 'reprint_frame',
            class: 'print-frame',
            style: 'display: none;'
        }).appendTo('body');

        $.ajax({
            url: 'reprint_bill.php',
            type: 'GET',
            data: { id: saleId },
            success: function(response) {
                if (response.success) {
                    printFrame.contents().find('body').html(response.bill_html);
                    setTimeout(function() {
                        printFrame[0].contentWindow.print();
                        printFrame.remove();
                    }, 500);
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Error reprinting bill. Please try again.');
            }

    }
    </script>

    <script>
    function viewBill(billFile) {
        const billViewContainer = document.getElementById('billViewContainer');
        const billViewModal = document.getElementById('billViewModal');
        
        // Create an iframe to load the bill
        const iframe = document.createElement('iframe');
        iframe.src = billFile;
        iframe.setAttribute('sandbox', 'allow-scripts allow-same-origin');
        
        // Clear previous content and add new iframe
        billViewContainer.innerHTML = '';
        billViewContainer.appendChild(iframe);
        
        // Show modal
        billViewModal.style.display = 'block';
    }

    // Close modal when clicking on close button
    document.querySelector('.close-modal').addEventListener('click', function() {
        document.getElementById('billViewModal').style.display = 'none';
    });

    // Close modal when clicking outside of it
    window.addEventListener('click', function(event) {
        const billViewModal = document.getElementById('billViewModal');
        if (event.target == billViewModal) {
            billViewModal.style.display = 'none';
        }
    });

    // Update recent transactions table to include view bill button
    function updateRecentTransactionsTable() {
        const rows = document.querySelectorAll('.recent-transactions tbody tr');
        rows.forEach(row => {
            const saleId = row.getAttribute('data-sale-id');
            const actionsCell = row.querySelector('.actions');
            
            if (actionsCell && saleId) {
                // Add view bill button next to reprint button
                const viewBillBtn = document.createElement('button');
                viewBillBtn.innerHTML = '<i class="fa fa-file-text"></i> View Bill';
                viewBillBtn.classList.add('btn-primary', 'btn-sm', 'ml-2');
                viewBillBtn.onclick = function() {
                    // AJAX call to get bill file
                    $.ajax({
                        url: 'get_bill_file.php',
                        method: 'POST',
                        data: { sale_id: saleId },
                        success: function(response) {
                            if (response.bill_file) {
                                viewBill(response.bill_file);
                            } else {
                                alert('Bill file not found.');
                            }
                        },
                        error: function() {
                            alert('Error retrieving bill file.');
                        }
                    });
                };
                
                actionsCell.appendChild(viewBillBtn);
            }
        });
    }

    // Call on page load
    $(document).ready(function() {
        updateRecentTransactionsTable();
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
