<?php
include 'db.php';
$id = "";
$username = "";
$status = "Y";

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT id, username, status FROM users WHERE id = $id");

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $username = $user['username'];
        $status = $user['status'];
    } else {
        echo "User not found.";
    }
}

$result = $conn->query("SELECT id, username, status, created_at,updated_at FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Setup</title>
</head>
<body>

    <h1>User Setup</h1>

    <form method="POST" action="add_user.php">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <label> Username:</label>
        <input type="text" name="username" value="<?php echo $username; ?>" placeholder="username" required>
        <label > Password: </label>
        <input type="password" name="password" placeholder="password">
        <label>
            <input type="radio" name="status" value="Y" <?php echo ($status === 'Y') ? 'checked' : ''; ?>> Active
        </label>
        <label>
            <input type="radio" name="status" value="N" <?php echo ($status === 'N') ? 'checked' : ''; ?>> Inactive
        </label>
        <button type="submit">Save</button>
    </form>

    <h2>All Users List</h2>
    <table border="1">
        <tr>
            <th>Id</th>
            <th>Username</th>
            <th>User Status</th>
            <th>Created At</th>
            <th>Updated At <th>
            <th>Action</th>
        </tr>

        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $status = ($row['status'] === 'Y') ? 'Active' : 'Inactive';
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['username']}</td>
                        <td>{$status}</td>
                        <td>{$row['created_at']}</td>
                        <td>{$row['updated_at']}</td>
                        <td></td>
                        <td><a href='usersetup.php?id={$row['id']}'>Edit</a></td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No users found</td></tr>";
        }
        ?>
    </table><br>
    <a href="setup.php"> Back </a>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
