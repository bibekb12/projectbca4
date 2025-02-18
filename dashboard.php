<?php
session_start();
include('db.php');

// Check if user is logged in
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

    // Check if transactions table exists
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
    <style>
        .invextry-dashboard {
            background-color: #f4f7fa;
            padding: 30px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .header-content h1 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .breadcrumb {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            font-size: 14px;
        }

        .breadcrumb-item {
            display: flex;
            align-items: center;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: '/';
            margin: 0 10px;
            color: #95a5a6;
        }

        .breadcrumb-item a {
            color: #7f8c8d;
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: #3498db;
        }

        .date-range-picker {
            position: relative;
        }

        .date-range-picker input {
            padding: 8px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
        }

        .date-range-picker i {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
        }

        .dashboard-stats {
            margin-bottom: 30px;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            display: flex;
            align-items: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            color: white;
            font-size: 24px;
        }

        .sales-card .stat-icon { background-color: #3498db; }
        .inventory-card .stat-icon { background-color: #2ecc71; }
        .suppliers-card .stat-icon { background-color: #e74c3c; }

        .stat-content {
            flex-grow: 1;
        }

        .stat-content h4 {
            color: #7f8c8d;
            margin-bottom: 10px;
            font-size: 16px;
            text-transform: uppercase;
        }

        .stat-value {
            font-size: 24px;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .stat-trend {
            display: flex;
            align-items: center;
            font-size: 14px;
        }

        .trend-icon {
            margin-right: 5px;
            font-size: 16px;
        }

        .trend-icon.up { color: #2ecc71; }
        .trend-icon.down { color: #e74c3c; }

        .dashboard-reports {
            margin-bottom: 30px;
        }

        .report-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .report-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .report-header h3 {
            color: #2c3e50;
            font-size: 18px;
        }

        .chart-filter {
            padding: 5px 10px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
        }

        .dashboard-quick-actions {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .quick-actions-header h3 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }

        .quick-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-color: #f4f7fa;
            border-radius: 10px;
            text-decoration: none;
            color: #2c3e50;
            transition: background-color 0.3s ease;
        }

        .quick-action-btn:hover {
            background-color: #e9f0f4;
        }

        .quick-action-btn i {
            font-size: 24px;
            margin-bottom: 10px;
            color: #3498db;
        }

        .quick-action-btn span {
            font-size: 14px;
            text-align: center;
        }

        @media screen and (max-width: 768px) {
            .stat-grid,
            .report-section,
            .quick-actions-grid {
                grid-template-columns: 1fr;
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
                <div class="header-actions">
                    <div class="date-range-picker">
                        <input type="text" id="daterange" placeholder="Select Date Range">
                        <i class="uil uil-calendar-alt"></i>
                    </div>
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
                                $sales_query = "SELECT SUM(amount) as total_sales FROM transactions WHERE type='Sale'";
                                $sales_result = $conn->query($sales_query);
                                $total_sales = $sales_result ? $sales_result->fetch_assoc()['total_sales'] : 0;
                                echo 'Rs. ' . number_format($total_sales, 2);
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
                            <div class="report-actions">
                                <select class="chart-filter">
                                    <option>Monthly</option>
                                    <option>Quarterly</option>
                                    <option>Yearly</option>
                                </select>
                            </div>
                        </div>
                        <canvas id="salesOverviewChart"></canvas>
                    </div>

                    <div class="report-card inventory-chart">
                        <div class="report-header">
                            <h3>Inventory Distribution</h3>
                            <div class="report-actions">
                                <select class="chart-filter">
                                    <option>By Category</option>
                                    <option>By Location</option>
                                </select>
                            </div>
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

        modeToggle.addEventListener("click", () => {
            body.classList.toggle("dark");
            if(body.classList.contains("dark")) {
                localStorage.setItem("mode", "dark");
            } else {
                localStorage.setItem("mode", "light");
            }
        });
    </script>
</body>
</html>
