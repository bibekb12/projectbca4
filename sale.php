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
</head>
<body>
    <?php include('includes/sidebar.php'); ?>

    <section class="dashboard">
        <div class="top">
            <i class="uil uil-bars sidebar-toggle"></i>
            <div class="user-greeting">
                <i class="uil uil-user-circle"></i>
                <span>Welcome, <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span></span>
            </div>
        </div>

        <div class="dash-content">
            <div class="overview">
                <div class="title">
                    <i class="fa fa-money"></i>
                    <span class="text">Sales Entry</span>
                </div>

                <!-- Customer Details Form -->
                <form id="saleForm" method="POST" action="process_sale.php">
                    <div class="form-container">
                        <div class="activity-title">
                            <i class="fa fa-user"></i>
                            <span>Customer Details</span>
                        </div>
                        <div class="form-group">
                            <label for="customer_contact">Contact:</label>
                            <input type="tel" 
                                   id="customer_contact" 
                                   name="customer_contact" 
                                   pattern="[0-9]{10}" 
                                   maxlength="10" 
                                   title="Please enter a valid 10-digit contact number">
                            <button type="button" id="searchCustomer" class="btn-secondary">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                        <div class="form-group">
                            <label for="customer_name">Customer Name:</label>
                            <input type="text" id="customer_name" name="customer_name" value="Cash" required>
                            <input type="hidden" id="customer_id" name="customer_id">
                        </div>
                    </div>

                    <!-- Sales Details -->
                    <div class="form-container">
                        <div class="activity-title">
                            <i class="fa fa-shopping-cart"></i>
                            <span>Sales Details</span>
                        </div>
                        <div class="form-group">
                            <label for="item_id">Item:</label>
                            <select name="item_id" id="item_id" required>
                                <option value="">Select Item</option>
                                <?php
                                $result = $conn->query("
                                    SELECT 
                                        id,
                                        itemname,
                                        sell_price,
                                        stock_quantity
                                    FROM items 
                                    WHERE status = 'Y' 
                                    AND stock_quantity > 0
                                    ORDER BY itemname
                                ");

                                if (!$result) {
                                    die("Database query failed: " . $conn->error);
                                }

                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='{$row['id']}' 
                                                data-price='{$row['sell_price']}'
                                                data-stock='{$row['stock_quantity']}'>
                                            {$row['itemname']} - Stock: {$row['stock_quantity']} - Price: \${$row['sell_price']}
                                          </option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="quantity">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" min="1" required>
                            <span id="stock-warning" class="text-danger" style="display:none;">
                                Exceeds available stock!
                            </span>
                        </div>

                        <div class="form-group">
                            <label for="price">Price:</label>
                            <input type="number" id="price" name="price" step="0.01" readonly>
                        </div>

                        <div class="form-group">
                            <label for="total">Total:</label>
                            <input type="number" id="total" name="total" readonly>
                        </div>

                        <div class="form-group">
                            <label for="discount_percent">Discount (%):</label>
                            <input type="number" id="discount_percent" name="discount_percent" min="0" max="100" value="0">
                        </div>

                        <div class="form-group">
                            <label for="payment_method">Payment Method:</label>
                            <select id="payment_method" name="payment_method" required>
                                <option value="cash">Cash</option>
                                <option value="card">Credit</option>
                                <option value="qrpay">QR Payment</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn-primary">
                                <i class="fa fa-save"></i> Complete Sale
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script src="script.js"></script>
    
    <script>
    $(document).ready(function() {
        let billItems = []; // Array to store bill items

        // Handle item selection
        $('#item_id').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const price = selectedOption.data('price');
            const stock = selectedOption.data('stock');
            
            $('#price').val(price);
            $('#quantity').attr('max', stock).val('');
            $('#total').val('');
            $('#stock-warning').hide();
        });

        // Handle quantity changes
        $('#quantity').on('input', function() {
            calculateTotal();
        });

        // Handle discount changes
        $('#discount_percent').on('input', function() {
            calculateTotal();
        });

        // Search customer by contact
        $('#searchCustomer').on('click', function() {
            const contact = $('#customer_contact').val();
            if (contact) {
                $.get('get_customer.php', { contact: contact })
                    .done(function(response) {
                        if (response.success) {
                            $('#customer_name').val(response.customer.name);
                            $('#customer_id').val(response.customer.id);
                        } else {
                            if (confirm('Customer not found. Would you like to add a new customer?')) {
                                window.location.href = 'add_customer.php?contact=' + contact;
                            }
                        }
                    })
                    .fail(function() {
                        alert('Error searching for customer');
                    });
            }
        });

        // Form submission
        $('#saleForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                customer_id: $('#customer_id').val(),
                customer_name: $('#customer_name').val(),
                customer_contact: $('#customer_contact').val(),
                item_id: $('#item_id').val(),
                quantity: $('#quantity').val(),
                price: $('#price').val(),
                total: $('#total').val(),
                discount_percent: $('#discount_percent').val(),
                payment_method: $('#payment_method').val()
            };

            $.ajax({
                url: 'process_sale.php',
                type: 'POST',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                success: function(response) {
                    if (response.success) {
                        // Open bill in new window
                        const printWindow = window.open('', '_blank');
                        printWindow.document.write(response.bill_html);
                        printWindow.document.close();

                        // Reset form
                        $('#saleForm')[0].reset();
                        $('#customer_name').val('Cash');
                        $('#customer_id').val('');
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error processing sale');
                }
            });
        });

        function calculateTotal() {
            const quantity = $('#quantity').val();
            const price = $('#price').val();
            const discountPercent = $('#discount_percent').val() || 0;
            const stock = $('#item_id').find('option:selected').data('stock');

            if (parseInt(quantity) > parseInt(stock)) {
                $('#stock-warning').show();
                $('#quantity').val(stock);
                return calculateTotal();
            }
            
            $('#stock-warning').hide();
            
            if (quantity && price) {
                const subtotal = quantity * price;
                const discount = (subtotal * discountPercent) / 100;
                const total = subtotal - discount;
                $('#total').val(total.toFixed(2));
            } else {
                $('#total').val('');
            }
        }

        // Add item to bill
        $('#addItem').on('click', function() {
            const itemSelect = $('#item_id');
            const selectedOption = itemSelect.find('option:selected');
            
            if (itemSelect.val() && $('#quantity').val()) {
                const item = {
                    id: itemSelect.val(),
                    name: selectedOption.text().split(' - ')[0],
                    quantity: parseInt($('#quantity').val()),
                    price: parseFloat($('#price').val()),
                    total: parseFloat($('#total').val())
                };

                billItems.push(item);
                updateBillPreview();
                
                // Reset form fields
                itemSelect.val('');
                $('#quantity').val('');
                $('#price').val('');
                $('#total').val('');
            }
        });

        // Generate final bill
        $('#generateBill').on('click', function() {
            if (billItems.length === 0) {
                alert('Please add items to the bill first');
                return;
            }

            const billData = {
                customer_id: $('#customer_id').val(),
                customer_name: $('#customer_name').val(),
                customer_contact: $('#customer_contact').val(),
                items: billItems,
                payment_method: $('#payment_method').val() || 'cash'
            };

            // Send bill data to server
            $.ajax({
                url: 'process_sale.php',
                type: 'POST',
                data: JSON.stringify(billData),
                contentType: 'application/json',
                success: function(response) {
                    if (response.success) {
                        // Open bill in new window
                        const printWindow = window.open('', '_blank');
                        printWindow.document.write(response.bill_html);
                        printWindow.document.close();

                        // Reset form and bill items
                        billItems = [];
                        updateBillPreview();
                        $('#saleForm')[0].reset();
                        $('#customer_name').val('Cash');
                        $('#customer_id').val('');
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Error processing sale. Please try again.');
                }
            });
        });

        // Update bill preview
        function updateBillPreview() {
            const tbody = $('#billItems');
            tbody.empty();
            
            let grandTotal = 0;
            
            billItems.forEach((item, index) => {
                tbody.append(`
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.quantity}</td>
                        <td>$${item.price.toFixed(2)}</td>
                        <td>$${item.total.toFixed(2)}</td>
                        <td>
                            <button type="button" class="btn-delete" onclick="removeItem(${index})">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
                grandTotal += item.total;
            });

            $('#grandTotal').text(grandTotal.toFixed(2));
        }

        // Remove item from bill
        window.removeItem = function(index) {
            billItems.splice(index, 1);
            updateBillPreview();
        };

        // Search customer
        $('#customer_contact').on('blur', function() {
            const contact = $(this).val();
            if (contact) {
                $.get('get_customer.php', { contact: contact })
                    .done(function(response) {
                        if (response.success) {
                            $('#customer_name').val(response.customer.name);
                            $('#customer_id').val(response.customer.id);
                        }
                    });
            }
        });
    });
    </script>
</body>
</html>
