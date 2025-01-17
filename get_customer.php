<?php
include 'db.php';

header('Content-Type: application/json');

if (isset($_GET['contact'])) {
    $contact = trim($conn->real_escape_string($_GET['contact']));
    
    if (!empty($contact)) {
        $query = "SELECT * FROM customers WHERE contact = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $contact);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $customer = $result->fetch_assoc();
            echo json_encode([
                'success' => true,
                'customer' => [
                    'id' => $customer['id'],
                    'name' => $customer['name'],
                    'contact' => $customer['contact']
                ]
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
            'message' => 'Contact number is empty'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Contact number not provided'
    ]);
}
?> 