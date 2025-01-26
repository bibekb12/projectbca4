<?php
include 'db.php';

try {
    // Start transaction
    $conn->begin_transaction();

    // Create sales table
    $sales_table = "CREATE TABLE IF NOT EXISTS sales (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT,
        customer_name VARCHAR(100),
        customer_contact VARCHAR(20),
        total_amount DECIMAL(10,2) NOT NULL,
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

    // Commit transaction
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Sales tables created successfully']);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?> 