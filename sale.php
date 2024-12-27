<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Panel</title>
</head>
<body>
    <h1>Sales Panel</h1>
    <form id="saleForm" method="POST" action="process_sale.php">
        <label for="item">Item:</label>
        <select name="item_id" required>
        <?php
        include('db.php');
        $result = $conn->query("SELECT id, name,price FROM items where stock_quantity>0");
        while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['id']}' data-itemcode='{$row['itemcode']}'>{$row['name']} $ {$row['price']}</option>";
        }
        ?>
    </select>
        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" required min="1">
        <?php $row = $result->fetch_assoc(); ?>
<input type="number" id="price" name="price" value="<?php echo $row['price']; ?>" required step="0.01">
    
        <label for="supplier">Supplier:</label>
        <select name="supplier_id" required>
            <?php
            $supplierResult = $conn->query("SELECT id, name FROM suppliers");
            while ($supplierRow = $supplierResult->fetch_assoc()) {
                echo "<option value='{$supplierRow['id']}'>{$supplierRow['name']}</option>";
            }
            ?>
        </select>
        
        <button type="submit">Save</button>
    </form>
    
    <h2>Current Sales</h2>
    <table border="1">
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>

    <?php
    $salesResult = $conn->query("SELECT t.id, i.name AS item_name, t.quantity, i.price AS item_price, t.supplier_id, t.transaction_date, t.type FROM transactions t JOIN items i ON t.product_id = i.id");
    while ($saleRow = $salesResult->fetch_assoc()) {
        $total = $saleRow['quantity'] * $price;
        echo "<tr>
                <td>{$saleRow['item_name']}</td>
                <td>{$saleRow['quantity']}</td>
                <td>{$price}</td>
                <td>{$total}</td>
              </tr>";
    }
    ?>
    </table>
    <br><a href="dashboard.php">Back</a>
    <script src="script.js"></script>
</body>
</html>
