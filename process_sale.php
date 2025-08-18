<?php
session_start();
header('Content-Type: application/json');

// Disable error display to prevent HTML output
ini_set('display_errors', 0);
error_reporting(0);

try {
    // Include database connection
    require_once 'db.php';

    // Validate session
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false, 
            'message' => 'Not logged in'
        ]);
        exit;
    }

    // Get raw POST data
    $raw_data = file_get_contents('php://input');

    // Parse JSON data
    $data = json_decode($raw_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid JSON: ' . json_last_error_msg()
        ]);
        exit;
    }

    // Validate input data
    if (!isset($data['items']) || !is_array($data['items']) || empty($data['items'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'No items provided in the sale'
        ]);
        exit;
    }

    // Validate database connection
    if (!$conn) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Database connection failed: ' . mysqli_connect_error()
        ]);
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    // Calculate subtotal from items
    $sub_total = 0;
    $validated_items = array();

    foreach ($data['items'] as $item) {
        // Validate each item
        if (!isset($item['id'], $item['quantity'], $item['price'], $item['total'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'message' => 'Invalid item data. Missing required fields.'
            ]);
            exit;
        }

        // Type cast and validate
        $item_id = intval($item['id']);
        $quantity = intval($item['quantity']);
        $price = floatval($item['price']);
        $total = floatval($item['total']);

        // Validate stock
        $stock_check = $conn->prepare("SELECT stock_quantity, itemname FROM items WHERE id = ?");
        if ($stock_check === false) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to prepare stock check: ' . $conn->error
            ]);
            exit;
        }

        $stock_check->bind_param('i', $item_id);
        $stock_check->execute();
        $stock_result = $stock_check->get_result();
        $stock_data = $stock_result->fetch_assoc();

        if (!$stock_data || $stock_data['stock_quantity'] < $quantity) {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'message' => 'Insufficient stock for item: ' . $stock_data['itemname']
            ]);
            exit;
        }

        $sub_total += $total;
        $validated_items[] = array(
            'item_id' => $item_id,
            'quantity' => $quantity,
            'price' => $price,
            'total' => $total
        );
    }

    // Calculate totals
    $discount_percent = isset($data['discount_percent']) ? floatval($data['discount_percent']) : 0;
    $discount_amount = ($sub_total * $discount_percent) / 100;
    $vat_percent = 13; // Fixed VAT rate
    $subtotal_after_discount = $sub_total - $discount_amount;
    $vat_amount = $subtotal_after_discount * ($vat_percent / 100);
    $net_total = $subtotal_after_discount + $vat_amount;

    // Prepare customer data
    $customer_id = isset($data['customer_id']) ? intval($data['customer_id']) : null;
    $customer_name = isset($data['customer_name']) ? $data['customer_name'] : 'Cash';
    $customer_contact = isset($data['customer_contact']) ? $data['customer_contact'] : '';
    $payment_method = isset($data['payment_method']) ? $data['payment_method'] : 'cash';
    $user_id = intval($_SESSION['user_id']);

    // Prepare sale insertion query
    $sale_query = "INSERT INTO sales (
        customer_id, customer_name, customer_contact, 
        sub_total, discount_percent, discount_amount,
        vat_percent, vat_amount, net_total,
        payment_method, sale_date, user_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

    // Prepare sale statement
    $stmt = $conn->prepare($sale_query);
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to prepare sale query: ' . $conn->error
        ]);
        exit;
    }

    // Bind sale parameters
    $bind_result = $stmt->bind_param('issddddddsi', 
        $customer_id,
        $customer_name,
        $customer_contact,
        $sub_total,
        $discount_percent,
        $discount_amount,
        $vat_percent,
        $vat_amount,
        $net_total,
        $payment_method,
        $user_id
    );

    if ($bind_result === false) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to bind sale query parameters: ' . $stmt->error
        ]);
        exit;
    }

    // Execute sale insertion
    $execute_result = $stmt->execute();
    if ($execute_result === false) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to insert sale: ' . $stmt->error
        ]);
        exit;
    }

    $sale_id = $conn->insert_id;
    if ($sale_id <= 0) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to get valid sale ID'
        ]);
        exit;
    }

    // Prepare sale items insertion
    $item_query = "INSERT INTO sale_items (sale_id, item_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)";
    $item_stmt = $conn->prepare($item_query);
    if ($item_stmt === false) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to prepare sale items query: ' . $conn->error
        ]);
        exit;
    }

    // Process each validated item
    foreach ($validated_items as $item) {
        // Bind and execute item insertion
        $item_bind_result = $item_stmt->bind_param('iiidd', 
            $sale_id, 
            $item['item_id'], 
            $item['quantity'], 
            $item['price'], 
            $item['total']
        );
        if ($item_bind_result === false) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to bind sale item parameters: ' . $item_stmt->error
            ]);
            exit;
        }

        $item_execute_result = $item_stmt->execute();
        if ($item_execute_result === false) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to insert sale item: ' . $item_stmt->error
            ]);
            exit;
        }

        // Update stock
        $update_stock = $conn->prepare("UPDATE items SET stock_quantity = stock_quantity - ? WHERE id = ?");
        if ($update_stock === false) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to prepare stock update: ' . $conn->error
            ]);
            exit;
        }

        $update_stock->bind_param('ii', $item['quantity'], $item['item_id']);
        $update_stock_result = $update_stock->execute();
        if ($update_stock_result === false) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to update stock: ' . $update_stock->error
            ]);
            exit;
        }
    }

    // Commit transaction
    $conn->commit();

    // Generate bill HTML
    $bill_html = '';
    try {
        // Fetch and add items to bill HTML
        $items_query = $conn->prepare("SELECT i.itemname, si.quantity, si.price, si.total 
                                       FROM sale_items si 
                                       JOIN items i ON si.item_id = i.id 
                                       WHERE si.sale_id = ?");
        
        // Check if prepare was successful
        if ($items_query === false) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to prepare bill items query: ' . $conn->error
            ]);
            exit;
        }

        // Bind parameters
        $bind_result = $items_query->bind_param('i', $sale_id);
        if ($bind_result === false) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to bind bill items query parameters: ' . $items_query->error
            ]);
            exit;
        }

        // Execute query
        $execute_result = $items_query->execute();
        if ($execute_result === false) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to execute bill items query: ' . $items_query->error
            ]);
            exit;
        }

        // Get results
        $items_result = $items_query->get_result();
        if ($items_result === false) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to get bill items result: ' . $items_query->error
            ]);
            exit;
        }

        // Start bill HTML
        $bill_html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sale Invoice #' . str_pad($sale_id, 6, '0', STR_PAD_LEFT) . '</title>
    <style>
        @page {
            size: A5;
            margin: 10mm;
        }
        body {
            width: 148mm;
            margin: 0 auto;
            padding: 0;
            font-family: "Courier New", monospace;
            font-size: 12px;
        }
        .bill-print {
            padding: 10mm;
            position: relative;
        }
        .bill-header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 5mm;
            margin-bottom: 5mm;
        }
        .bill-header h2 {
            margin: 0;
            font-size: 18px;
        }
        .sale-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5mm;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 3mm;
        }
        .customer-info {
            margin-bottom: 5mm;
        }
        .bill-items {
            width: 100%;
            border-collapse: collapse;
            margin: 5mm 0;
        }
        .bill-items th {
            border-bottom: 1px solid #000;
            text-align: left;
            padding: 2mm;
        }
        .bill-items td {
            padding: 2mm;
            border-bottom: 1px dotted #ccc;
        }
        .bill-totals {
            margin-top: 5mm;
            border-top: 1px dashed #000;
            padding-top: 5mm;
            text-align: right;
        }
        .bill-footer {
            text-align: center;
            margin-top: 10mm;
            font-size: 11px;
        }
        .reprint-btn {
            position: absolute;
            top: 10mm;
            right: 10mm;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            text-decoration: none;
            font-size: 10px;
        }
        @media print {
            body {
                width: 148mm;
                height: 210mm;
            }
            .reprint-btn {
                display: none;
            }
        }
    </style>
    <script>
        function printBill() {
            window.print();
        }
    </script>
</head>
<body>
    <div class="bill-print">
        <a href="#" class="reprint-btn" onclick="printBill()">🖨️ Reprint Bill</a>
        
        <div class="bill-header">
            <h2>Inventory Management System</h2>
            <p>Sale Invoice</p>
        </div>
        
        <div class="sale-details">
            <div>
                <p><strong>Invoice #:</strong> ' . str_pad($sale_id, 6, '0', STR_PAD_LEFT) . '</p>
                <p><strong>Date & Time:</strong> ' . date('Y-m-d H:i:s') . '</p>
            </div>
            <div style="text-align: right;">
                <p><strong>Payment Method:</strong> ' . htmlspecialchars($payment_method) . '</p>
            </div>
        </div>

        <div class="customer-info">
            <p><strong>Customer:</strong> ' . htmlspecialchars($customer_name) . '</p>
            <p><strong>Contact:</strong> ' . htmlspecialchars($customer_contact) . '</p>
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

        // Check if any items were found
        if ($items_result->num_rows === 0) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'No items found for sale ID: ' . $sale_id
            ]);
            exit;
        }

        // Add items to bill
        while ($item = $items_result->fetch_assoc()) {
            $bill_html .= '
                <tr>
                    <td>' . htmlspecialchars($item['itemname']) . '</td>
                    <td>' . $item['quantity'] . '</td>
                    <td>' . number_format($item['price'], 2) . '</td>
                    <td>' . number_format($item['total'], 2) . '</td>
                </tr>';
        }

        $bill_html .= '
            </tbody>
        </table>
        <div class="bill-totals">
            <p><strong>Subtotal:</strong> ' . number_format($sub_total, 2) . '</p>
            <p><strong>Discount (' . $discount_percent . '%):</strong> ' . number_format($discount_amount, 2) . '</p>
            <p><strong>VAT (13%):</strong> ' . number_format($vat_amount, 2) . '</p>
            <p><strong>Net Total:</strong> ' . number_format($net_total, 2) . '</p>
        </div>
        <div class="bill-footer">
            <p>Thank you for your purchase!</p>
        </div>
    </div>
</body>
</html>';

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Error generating bill: ' . $e->getMessage()
        ]);
        exit;
    }

    // Create bills directory if not exists
    $bills_dir = 'bills/';
    if (!file_exists($bills_dir)) {
        mkdir($bills_dir, 0777, true);
    }

    // Generate unique bill filename
    $bill_filename = $bills_dir . 'bill_' . str_pad($sale_id, 6, '0', STR_PAD_LEFT) . '_' . date('Ymd_His') . '.html';

    // Save bill HTML to file
    try {
        file_put_contents($bill_filename, $bill_html);
        } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Error saving bill: ' . $e->getMessage()
        ]);
        exit;
    }

    // Update sales table to include bill filename
    $update_bill_query = $conn->prepare("UPDATE sales SET bill_file = ? WHERE id = ?");
    $update_bill_query->bind_param('si', $bill_filename, $sale_id);
    $update_bill_query->execute();
    if ($update_bill_query->error) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Error updating sales table: ' . $update_bill_query->error
        ]);
        exit;
    }

    // Prepare response
    $response = array(
        'success' => true,
        'sale_id' => $sale_id,
        'bill_html' => $bill_html,
        'message' => 'Sale processed successfully'
    );

    echo json_encode($response);
    exit;

} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->errno) {
        $conn->rollback();
    }

    // Log the full error

    // Send error response
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'An unexpected error occurred'
    ]);
    exit;
}
?>
