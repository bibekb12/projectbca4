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
    <title>Stock Report</title>
    <link rel="icon" href="images/inv-logo.png" type="image/icon type">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/includes/css/stock.css">
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
                            if ($row['stock_quantity'] == 0) {
                                $status_text = 'Out of Stock';
                            } elseif ($row['stock_quantity'] < 10) {
                                $status_text = 'Low Stock';
                            } else {
                                $status_text = 'In Stock';
                            }
                            
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
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pM8ODewa9r" crossorigin="anonymous"></script>
</body>
</html>
