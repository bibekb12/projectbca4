<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <?php include('includes/sidebar.php'); ?>
    
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
                    <span class="text">Total Sales</span>
                    <span class="number">
                        <?php
                            include('db.php');
                            $result = $conn->query("SELECT SUM(totalamount) as total FROM vw_transaction WHERE type='Sale'");
                            $row = $result->fetch_assoc();
                            $total = isset($row['total']) ? $row['total'] : 0;
                            echo '$' . number_format($total, 2);
                        ?>
                    </span>
                </div>
                <div class="box box2">
                    <i class="fa fa-shopping-cart"></i>
                    <span class="text">Total Purchases</span>
                    <span class="number">
                        <?php
                            $result = $conn->query("SELECT SUM(totalamount) as total FROM vw_transaction WHERE type='Purchase'");
                            $row = $result->fetch_assoc();
                            $total = isset($row['total']) ? $row['total'] : 0;
                            echo '$' . number_format($total, 2);
                        ?>
                    </span>
                </div>
                <div class="box box3">
                    <i class="fa fa-line-chart"></i>
                    <span class="text">Net Profit</span>
                    <span class="number">
                        <?php
                            $result = $conn->query("SELECT 
                                (SELECT SUM(totalamount) FROM vw_transaction WHERE type='Sale') -
                                (SELECT SUM(totalamount) FROM vw_transaction WHERE type='Purchase') as profit");
                            $row = $result->fetch_assoc();
                            $profit = isset($row['profit']) ? $row['profit'] : 0;
                            echo '$' . number_format($profit, 2);
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
                        $result = $conn->query("
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
                        ");

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
                                    <td>\${$row['price']}</td>
                                    <td class='{$status_class}'>{$row['stock_quantity']}</td>
                                    <td>\$" . number_format($row['stock_value'], 2) . "</td>
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
                        $result = $conn->query("SELECT 
                            t.Date as transaction_date,
                            t.type,
                            t.name as product_name,
                            t.quantity,
                            t.totalamount,
                            t.username
                            FROM vw_transaction t
                            ORDER BY t.Date DESC
                            LIMIT 10");
                        
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
                                        <td>\$" . number_format($row['totalamount'], 2) . "</td>
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
        </div>
    </section>

    <script src="script.js"></script>
</body>
</html>