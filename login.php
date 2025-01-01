<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

include('db.php'); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = trim($_POST['password']);
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields";
        header('Location: index.php');
        exit();
    }

    // Prepare statement to prevent SQL injection
    $query = "SELECT * FROM users WHERE username = ? AND status = 'Y'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Check if user exists
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = intval($user['id']);
            header('Location: dashboard.php');
            exit();
        } else {
            $_SESSION['error'] = "Invalid username or password";
            header('Location: index.php');
            exit();
        }
    } else {
        $_SESSION['error'] = "Invalid username or password";
        header('Location: index.php');
        exit();
    }
}

// If someone tries to access this file directly without POST
header('Location: index.php');
exit();
?>
