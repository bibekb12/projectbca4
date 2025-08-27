<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Initialize variables with default values
$monthly_sales = 0;
$monthly_expenses = 0;
$monthly_profit = 0;
$stock_data = array('low_stock' => 0, 'total_items' => 0);
$sales_trend_data = array();

// Get current month
$current_month = date('Y-m');

// Debug information
$debug_info = array();

try {
    // Test database connection
    if ($conn->connect_error) {
        $debug_info[] = "Database connection failed: " . $conn->connect_error;
    } else {
        $debug_info[] = "Database connection successful";
    }

    $check_transactions = $conn->query("SHOW TABLES LIKE 'transactions'");
    if ($check_transactions && $check_transactions->num_rows > 0) {
        $debug_info[] = "Transactions table exists";
    } else {
        $debug_info[] = "Transactions table does not exist";
    }

    // Check if items table exists
    $check_items = $conn->query("SHOW TABLES LIKE 'items'");
    if ($check_items && $check_items->num_rows > 0) {
        $debug_info[] = "Items table exists";
    } else {
        $debug_info[] = "Items table does not exist";
    }

    // Simple sales query
    $sales_query = "SELECT COUNT(*) as count FROM transactions WHERE type = 'Sale'";
    $sales_result = $conn->query($sales_query);
    if ($sales_result) {
        $sales_count = $sales_result->fetch_assoc()['count'];
        $debug_info[] = "Found {$sales_count} sales transactions";
    } else {
        $debug_info[] = "Error in sales query: " . $conn->error;
    }

    // Simple items query
    $items_query = "SELECT COUNT(*) as count FROM items WHERE status = 'Y'";
    $items_result = $conn->query($items_query);
    if ($items_result) {
        $items_count = $items_result->fetch_assoc()['count'];
        $debug_info[] = "Found {$items_count} active items";
    } else {
        $debug_info[] = "Error in items query: " . $conn->error;
    }

} catch (Exception $e) {
    $debug_info[] = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S I M S</title>
    <link rel="icon" href="images/inv-logo.png" type="image/icon type">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="includes/css/dashboard.css">
</head>
<body>
    <?php include('includes/sidebar.php'); ?>

    <section class="dashboard">
        <div class="top">Dashboard
        <div class="user-greeting">
            <i class="uil uil-user-circle"></i>
            <span>Welcome, <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        </div>

        <div class="dash-content invextry-dashboard">
            <div class="dashboard-header">
                <div class="header-content">
                    <h1>Dashboard Monthly Overview</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="dashboard-stats">
                <div class="stat-grid">
                    <div class="stat-card sales-card">
                        <div class="stat-icon">
                            <i class="uil uil-shopping-cart-alt"></i>
                        </div>
                        <div class="stat-content">
                            <h4>Total Sales</h4>
                            <div class="stat-value">
                                <?php
                                $sales_query = "SELECT SUM(net_total) as total_sales FROM sales ";
                                $sales_result = $conn->query($sales_query);
                                $total_sales = $sales_result ? $sales_result->fetch_assoc()['total_sales'] : 0;
                                $total_sales = $total_sales ?? 0;
                                echo 'Rs. ' . number_format((float)$total_sales, 2);
                                ?>
                            </div>
                            <div class="stat-trend">
                                <span class="trend-icon up">
                                    <i class="uil uil-arrow-up"></i> 15.5%
                                </span>
                                <span class="trend-period">Since last month</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card inventory-card">
                        <div class="stat-icon">
                            <i class="uil uil-box"></i>
                        </div>
                        <div class="stat-content">
                            <h4>Total Inventory</h4>
                            <div class="stat-value">
                                <?php
                                $inventory_query = "SELECT COUNT(*) as total_items FROM items WHERE status='Y'";
                                $inventory_result = $conn->query($inventory_query);
                                $total_items = $inventory_result ? $inventory_result->fetch_assoc()['total_items'] : 0;
                                echo number_format($total_items);
                                ?>
                            </div>
                            <div class="stat-trend">
                                <span class="trend-icon down">
                                    <i class="uil uil-arrow-down"></i> 3.5%
                                </span>
                                <span class="trend-period">Since last week</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card suppliers-card">
                        <div class="stat-icon">
                            <i class="uil uil-truck"></i>
                        </div>
                        <div class="stat-content">
                            <h4>Active Suppliers</h4>
                            <div class="stat-value">
                                <?php
                                $suppliers_query = "SELECT COUNT(*) as total_suppliers FROM suppliers WHERE status='Y'";
                                $suppliers_result = $conn->query($suppliers_query);
                                $total_suppliers = $suppliers_result ? $suppliers_result->fetch_assoc()['total_suppliers'] : 0;
                                echo number_format($total_suppliers);
                                ?>
                            </div>
                            <div class="stat-trend">
                                <span class="trend-icon up">
                                    <i class="uil uil-arrow-up"></i> 5.2%
                                </span>
                                <span class="trend-period">Since last quarter</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-reports">
                <div class="report-section">
                    <div class="report-card sales-chart">
                        <div class="report-header">
                            <h3>Sales Overview</h3>
                        </div>
                        <canvas id="salesOverviewChart"></canvas>
                    </div>

                    <div class="report-card inventory-chart">
                        <div class="report-header">
                            <h3>Inventory Distribution</h3>
                        </div>
                        <canvas id="inventoryDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        <?php if ($is_admin): ?>
            <div class="dashboard-quick-actions">
                <div class="quick-actions-header">
                    <h3>Quick Actions</h3>
                </div>
                <div class="quick-actions-grid">
                    <a href="purchase.php" class="quick-action-btn">
                        <i class="uil uil-shopping-basket"></i>
                        <span>New Purchase</span>
                    </a>
                    <a href="sale.php" class="quick-action-btn">
                        <i class="uil uil-money-bill"></i>
                        <span>Create Sale</span>
                    </a>
                    <a href="itemsetup.php" class="quick-action-btn">
                        <i class="uil uil-box"></i>
                        <span>Manage Items</span>
                    </a>
                    <a href="suppliersetup.php" class="quick-action-btn">
                        <i class="uil uil-truck"></i>
                        <span>Manage Suppliers</span>
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="dashboard-quick-actions">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>All Items Sold</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch all distinct items sold
                        $sold_items_query = $conn->query("SELECT i.itemname, SUM(si.quantity) as total_quantity 
                            FROM sale_items si 
                            JOIN items i ON si.item_id = i.id 
                            GROUP BY i.itemname 
                            ORDER BY total_quantity DESC");

                        if ($sold_items_query === false) {
                            echo "<tr><td colspan='2'>Error fetching sold items: " . $conn->error . "</td></tr>";
                        } elseif ($sold_items_query->num_rows === 0) {
                            echo "<tr><td colspan='2'>No items sold found.</td></tr>";
                        } else {
                            while ($item = $sold_items_query->fetch_assoc()) {
                                echo "<tr>
                                    <td>" . htmlspecialchars($item['itemname']) . "</td>
                                    <td>" . htmlspecialchars($item['total_quantity']) . "</td>
                                </tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
                <table>
                    <thead>
                        <tr>
                            <th>All Suppliers</th>
                            <th>Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch all active suppliers
                        $suppliers_query = $conn->query("SELECT name, contact 
                            FROM suppliers 
                            WHERE status = 'Y' 
                            ORDER BY name ASC");

                        if ($suppliers_query === false) {
                            echo "<tr><td colspan='2'>Error fetching suppliers: " . $conn->error . "</td></tr>";
                        } elseif ($suppliers_query->num_rows === 0) {
                            echo "<tr><td colspan='2'>No active suppliers found.</td></tr>";
                        } else {
                            while ($supplier = $suppliers_query->fetch_assoc()) {
                                echo "<tr>
                                    <td>" . htmlspecialchars($supplier['name']) . "</td>
                                    <td>" . htmlspecialchars($supplier['contact']) . "</td>
                                </tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Sales Overview Chart
        const salesCtx = document.getElementById('salesOverviewChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Sales',
                    data: [12, 19, 3, 5, 2, 3],
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.2)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // Inventory Distribution Chart
        const inventoryCtx = document.getElementById('inventoryDistributionChart').getContext('2d');
        new Chart(inventoryCtx, {
            type: 'pie',
            data: {
                labels: ['Keyboard', 'Mouse', 'Cooling Fan', 'Power Cable'],
                datasets: [{
                    data: [30, 25, 20, 25],
                    backgroundColor: ['#3498db', '#2ecc71', '#e74c3c', '#f39c12']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>

    <script>
        const body = document.querySelector("body"),
        sidebar = body.querySelector("nav"),
        modeToggle = body.querySelector(".mode-toggle");

        let getMode = localStorage.getItem("mode");
        if(getMode && getMode === "dark") {
            body.classList.toggle("dark");
        }

        let getStatus = localStorage.getItem("status");
        if(getStatus && getStatus === "close") {
            sidebar.classList.toggle("close");
        }
    </script>
</body>
</html>
