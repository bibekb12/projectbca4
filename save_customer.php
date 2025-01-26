<?php
include 'db.php';

function saveCustomer($name, $contact) {
    global $conn;
    
    // Check if customer already exists
    $check = $conn->prepare("SELECT id FROM customers WHERE contact = ?");
    $check->bind_param("s", $contact);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
        return $customer['id'];
    }
    
    // Insert new customer
    $stmt = $conn->prepare("INSERT INTO customers (name, contact) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $contact);
    
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    
    return null;
}
?> 