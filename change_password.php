<?php
session_start(); 
require 'db.php';

$password_changed = isset($_SESSION['password_changed']) && $_SESSION['password_changed'] === true;

if ($password_changed) {
    unset($_SESSION['password_changed']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'])) {
    header('Content-Type: application/json'); 

    $response = ["success" => false, "error" => ""];

    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $current_password = mysqli_real_escape_string($conn, trim($_POST['currentPassword']));
    $new_password = mysqli_real_escape_string($conn, trim($_POST['newPassword']));
    
    if (empty($username) || empty($current_password) || empty($new_password)) {
        $response["error"] = "Please fill in all fields.";
    } else {
        $query = "SELECT * FROM users WHERE username = ? AND status = 'Y'";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($current_password, $user['password'])) {
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $update_query = "UPDATE users SET password = ? WHERE username = ?";
                $stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($stmt, "ss", $new_password_hash, $username);
                
                if (mysqli_stmt_execute($stmt)) {
                    $response["success"] = true;
                    
                
                    $_SESSION['password_changed'] = true;
                } else {
                    $response["error"] = "Error updating password.";
                }
            } else {
                $response["error"] = "Invalid current password.";
            }
        } else {
            $response["error"] = "User not found.";
        }
    }

    echo json_encode($response);
    exit(); 
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        input {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            margin-bottom: 1px;
        }
        .btn-cancel {
            background: #dc3545;
            margin-top: 10px;
        }
        .form-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .error {
            color: red;
            font-size: 12px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h3>Change Password</h3>
        <form id="passwordForm">
            <input type="text" id="username" name="username" placeholder="Username" required>
            <input type="password" id="currentPassword" name="currentPassword" placeholder="Current Password" required>
            <input type="password" id="newPassword" name="newPassword" placeholder="New Password" required>
            <input type="password" id="confirmPassword" placeholder="Confirm Password" required>
            <p class="error" id="errorMessage"></p>
            <div class="form-buttons">
                <button type="submit">Change Password</button>
                <button type="button" class="btn btn-cancel btn-danger" onclick="window.location.href='index.php'">Home</button>
            </div>
        </form>
    </div>

    <script>
        const passwordChanged = <?php echo json_encode($password_changed); ?>;
        if (passwordChanged) {
            if (confirm("Password changed successfully!")) {
                window.location.href = "index.php";
            }
        }

        document.getElementById("passwordForm").addEventListener("submit", function(event) {
            event.preventDefault();
            let username = document.getElementById("username").value;
            let currentPassword = document.getElementById("currentPassword").value;
            let newPassword = document.getElementById("newPassword").value;
            let confirmPassword = document.getElementById("confirmPassword").value;
            let errorMessage = document.getElementById("errorMessage");

            errorMessage.textContent = "";

            if (newPassword.length < 3) {
                errorMessage.textContent = "Password must be at least 3 characters.";
                return;
            }

            if (newPassword !== confirmPassword) {
                errorMessage.textContent = "New passwords do not match!";
                return;
            }

            fetch("change_password.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: new URLSearchParams({
                    username: username,
                    currentPassword: currentPassword,
                    newPassword: newPassword
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    errorMessage.textContent = data.error || "Something went wrong!";
                }
            })
            .catch(error => {
                errorMessage.textContent = "Server error. Try again later.";
            });
        });
    </script>

</body>
</html>
