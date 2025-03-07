<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['username']) && isset($_SESSION['role'])) {
    header('Location: dashboard.php');
    exit();
}

include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    // Sanitize inputs
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = trim($_POST['password']);
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
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
                // Set session variables
                $_SESSION['user_id'] = intval($user['id']);
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header('Location: dashboard.php');
                exit();
            } else {
                $error = "Invalid username or password";
            }
        } else {
            $error = "Invalid username or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory Management System</title>
    <link rel="icon" href="images/inv-logo.png" type="image/icon type">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="includes/css/style.css">
    <link rel="stylesheet" href="includes/css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="images/inv-logo.png" alt="Logo">
            <h2>Inventory Management System</h2>
        </div>
        
        <form method="POST" action="">
            <?php if(isset($error)) { ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php } ?>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter username" required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter password" required>
            </div>
            
            <button type="submit" name="login" class="login-btn">
                Login
            </button>
            <div class="change-password">
                <a href="change_password.php">Change Password</a>
            </div>
            <div class="login-footer">
                Design and develop by © <a href="http://www.bibekbhandari.com.np">Bibek Bhandari</a>
            </div>
        </form>
    </div>
</body>
</html>
