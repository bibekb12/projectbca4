<?php
include 'db.php';

function tableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result->num_rows > 0;
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Create users table if not exists
    $users_table = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(15) UNIQUE,
        password VARCHAR(255) NOT NULL,
        status CHAR(1) DEFAULT 'Y',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT NULL,
        role ENUM('admin', 'user') NOT NULL DEFAULT 'user'
    )";
    
    if (!$conn->query($users_table)) {
        throw new Exception("Error creating users table: " . $conn->error);
    }

    // Insert admin user
    $admin_user = "INSERT INTO users (username, password, role) VALUES ('admin', '" . password_hash('admin', PASSWORD_BCRYPT) . "', 'admin')";
    
    if (!$conn->query($admin_user)) {
        throw new Exception("Error inserting admin user: " . $conn->error);
    }

    // Create suppliers table
    $suppliers_table = "CREATE TABLE IF NOT EXISTS suppliers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        contact VARCHAR(20),
        email VARCHAR(100),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        panno INT,
        status CHAR(1) DEFAULT 'Y'
    )";
    
    if (!$conn->query($suppliers_table)) {
        throw new Exception("Error creating suppliers table: " . $conn->error);
    }

    // Create customers table
    $customers_table = "CREATE TABLE IF NOT EXISTS customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        contact VARCHAR(20) UNIQUE,
        email VARCHAR(100),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($customers_table)) {
        throw new Exception("Error creating customers table: " . $conn->error);
    }

    // Create items table
    $items_table = "CREATE TABLE IF NOT EXISTS items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        description TEXT,
        cost_price DECIMAL(10,2) NOT NULL,
        sell_price DECIMAL(10,2) NOT NULL,
        stock_quantity INT NOT NULL DEFAULT 0,
        reorder_level INT DEFAULT 10,
        supplier_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        itemcode VARCHAR(5),
        itemname VARCHAR(50),
        status CHAR(1) DEFAULT 'Y',
        last_sale_date DATETIME NULL,
        last_purchase_date DATETIME NULL,
        total_sales_quantity INT DEFAULT 0,
        total_purchases_quantity INT DEFAULT 0,
        FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
    )";
    
    if (!$conn->query($items_table)) {
        throw new Exception("Error creating items table: " . $conn->error);
    }

    // Create sales table
    // bill_file: Stores the path to the generated HTML bill for each sale
    $sales_table = "CREATE TABLE IF NOT EXISTS sales (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NULL,
        customer_name VARCHAR(100) NOT NULL,
        customer_contact VARCHAR(20),
        total_amount DECIMAL(10,2) NOT NULL,
        sub_total DECIMAL(10,2) NOT NULL,
        discount_percent DECIMAL(5,2) DEFAULT 0,
        discount_amount DECIMAL(10,2) DEFAULT 0,
        vat_percent DECIMAL(5,2) DEFAULT 13,
        vat_amount DECIMAL(10,2) DEFAULT 0,
        net_total DECIMAL(10,2) NOT NULL,
        payment_method ENUM('cash','credit','bank','card','qrpay') DEFAULT 'cash',
        sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        user_id INT,
        bill_file VARCHAR(255) NULL COMMENT 'Stores path to generated HTML bill for each sale transaction',
        device_info VARCHAR(255) NULL,
        browser_info VARCHAR(255) NULL,
        sale_location VARCHAR(100) NULL,
        bill_generation_status ENUM('success', 'failed', 'partial') DEFAULT 'success',
        FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )";
    
    if (!$conn->query($sales_table)) {
        throw new Exception("Error creating sales table: " . $conn->error);
    }

    // Create sale_items table
    $sale_items_table = "CREATE TABLE IF NOT EXISTS sale_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sale_id INT NOT NULL,
        item_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
        FOREIGN KEY (item_id) REFERENCES items(id)
    )";
    
    if (!$conn->query($sale_items_table)) {
        throw new Exception("Error creating sale_items table: " . $conn->error);
    }

    // Create purchases table
    $purchases_table = "CREATE TABLE IF NOT EXISTS purchases (
        id INT AUTO_INCREMENT PRIMARY KEY,
        supplier_id INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        user_id INT,
        FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    if (!$conn->query($purchases_table)) {
        throw new Exception("Error creating purchases table: " . $conn->error);
    }

    // Create purchase_items table
    $purchase_items_table = "CREATE TABLE IF NOT EXISTS purchase_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        purchase_id INT NOT NULL,
        item_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE,
        FOREIGN KEY (item_id) REFERENCES items(id)
    )";
    
    if (!$conn->query($purchase_items_table)) {
        throw new Exception("Error creating purchase_items table: " . $conn->error);
    }

    // Create roles table
    $roles_table = "CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        userid INT,
        group_name ENUM('admin', 'user'),
        permissions VARCHAR(50),
        FOREIGN KEY (userid) REFERENCES users(id)
    )";
    
    if (!$conn->query($roles_table)) {
        throw new Exception("Error creating roles table: " . $conn->error);
    }

    // Create transactions table
    $transactions_table = "CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        user_id INT NOT NULL,
        supplier_id INT,
        type ENUM('Sale', 'Purchase') NOT NULL,
        quantity INT NOT NULL,
        transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES items(id),
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
    )";
    
    if (!$conn->query($transactions_table)) {
        throw new Exception("Error creating transactions table: " . $conn->error);
    }

    // Create log table for tracking system events
    $log_table = "CREATE TABLE IF NOT EXISTS system_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        log_type ENUM('error', 'warning', 'info', 'sale', 'purchase', 'inventory') NOT NULL,
        message TEXT NOT NULL,
        user_id INT NULL,
        ip_address VARCHAR(45),
        log_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )";
    
    if (!$conn->query($log_table)) {
        throw new Exception("Error creating system_logs table: " . $conn->error);
    }

    // Create bill storage configuration table
    $bill_config_table = "CREATE TABLE IF NOT EXISTS bill_configurations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_name VARCHAR(100) DEFAULT 'SIMPLE IMS',
        company_address TEXT,
        vat_number VARCHAR(50),
        default_vat_rate DECIMAL(5,2) DEFAULT 13.00,
        bill_header_text TEXT,
        bill_footer_text TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($bill_config_table)) {
        throw new Exception("Error creating bill_configurations table: " . $conn->error);
    }

    // Insert default bill configuration
    $default_bill_config = "INSERT INTO bill_configurations 
        (company_name, company_address, vat_number, bill_header_text, bill_footer_text) 
        VALUES (
            'SIMPLE IMS', 
            'Inventory Management System Headquarters', 
            'VAT-123456', 
            'Official Tax Invoice', 
            'Thank you for your business. All sales are final.'
        ) ON DUPLICATE KEY UPDATE id = id";
    
    if (!$conn->query($default_bill_config)) {
        throw new Exception("Error inserting default bill configuration: " . $conn->error);
    }

    // Create bill template table for storing different bill styles
    $bill_templates_table = "CREATE TABLE IF NOT EXISTS bill_templates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        template_name VARCHAR(50) NOT NULL,
        template_type ENUM('html', 'pdf') DEFAULT 'html',
        template_content TEXT NOT NULL,
        is_default BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($bill_templates_table)) {
        throw new Exception("Error creating bill_templates table: " . $conn->error);
    }

    // Insert default bill template
    $default_bill_template = "INSERT INTO bill_templates 
        (template_name, template_type, template_content, is_default) 
        VALUES (
            'Default HTML Bill', 
            'html', 
            '<!DOCTYPE html>
<html>
<head>
    <title>Sales Invoice</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .bill-header { text-align: center; }
        .bill-details { margin: 20px 0; }
        .bill-items { width: 100%; border-collapse: collapse; }
        .bill-items th, .bill-items td { border: 1px solid #ddd; padding: 8px; }
    </style>
</head>
<body>
    <div class=\"bill-header\">
        <h1>{{COMPANY_NAME}}</h1>
        <p>Tax Invoice</p>
    </div>
    <div class=\"bill-details\">
        <p>Invoice #: {{INVOICE_NUMBER}}</p>
        <p>Date: {{SALE_DATE}}</p>
    </div>
    <table class=\"bill-items\">
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            {{ITEMS_LIST}}
        </tbody>
    </table>
    <div class=\"bill-totals\">
        <p>Subtotal: {{SUBTOTAL}}</p>
        <p>Discount: {{DISCOUNT}}</p>
        <p>VAT: {{VAT}}</p>
        <p>Net Total: {{NET_TOTAL}}</p>
    </div>
</body>
</html>', 
            TRUE
        ) ON DUPLICATE KEY UPDATE id = id";
    
    if (!$conn->query($default_bill_template)) {
        throw new Exception("Error inserting default bill template: " . $conn->error);
    }

    // Commit transaction before creating view
    $conn->commit();
    
    // Verify items table creation
    if (!tableExists($conn, 'items')) {
        throw new Exception("Failed to create items table");
    }

        echo json_encode(['success' => true, 'message' => 'Database setup completed successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        if (strpos($e->getMessage(), 'CREATE VIEW command denied') !== false) {
            echo json_encode(['success' => false, 'message' => 'CREATE VIEW command denied. Please check your database permissions.']);
        } else {
            throw $e;
        }
    }

 catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        .success {
            color: green;
            background: #e8f5e9;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .error {
            color: red;
            background: #ffebee;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .info {
            color: #1e3c72;
            background: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h2>Database Setup Status</h2>
    <div id="status"></div>
</body>
</html>
