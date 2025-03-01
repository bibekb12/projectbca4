<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include('db.php'); // Ensure this file exists and is correct

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Panel</title>
    <link rel="icon" href="images/inv-logo.png" type="image/icon type">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .form-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin: 20px;
            margin-left: 270px;
            width: calc(100% - 290px);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 5px;
            color: #495057;
            font-weight: 600;
        }

        .form-group input, 
        .form-group select {
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-group input:focus, 
        .form-group select:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .form-submit {
            grid-column: span 2;
            display: flex;
            justify-content: flex-end;
            margin-top: 15px;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background-color 0.2s;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        @media (max-width: 768px) {
            .form-grid,
            .form-row {
                grid-template-columns: 1fr;
            }

            .form-submit {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>
    <?php include('includes/sidebar.php'); ?>

    <section class="dashboard">
        <div class="top">
            <div class="user-greeting">
                <i class="uil uil-user-circle"></i>
                <span>Welcome, <span class="username"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?></span></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        <div class="dash-content">
            <?php if ($is_admin): ?>
            <!-- Summary Boxes -->
            <div class="boxes">
                <div class="box box1">
                    <i class="fa fa-shopping-cart"></i>
                    <span class="text">Today's Purchases</span>
                    <span class="number">
                        <?php
                            $today = date('Y-m-d');
                            $query = "SELECT SUM(total_amount) as total FROM purchases 
                                WHERE DATE(purchase_date) = '$today'";
                            $result = $conn->query($query);
                            if ($result) {
                                $row = $result->fetch_assoc();
                                echo $row['total'] ? 'Rs. ' . number_format($row['total'], 2) : 'Rs. 0.00';
                            } else {
                                echo 'Rs. 0.00';
                            }
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
                            $total = isset($row['total']) ? $row['total'] : 0; // Use ternary operator
                            echo number_format($total);
                        ?>
                    </span>
                </div>
                <div class="box box3">
                    <i class="fa fa-cubes"></i>
                    <span class="text">Total Stock Value</span>
                    <span class="number">
                        <?php
                            $result = $conn->query("SELECT SUM(stock_quantity * sell_price) as total FROM items");
                            if (!$result) {
                                die("Database query failed: " . $conn->error);
                            }
                            $row = $result->fetch_assoc();
                            $total = isset($row['total']) ? $row['total'] : 0;
                            echo 'Rs. ' . number_format($total, 2);
                        ?>
                    </span>
                </div>
            </div>
            <?php endif ?>

            <!-- Purchase Form -->
            <div class="form-container">
                <div class="activity-title">
                    <i class="fa fa-plus-circle"></i>
                    <span>New Purchase</span>
                </div>
                <form method="POST" action="process_purchase.php" class="form-grid">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="product">Product:</label>
                            <select name="product_id" required>
                                <?php
                                $result = $conn->query("SELECT id, itemname as name, sell_price FROM items WHERE status='Y'");
                                if (!$result) {
                                    die("Database query failed: " . $conn->error);
                                }
                                
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='{$row['id']}'>{$row['name']} </option>";
                                    }
                                } else {
                                    echo "<option value=''>No active items available</option>";
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
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="supplier_bill_number">Supplier Bill No:</label>
                            <input type="text" name="supplier_bill_number" placeholder="Bill Number" required>
                        </div>
                        <div class="form-group">
                            <label for="costprice">Purchase Price:</label>
                            <input type="number" name="costprice" step="0.01" placeholder="Cost Price" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="quantity">Quantity:</label>
                            <input type="number" name="quantity" placeholder="Quantity" required min="1">
                        </div>
                        <div class="form-group">
                            <label for="sellprice">Selling Price:</label>
                            <input type="number" name="sellprice" step="0.01" placeholder="Selling Price" required>
                        </div>
                    </div>
                    <div class="form-group form-submit">
                        <button type="submit" class="btn-primary" style="margin: 0 auto;">
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
                        $result = $conn->query("
                            SELECT 
                                p.id,
                                p.purchase_date as Date,
                                i.itemname as name,
                                s.name as suppliername,
                                pi.quantity,
                                pi.price as costprice,
                                (pi.quantity * pi.price) as totalamount,
                                i.sell_price as sellprice
                            FROM purchases p
                            JOIN purchase_items pi ON p.id = pi.purchase_id
                            JOIN items i ON pi.item_id = i.id
                            JOIN suppliers s ON p.supplier_id = s.id
                            ORDER BY p.purchase_date DESC
                        ");

                        if (!$result) {
                            die("Database query failed: " . $conn->error);
                        }

                        if ($result->num_rows > 0) {
                            $sn = 1;
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                        <td>{$sn}</td>
                                        <td>" . date('Y-m-d H:i', strtotime($row['Date'])) . "</td>
                                        <td>{$row['name']}</td>
                                        <td>{$row['suppliername']}</td>
                                        <td>{$row['quantity']}</td>
                                        <td>Rs. {$row['costprice']}</td>
                                        <td>Rs. {$row['totalamount']}</td>
                                        <td>Rs. {$row['sellprice']}</td>
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