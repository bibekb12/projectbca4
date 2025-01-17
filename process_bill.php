<?php
session_start();
include 'db.php';

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Start transaction
$conn->begin_transaction();

try {
    // Handle customer
    $customer_name = trim($data['customer']['name']);
    $customer_contact = trim($data['customer']['contact']);
    $is_existing = $data['customer']['isExisting'];
    
    if (!$is_existing && $customer_name !== 'Cash' && !empty($customer_contact)) {
        // Insert new customer
        $stmt = $conn->prepare("INSERT INTO customers (name, contact) VALUES (?, ?)");
        $stmt->bind_param("ss", $customer_name, $customer_contact);
        $stmt->execute();
        $customer_id = $stmt->insert_id;
    } else if ($is_existing) {
        // Get existing customer ID
        $stmt = $conn->prepare("SELECT id FROM customers WHERE contact = ?");
        $stmt->bind_param("s", $customer_contact);
        $stmt->execute();
        $result = $stmt->get_result();
        $customer = $result->fetch_assoc();
        $customer_id = $customer['id'];
    } else {
        $customer_id = null; // For 'Cash' customers
    }

    // Generate bill number (YYYYMMDD-XXXX format)
    $bill_no = date('Ymd') . '-' . sprintf("%04d", rand(1, 9999));

    // Insert sale
    $stmt = $conn->prepare("INSERT INTO sales (bill_no, customer_id, total_amount, user_id, sale_date) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("sidd", $bill_no, $customer_id, $data['total'], $_SESSION['user_id']);
    $stmt->execute();
    $sale_id = $stmt->insert_id;

    // Insert sale items and update inventory
    foreach ($data['items'] as $item) {
        // Get the sell_price from items table
        $stmt = $conn->prepare("SELECT sell_price FROM items WHERE id = ?");
        $stmt->bind_param("i", $item['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $item_data = $result->fetch_assoc();
        $sell_price = $item_data['sell_price'];
        
        // Calculate total for this item
        $item_total = $sell_price * $item['quantity'];

        // Insert sale item
        $stmt = $conn->prepare("INSERT INTO sales_items (sale_id, item_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiddd", $sale_id, $item['id'], $item['quantity'], $sell_price, $item_total);
        $stmt->execute();

        // Update inventory
        $stmt = $conn->prepare("UPDATE items SET stock_quantity = stock_quantity - ? WHERE id = ?");
        $stmt->bind_param("ii", $item['quantity'], $item['id']);
        $stmt->execute();

        // Insert into transactions
        $stmt = $conn->prepare("INSERT INTO transactions (product_id, user_id, type, quantity) VALUES (?, ?, 'Sale', ?)");
        $stmt->bind_param("iii", $item['id'], $_SESSION['user_id'], $item['quantity']);
        $stmt->execute();
    }

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true, 'bill_no' => $bill_no]);
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 