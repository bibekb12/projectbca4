<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Fetch items from the database
$query = "SELECT id, itemcode, itemname, sell_price, stock_quantity FROM items WHERE status = 'active' AND stock_quantity > 0";
$result = $conn->query($query);

$items = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}

// Return items as JSON
header('Content-Type: application/json');
echo json_encode(['success' => true, 'items' => $items]);

$conn->close();
?>
