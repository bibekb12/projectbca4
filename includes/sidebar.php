<?php
// Get the current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav>
    <div class="logo-name">
        <div class="logo-image">
            <img src="images/logo.png" alt="">
        </div>
        <span class="logo_name">Inventory Management</span>
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
                <a href="usersetup.php" class="<?php echo ($current_page == 'usersetup.php') ? 'active' : ''; ?>">
                    <i class="uil uil-users-alt"></i>
                    <span class="link-name">User Setup</span>
                </a>
            </li>
            <li>
                <a href="itemsetup.php" class="<?php echo ($current_page == 'itemsetup.php') ? 'active' : ''; ?>">
                    <i class="uil uil-box"></i>
                    <span class="link-name">Item Setup</span>
                </a>
            </li>
            <li>
                <a href="suppliersetup.php" class="<?php echo ($current_page == 'suppliersetup.php') ? 'active' : ''; ?>">
                    <i class="uil uil-truck"></i>
                    <span class="link-name">Supplier Setup</span>
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