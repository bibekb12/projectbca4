<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="icon" href="images/inv-logo.png" type="image/icon type">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <?php include('includes/sidebar.php'); ?>
    
    <section class="dashboard">
    <div class="top">
            <div class="search-box">
                <!-- Search box content -->
            </div>
            <div class="user-greeting">
                <i class="uil uil-user-circle"></i>
                <span>Welcome, <span class="username"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?></span></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        <div class="dash-content">
            <!-- Summary Boxes -->
            <div class="boxes">
                <div class="box box1">
                    <i class="fa fa-money"></i>
                    <span class="text">Total Sales</span>
                    <span class="number">
                        <?php
                            require('db.php');
                            $query = "SELECT SUM(net_total) as total FROM sales ";
                            $result = $conn->query($query);
                            if ($result) {
                                $row = $result->fetch_assoc();
                                $total = isset($row['total']) ? $row['total'] : 0;
                                echo 'Rs. ' . number_format($total, 2);
                            } else {
                                echo 'Rs. 0.00';
                            }
                        ?>
                    </span>
                </div>
                <div class="box box2">
                    <i class="fa fa-shopping-cart"></i>
                    <span class="text">Total Purchases</span>
                    <span class="number">
                        <?php
                            require('db.php');
                            $query = "SELECT SUM(total_amount) as total from purchases ";
                            $result = $conn->query($query);
                            if ($result) {
                                $row = $result->fetch_assoc();
                                $total = isset($row['total']) ? $row['total'] : 0;
                                echo 'Rs. ' . number_format($total, 2);
                            
                            }
                        ?>
                    </span>
                </div>
                <div class="box box3">
                    <i class="fa fa-line-chart"></i>
                    <span class="text">Net Inventory</span>
                    <span class="number">
                        <?php
                        require('db.php');
                            $query = "SELECT 
                                (SELECT SUM(total_amount) from purchases )-
                                (SELECT SUM(net_total) as total FROM sales ) as profit";
                            $result = $conn->query($query);
                            if ($result) {
                                $row = $result->fetch_assoc();
                                $profit = isset($row['profit']) ? $row['profit'] : 0;
                                echo 'Rs. ' . number_format($profit, 2);
                            }
                        ?>
                    </span>
                </div>
            </div>

            <!-- Inventory Status -->
            <div class="table-container">
                <div class="activity-title">
                    <i class="fa fa-cubes"></i>
                    <span>Inventory Status</span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>SN</th>
                            <th>Product</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Stock Quantity</th>
                            <th>Stock Value</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include('db.php');
                        $query = "
                            SELECT 
                                id,
                                itemname as name,
                                description,
                                sell_price as price,
                                stock_quantity,
                                (stock_quantity * sell_price) as stock_value,
                                status
                            FROM items
                            ORDER BY itemname
                        ";

                        $result = $conn->query($query);

                        if (!$result) {
                            die("Database query failed: " . $conn->error);
                        }

                        $sn = 1;
                        while ($row = $result->fetch_assoc()) {
                            $status_class = $row['stock_quantity'] < 10 ? 'text-danger' : 'text-success';
                            $status_text = $row['stock_quantity'] < 10 ? 'Low Stock' : 'In Stock';
                            
                            echo "<tr>
                                    <td>{$sn}</td>
                                    <td>{$row['name']}</td>
                                    <td>{$row['description']}</td>
                                    <td>Rs. {$row['price']}</td>
                                    <td class='{$status_class}'>{$row['stock_quantity']}</td>
                                    <td>Rs. " . number_format($row['stock_value'], 2) . "</td>
                                    <td class='{$status_class}'>{$status_text}</td>
                                </tr>";
                            $sn++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent Transactions -->
            <div class="table-container">
                <div class="activity-title">
                    <i class="fa fa-history"></i>
                    <span>Recent Transactions</span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>SN</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Amount</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT 
        s.id,
        s.sale_date AS transaction_date,
        'Sale' AS type,
        i.itemname AS product_name,
        si.quantity,
        (si.quantity * si.price) AS totalamount,
        u.username
    FROM sales s
    JOIN sale_items si ON s.id = si.sale_id
    JOIN items i ON si.item_id = i.id
    JOIN users u ON s.user_id = u.id
    UNION
    SELECT 
        p.id,
        p.purchase_date AS transaction_date,
        'Purchase' AS type,
        i.itemname AS product_name,
        pi.quantity,
        (pi.quantity * pi.price) AS totalamount,
        u.username
    FROM purchases p
    JOIN purchase_items pi ON p.id = pi.purchase_id
    JOIN items i ON pi.item_id = i.id
    JOIN users u ON p.user_id = u.id
    ORDER BY transaction_date DESC
    LIMIT 10
";
                        
                        $result = $conn->query($query);
                        
                        if ($result) {
                            $sn = 1; // Reset serial number for second table
                            while ($row = $result->fetch_assoc()) {
                                $type_class = $row['type'] == 'Sale' ? 'text-success' : 'text-primary';
                                echo "<tr>
                                        <td>{$sn}</td>
                                        <td>" . date('Y-m-d H:i', strtotime($row['transaction_date'])) . "</td>
                                        <td class='{$type_class}'>{$row['type']}</td>
                                        <td>{$row['product_name']}</td>
                                        <td>{$row['quantity']}</td>
                                        <td>Rs. " . number_format($row['totalamount'], 2) . "</td>
                                        <td>{$row['username']}</td>
                                    </tr>";
                                $sn++; // Increment serial number
                            }
                        } else {
                            echo "<tr><td colspan='7'>No transactions found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Add link to User Collection Report if admin -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div class="tabs">
                <a href="user_collection_report.php" class="tab" >User Collection</a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <script src="script.js"></script>
</body>
</html>