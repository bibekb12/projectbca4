<?php
session_start();
include('db.php'); 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);
//commented for the testing
    // if ($user && password_verify($password, $user['password'])) {
    //     $_SESSION['user_id'] = $user['id'];
    //     header('Location: dashboard.php');
    //     exit();
    // } else {
    //     echo "Invalid credentials.";
    // }
}
?>
