<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$id = "";
$itemcode = "";
$itemname = "";
$description = "";
$status = "Y";
$price = '0';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT id, itemcode, itemname, description, status, sell_price FROM items WHERE id = $id");

    if ($result && $result->num_rows > 0) {
        $item = $result->fetch_assoc();
        $itemcode = $item['itemcode'];
        $itemname = $item['itemname'];
        $status = $item['status'];
        $description = $item['description'];
        $sellprice = $item['sell_price'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Setup</title>
    <link rel="icon" href="images/inv-logo.png" type="image/icon type">
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <section class="dashboard">
        <div class="top">
            <div class="user-greeting">
                <i class="uil uil-user-circle"></i>
                <span>Welcome, <span class="username"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?></span></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        <div class="dash-content">
            <div class="overview">
                <div class="title">
                    <i class="uil uil-box"></i>
                    <span class="text">Item Management</span>
                </div>

                <div class="setup-box">
                    <button class="btn add-user-btn" onclick="toggleForm()">
                        <i class="uil uil-plus"></i> Add New Item
                    </button>

                    <div id="itemForm" class="setup-form" style="display: none;">
                        <form method="POST" action="add_item.php">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="setup-form-grid">
                                <div class="form-group">
                                    <label>Item Code:</label>
                                    <input type="text" name="itemcode" value="<?php echo $itemcode; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Item Name:</label>
                                    <input type="text" name="name" value="<?php echo $itemname; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Sell Price:</label>
                                    <input type="number" name="price" value="<?php echo $price; ?>" step="0.01" required>
                                </div>
                                <div class="form-group">
                                    <label>Status:</label>
                                    <div class="radio-group">
                                        <label class="radio-label">
                                            <input type="radio" name="status" value="Y" <?php echo ($status === 'Y') ? 'checked' : ''; ?>> Active
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="status" value="N" <?php echo ($status === 'N') ? 'checked' : ''; ?>> Inactive
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Description:</label>
                                <textarea name="description"><?php echo $description; ?></textarea>
                            </div>
                            <div class="form-buttons">
                                <button type="submit" class="btn">Save</button>
                                <button type="button" class="btn btn-cancel" onclick="toggleForm()">Cancel</button>
                            </div>
                        </form>
                    </div>

                    <div class="activity">
                        <div class="title">
                            <i class="uil uil-clock-three"></i>
                            <span class="text">All Items List</span>
                        </div>
                        <div class="table-container">
                            <table border="1">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Item Code</th>
                                        <th>Name</th>
                                        <th>Sell Price</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $result = $conn->query("SELECT id, itemcode, itemname, sell_price, status FROM items");
                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $status = ($row['status'] === 'Y') ? 'Active' : 'Inactive';
                                            echo "<tr>
                                                    <td>{$row['id']}</td>
                                                    <td>{$row['itemcode']}</td>
                                                    <td>{$row['itemname']}</td>
                                                    <td>{$row['sell_price']}</td>
                                                    <td><span class='status-badge status-" . strtolower($status) . "'>{$status}</span></td>
                                                    <td>
                                                        <a href='javascript:void(0)' onclick='editItem({$row['id']})' class='edit-link'>
                                                            <i class='uil uil-edit'></i> Edit
                                                        </a>
                                                    </td>
                                                </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='6' style='text-align: center;'>No items found</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="navigation">
                        <a href="setup.php" class="btn">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        function toggleForm() {
            const form = document.getElementById('itemForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function editItem(itemId) {
            // Show the form
            const form = document.getElementById('itemForm');
            form.style.display = 'block';
            
            // Fetch item data using AJAX
            fetch(`get_item.php?id=${itemId}`)
                .then(response => response.json())
                .then(data => {
                    document.querySelector('input[name="id"]').value = data.id;
                    document.querySelector('input[name="itemcode"]').value = data.itemcode;
                    document.querySelector('input[name="itemname"]').value = data.name;
                    document.querySelector('input[name="sell_price"]').value = data.price;
                    document.querySelector('textarea[name="description"]').value = data.description;
                    document.querySelector(`input[name="status"][value="${data.status}"]`).checked = true;
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>