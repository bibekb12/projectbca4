<form method="POST" action="process_purchase.php">
    <label for="product">Product:</label>
    <select name="product_id" required>
        <?php
        include('db.php');
        $result = $conn->query("SELECT id, name FROM items where status='Y'");
        while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['id']}'>{$row['name']}</option>";
        }
        ?>
    </select>
    
    <label for="supplier">Supplier:</label>
    <select name="supplier" required>
    <?php
        include('db.php');
        $result = $conn->query("SELECT id, name FROM suppliers where status='Y'");
        while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['id']}'>{$row['name']}</option>";
        }
        ?>
    </select>

    <label for="quantity">Quantity:</label>
    <input type="number" name="quantity" required>

    <button type="submit">Add Purchase</button>
</form>
<div>
    <tr>
    <h1> Purchase Detail</h1>
    <table border="1">
        <td>Date</td>
        <td>Product</td>
        <td>Supplier</td>
        <td>Quantity</td>
        <td>Total Amount</td>
        <td>User Name</td>
        <td>Action</td>
    </tr>
    <?php
    include('db.php');
    $result = $conn->query("SELECT * FROM vw_transaction where type='Purchase'");
    if ($result && $result->num_rows > 0) 
    {
        while ($row = $result->fetch_assoc()) 
        {
            echo "<tr>
                <td>{$row['Date']}</td>
                <td>{$row['name']}</td>
                <td>{$row['suppliername']}</td>
                <td>{$row['quantity']}</td>
                <td>{$row['totalamount']}</td>
                <td>{$row['username']}</td>
                <td><a href='purchase.php?id={$row['id']}'>Delete</a></td>
            </tr>";
        }
    }
    else
    {
        echo "No data available";
    }
    ?>
    </table>
    <br><a href="dashboard.php">Back</a>


