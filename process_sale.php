<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

try {
    // Get POST data and log it for debugging
    $raw_data = file_get_contents('php://input');
    error_log("Received sale data: " . $raw_data);
    
    $data = json_decode($raw_data, true);
    
    // Validate received data
    if (!$data) {
        throw new Exception('Failed to parse JSON data: ' . json_last_error_msg());
    }
    
    if (empty($data['items'])) {
        throw new Exception('No items in sale');
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Calculate totals
        $sub_total = floatval($data['sub_total']);
        $discount_percent = floatval($data['discount_percent']);
        $discount_amount = ($sub_total * $discount_percent) / 100;
        $vat_percent = 13; // Fixed VAT rate
        $vat_amount = floatval($data['vat_amount']);
        $net_total = floatval($data['net_total']);

        // Prepare customer data
        $customer_id = isset($data['customer_id']) && !empty($data['customer_id']) ? intval($data['customer_id']) : NULL;
        $customer_name = isset($data['customer_name']) && !empty($data['customer_name']) ? $data['customer_name'] : 'Cash';
        $customer_contact = isset($data['customer_contact']) ? $data['customer_contact'] : '';
        $payment_method = isset($data['payment_method']) ? $data['payment_method'] : 'cash';
        $user_id = intval($_SESSION['user_id']);

        // Insert sale record
        $sale_query = "INSERT INTO sales (
            customer_id, customer_name, customer_contact, 
            sub_total, discount_percent, discount_amount,
            vat_percent, vat_amount, net_total,
            payment_method, sale_date, user_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

        $stmt = $conn->prepare($sale_query);
        if (!$stmt) {
            throw new Exception('Failed to prepare sale query: ' . $conn->error);
        }

        $stmt->bind_param('issddddddsi', 
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

        if (!$stmt->execute()) {
            throw new Exception('Failed to insert sale: ' . $stmt->error);
        }

        $sale_id = $conn->insert_id;

        // Insert sale items
        foreach ($data['items'] as $item) {
            // Validate item data
            if (!isset($item['id'], $item['quantity'], $item['price'], $item['total'])) {
                throw new Exception('Invalid item data');
            }

            // Check stock availability
            $stock_check = $conn->prepare("SELECT stock_quantity FROM items WHERE id = ?");
            $stock_check->bind_param('i', $item['id']);
            $stock_check->execute();
            $stock_result = $stock_check->get_result();
            $stock_data = $stock_result->fetch_assoc();

            if (!$stock_data || $stock_data['stock_quantity'] < $item['quantity']) {
                throw new Exception('Insufficient stock for item ID: ' . $item['id']);
            }

            // Update stock
            $update_stock = $conn->prepare("UPDATE items SET stock_quantity = stock_quantity - ? WHERE id = ?");
            $update_stock->bind_param('ii', $item['quantity'], $item['id']);
            if (!$update_stock->execute()) {
                throw new Exception('Failed to update stock: ' . $update_stock->error);
            }

            // Insert sale item
            $insert_item = $conn->prepare("INSERT INTO sale_items (sale_id, item_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
            $insert_item->bind_param('iiidd', $sale_id, $item['id'], $item['quantity'], $item['price'], $item['total']);
            if (!$insert_item->execute()) {
                throw new Exception('Failed to insert sale item: ' . $insert_item->error);
            }
        }

        // Commit transaction
        $conn->commit();

        // Generate bill HTML
        $bill_html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sale Invoice</title>
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
        @media print {
            body {
                width: 148mm;
                height: 210mm;
            }
        }
    </style>
</head>
<body>
    <div class="bill-print">
        <div class="bill-header">
            <h2>SIMPLE IMS</h2>
            <p>Sale Invoice</p>
            <p>Invoice #: ' . str_pad($sale_id, 6, '0', STR_PAD_LEFT) . '</p>
            <p>Date: ' . date('Y-m-d H:i:s') . '</p>
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
        
        foreach ($data['items'] as $item) {
            $bill_html .= '<tr>
                <td>' . htmlspecialchars($item['name']) . '</td>
                <td>' . htmlspecialchars($item['quantity']) . '</td>
                <td>$' . number_format($item['price'], 2) . '</td>
                <td>$' . number_format($item['total'], 2) . '</td>
            </tr>';
        }
        
        $bill_html .= '</tbody></table>
        <div class="bill-totals">
            <p>Sub Total: $' . number_format($sub_total, 2) . '</p>
            <p>Discount (' . number_format($discount_percent, 2) . '%): $' . number_format($discount_amount, 2) . '</p>
            <p>VAT (' . number_format($vat_percent, 2) . '%): $' . number_format($vat_amount, 2) . '</p>
            <p><strong>Net Total: $' . number_format($net_total, 2) . '</strong></p>
            <p>Payment Method: ' . ucfirst($payment_method) . '</p>
        </div>
        <div class="bill-footer">
            <p>Thank you for Purchase</p>
            <p>Billed by: ' . htmlspecialchars($_SESSION['username']) . '</p>
        </div>
    </div>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>';

        echo json_encode([
            'success' => true,
            'sale_id' => $sale_id,
            'bill_html' => $bill_html
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Sale processing error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

} catch (Exception $e) {
    error_log("Sale processing error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
