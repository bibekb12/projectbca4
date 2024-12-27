<?php
include_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST['id']);
    $username = $_POST['username'];
    $password = $_POST['password'];
    $status = $_POST['status'];

    if ($id) {
        if (!empty($password)) {
            $sql = "UPDATE users SET username='$username', password='$password', status='$status', updated_at=NOW() WHERE id=$id";
        } else {
            $sql = "UPDATE users SET username='$username', status='$status', updated_at=NOW() WHERE id=$id";
        }
    } else {
        $sql = "INSERT INTO users (username, password, status) VALUES ('$username', '$password', '$status')";
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
