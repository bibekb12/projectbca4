<?php
session_start();
// Check if user is logged in and has a valid role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
}

// Check if role is valid
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'user') {
    session_unset();
    session_destroy();
    header('Location: index.php?error=invalid_role');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/inv-logo.png" type="image/icon type">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Admin Dashboard Panel</title>
</head>
<body>
<?php include('includes/sidebar.php'); ?>

    <section class="dashboard">
        <div class="top">
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
                <a href="logout.php" class="logout-btn">Logout</a>
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
        modeToggle = body.querySelector(".mode-toggle");

        let getMode = localStorage.getItem("mode");
        if(getMode && getMode === "dark") {
            body.classList.toggle("dark");
        }

        let getStatus = localStorage.getItem("status");
        if(getStatus && getStatus === "close") {
            sidebar.classList.toggle("close");
            // Update tooltip text based on sidebar state
            // Removed sidebarToggle.setAttribute('data-tooltip', 'Expand');
        }

        // Removed sidebarToggle.addEventListener("click", () => {
        //     sidebar.classList.toggle("close");
        //     // Update tooltip text based on sidebar state
        //     const isClose = sidebar.classList.contains("close");
        //     // Removed sidebarToggle.setAttribute('data-tooltip', isClose ? 'Expand' : 'Minimize');
        //     localStorage.setItem("status", isClose ? "close" : "open");
        // });
    </script>
</body>
</html>
