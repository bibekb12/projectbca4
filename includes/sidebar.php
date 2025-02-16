<?php
// Get the current page name
$current_page = basename($_SERVER['PHP_SELF']);
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<link rel="icon" href="images/t-logo.png" type="image/icon type">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<nav>
    <div class="logo-name">
        <div class="logo-image">
            <img src="images/logo.png" alt="">
        </div>
        <span class="logo_name">S I M S</span>
    </div>

    <div class="menu-items">
        <ul class="nav-links">
            <li>
                <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="uil uil-estate"></i>
                    <span class="link-name">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="sale.php" class="<?php echo ($current_page == 'sale.php') ? 'active' : ''; ?>">
                    <i class="fa fa-money" ></i>
                    <span class="link-name">Sale</span>
                </a>
            </li>
            <li>
                <a href="purchase.php" class="<?php echo ($current_page == 'purchase.php') ? 'active' : ''; ?>">
                    <i class="fa fa-shopping-cart"></i>
                    <span class="link-name">Purchase</span>
                </a>
            </li>
            <li>
                <a href="report.php" class="<?php echo ($current_page == 'report.php') ? 'active' : ''; ?>">
                    <i class="uil uil-chart"></i>
                    <span class="link-name">Report</span>
                </a>
            </li>
            <?php if ($is_admin): ?>
            <li>
                <a href="setup.php" class="<?php echo ($current_page == 'setup.php') ? 'active' : ''; ?>">
                    <i class="uil uil-setting"></i>
                    <span class="link-name">Setup</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</nav> 