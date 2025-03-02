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
    <link rel="stylesheet" href="includes/css/sale.css">

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
                <p>Date: <?php echo $today; ?> <span id="currentTime"></span></p>
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
                <span class="date-filter">Today: <?php echo $today; ?></span>
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

    <script src="includes/js/sale.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script> -->
</body>
</html>
