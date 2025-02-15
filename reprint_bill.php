<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No sale ID provided']);
    exit();
}

try {
    $sale_id = intval($_GET['id']);
    
    // Get sale details
    $sale_query = "SELECT s.*, u.username FROM sales s 
                   JOIN users u ON s.user_id = u.id 
                   WHERE s.id = ?";
    $stmt = $conn->prepare($sale_query);
    $stmt->bind_param('i', $sale_id);
    $stmt->execute();
    $sale = $stmt->get_result()->fetch_assoc();

    if (!$sale) {
        throw new Exception('Sale not found');
    }

    // Get sale items
    $items_query = "SELECT si.*, i.itemname as name 
                   FROM sale_items si 
                   JOIN items i ON si.item_id = i.id 
                   WHERE si.sale_id = ?";
    $stmt = $conn->prepare($items_query);
    $stmt->bind_param('i', $sale_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Generate bill HTML
    $bill_html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Sale Invoice (Reprint)</title>
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
                <p>Sale Invoice (REPRINT)</p>
                <p>Invoice #: ' . str_pad($sale['id'], 6, '0', STR_PAD_LEFT) . '</p>
                <p>Date: ' . date('Y-m-d H:i:s', strtotime($sale['sale_date'])) . '</p>
            </div>
            <div class="customer-info">
                <p><strong>Customer:</strong> ' . htmlspecialchars($sale['customer_name']) . '</p>
                <p><strong>Contact:</strong> ' . htmlspecialchars($sale['customer_contact']) . '</p>
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
        
        foreach ($items as $item) {
            $bill_html .= '<tr>
                <td>' . htmlspecialchars($item['name']) . '</td>
                <td>' . htmlspecialchars($item['quantity']) . '</td>
                <td>Rs. ' . number_format($item['price'], 2) . '</td>
                <td>Rs. ' . number_format($item['total'], 2) . '</td>
            </tr>';
        }
        
        $bill_html .= '</tbody></table>
            <div class="bill-totals">
                <p>Sub Total: Rs. ' . number_format($sale['sub_total'], 2) . '</p>
                <p>Discount (' . number_format($sale['discount_percent'], 2) . '%): Rs. ' . number_format($sale['discount_amount'], 2) . '</p>
                <p>VAT (' . number_format($sale['vat_percent'], 2) . '%): Rs. ' . number_format($sale['vat_amount'], 2) . '</p>
                <p><strong>Net Total: Rs. ' . number_format($sale['net_total'], 2) . '</strong></p>
                <p>Payment Method: ' . ucfirst($sale['payment_method']) . '</p>
            </div>
            <div class="bill-footer">
                <p>Thank you for Purchase</p>
                <p>Billed by: ' . htmlspecialchars($sale['username']) . '</p>
                <p style="font-style: italic; color: #666;">Reprinted copy</p>
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
        'bill_html' => $bill_html
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
