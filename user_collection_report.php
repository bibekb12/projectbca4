<?php
session_start();
include('db.php');

// Set default date to today's date
$default_date = date('Y-m-d');
$selected_date = isset($_POST['date']) ? $_POST['date'] : $default_date;
$selected_user = isset($_POST['user']) ? $_POST['user'] : '';

// Fetch users for the user filter dropdown
$user_result = $conn->query("SELECT DISTINCT username FROM users");
if (!$user_result) {
    die("Query failed: " . $conn->error);
}
$users = [];
while ($row = $user_result->fetch_assoc()) {
    $users[] = $row['username'];
}

// Fetch total collection based on filters
$query = "SELECT SUM(totalamount) as total FROM vw_transaction WHERE type='Sale' AND DATE(Date) = '$selected_date'";
if ($selected_user) {
    $query .= " AND username = '$selected_user'";
}
$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}
$row = $result->fetch_assoc();
$total_collection = isset($row['total']) ? $row['total'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Collection Report</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include('includes/sidebar.php'); ?>
    
    <section class="dashboard">
        <div class="top">
            <i class="uil uil-bars sidebar-toggle"></i>
            <div class="user-greeting">
                <i class="uil uil-user-circle"></i>
                <span>Welcome, <span class="username"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?></span></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        <div class="tabs">
            <!-- Remove the user collection tab -->
            <!-- <a href="user_collection_report.php" class="tab active">User Collection</a> -->
            <!-- Add other tabs here -->
        </div>
        <div class="dash-content">
            <div class="filter-form">
                <form method="POST" action="">
                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($selected_date); ?>">
                    
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
                <p>Rs. <?php echo number_format($total_collection, 2); ?></p>
            </div>
        </div>
    </section>

    <script src="script.js"></script>
</body>
</html>
