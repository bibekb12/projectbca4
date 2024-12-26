<?php
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$database = "inventory";

$conn = new mysqli($servername, $dbusername, $dbpassword, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = "";
$user = ["username" => "", "status" => "Y"]; 
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT id, username, status FROM users WHERE id = $id");
    $user = $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST['id']);
    $username = $_POST['username'];
    $password = $_POST['password'];
    $status = $_POST['active'];

    if ($id) {
        if (!empty($password)) {
            $sql = "UPDATE users SET username='$username', password='$password', status='$status', updated_at=NOW() WHERE id=$id";
        } else {
            $sql = "UPDATE users SET username='$username', status='$status', updated_at=NOW() WHERE id=$id";
        }
    } else {
        $sql = "INSERT INTO users (username, password, status, updated_at) VALUES ('$username', '$password', '$status', NOW())";
    }

    if ($conn->query($sql) === TRUE) {
        echo $id ? "User updated successfully" : "New user added successfully";
        header("Location: usersetup.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>
