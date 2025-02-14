<?php
session_start();
include_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST['id']);
    $username = $_POST['username'];
    $status = $_POST['status'];
    $role = $_POST['role']; // Get the role from POST data
    
    // For new users or when password is changed
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    if (!empty($id)) {
        // Update existing user
        if (!empty($_POST['password'])) {
            // If password is provided, update password too
            $query = "UPDATE users SET 
                     username = ?, 
                     password = ?, 
                     status = ?,
                     role = ?,
                     updated_at = CURRENT_TIMESTAMP 
                     WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssi", $username, $password, $status, $role, $id);
        } else {
            // If no password provided, update other fields only
            $query = "UPDATE users SET 
                     username = ?, 
                     status = ?,
                     role = ?,
                     updated_at = CURRENT_TIMESTAMP 
                     WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssi", $username, $status, $role, $id);
        }
    } else {
        // Insert new user
        $query = "INSERT INTO users (username, password, status, role, created_at) 
                 VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $username, $password, $status, $role);
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
header('Location: usersetup.php');
exit();
?>
