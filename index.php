<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login Page</title>
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body>
    <div class="login-container">
      <div class="login-box">
        <h1>Inventory Management System</h1>
        <h2>Login</h2>
        <form action="login.php" method="post">
          <div class="textbox">
            <input
              type="text"
              placeholder="Username"
              name="username"
              required
            />
          </div>
          <div class="textbox">
            <input
              type="password"
              placeholder="Password"
              name="password"
              required
            />
          </div>
          <input type="submit" value="Login" class="btn" />
        </form>
      </div>
    </div>
    <?php
    if (isset($_SESSION['error'])) {
        echo '<div class="error">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
    ?>
  </body>
</html>
