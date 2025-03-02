<?php
session_start();
header('Content-Type: application/json');

// Disable error display to prevent HTML output
ini_set('display_errors', 0);
error_reporting(0);

try {
    // Include database connection
    require_once 'db.php';

    // Validate session
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false, 
            'message' => 'Not logged in'
        ]);
        exit;
    }

    if (isset($_GET['contact'])) {
        $contact = $_GET['contact'];
        
        // Validate contact number format
        if (!preg_match('/^[0-9]{3,10}$/', $contact)) {
            http_response_code(400);
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
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Contact number required'
        ]);
        exit;
    }
} catch (Exception $e) {
    // Log the error server-side
    error_log('Customer lookup error: ' . $e->getMessage());
    
    // Return a generic error response
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'An unexpected error occurred'
    ]);
    exit;
} finally {
    $conn->close();
}
?>