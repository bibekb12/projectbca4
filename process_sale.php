<?php
session_start();
include 'db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

//Log function
function custom_log($message) {
    $log_file = '/xamppbca/xampp/htdocs/bca4/sale_process_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

header('Content-Type: application/json');

try {
    // Validate session
    if (!isset($_SESSION['user_id'])) {
        custom_log('Sale attempt without login');
        throw new Exception('Not logged in');
    }

    // Get raw POST data
    $raw_data = file_get_contents('php://input');
    custom_log('Received raw data: ' . $raw_data);

    // Parse JSON data
    $data = json_decode($raw_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        custom_log('JSON decode error: ' . json_last_error_msg());
        throw new Exception('Failed to parse JSON: ' . json_last_error_msg());
    }

    // Validate input data
    if (!isset($data['items']) || !is_array($data['items']) || empty($data['items'])) {
        custom_log('No items in sale data');
        throw new Exception('No items provided in the sale');
    }

    // Validate database connection
    if (!$conn) {
        custom_log('Database connection failed: ' . mysqli_connect_error());
        throw new Exception('Database connection failed: ' . mysqli_connect_error());
    }

    // Start transaction
    $conn->begin_transaction();
    custom_log('Transaction started');

    // Calculate subtotal from items
    $sub_total = 0;
    $validated_items = array();

    foreach ($data['items'] as $item) {
        // Validate each item
        if (!isset($item['id'], $item['quantity'], $item['price'], $item['total'])) {
            custom_log('Malformed item data: ' . json_encode($item));
            throw new Exception('Invalid item data. Missing required fields.');
        }

        // Type cast and validate
        $item_id = intval($item['id']);
        $quantity = intval($item['quantity']);
        $price = floatval($item['price']);
        $total = floatval($item['total']);

        // Validate stock
        $stock_check = $conn->prepare("SELECT stock_quantity, itemname FROM items WHERE id = ?");
        if ($stock_check === false) {
            custom_log('Stock check prepare failed: ' . $conn->error);
            throw new Exception('Failed to prepare stock check: ' . $conn->error);
        }

        $stock_check->bind_param('i', $item_id);
        $stock_check->execute();
        $stock_result = $stock_check->get_result();
        $stock_data = $stock_result->fetch_assoc();

        if (!$stock_data || $stock_data['stock_quantity'] < $quantity) {
            custom_log('Insufficient stock for item: ' . $stock_data['itemname'] . '. Available: ' . $stock_data['stock_quantity'] . ', Requested: ' . $quantity);
            throw new Exception('Insufficient stock for item: ' . $stock_data['itemname']);
        }

        $sub_total += $total;
        $validated_items[] = array(
            'item_id' => $item_id,
            'quantity' => $quantity,
            'price' => $price,
            'total' => $total
        );
    }
    custom_log('Subtotal calculated: ' . $sub_total);

    // Calculate totals
    $discount_percent = isset($data['discount_percent']) ? floatval($data['discount_percent']) : 0;
    $discount_amount = ($sub_total * $discount_percent) / 100;
    $vat_percent = 13; // Fixed VAT rate
    $subtotal_after_discount = $sub_total - $discount_amount;
    $vat_amount = $subtotal_after_discount * ($vat_percent / 100);
    $net_total = $subtotal_after_discount + $vat_amount;

    custom_log('Financial calculations - Discount: ' . $discount_amount . ', VAT: ' . $vat_amount . ', Net Total: ' . $net_total);

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
        custom_log('Sale query prepare failed: ' . $conn->error);
        throw new Exception('Failed to prepare sale query: ' . $conn->error);
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
        custom_log('Sale query bind failed: ' . $stmt->error);
        throw new Exception('Failed to bind sale query parameters: ' . $stmt->error);
    }

    // Execute sale insertion
    $execute_result = $stmt->execute();
    if ($execute_result === false) {
        custom_log('Sale insertion failed: ' . $stmt->error);
        throw new Exception('Failed to insert sale: ' . $stmt->error);
    }

    $sale_id = $conn->insert_id;
    if ($sale_id <= 0) {
        custom_log('Invalid sale ID generated');
        throw new Exception('Failed to get valid sale ID');
    }
    custom_log('Sale inserted with ID: ' . $sale_id);

    // Prepare sale items insertion
    $item_query = "INSERT INTO sale_items (sale_id, item_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)";
    $item_stmt = $conn->prepare($item_query);
    if ($item_stmt === false) {
        custom_log('Sale items query prepare failed: ' . $conn->error);
        throw new Exception('Failed to prepare sale items query: ' . $conn->error);
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
            custom_log('Sale item bind failed: ' . $item_stmt->error);
            throw new Exception('Failed to bind sale item parameters: ' . $item_stmt->error);
        }

        $item_execute_result = $item_stmt->execute();
        if ($item_execute_result === false) {
            custom_log('Sale item insertion failed: ' . $item_stmt->error);
            throw new Exception('Failed to insert sale item: ' . $item_stmt->error);
        }

        // Update stock
        $update_stock = $conn->prepare("UPDATE items SET stock_quantity = stock_quantity - ? WHERE id = ?");
        if ($update_stock === false) {
            custom_log('Stock update prepare failed: ' . $conn->error);
            throw new Exception('Failed to prepare stock update: ' . $conn->error);
        }

        $update_stock->bind_param('ii', $item['quantity'], $item['item_id']);
        $update_stock_result = $update_stock->execute();
        if ($update_stock_result === false) {
            custom_log('Stock update failed: ' . $update_stock->error);
            throw new Exception('Failed to update stock: ' . $update_stock->error);
        }
    }

    // Commit transaction
    $conn->commit();
    custom_log('Sale transaction completed successfully');

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
            custom_log('Bill items query prepare failed: ' . $conn->error);
            throw new Exception('Failed to prepare bill items query: ' . $conn->error);
        }

        // Bind parameters
        $bind_result = $items_query->bind_param('i', $sale_id);
        if ($bind_result === false) {
            custom_log('Bill items query bind failed: ' . $items_query->error);
            throw new Exception('Failed to bind bill items query parameters: ' . $items_query->error);
        }

        // Execute query
        $execute_result = $items_query->execute();
        if ($execute_result === false) {
            custom_log('Bill items query execution failed: ' . $items_query->error);
            throw new Exception('Failed to execute bill items query: ' . $items_query->error);
        }

        // Get results
        $items_result = $items_query->get_result();
        if ($items_result === false) {
            custom_log('Failed to get bill items result: ' . $items_query->error);
            throw new Exception('Failed to get bill items result: ' . $items_query->error);
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
            <h2>SIMPLE IMS</h2>
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
            custom_log('No items found for sale ID: ' . $sale_id);
            throw new Exception('No items found for this sale');
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
        // Log bill generation error
        custom_log('Bill generation error: ' . $e->getMessage());
        $bill_html = 'Error generating bill: ' . $e->getMessage();
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
        custom_log('Bill saved successfully: ' . $bill_filename);
    } catch (Exception $e) {
        custom_log('Error saving bill: ' . $e->getMessage());
    }

    // Update sales table to include bill filename
    $update_bill_query = $conn->prepare("UPDATE sales SET bill_file = ? WHERE id = ?");
    $update_bill_query->bind_param('si', $bill_filename, $sale_id);
    $update_bill_query->execute();
    if ($update_bill_query->error) {
        custom_log('Error updating bill filename in sales table: ' . $update_bill_query->error);
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
    custom_log('Sale processing error: ' . $e->getMessage());

    // Send error response
    $response = array(
        'success' => false,
        'message' => $e->getMessage()
    );

    echo json_encode($response);
    exit;
}
?>
