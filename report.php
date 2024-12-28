<h1>Purchase Report</h1>
<table border="1">
    <tr>
        <th>Product</th>
        <th>Description</th>
        <th>Price</th>
        <th>Stock Quantity</th>
    </tr>
    <?php
    include('db.php');
    $result = $conn->query("SELECT * FROM items");
    while ($row = $result->fetch_assoc()) {
        $low_stock = $row['stock_quantity'] < 10 ? 'style="color:red;"' : '';
        echo "<tr>
            <td>{$row['name']}</td>
            <td>{$row['description']}</td>
            <td>{$row['price']}</td>
            <td $low_stock>{$row['stock_quantity']}</td>
        </tr>";
    }
    ?>
</table>

<h1>Sale Report</h1>
<table border="1">
    <tr>
        <th>Product</th>
        <th>Description</th>
        <th>Price</th>
        <th>Stock Quantity</th>
    </tr>
    <?php
    include('db.php');
    $result = $conn->query("SELECT * FROM items");
    while ($row = $result->fetch_assoc()) {
        $low_stock = $row['stock_quantity'] < 10 ? 'style="color:red;"' : '';
        echo "<tr>
            <td>{$row['name']}</td>
            <td>{$row['description']}</td>
            <td>{$row['price']}</td>
            <td $low_stock>{$row['stock_quantity']}</td>
        </tr>";
    }
    ?>
</table>
<br><a href="dashboard.php">Back</a>