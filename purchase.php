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
