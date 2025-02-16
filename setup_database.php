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
    $admin_user = "INSERT INTO users (username, password, role) VALUES ('admin', '" . password_hash('admin_password', PASSWORD_BCRYPT) . "', 'admin')";
    
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
        FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
    )";
    
    if (!$conn->query($items_table)) {
        throw new Exception("Error creating items table: " . $conn->error);
    }

    // Create sales table
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
        payment_method ENUM('cash', 'credit', 'bank') DEFAULT 'cash',
        sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        user_id INT,
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

    // Commit transaction before creating view
    $conn->commit();
    
    // Verify items table creation
    if (!tableExists($conn, 'items')) {
        throw new Exception("Failed to create items table");
    }

    // Create vw_transaction view in a new transaction
    $conn->begin_transaction();
    try {
        $vw_transaction = "CREATE OR REPLACE VIEW vw_transaction AS
            SELECT 
                s.id,
                s.sale_date AS Date,
                'Sale' AS type,
                i.itemname AS name,
                si.quantity,
                s.total_amount AS totalamount,
                u.username
            FROM sales s
            JOIN sale_items si ON s.id = si.sale_id
            JOIN items i ON si.item_id = i.id
            JOIN users u ON s.user_id = u.id
            UNION ALL
            SELECT 
                p.id,
                p.purchase_date AS Date,
                'Purchase' AS type,
                i.itemname AS name,
                pi.quantity,
                p.total_amount AS totalamount,
                u.username
            FROM purchases p
            JOIN purchase_items pi ON p.id = pi.purchase_id
            JOIN items i ON pi.item_id = i.id
            JOIN users u ON p.user_id = u.id";
        
        if (!$conn->query($vw_transaction)) {
            throw new Exception("Error creating vw_transaction view: " . $conn->error);
        }
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Database setup completed successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        if (strpos($e->getMessage(), 'CREATE VIEW command denied') !== false) {
            echo json_encode(['success' => false, 'message' => 'CREATE VIEW command denied. Please check your database permissions.']);
        } else {
            throw $e;
        }
    }

} catch (Exception $e) {
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
