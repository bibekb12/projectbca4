<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styledash.css">
    <title>Add Item</title>
</head>
<body>
    <h1>Add New Item</h1>
    <form method="POST" action="add_item.php">
        <input type="text" name="name" placeholder="Item Name" required>
        <textarea name="description" placeholder="Description"></textarea>
        <input type="number" name="quantity" placeholder="Quantity" required>
        <input type="text" name="price" placeholder="Price" required>
        <select name="supplier_id">
            <!-- Populate with suppliers from the database -->
            <?php
            // Database connection
            $conn = new mysqli('localhost', 'username', 'password', 'database');
            $result = $conn->query("SELECT id, name FROM suppliers");
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
            }
            $conn->close();
            ?>
        </select>
        <button type="submit">Add Item</button>
    </form>
</body>
</html>