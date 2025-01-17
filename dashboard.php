<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    // Clear any existing session data
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Admin Dashboard Panel</title>
</head>
<body>
    <nav>
        <div class="logo-name">
            <div class="logo-image">
               <img src="images/logo.png" alt="">
            </div>
            <span class="logo_name">Inventory Management</span>
        </div>
        <div class="menu-items">
            <ul class="nav-links">
                <li><a href="#">
                    <i class="uil uil-estate"></i>
                    <span class="link-name">Dashboard</span>
                </a></li>
                <li><a href="sale.php">
                    <i class="fa fa-money" aria-hidden="true"></i>
                    <span class="link-name">Sale</span>
                </a></li>
                <li><a href="purchase.php">
                    <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                    <span class="link-name">Purchase</span>
                </a></li>
                <li><a href="report.php">
                    <i class="uil uil-chart"></i>
                    <span class="link-name">Report</span>
                </a></li>
                <li> <a href="setup.php">
                    <i class="uil uil-setting"></i>
                    <span class="link-name"> Setup </span> 
            </ul>
            
            <ul class="logout-mode">
                <li><a href="logout.php">
                    <i class="uil uil-signout"></i>
                    <span class="link-name">Logout</span>
                </a></li>
                </div>
            </li>
            </ul>
        </div>
    </nav>
    <section class="dashboard">
        <div class="top">
            <i class="uil uil-bars sidebar-toggle"></i>
            <div class="user-greeting">
                <i class="uil uil-user-circle"></i>
                <span>Welcome, <span class="username">
                    <?php 
                    if(isset($_SESSION['username'])) {
                        echo htmlspecialchars($_SESSION['username']);
                    } else {
                        header('Location: index.php');
                        exit();
                    }
                    ?>
                </span></span>
            </div>
        </div>
        <div class="dash-content">
            <div class="overview">
                <div class="title">
                    <i class="uil uil-estate"></i>
                    <span class="text">Dashboard</span>
                </div>
                <div class="boxes">
                    <div class="box box1">
                        <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                        <span class="text">Purchased</span>
                        <span class="number"> <?php 
            include('db.php'); 
            $result = $conn->query("SELECT SUM(totalamount) AS total FROM vw_transaction where type='Purchase'");
            if ($result && $row = $result->fetch_assoc()) {
                echo $row['total'];
            } else {
                echo "0";
            }
            $conn->close();
            ?></span>
                    </div>
                    <div class="box box2">
                        <i class="fa fa-money" aria-hidden="true"></i>
                        <span class="text">Sales</span>
                        <span class="number"> <?php 
            include('db.php'); 
            $result = $conn->query("SELECT SUM(totalamount) AS total FROM vw_transaction where type='Sale'");
            if ($result && $row = $result->fetch_assoc()) {
                echo $row['total'];
            } else {
                echo "0";
            }
            $conn->close();
            ?></span>
                    </div>
                    <div class="box box3">
                        <i class="fa fa-file-text-o" aria-hidden="true"></i>
                        <span class="text">Stock</span>
                        <span class="number"> <?php 
            include('db.php'); 
            $result = $conn->query("select sum(stock_quantity) as total from items");
            if ($result && $row = $result->fetch_assoc()) {
                echo $row['total'];
            } else {
                echo "0";
            }
            $conn->close();
            ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </section>
    <script src="script.js"></script>
    <script>
        const body = document.querySelector("body"),
        sidebar = body.querySelector("nav"),
        sidebarToggle = body.querySelector(".sidebar-toggle"),
        modeToggle = body.querySelector(".mode-toggle");

        let getMode = localStorage.getItem("mode");
        if(getMode && getMode === "dark") {
            body.classList.toggle("dark");
        }

        let getStatus = localStorage.getItem("status");
        if(getStatus && getStatus === "close") {
            sidebar.classList.toggle("close");
            // Update tooltip text based on sidebar state
            sidebarToggle.setAttribute('data-tooltip', 'Expand');
        }

        sidebarToggle.addEventListener("click", () => {
            sidebar.classList.toggle("close");
            // Update tooltip text based on sidebar state
            const isClose = sidebar.classList.contains("close");
            sidebarToggle.setAttribute('data-tooltip', isClose ? 'Expand' : 'Minimize');
            localStorage.setItem("status", isClose ? "close" : "open");
        });
    </script>
</body>
</html>
