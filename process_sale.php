<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];

    if ($quantity <= 0 || $price <= 0) {
        die("Invalid quantity or price.");
    }

    session_start();
    $user_id = $_SESSION['user_id'];
    $supplier_id = isset($_POST['supplier_id']) ? $_POST['supplier_id'] : null;

    if (is_null($supplier_id)) {
        die("Error: supplier_id must be provided.");
    }

    $stmt = $conn->prepare("INSERT INTO transactions (product_id, user_id, supplier_id, type, quantity) VALUES (?, ?, ?, 'Sale', ?)");
    $stmt->bind_param("iiis", $item_id, $user_id, $supplier_id, $quantity);
    
    if ($stmt->execute()) 
    {
        $conn->query("UPDATE items SET stock_quantity = stock_quantity - $quantity WHERE id = $item_id");
        echo "Sale processed successfully.";
    } else {
        echo "Error processing sale: " . $conn->error;
    }
    header('location:sale.php');

}
?>
