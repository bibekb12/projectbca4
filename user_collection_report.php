<?php
session_start();
include('db.php');

// Set default date to today's date
$default_date = date('Y-m-d');
$from_date = isset($_POST['from_date']) ? $_POST['from_date'] : $default_date;
$to_date = isset($_POST['to_date']) ? $_POST['to_date'] : $default_date;
$selected_user = isset($_POST['user']) ? $_POST['user'] : '';

// Fetch distinct users
$user_result = $conn->query("SELECT DISTINCT username FROM users u 
    JOIN sales s ON u.id = s.user_id");
if (!$user_result) {
    die("Query failed: " . $conn->error);
}
$users = [];
while ($row = $user_result->fetch_assoc()) {
    $users[] = $row['username'];
}

// Fetch collections based on filters
$query = "SELECT u.username AS user_name, DATE(s.sale_date) AS sale_date, SUM(s.net_total) as total 
    FROM sales s
    JOIN users u ON s.user_id = u.id
    WHERE DATE(s.sale_date) ";

$query .= " GROUP BY u.username, DATE(s.sale_date)
            ORDER BY DATE(s.sale_date) DESC";
$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}
$collections = [];
while ($row = $result->fetch_assoc()) {
    $collections[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Collection Report</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="images/inv-logo.png" type="image/icon type">
    <style>
        .filter-form {
            margin-bottom: 20px;
        }
        .filter-form label {
            margin-right: 10px;
        }
        .filter-form input, .filter-form select {
            margin-right: 20px;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
        }
        .report-table th, .report-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .report-table th {
            background-color:rgb(39, 85, 185);
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
            
            <div class="report-result">
                <h3>Total Collection from Sales</h3>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Date</th>
                            <th>Total Collection (Rs.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($collections as $collection): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($collection['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($collection['sale_date']); ?></td>
                                <td><?php echo number_format($collection['total'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <script src="script.js"></script>
</body>
</html>
