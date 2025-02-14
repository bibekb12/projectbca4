<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    var_dump($data);
    die;
    
    if (!$data || !isset($data['items']) || empty($data['items'])) {
        throw new Exception('Invalid data received');
    }

    // Start transaction
    $conn->begin_transaction();

    // Calculate totals
    $total_amount = 0;
    foreach ($data['items'] as $item) {
        $total_amount += $item['total'];
    }

    // Insert sale record
    $sale_query = "INSERT INTO sales (
        customer_id, 
        customer_name, 
        customer_contact,
        total_amount,
        payment_method,
        sale_date,
        user_id
    ) VALUES (?, ?, ?, ?, ?, NOW(), ?)";

    $stmt = $conn->prepare($sale_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare sale query: ' . $conn->error);
    }

    $customer_id = $data['customer_id'] ?: null;
    $customer_name = $data['customer_name'] ?: 'Walk-in Customer';
    $customer_contact = $data['customer_contact'] ?: '';
    $payment_method = $data['payment_method'] ?: 'cash';
    $user_id = $_SESSION['user_id'];

    $stmt->bind_param('issdsi', 
        $customer_id,
        $customer_name,
        $customer_contact,
        $total_amount,
        $payment_method,
        $user_id
    );

    if (!$stmt->execute()) {
        throw new Exception('Failed to insert sale: ' . $stmt->error);
    }

    $sale_id = $conn->insert_id;

    // Insert sale items
    $items_query = "INSERT INTO sale_items (sale_id, item_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($items_query);

    foreach ($data['items'] as $item) {
        // Update stock quantity
        $update_stock = "UPDATE items SET stock_quantity = stock_quantity - ? WHERE id = ?";
        $stock_stmt = $conn->prepare($update_stock);
        $stock_stmt->bind_param('ii', $item['quantity'], $item['id']);
        if (!$stock_stmt->execute()) {
            throw new Exception('Failed to update stock quantity');
        }

        // Insert sale item
        $stmt->bind_param('iiidd', 
            $sale_id, 
            $item['id'], 
            $item['quantity'], 
            $item['price'], 
            $item['total']
        );
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert sale item');
        }
    }

    // Commit transaction
    $conn->commit();

    // Generate bill HTML
    $bill_html = generateBillHTML($sale_id, $data);

    echo json_encode([
        'success' => true,
        'sale_id' => $sale_id,
        'bill_html' => $bill_html
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Close connection
if (isset($conn)) {
    $conn->close();
}

// Helper function to generate bill HTML
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
            <p><strong>Customer:</strong> ' . htmlspecialchars($data['customer_name']) . '</p>
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
            <td>' . number_format($item['price'], 2) . '</td>
            <td>' . number_format($item['total'], 2) . '</td>
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
?>
