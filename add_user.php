<?php
session_start();
include_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize inputs
    $id = intval($_POST['id']);
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $passwordform = trim($_POST['password']);
    $status = mysqli_real_escape_string($conn, trim($_POST['status']));
    
    // Prepare statement based on whether it's an update or insert
    if ($id) {
        if (!empty($passwordform)) {
            // Update with new password
            $password = password_hash($passwordform, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username=?, password=?, status=?, updated_at=NOW() WHERE id=?");
            $stmt->bind_param("sssi", $username, $password, $status, $id);
        } else {
            // Update without changing password
            $stmt = $conn->prepare("UPDATE users SET username=?, status=?, updated_at=NOW() WHERE id=?");
            $stmt->bind_param("ssi", $username, $status, $id);
        }
    } else {
        // New user insert
        $password = password_hash($passwordform, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, status) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $status);
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = $id ? "User updated successfully" : "New user added successfully";
        header("Location: usersetup.php");
        exit();
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
        header("Location: usersetup.php");
        exit();
    }
    
    $stmt->close();
}

$conn->close();
?>
