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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        :root {
            --primary-color: #695CFE;
            --secondary-color: #6C757D;
            --background-color: #F6F5FF;
            --text-color: #707070;
            --error-color: #DC3545;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--background-color);
            padding: 20px;
        }

        .login-container {
            position: relative;
            max-width: 400px;
            width: 100%;
            background: #fff;
            padding: 40px 30px;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
        }

        .login-container .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
        }

        .login-container .logo img {
            width: 60px;
            margin-right: 15px;
        }

        .login-container .logo h2 {
            color: var(--primary-color);
            font-size: 1.8rem;
            font-weight: 600;
        }

        .login-container form {
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            color: var(--text-color);
            font-weight: 500;
            font-size: 0.9rem;
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            height: 45px;
            width: 100%;
            outline: none;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 0 15px;
            font-size: 0.95rem;
            color: var(--text-color);
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            border-color: var(--primary-color);
        }

        .error-message {
            color: var(--error-color);
            font-size: 0.85rem;
            margin-top: 5px;
            text-align: center;
            margin-bottom: 15px;
        }

        .login-btn {
            width: 100%;
            height: 45px;
            background: var(--primary-color);
            border: none;
            border-radius: 6px;
            color: #fff;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            background: #5344FE;
        }

        .login-footer {
            margin-top: 20px;
            text-align: center;
            color: var(--text-color);
            font-size: 0.9rem;
        }

        @media screen and (max-width: 400px) {
            .login-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="images/inv-logo.png" alt="Logo">
            <h2>SIMS</h2>
        </div>
        
        <form method="POST" action="">
            <?php if(isset($error)) { ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php } ?>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" name="login" class="login-btn">
                Login
            </button>
        </form>
        
        <div class="login-footer">
            Stock Inventory Management System
        </div>
    </div>
</body>
</html>
