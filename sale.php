<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Panel</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <nav>
        <div class="logo-name">
            <div class="logo-image">
               <img src="images/logo.png" alt="">
            </div>
            <span class="logo_name">Inventory Management</span>
        </div>
        <div class="menu-items">
            <ul class="nav-links">
                <li><a href="#">
                    <i class="uil uil-estate"></i>
                    <span class="link-name">Dahsboard</span>
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
                <li> <a href="setup.php">
                    <i class="uil uil-setting"></i>
                    <span class="link-name"> Setup </span> 
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
            <!-- Summary Boxes -->
            <div class="boxes">
                <div class="box box1">
                    <i class="fa fa-money"></i>
                    <span class="text">Today's Sales</span>
                    <span class="number">
                        <?php
                            include('db.php');
                            $today = date('Y-m-d');
                            $result = $conn->query("SELECT SUM(total_amount) as total 
                                FROM sales 
                                WHERE DATE(sale_date) = '$today'");
                            if ($result) {
                                $row = $result->fetch_assoc();
                                $total = $row['total'];
                                if($total == null) $total = 0;
                                echo '$' . number_format($total, 2);
                            } else {
                                echo '$0.00';
                            }
                        ?>
                    </span>
                </div>
                <div class="box box2">
                    <i class="fa fa-users"></i>
                    <span class="text">Today's Customers</span>
                    <span class="number">
                        <?php
                            $result = $conn->query("SELECT COUNT(DISTINCT customer_id) as total 
                                FROM sales 
                                WHERE DATE(sale_date) = '$today'");
                            if ($result) {
                                $row = $result->fetch_assoc();
                                $total = $row['total'];
                                if($total == null) $total = 0;
                                echo number_format($total);
                            } else {
                                echo '0';
                            }
                        ?>
                    </span>
                </div>
                <div class="box box3">
                    <i class="fa fa-shopping-cart"></i>
                    <span class="text">Total Items Sold</span>
                    <span class="number">
                        <?php
                            $result = $conn->query("SELECT SUM(quantity) as total 
                                FROM sales 
                                WHERE DATE(sale_date) = '$today'");
                            if ($result) {
                                $row = $result->fetch_assoc();
                                $total = $row['total'];
                                if($total == null) $total = 0;
                                echo number_format($total);
                            } else {
                                echo '0';
                            }
                        ?>
                    </span>
                </div>
            </div>

            <!-- Sales Form -->
            <div class="form-container">
                <div class="activity-title">
                    <i class="fa fa-user"></i>
                    <span>Customer Details</span>
                </div>
                <form id="customerForm" class="form-grid">
                    <div class="form-group">
                        <label for="customer_contact">Contact:</label>
                        <input type="text" id="customer_contact" name="customer_contact" required>
                    </div>
                    <div class="form-group">
                        <label for="customer_name">Customer Name:</label>
                        <input type="text" id="customer_name" name="customer_name" value="Cash" required>
                    </div>
                </form>
            </div>

            <!-- Modify your sales form -->
            <div class="form-container">
                <div class="activity-title">
                    <i class="fa fa-shopping-cart"></i>
                    <span>Sales Bill</span>
                </div>
                <form id="saleForm" method="POST" action="process_sale.php" class="form-grid">
                    <input type="hidden" id="customer_id" name="customer_id">
                    <div class="form-group">
                        <label for="item">Item:</label>
                        <select name="item_id" id="item_select" required>
                            <option value="">Select Item</option>
                            <?php
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
                        <label for="price">Selling Price:</label>
                        <input type="number" id="price" name="price" step="0.01" required readonly>
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

            <!-- Sales Table -->
            <div class="table-container">
                <div class="activity-title">
                    <i class="uil uil-clock-three"></i>
                    <span>Recent Sales</span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>SN</th>
                            <th>Date</th>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT s.*, i.name as item_name, i.sell_price, u.username 
                                 FROM sales s 
                                 JOIN items i ON s.item_id = i.id 
                                 JOIN users u ON s.user_id = u.id 
                                 ORDER BY s.sale_date DESC 
                                 LIMIT 10";
                        $result = $conn->query($query);
                        if($result) {
                            $sn = 1;
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                        <td>{$sn}</td>
                                        <td>" . date('Y-m-d H:i', strtotime($row['sale_date'])) . "</td>
                                        <td>{$row['item_name']}</td>
                                        <td>{$row['quantity']}</td>
                                        <td>\${$row['sell_price']}</td>
                                        <td>\${$row['total_amount']}</td>
                                        <td>{$row['username']}</td>
                                    </tr>";
                                $sn++;
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <script src="script.js"></script>
</body>
</html>
