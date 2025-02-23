<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('HTTP/1.1 403 Forbidden');
    exit(json_encode(['error' => 'Unauthorized access']));
}

include('db.php');

// Validate input
if (!isset($_POST['sale_id']) || !is_numeric($_POST['sale_id'])) {
    header('HTTP/1.1 400 Bad Request');
    exit(json_encode(['error' => 'Invalid sale ID']));
}

$sale_id = intval($_POST['sale_id']);

// Fetch bill file from database
$bill_query = $conn->prepare("SELECT bill_file FROM sales WHERE id = ?");
$bill_query->bind_param('i', $sale_id);
$bill_query->execute();
$result = $bill_query->get_result();

if ($result->num_rows === 0) {
    header('HTTP/1.1 404 Not Found');
    exit(json_encode(['error' => 'Bill not found']));
}

$sale = $result->fetch_assoc();
$bill_file = $sale['bill_file'];

// Validate bill file exists
if (!$bill_file || !file_exists($bill_file)) {
    header('HTTP/1.1 404 Not Found');
    exit(json_encode(['error' => 'Bill file not found']));
}

// Return bill file path
header('Content-Type: application/json');
echo json_encode(['bill_file' => $bill_file]);
exit();
