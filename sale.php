<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}
include('db.php');
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
            margin-bottom: 15px;
        }

        .bill-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 14px;
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

        /* Add to your existing styles */
        .print-frame {
            display: none;
            position: absolute;
            width: 0;
            height: 0;
            border: none;
        }

        /* Update bill-form styles */
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
    </style>
</head>
<body>
    <?php include('includes/sidebar.php'); ?>

    <div class="main-content">
        <!-- Summary Boxes -->
        <div class="sale-section">
            <h2>Today's Summary</h2>
            <div class="form-row">
                <div class="boxes">
                    <div class="box box1">
                        <i class="fa fa-money"></i>
                        <span class="text">Today's Sales</span>
                        <span class="number">
                            <?php
                                $today = date('Y-m-d');
                                $result = $conn->query("SELECT SUM(net_total) as total FROM sales WHERE DATE(sale_date) = '$today'");
                                $row = $result->fetch_assoc();
                                $total = isset($row['total']) ? $row['total'] : 0;
                                echo '$' . number_format($total, 2);
                            ?>
                        </span>
                    </div>
                    <div class="box box2">
                        <i class="fa fa-shopping-cart"></i>
                        <span class="text">Today's Transactions</span>
                        <span class="number">
                            <?php
                                $result = $conn->query("SELECT COUNT(*) as count FROM sales WHERE DATE(sale_date) = '$today'");
                                $row = $result->fetch_assoc();
                                echo $row['count'];
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Entry Form -->
        <div class="sale-section">
            <h2>New Sale</h2>
            <form id="saleForm" class="bill-form">
                <div class="bill-header">
                    <h2>SIMPLE IMS</h2>
                    <p>Sale Invoice</p>
                    <p>Date: <?php echo date('Y-m-d H:i:s'); ?></p>
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
                    <div class="bill-number">
                        <p>Bill No: <span id="billNumber">-</span></p>
                    </div>
                </div>

                <div class="item-entry">
                    <div class="form-group">
                        <select id="item_select">
                            <option value="">Select Item</option>
                            <?php
                            $items = $conn->query("SELECT id, itemname, sell_price, stock_quantity FROM items WHERE status = 'Y'");
                            while ($item = $items->fetch_assoc()) {
                                echo "<option value='{$item['id']}' 
                                      data-price='{$item['sell_price']}'
                                      data-stock='{$item['stock_quantity']}'>
                                      {$item['itemname']}
                                      </option>";
                            }
                            ?>
                        </select>
                        <input type="number" id="quantity" min="1" placeholder="Qty">
                        <input type="number" id="price" readonly>
                        <button type="button" id="addItem">Add</button>
                    </div>
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
                        <span>$<span id="subtotal">0.00</span></span>
                    </div>
                    <div>
                        <span>Discount:</span>
                        <input type="number" id="discount_percent" min="0" max="100" value="0" style="width: 50px">%
                        <span>$<span id="discount">0.00</span></span>
                    </div>
                    <div>
                        <span>VAT (13%):</span>
                        <span>$<span id="vat">0.00</span></span>
                    </div>
                    <div>
                        <strong>Net Total:</strong>
                        <strong>$<span id="netTotal">0.00</span></strong>
                    </div>
                    <div>
                        <span>Payment Method:</span>
                        <select id="payment_method">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="upi">UPI</option>
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
            <h2>Recent Transactions</h2>
            <table class="bill-table">
                <thead>
                    <tr>
                        <th>Bill No</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $recent_sales = $conn->query("SELECT * FROM sales 
                        WHERE DATE(sale_date) = '$today' 
                        ORDER BY sale_date DESC 
                        LIMIT 10");
                    
                    while ($sale = $recent_sales->fetch_assoc()) {
                        echo "<tr>
                            <td>" . str_pad($sale['id'], 6, '0', STR_PAD_LEFT) . "</td>
                            <td>" . htmlspecialchars($sale['customer_name']) . "</td>
                            <td>$" . number_format($sale['net_total'], 2) . "</td>
                            <td>" . date('h:i A', strtotime($sale['sale_date'])) . "</td>
                            <td>
                                <button onclick='reprintBill(" . $sale['id'] . ")' class='btn-primary'>
                                    <i class='fa fa-print'></i> Reprint
                                </button>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        let billItems = [];

        // Handle item selection
        $('#item_select').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const price = selectedOption.data('price');
            const stock = selectedOption.data('stock');
            
            $('#price').val(price);
            $('#quantity').attr('max', stock).val('');
            $('#total').val('');
        });

        // Calculate total when quantity changes
        $('#quantity').on('input', function() {
            const quantity = parseInt($(this).val()) || 0;
            const price = parseFloat($('#price').val()) || 0;
            const total = quantity * price;
            $('#total').val(total.toFixed(2));
        });

        // Add item to bill
        $('#addItem').on('click', function() {
            const itemSelect = $('#item_select');
            const selectedOption = itemSelect.find('option:selected');
            
            if (!itemSelect.val()) {
                alert('Please select an item');
                return;
            }
            
            const quantity = parseInt($('#quantity').val());
            if (!quantity || quantity <= 0) {
                alert('Please enter a valid quantity');
                return;
            }

            const item = {
                id: parseInt(itemSelect.val()),
                name: selectedOption.text(),
                quantity: quantity,
                price: parseFloat($('#price').val()),
                total: parseFloat($('#total').val() || (quantity * parseFloat($('#price').val())))
            };

            billItems.push(item);
            updateBillPreview();
            
            // Reset form fields
            itemSelect.val('');
            $('#quantity').val('');
            $('#price').val('');
            $('#total').val('');
        });

        // Complete sale
        $('#completeSale').on('click', function() {
            if (billItems.length === 0) {
                alert('Please add items to the bill first');
                return;
            }

            const saleData = {
                customer_name: $('#customer_name').val() || 'Cash',
                customer_contact: $('#customer_contact').val() || '',
                items: billItems,
                sub_total: parseFloat($('#subtotal').text()),
                discount_percent: parseFloat($('#discount_percent').val()) || 0,
                vat_amount: parseFloat($('#vat').text()),
                net_total: parseFloat($('#netTotal').text()),
                payment_method: $('#payment_method').val() || 'cash'
            };

            // Create print iframe
            const printFrame = $('<iframe>', {
                name: 'print_frame',
                class: 'print-frame',
                style: 'display: none;'
            }).appendTo('body');

            $.ajax({
                url: 'process_sale.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(saleData),
                success: function(response) {
                    if (response.success) {
                        // Write bill to iframe and print
                        printFrame.contents().find('body').html(response.bill_html);
                        setTimeout(function() {
                            printFrame[0].contentWindow.print();
                            // Reset form after printing
                            billItems = [];
                            updateBillPreview();
                            $('#saleForm')[0].reset();
                            $('#customer_name').val('Cash');
                            printFrame.remove();
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

        function calculateTotal() {
            const quantity = parseInt($('#quantity').val()) || 0;
            const price = parseFloat($('#price').val()) || 0;
            
            if (quantity && price) {
                const total = quantity * price;
                $('#total').val(total.toFixed(2));
            } else {
                $('#total').val('');
            }
        }

        function updateBillPreview() {
            const tbody = $('#billItems');
            tbody.empty();
            
            let subtotal = 0;
            
            billItems.forEach((item, index) => {
                tbody.append(`
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.quantity}</td>
                        <td>$${item.price.toFixed(2)}</td>
                        <td>$${item.total.toFixed(2)}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(${index})">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
                subtotal += item.total;
            });

            // Calculate totals
            const discountPercent = parseFloat($('#discount_percent').val()) || 0;
            const discount = (subtotal * discountPercent) / 100;
            const vat = ((subtotal - discount) * 0.13); // 13% VAT
            const netTotal = subtotal - discount + vat;

            // Update totals display
            $('#subtotal').val(subtotal.toFixed(2));
            $('#vat').val(vat.toFixed(2));
            $('#netTotal').val(netTotal.toFixed(2));
        }

        window.removeItem = function(index) {
            billItems.splice(index, 1);
            updateBillPreview();
        };

        // Add handler for sidebar toggle
        $('.sidebar-toggle').on('click', function() {
            $('.main-content').toggleClass('full-width');
        });
    });
    </script>

    <!-- Add this JavaScript for reprint functionality -->
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
        });
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
