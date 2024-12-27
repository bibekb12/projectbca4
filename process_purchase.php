<?php
include('db.php');

$product_id = $_POST['product_id'];
$quantity = intval($_POST['quantity']);

$conn->query("UPDATE items SET stock_quantity = stock_quantity + $quantity WHERE id = $product_id");

$conn->query("INSERT INTO transactions (product_id, type, quantity) VALUES ($product_id, 'Purchase', $quantity)");

header('Location: stock.php');
?>
