<?php
session_start();
include 'db.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Invalid data received: ' . json_last_error_msg());
    }

    // Validate required data
    if (empty($data['customer_name']) || empty($data['items']) || !is_array($data['items'])) {
        throw new Exception('Missing required data');
    }

    // Start transaction
    $conn->begin_transaction();

    // Save customer if contact is provided
    $customer_id = null;
    if (!empty($data['customer_contact']) && $data['customer_name'] !== 'Cash') {
        // Call add_customer.php via internal request
        $customer_data = array(
            'name' => $data['customer_name'],
            'contact' => $data['customer_contact']
        );
        
        $ch = curl_init('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/add_customer.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($customer_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            if ($result && isset($result['customer_id'])) {
                $customer_id = $result['customer_id'];
            }
        }
    }

    // Insert into sales table
    $sale_query = "INSERT INTO sales (
        customer_id, customer_name, customer_contact, 
        total_amount, sub_total, discount_percent, discount_amount,
        vat_percent, vat_amount, net_total, payment_method,
        sale_date, user_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
    $stmt = $conn->prepare($sale_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare sale query: ' . $conn->error);
    }

    $stmt->bind_param("issdddddddsi", 
        $customer_id,
        $data['customer_name'],
        $data['customer_contact'],
        $data['sub_total'],
        $data['sub_total'],
        $data['discount_percent'],
        $data['discount_amount'],
        $data['vat_percent'],
        $data['vat_amount'],
        $data['net_total'],
        $data['payment_method'],
        $_SESSION['user_id']
    );

    if (!$stmt->execute()) {
        throw new Exception('Failed to insert sale: ' . $stmt->error);
    }
    $sale_id = $conn->insert_id;

    // Insert sale items
    $item_query = "INSERT INTO sale_items (sale_id, item_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)";
    $item_stmt = $conn->prepare($item_query);
    if (!$item_stmt) {
        throw new Exception('Failed to prepare item query: ' . $conn->error);
    }

    // Update inventory
    $update_stock = "UPDATE items SET stock_quantity = stock_quantity - ? WHERE id = ?";
    $stock_stmt = $conn->prepare($update_stock);
    if (!$stock_stmt) {
        throw new Exception('Failed to prepare stock update query: ' . $conn->error);
    }

    foreach ($data['items'] as $item) {
        // Validate item data
        if (empty($item['id']) || empty($item['quantity']) || empty($item['price'])) {
            throw new Exception('Invalid item data');
        }

        // Insert sale item
        $item_stmt->bind_param("iiddd",
            $sale_id,
            $item['id'],
            $item['quantity'],
            $item['price'],
            $item['total']
        );
        
        if (!$item_stmt->execute()) {
            throw new Exception('Failed to insert sale item: ' . $item_stmt->error);
        }

        // Update stock
        $stock_stmt->bind_param("di", $item['quantity'], $item['id']);
        if (!$stock_stmt->execute()) {
            throw new Exception('Failed to update stock: ' . $stock_stmt->error);
        }
    }

    // Generate bill HTML for printing
    $bill_html = generateBillHTML($sale_id, $data);
    
    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'sale_id' => $sale_id,
        'bill_html' => $bill_html
    ]);

} catch (Exception $e) {
    // Rollback on error
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }
    
    // Log the error
    error_log('Sale Error: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error processing sale: ' . $e->getMessage()
    ]);
}

function generateBillHTML($sale_id, $data) {
    $html = '
    <div class="bill-print">
        <div class="bill-header">
            <h2>SIMPLE IMS</h2>
            <p>Sale Invoice</p>
            <p>Invoice #: ' . str_pad($sale_id, 6, '0', STR_PAD_LEFT) . '</p>
            <p>Date: ' . date('Y-m-d H:i:s') . '</p>
        </div>
        <div class="customer-info">
            <p><strong>Customer Name:</strong> ' . htmlspecialchars($data['customer_name']) . '</p>
            <p><strong>Contact:</strong> ' . htmlspecialchars($data['customer_contact']) . '</p>
        </div>
        <table class="bill-items">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($data['items'] as $item) {
        $html .= '<tr>
            <td>' . htmlspecialchars($item['name']) . '</td>
            <td>' . htmlspecialchars($item['quantity']) . '</td>
            <td>' . number_format((float)$item['price'], 2) . '</td>
            <td>' . number_format((float)$item['total'], 2) . '</td>
        </tr>';
    }
    
    $html .= '</tbody></table>
        <div class="bill-totals">
            <table>
                <tr>
                    <td>Sub Total:</td>
                    <td>' . number_format($data['sub_total'], 2) . '</td>
                </tr>
                <tr>
                    <td>Discount (' . number_format($data['discount_percent'], 2) . '%):</td>
                    <td>' . number_format($data['discount_amount'], 2) . '</td>
                </tr>
                <tr>
                    <td>VAT (' . number_format($data['vat_percent'], 2) . '%):</td>
                    <td>' . number_format($data['vat_amount'], 2) . '</td>
                </tr>
                <tr>
                    <td><strong>Net Total:</strong></td>
                    <td><strong>' . number_format($data['net_total'], 2) . '</strong></td>
                </tr>
                <tr>
                    <td>Payment Method:</td>
                    <td>' . ucfirst($data['payment_method']) . '</td>
                </tr>
            </table>
        </div>
        <div class="bill-footer">
            <p>Thank you for your business!</p>
        </div>
    </div>';
    
    return $html;
}

// Close database connection
if (isset($conn)) {
    $conn->close();
}
?>
