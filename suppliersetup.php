<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$id = "";
$suppliername = "";
$pannumber = "";
$suppliercontact = "";
$status = "Y";

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT id, name, contact, panno, status FROM suppliers WHERE id = $id");

    if ($result && $result->num_rows > 0) {
        $supplier = $result->fetch_assoc();
        $suppliername = $supplier['name'];
        $status = $supplier['status'];
        $pannumber = $supplier['panno'];
        $suppliercontact = $supplier['contact'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Setup</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <section class="dashboard">
    <div class="top">
            <i class="uil uil-bars sidebar-toggle"></i>
            <div class="user-greeting">
                <i class="uil uil-user-circle"></i>
                <span>Welcome, <span class="username"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?></span></span>
            </div>
        </div>
        <div class="dash-content">
            <div class="overview">
                <div class="title">
                    <i class="uil uil-truck"></i>
                    <span class="text">Supplier Management</span>
                </div>

                <div class="setup-box">
                    <button class="btn add-user-btn" onclick="toggleForm()">
                        <i class="uil uil-plus"></i> Add New Supplier
                    </button>

                    <div id="supplierForm" class="setup-form" style="display: none;">
                        <form method="POST" action="add_supplier.php">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="setup-form-grid">
                                <div class="form-group">
                                    <label>Supplier Name:</label>
                                    <input type="text" name="suppliername" value="<?php echo $suppliername; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Contact No:</label>
                                    <input type="text" name="contactno" value="<?php echo $suppliercontact; ?>">
                                </div>
                                <div class="form-group">
                                    <label>PAN No:</label>
                                    <input type="text" name="pannumber" value="<?php echo $pannumber; ?>">
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
                            <div class="form-buttons">
                                <button type="submit" class="btn">Save</button>
                                <button type="button" class="btn btn-cancel" onclick="toggleForm()">Cancel</button>
                            </div>
                        </form>
                    </div>

                    <div class="activity">
                        <div class="title">
                            <i class="uil uil-clock-three"></i>
                            <span class="text">All Suppliers List</span>
                        </div>
                        <div class="table-container">
                            <table border="1">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Supplier Name</th>
                                        <th>Contact</th>
                                        <th>PAN No</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $result = $conn->query("SELECT id, name, contact, panno, status FROM suppliers");
                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $status = ($row['status'] === 'Y') ? 'Active' : 'Inactive';
                                            echo "<tr>
                                                    <td>{$row['id']}</td>
                                                    <td>{$row['name']}</td>
                                                    <td>{$row['contact']}</td>
                                                    <td>{$row['panno']}</td>
                                                    <td><span class='status-badge status-" . strtolower($status) . "'>{$status}</span></td>
                                                    <td>
                                                        <a href='javascript:void(0)' onclick='editSupplier({$row['id']})' class='edit-link'>
                                                            <i class='uil uil-edit'></i> Edit
                                                        </a>
                                                    </td>
                                                </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='6' style='text-align: center;'>No suppliers found</td></tr>";
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
            const form = document.getElementById('supplierForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function editSupplier(supplierId) {
            // Show the form
            const form = document.getElementById('supplierForm');
            form.style.display = 'block';
            
            // Fetch supplier data using AJAX
            fetch(`get_supplier.php?id=${supplierId}`)
                .then(response => response.json())
                .then(data => {
                    document.querySelector('input[name="id"]').value = data.id;
                    document.querySelector('input[name="suppliername"]').value = data.name;
                    document.querySelector('input[name="contactno"]').value = data.contact;
                    document.querySelector('input[name="pannumber"]').value = data.panno;
                    document.querySelector(`input[name="status"][value="${data.status}"]`).checked = true;
                })
                .catch(error => console.error('Error:', error));
        }

        // Add this for dark mode toggle
        const body = document.querySelector("body"),
        modeToggle = body.querySelector(".mode-toggle"),
        sidebar = body.querySelector("nav");

        let getMode = localStorage.getItem("mode");
        if(getMode && getMode === "dark") {
            body.classList.toggle("dark");
        }

        let getStatus = localStorage.getItem("status");
        if(getStatus && getStatus === "close") {
            sidebar.classList.toggle("close");
        }

        modeToggle.addEventListener("click", () => {
            body.classList.toggle("dark");
            if(body.classList.contains("dark")) {
                localStorage.setItem("mode", "dark");
            } else {
                localStorage.setItem("mode", "light");
            }
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
