<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Panel</title>
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
                    <i class="fa fa-shopping-cart"></i>
                    <span class="text">Today's Purchases</span>
                    <span class="number">
                        <?php
                            include('db.php');
                            $today = date('Y-m-d');
                            $result = $conn->query("SELECT SUM(totalamount) as total FROM vw_transaction 
                                WHERE type='Purchase' AND DATE(Date) = '$today'");
                            $row = $result->fetch_assoc();
                            $total = $row['total'];
                            if($total == null) $total = 0;
                            echo '$' . number_format($total, 2);
                        ?>
                    </span>
                </div>
                <div class="box box2">
                    <i class="fa fa-truck"></i>
                    <span class="text">Active Suppliers</span>
                    <span class="number">
                        <?php
                            $result = $conn->query("SELECT COUNT(*) as total FROM suppliers WHERE status='Y'");
                            $row = $result->fetch_assoc();
                            $total = $row['total'];
                            if($total == null) $total = 0;
                            echo number_format($total);
                        ?>
                    </span>
                </div>
                <div class="box box3">
                    <i class="fa fa-cubes"></i>
                    <span class="text">Total Stock Value</span>
                    <span class="number">
                        <?php
                            $result = $conn->query("SELECT SUM(stock_quantity * price) as total FROM items");
                            $row = $result->fetch_assoc();
                            $total = $row['total'];
                            if($total == null) $total = 0;
                            echo '$' . number_format($total, 2);
                        ?>
                    </span>
                </div>
            </div>

            <!-- Purchase Form -->
            <div class="form-container">
                <div class="activity-title">
                    <i class="fa fa-plus-circle"></i>
                    <span>New Purchase</span>
                </div>
                <form method="POST" action="process_purchase.php" class="form-grid">
                    <div class="form-group">
                        <label for="product">Product:</label>
                        <select name="product_id" required>
                            <?php
                            $result = $conn->query("SELECT id, name, price FROM items WHERE status='Y'");
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>{$row['name']} - ${$row['price']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="supplier">Supplier:</label>
                        <select name="supplier" required>
                            <?php
                            $result = $conn->query("SELECT id, name FROM suppliers WHERE status='Y'");
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>{$row['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="costprice">Purchase Price:</label>
                        <input type="number" name="costprice" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity:</label>
                        <input type="number" name="quantity" required min="1">
                    </div>
                    <div class="form-group">
                        <label for="sellprice">Selling Price:</label>
                        <input type="number" name="sellprice" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-primary">
                            <i class="fa fa-save"></i> Add Purchase
                        </button>
                    </div>
                </form>
            </div>

            <!-- Purchase Table -->
            <div class="table-container">
                <div class="activity-title">
                    <i class="uil uil-clock-three"></i>
                    <span>Recent Purchases</span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>SN</th>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Supplier</th>
                            <th>Quantity</th>
                            <th>Cost Price</th>
                            <th>Total</th>
                            <th>Selling Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM vw_transaction WHERE type='Purchase' ORDER BY Date DESC");
                        if($result) {
                            $sn = 1;
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                        <td>{$sn}</td>
                                        <td>" . date('Y-m-d H:i', strtotime($row['Date'])) . "</td>
                                        <td>{$row['name']}</td>
                                        <td>{$row['suppliername']}</td>
                                        <td>{$row['quantity']}</td>
                                        <td>\${$row['costprice']}</td>
                                        <td>\${$row['totalamount']}</td>
                                        <td>\${$row['sellprice']}</td>
                                        <td>
                                            <form method='POST' action='process_purchase.php' style='display:inline;'>
                                                <input type='hidden' name='delete_id' value='{$row['id']}'>
                                                <button type='submit' name='delete' class='btn-delete' onclick='return confirm(\"Are you sure?\")'>
                                                    <i class='fa fa-trash'></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>";
                                $sn++;
                            }
                        } else {
                            echo "<tr><td colspan='9'>No purchases found</td></tr>";
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