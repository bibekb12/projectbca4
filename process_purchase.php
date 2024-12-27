<?php 
session_start();

include('db.php');

$product_id = $_POST['product_id'];
$quantity = intval($_POST['quantity']);
$supplier = intval($_POST['supplier']);
$sellingprice=floatval($_POST['sellingprice'])
$userid = $_SESSION['user_id'];

$update_stock = $conn->query("UPDATE items SET stock_quantity = stock_quantity + $quantity,sell_price=$sellingprice WHERE id = $product_id");

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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ?");
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        echo "Record deleted successfully.";
    } else {
        echo "Error deleting record: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
    header("Location: purchase.php");
    exit();
}
?>
?>
