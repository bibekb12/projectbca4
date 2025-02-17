<?php
session_start();
include 'db.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Access denied. Admin privileges required.";
    header('Location: dashboard.php');
    exit();
}

$id = "";
$username = "";
$status = "Y";
$role= "user";

// Query for editing a specific user
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT id, username, status,role FROM users WHERE id = $id");

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $username = $user['username'];
        $status = $user['status'];
        $role = $user['role'];
    }
}

// Query for all users - Add this line
$all_users = $conn->query("SELECT id, username, status, created_at, updated_at, role FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Setup</title>
    <link rel="icon" href="images/inv-logo.png" type="image/icon type">
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <section class="dashboard">
    <div class="top">
            <div class="search-box">
                <i class="uil uil-search"></i>
                <input type="text" placeholder="Search here...">
            </div>
            <div class="user-greeting">
                <i class="uil uil-user-circle"></i>
                <span>Welcome, <span class="username"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?></span></span>
            </div>
        </div>
        <div class="dash-content">
            <div class="overview">
                <div class="title">
                    <i class="uil uil-users-alt"></i>
                    <span class="text">User Management</span>
                </div>

                <div class="setup-box">
                    <button class="btn add-user-btn" onclick="toggleForm()">
                        <i class="uil uil-plus"></i> Add New User
                    </button>

                    <div id="userForm" class="setup-form" style="display: none;">
                        <form method="POST" action="add_user.php" class="setup-form">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="textbox">
                                <label>Username:</label>
                                <input type="text" name="username" value="<?php echo $username; ?>" placeholder="Username" required>
                            </div>
                            <div class="textbox">
                                <label>Password:</label>
                                <input type="password" name="password" placeholder="Password">
                            </div>
                            <div class="radio-group">User Status:
                                <label class="radio-label">
                                    <input type="radio" name="status" value="Y" <?php echo ($status === 'Y') ? 'checked' : ''; ?>> Active
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="status" value="N" <?php echo ($status === 'N') ? 'checked' : ''; ?>> Inactive
                                </label>
                            </div>
                            <div class="radio-group">User Role:
                                <label class="radio-label">
                                    <input type="radio" name="role" value="admin" <?php echo ($role === 'admin') ? 'checked' : ''; ?>> Admin
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="role" value="user" <?php echo ($role === 'user') ? 'checked' : ''; ?>> User
                                </label>
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
                            <span class="text">All Users List</span>
                        </div>
                        <div class="table-container">
                            <table border="1">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Updated At</th>
                                        <th>User Role </th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($all_users && $all_users->num_rows > 0) {
                                        while ($row = $all_users->fetch_assoc()) {
                                            $status = ($row['status'] === 'Y') ? 'Active' : 'Inactive';
                                            $role = ($row['role']==='admin')?'admin':'user';
                                            echo "<tr>
                                                    <td>{$row['id']}</td>
                                                    <td>{$row['username']}</td>
                                                    <td><span class='status-badge status-" . strtolower($status) . "'>{$status}</span></td>
                                                    <td>{$row['created_at']}</td>
                                                    <td>{$row['updated_at']}</td>
                                                    <td>{$row['role']}</td>
                                                    <td>
                                                        <a href='javascript:void(0)' onclick='editUser({$row['id']})' class='edit-link'>
                                                            <i class='uil uil-edit'></i> Edit
                                                        </a>
                                                    </td>
                                                </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='6' style='text-align: center;'>No users found</td></tr>";
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
            const form = document.getElementById('userForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            
            // Reset form when hiding
            if (form.style.display === 'none') {
                document.querySelector('form').reset();
                document.querySelector('input[name="id"]').value = '';
            }
        }

        function editUser(userId) {
            // Show the form
            const form = document.getElementById('userForm');
            form.style.display = 'block';
            
            // Fetch user data using AJAX
            fetch(`get_user.php?id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    // Set form values
                    document.querySelector('input[name="id"]').value = data.id;
                    document.querySelector('input[name="username"]').value = data.username;
                    
                    // Set status radio button
                    const statusRadio = document.querySelector(`input[name="status"][value="${data.status}"]`);
                    if (statusRadio) statusRadio.checked = true;
                    
                    // Set role radio button
                    const roleRadio = document.querySelector(`input[name="role"][value="${data.role}"]`);
                    if (roleRadio) roleRadio.checked = true;
                    
                    // Clear password field for security
                    document.querySelector('input[name="password"]').value = '';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error fetching user data');
                });
        }

        // Add dark mode toggle
        const body = document.querySelector("body"),
        modeToggle = body.querySelector(".mode-toggle"),
        sidebar = body.querySelector("nav");

        let getStatus = localStorage.getItem("status");
        if(getStatus && getStatus === "close") {
            sidebar.classList.toggle("close");
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
