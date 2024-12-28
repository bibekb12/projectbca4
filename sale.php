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
        $result = $conn->query("SELECT id, name,sell_price,price FROM items where stock_quantity>0");
        while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['id']}' data-itemcode='{$row['itemcode']}'>{$row['name']} $ {$row['price']}</option>";
        }
        ?>
    </select>
        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" required min="1">
        
        <label for="price">Price:</label>
        <input type="number" id="price" name="price" value="<?php echo $row['sell_price']; ?>" required step="0.01">
        <button type="submit">Save</button>
    </form>
    
    <h2>Current Sales</h2>
    <table border="1">
            <tr>
                <th>Date</th>
                <th>Item</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
                <th>User Name</th>
            </tr>

    <?php
    $salesResult = $conn->query("SELECT t.id, i.name AS item_name, t.quantity, i.price AS item_price, t.supplier_id, t.transaction_date, t.type,(select username from users u where t.user_id=u.id) as username FROM transactions t  JOIN items i ON t.product_id = i.id where t.type='Sale' order by t.transaction_date desc");
    while ($saleRow = $salesResult->fetch_assoc()) {
        $total = $saleRow['quantity'] * $saleRow['item_price'];
        echo "<tr>
                <td>$saleRow[transaction_date]</td>
                <td>{$saleRow['item_name']}</td>
                <td>{$saleRow['quantity']}</td>
                <td>{$saleRow['item_price']}</td>
                <td>{$total}</td>
                <td>{$saleRow['username']}</td>
             </tr>";
    }
    ?>
    </table>
    <br><a href="dashboard.php">Back</a>
    <script src="script.js"></script>
</body>
</html>
