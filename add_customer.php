<?php
session_start();
include 'db.php';

// Function to save customer
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

// Check if this is an API request
$isApi = !empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;

if ($isApi) {
    // Handle API request
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || empty($data['name']) || empty($data['contact'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid data']);
        exit;
    }
    
    $customer_id = saveCustomer($data['name'], $data['contact']);
    
    if ($customer_id) {
        echo json_encode(['success' => true, 'customer_id' => $customer_id]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save customer']);
    }
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['name']) || empty($_POST['contact'])) {
        echo json_encode(['error' => 'Name and contact are required']);
        exit;
    }
    
    $customer_id = saveCustomer($_POST['name'], $_POST['contact']);
    
    if ($customer_id) {
        echo json_encode(['success' => true, 'message' => 'Customer added successfully']);
    } else {
        echo json_encode(['error' => 'Failed to add customer']);
    }
    exit;
}

// If not POST or API request, show the form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Customer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container">
        <h2>Add New Customer</h2>
        <form id="customerForm" method="POST">
            <div class="form-group">
                <label for="name">Customer Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="contact">Contact Number:</label>
                <input type="tel" id="contact" name="contact" 
                       pattern="[0-9]{10}" maxlength="10" 
                       title="Please enter a valid 10-digit contact number" required>
            </div>
            <button type="submit" class="btn-primary">Add Customer</button>
        </form>
        <div id="message"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#customerForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: 'add_customer.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#message').html('<div class="success">' + response.message + '</div>');
                        $('#customerForm')[0].reset();
                    } else {
                        $('#message').html('<div class="error">' + response.error + '</div>');
                    }
                },
                error: function() {
                    $('#message').html('<div class="error">Error adding customer</div>');
                }
            });
        });
    });
    </script>
</body>
</html> 