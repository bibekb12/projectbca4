<?php 
session_start();

include('db.php');

$product_id = $_POST['product_id'];
$quantity = intval($_POST['quantity']);
$supplier = intval($_POST['supplier']);
$userid = $_SESSION['user_id'];

$update_stock = $conn->query("UPDATE items SET stock_quantity = stock_quantity + $quantity WHERE id = $product_id");

if ($update_stock) 
    {
    $insert_transaction = $conn->query("INSERT INTO transactions (product_id, type, quantity, supplier_id,user_id) VALUES ($product_id, 'Purchase', $quantity, $supplier,$userid)");

    if ($insert_transaction) {
        header('Location: purchase.php');
    } else 
    {
        echo "Error: " . $conn->error;
    }
} 
else
{
    echo "Error: " . $conn->error;
}
?>
