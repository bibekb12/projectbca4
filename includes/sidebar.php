<?php
// Get the current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav>
    <div class="logo-name">
        <div class="logo-image">
            <img src="images/logo.png" alt="">
        </div>
        <span class="logo_name">SIMPLE IMS</span>
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
                    <i class="fa fa-money" aria-hidden="true"></i>
                    <span class="link-name">Sale</span>
                </a>
            </li>
            <li>
                <a href="purchase.php" class="<?php echo ($current_page == 'purchase.php') ? 'active' : ''; ?>">
                    <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                    <span class="link-name">Purchase</span>
                </a>
            </li>
            <li>
                <a href="report.php" class="<?php echo ($current_page == 'report.php') ? 'active' : ''; ?>">
                    <i class="uil uil-chart"></i>
                    <span class="link-name">Report</span>
                </a>
            </li>
            <li>
                <a href="setup.php" class="<?php echo ($current_page == 'setup.php') ? 'active' : ''; ?>">
                    <i class="uil uil-setting"></i>
                    <span class="link-name">Setup</span>
                </a>
            </li>
        </ul>

        <ul class="logout-mode">
            <li>
                <a href="logout.php">
                    <i class="uil uil-signout"></i>
                    <span class="link-name">Logout</span>
                </a>
            </li>
        </ul>
    </div>
</nav> 