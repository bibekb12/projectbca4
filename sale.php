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
                    <input type="number" id="price" readonly placeholder="Price">
                </div>
                <div class="form-group">
                    <input type="number" id="total" readonly placeholder="Total">
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
                    s.customer_contact, 
                    u.username AS sale_user 
                    FROM sales s 
                    LEFT JOIN customers c ON s.customer_id = c.id 
                    LEFT JOIN users u ON s.user_id = u.id
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
                                    <button type='button' class='btn-primary btn-sm' onclick='showBillOptions(" . $sale['id'] . ")'>
                                        <i class='fa fa-file-text'></i> View/Reprint Bill
                                    </button>
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

    <div class="dashboard-quick-actions">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Recent Items Sold</th>
                        <th>Items</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch recent items sold
                    $recent_sold_items = $conn->query("SELECT i.itemname, si.quantity 
                        FROM sale_items si 
                        JOIN items i ON si.item_id = i.id 
                        ORDER BY si.sale_date DESC 
                        LIMIT 5");

                    if ($recent_sold_items === false) {
                        echo "<tr><td colspan='2'>Error fetching sold items: " . $conn->error . "</td></tr>";
                    } elseif ($recent_sold_items->num_rows === 0) {
                        echo "<tr><td colspan='2'>No recent items sold found.</td></tr>";
                    } else {
                        while ($item = $recent_sold_items->fetch_assoc()) {
                            echo "<tr>
                                <td>" . htmlspecialchars($item['itemname']) . "</td>
                                <td>" . htmlspecialchars($item['quantity']) . "</td>
                            </tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
            <table>
                <thead>
                    <tr>
                        <th>Recent Items Purchased</th>
                        <th>Items</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch recent items purchased
                    $recent_purchased_items = $conn->query("SELECT i.itemname, pi.quantity 
                        FROM purchase_items pi 
                        JOIN items i ON pi.item_id = i.id 
                        ORDER BY pi.purchase_date DESC 
                        LIMIT 5");

                    if ($recent_purchased_items === false) {
                        echo "<tr><td colspan='2'>Error fetching purchased items: " . $conn->error . "</td></tr>";
                    } elseif ($recent_purchased_items->num_rows === 0) {
                        echo "<tr><td colspan='2'>No recent items purchased found.</td></tr>";
                    } else {
                        while ($item = $recent_purchased_items->fetch_assoc()) {
                            echo "<tr>
                                <td>" . htmlspecialchars($item['itemname']) . "</td>
                                <td>" . htmlspecialchars($item['quantity']) . "</td>
                            </tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
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

    <!-- Bill Options Modal -->
    <div id="billOptionsModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div id="billOptionsContainer">
                <h2>Bill Preview</h2>
                <iframe id="billPreviewIframe" width="100%" height="500px"></iframe>
                <button onclick="viewBill()">View Bill</button>
                <button onclick="reprintBill()">Reprint Bill</button>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Close modal when clicking outside of it
        window.addEventListener('click', function(event) {
            const billViewModal = document.getElementById('billViewModal');
            const billOptionsModal = document.getElementById('billOptionsModal');
            if (event.target == billViewModal) {
                billViewModal.style.display = 'none';
            } else if (event.target == billOptionsModal) {
                billOptionsModal.style.display = 'none';
            }
        });

        // Close modal when clicking the close button
        document.querySelectorAll('.close-modal').forEach(function(closeButton) {
            closeButton.addEventListener('click', function() {
                document.getElementById('billViewModal').style.display = 'none';
                document.getElementById('billOptionsModal').style.display = 'none';
            });
        });

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

            // Append item to the bill items table
            $('#billItems').append(`
                <tr>
                    <td>${item.name}</td>
                    <td>${item.quantity}</td>
                    <td>Rs. ${item.price.toFixed(2)}</td>
                    <td>Rs. ${item.total.toFixed(2)}</td>
                    <td><button type="button" class="remove-item-btn">Remove</button></td>
                </tr>
            `);

            // Reset form fields
            itemSelect.val('');
            $('#quantity').val('');
            $('#price').val('');
            $('#total').val('');

            // Update bill totals
            updateBillTotals();
        });

        // Remove item from bill
        $(document).on('click', '.remove-item-btn', function() {
            $(this).closest('tr').remove();
            updateBillTotals();
        });

        // Function to update bill totals
        function updateBillTotals() {
            let subtotal = 0;
            $('#billItems tr').each(function() {
                const total = parseFloat($(this).find('td:eq(3)').text().replace('Rs. ', '')) || 0;
                subtotal += total;
            });

            const discountPercent = parseFloat($('#discount_percent').val()) || 0;
            const discount = (subtotal * discountPercent) / 100;
            const vat = (subtotal - discount) * 0.13;
            const netTotal = subtotal - discount + vat;

            $('#subtotal').text(subtotal.toFixed(2));
            $('#discount').text(discount.toFixed(2));
            $('#vat').text(vat.toFixed(2));
            $('#netTotal').text(netTotal.toFixed(2));
        }

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

        // AJAX to get the customer name based on contact number
        $('#customer_contact').on('change', function() {
            const contact = $(this).val();
            if (contact.length === 10) {
                $.ajax({
                    url: 'get_customer.php',
                    method: 'GET',
                    data: { contact: contact },
                    success: function(response) {
                        if (response.success) {
                            $('#customer_name').val(response.customer.name);
                        } else {
                            $('#customer_name').val('Cash');
                        }
                    },
                    error: function() {
                        $('#customer_name').val('Cash');
                    }
                });
            } else {
                $('#customer_name').val('Cash');
            }
        });
    });

    function showBillOptions(saleId) {
        const billOptionsModal = document.getElementById('billOptionsModal');
        const billOptionsContainer = document.getElementById('billOptionsContainer');
        const billPreviewIframe = document.getElementById('billPreviewIframe');
        
        // Set sale ID for modal buttons
        billOptionsContainer.setAttribute('data-sale-id', saleId);
        
        // AJAX call to get bill file for preview
        $.ajax({
            url: 'get_bill_file.php',
            method: 'POST',
            data: { sale_id: saleId },
            success: function(response) {
                if (response.bill_file) {
                    // Load the bill file into the iframe
                    billPreviewIframe.src = response.bill_file;
                    
                    // Show modal
                    billOptionsModal.style.display = 'block';
                } else {
                    alert('Bill file not found.');
                }
            },
            error: function() {
                alert('Error retrieving bill file.');
            }
        });
    }

    function viewBill() {
        const billOptionsContainer = document.getElementById('billOptionsContainer');
        const saleId = billOptionsContainer.getAttribute('data-sale-id');
        
        // AJAX call to get bill file
        $.ajax({
            url: 'get_bill_file.php',
            method: 'POST',
            data: { sale_id: saleId },
            success: function(response) {
                if (response.bill_file) {
                    const billViewModal = document.getElementById('billViewModal');
                    const billViewContainer = document.getElementById('billViewContainer');
                    
                    // Create an iframe to load the bill
                    const iframe = document.createElement('iframe');
                    iframe.src = response.bill_file;
                    iframe.setAttribute('sandbox', 'allow-scripts allow-same-origin');
                    
                    // Clear previous content and add new iframe
                    billViewContainer.innerHTML = '';
                    billViewContainer.appendChild(iframe);
                    
                    // Show modal
                    billViewModal.style.display = 'block';
                    
                    // Hide bill options modal
                    document.getElementById('billOptionsModal').style.display = 'none';
                } else {
                    alert('Bill file not found.');
                }
            },
            error: function() {
                alert('Error retrieving bill file.');
            }
        });
    }

    function reprintBill() {
        const billOptionsContainer = document.getElementById('billOptionsContainer');
        const saleId = billOptionsContainer.getAttribute('data-sale-id');
        
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
                        
                        // Hide bill options modal
                        document.getElementById('billOptionsModal').style.display = 'none';
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
