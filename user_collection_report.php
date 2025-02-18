<?php
session_start();
include('db.php');

// Set default date to today's date
$default_date = date('Y-m-d');
$from_date = isset($_POST['from_date']) ? $_POST['from_date'] : $default_date;
$to_date = isset($_POST['to_date']) ? $_POST['to_date'] : $default_date;
$selected_user = isset($_POST['user']) ? $_POST['user'] : '';

// Fetch users for the user filter dropdown
$user_result = $conn->query("SELECT DISTINCT username FROM users u 
    JOIN sales s ON u.id = s.user_id");
if (!$user_result) {
    die("Query failed: " . $conn->error);
}
$users = [];
while ($row = $user_result->fetch_assoc()) {
    $users[] = $row['username'];
}

// Fetch total collection based on filters
$query = "SELECT u.username AS user_name, SUM(s.total_amount) as total 
    FROM sales s
    JOIN users u ON s.user_id = u.id
    WHERE DATE(s.sale_date) BETWEEN '$from_date' AND '$to_date'";
if ($selected_user) {
    $query .= " AND u.username = '$selected_user'";
}
$query .= " GROUP BY u.username";
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
            background-color: #f2f2f2;
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
            <div class="filter-form">
                <form method="POST" action="">
                    <label for="from_date">From Date:</label>
                    <input type="date" id="from_date" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>">
                    
                    <label for="to_date">To Date:</label>
                    <input type="date" id="to_date" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>">
                    
                    <label for="user">User:</label>
                    <select id="user" name="user">
                        <option value="">All Users</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo htmlspecialchars($user); ?>" <?php echo $selected_user == $user ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit">Filter</button>
                </form>
            </div>
            <div class="report-result">
                <h3>Total Collection from Sales</h3>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Total Collection (Rs.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($collections as $collection): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($collection['user_name']); ?></td>
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
