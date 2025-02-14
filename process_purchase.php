<?php 
session_start();

include('db.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Handle purchase submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['delete'])) {
    try {
        // Start transaction
        $conn->begin_transaction();

        // Get form data
        $product_id = $_POST['product_id'];
        $supplier_id = $_POST['supplier'];
        $quantity = $_POST['quantity'];
        $cost_price = $_POST['costprice'];
        $sell_price = $_POST['sellprice'];
        $total_amount = $quantity * $cost_price;
        $user_id = $_SESSION['user_id'];

        // Insert into purchases table
        $purchase_query = "INSERT INTO purchases (supplier_id, total_amount, purchase_date, user_id) 
                          VALUES (?, ?, NOW(), ?)";
        $stmt = $conn->prepare($purchase_query);
        if (!$stmt) {
            throw new Exception('Failed to prepare purchase query: ' . $conn->error);
        }

        $stmt->bind_param("idi", $supplier_id, $total_amount, $user_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert purchase: ' . $stmt->error);
        }
        $purchase_id = $conn->insert_id;

        // Insert into purchase_items table
        $items_query = "INSERT INTO purchase_items (purchase_id, item_id, quantity, price) 
                       VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($items_query);
        if (!$stmt) {
            throw new Exception('Failed to prepare items query: ' . $conn->error);
        }

        $stmt->bind_param("iiid", $purchase_id, $product_id, $quantity, $cost_price);
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert purchase items: ' . $stmt->error);
        }

        // Update item stock quantity and sell price
        $update_query = "UPDATE items SET 
                        stock_quantity = stock_quantity + ?,
                        sell_price = ?
                        WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        if (!$stmt) {
            throw new Exception('Failed to prepare update query: ' . $conn->error);
        }

        $stmt->bind_param("idi", $quantity, $sell_price, $product_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to update item: ' . $stmt->error);
        }

        // Commit transaction
        $conn->commit();
        
        // Redirect back to purchase page
        header('Location: purchase.php');
        exit();

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}

// Handle purchase deletion
if (isset($_POST['delete']) && isset($_POST['delete_id'])) {
    try {
        $conn->begin_transaction();

        $purchase_id = intval($_POST['delete_id']);

        // Get purchase items to update stock
        $items_query = "SELECT item_id, quantity FROM purchase_items WHERE purchase_id = ?";
        $stmt = $conn->prepare($items_query);
        $stmt->bind_param("i", $purchase_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            // Decrease stock quantity
            $update_query = "UPDATE items SET stock_quantity = stock_quantity - ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ii", $row['quantity'], $row['item_id']);
            $stmt->execute();
        }

        // Delete purchase items
        $delete_items = "DELETE FROM purchase_items WHERE purchase_id = ?";
        $stmt = $conn->prepare($delete_items);
        $stmt->bind_param("i", $purchase_id);
        $stmt->execute();

        // Delete purchase
        $delete_purchase = "DELETE FROM purchases WHERE id = ?";
        $stmt = $conn->prepare($delete_purchase);
        $stmt->bind_param("i", $purchase_id);
        $stmt->execute();

        $conn->commit();
        header('Location: purchase.php');
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}

// If we get here, something went wrong
header('Location: purchase.php');
exit();
?>
