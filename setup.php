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
    <link rel="icon" href="images/inv-logo.png" type="image/icon type">
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
<?php include('includes/sidebar.php'); ?>

    <section class="dashboard">
        <div class="top">
            <div class="user-greeting">
                <i class="uil uil-user-circle"></i>
                <span>Welcome, <span class="username"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?></span></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <div class="dash-content">
                <div class="setup-container">
                    <div class="setup-grid">
                        
                        <!-- User Setup Card -->
                        <div class="setup-card">
                            <i class="uil uil-users-alt icon"></i>
                            <h3>User Management</h3>
                            <p>Manage system users, roles, and permissions</p>
                            <a href="usersetup.php" class="setup-btn">Manage Users</a>
                        </div>

                        <!-- Item Setup Card -->
                        <div class="setup-card">
                            <i class="uil uil-box icon"></i>
                            <h3>Item Setup</h3>
                            <p>Configure items, categories, and inventory</p>
                            <a href="itemsetup.php" class="setup-btn">Manage Items</a>
                        </div>
                        
                        <!-- Supplier Setup Card -->
                        <div class="setup-card">
                            <i class="uil uil-store icon"></i>
                            <h3>Supplier Setup</h3>
                            <p>Manage supplier information and details</p>
                            <a href="suppliersetup.php" class="setup-btn">Manage Supplier</a>
                        </div>
                        <!-- Database Setup Card -->
                        <div class="setup-card">
                            <i class="uil uil-database icon"></i>
                            <h3>Database Setup</h3>
                            <p>Configure and manage database tables</p>
                            <button id="setupDatabase" class="setup-btn">Setup Database</button>
                            <div id="dbStatus" class="setup-status"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="script.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#setupDatabase').on('click', function() {
            const button = $(this);
            const statusDiv = $('#dbStatus');
            
            // Disable button and show loading
            button.prop('disabled', true).text('Setting up...');
            statusDiv.html('<div class="info">Setting up database...</div>');
            
            // Make AJAX call to setup_database.php
            $.ajax({
                url: 'setup_database.php',
                method: 'GET',
                success: function(response) {
                    statusDiv.html('<div class="success">Database setup completed successfully!</div>');
                },
                error: function(xhr, status, error) {
                    statusDiv.html('<div class="error">Error setting up database: ' + error + '</div>');
                },
                complete: function() {
                    button.prop('disabled', false).text('Setup Database');
                }
            });
        });
    });
    </script>

    <style>
    /* Add these styles to your existing CSS */
    .setup-status {
        margin-top: 15px;
        font-size: 0.9em;
    }

    .setup-status .success {
        color: #28a745;
        background: #e8f5e9;
        padding: 10px;
        border-radius: 5px;
    }

    .setup-status .error {
        color: #dc3545;
        background: #ffebee;
        padding: 10px;
        border-radius: 5px;
    }

    .setup-status .info {
        color: #1e3c72;
        background: #e3f2fd;
        padding: 10px;
        border-radius: 5px;
    }

    #setupDatabase:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
    </style>
</body>
</html>