<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="top">
            <i class="uil uil-bars sidebar-toggle"></i>
            <div class="user-greeting">
                <i class="uil uil-user-circle"></i>
                <span>Welcome, <span class="username"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?></span></span>
            </div>
        </div>

    <section class="dashboard">
        <div class="top">
            <i class="uil uil-bars sidebar-toggle" data-tooltip="Minimize"></i>
        </div>
        <div class="dash-content">
            <div class="overview">
                <div class="title">
                    <i class="uil uil-setting"></i>
                    <span class="text">System Setup</span>
                </div>

                <div class="setup-box">
                    <div class="setup-grid">
                        <a href="usersetup.php" class="setup-card">
                            <i class="uil uil-users-alt"></i>
                            <span class="text">User Setup</span>
                        </a>
                        <a href="itemsetup.php" class="setup-card">
                            <i class="uil uil-box"></i>
                            <span class="text">Item Setup</span>
                        </a>
                        <a href="suppliersetup.php" class="setup-card">
                            <i class="uil uil-truck"></i>
                            <span class="text">Supplier Setup</span>
                        </a>
                        <!-- Add more setup options as needed -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Dark mode toggle
        const body = document.querySelector("body"),
        modeToggle = body.querySelector(".mode-toggle"),
        sidebar = body.querySelector("nav"),
        sidebarToggle = body.querySelector(".sidebar-toggle");

        let getMode = localStorage.getItem("mode");
        if(getMode && getMode === "dark") {
            body.classList.toggle("dark");
        }

        let getStatus = localStorage.getItem("status");
        if(getStatus && getStatus === "close") {
            sidebar.classList.toggle("close");
        }

        sidebarToggle.addEventListener("click", () => {
            sidebar.classList.toggle("close");
            if(sidebar.classList.contains("close")){
                localStorage.setItem("status", "close");
            }else{
                localStorage.setItem("status", "open");
            }
        });

        modeToggle.addEventListener("click", () => {
            body.classList.toggle("dark");
            if(body.classList.contains("dark")) {
                localStorage.setItem("mode", "dark");
            } else {
                localStorage.setItem("mode", "light");
            }
        });
    </script>
</body>
</html>