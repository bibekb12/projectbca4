<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (isset($_GET['contact'])) {
    $contact = $_GET['contact'];
    
    // Validate contact number format
    if (!preg_match('/^[0-9]{3,10}$/', $contact)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid contact number format'
        ]);
        exit;
    }
    
    $contact = mysqli_real_escape_string($conn, $contact);
    
    $query = "SELECT id, name, contact FROM customers WHERE contact = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $contact);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'customer' => $customer
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Customer not found'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Contact number required'
    ]);
}

$conn->close();
?> 