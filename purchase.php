<form method="POST" action="process_purchase.php">
    <label for="product">Product:</label>
    <select name="product_id" required>
        <?php
        include('db.php');
        $result = $conn->query("SELECT id, name,price FROM items where status='Y'");
        while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['id']}'>{$row['name']} $ {$row['price']}</option>";
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
    <label for ="costprice">Purchase Price</label>
    <input type="number" name="costprice" required>
    <label for="quantity">Quantity:</label>
    <input type="number" name="quantity" required>
    <label for="price">Total:</label>
    <input type="number" name="total" readonly>
    <label for ="sellprice">Selling Price: </label>
    <input type="number" name="sellprice" required>

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
        <td>Cost Price</td>
        <td>Total Amount</td>
        <td>Selling Price</td>
        <td>User Name</td>
        <td>Action</td>
    </tr>
    <?php
    include('db.php');
    $result = $conn->query("SELECT * FROM vw_transaction where type='Purchase' order by date desc");
    if ($result && $result->num_rows > 0) 
    {
        while ($row = $result->fetch_assoc()) 
        {
            echo "<tr>
                <td>{$row['Date']}</td>
                <td>{$row['name']}</td>
                <td>{$row['suppliername']}</td>
                <td>{$row['quantity']}</td>
                <td>{$row['costprice']}</td>
                <td>{$row['totalamount']}</td>
                <td>{$row['sellprice']}
                <td>{$row['username']}</td>
                <td>
                    <form method='POST' action='process_purchase.php' style='display:inline;'>
                        <input type='hidden' name='delete_id' value='{$row['id']}'>
                        <button type='submit' name='delete' onclick='return confirm(\"Are you sure you want to delete this record?\");'>Delete</button>
                    </form>
                </td>
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


<script src="script.js" ></script>