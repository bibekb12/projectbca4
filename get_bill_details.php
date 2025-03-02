<?php
session_start();
header('Content-Type: application/json');

// Include database connection
include('db.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

try {
    // Validate sale ID
    $sale_id = isset($_GET['sale_id']) ? intval($_GET['sale_id']) : 0;
    if ($sale_id <= 0) {
        throw new Exception('Invalid sale ID');
    }

    // Fetch sale details
    $sale_query = $conn->prepare("SELECT * FROM sales WHERE id = ?");
    $sale_query->bind_param('i', $sale_id);
    $sale_query->execute();
    $sale_result = $sale_query->get_result();
    
    if ($sale_result->num_rows === 0) {
        throw new Exception('Sale not found');
    }
    
    $sale = $sale_result->fetch_assoc();

    // Fetch sale items
    $items_query = $conn->prepare("SELECT * FROM sale_items WHERE sale_id = ?");
    $items_query->bind_param('i', $sale_id);
    $items_query->execute();
    $items_result = $items_query->get_result();

    // Generate bill HTML
    $bill_html = "<div class='bill-view'>";
    $bill_html .= "<h2>Sale Invoice #" . str_pad($sale_id, 6, '0', STR_PAD_LEFT) . "</h2>";
    $bill_html .= "<p>Date: " . htmlspecialchars($sale['sale_date']) . "</p>";
    $bill_html .= "<p>Customer: " . htmlspecialchars($sale['customer_name']) . "</p>";
    
    $bill_html .= "<table>";
    $bill_html .= "<thead><tr><th>Item</th><th>Quantity</th><th>Price</th><th>Total</th></tr></thead>";
    $bill_html .= "<tbody>";
    
    while ($item = $items_result->fetch_assoc()) {
        $bill_html .= "<tr>";
        $bill_html .= "<td>" . htmlspecialchars($item['item_name']) . "</td>";
        $bill_html .= "<td>" . htmlspecialchars($item['quantity']) . "</td>";
        $bill_html .= "<td>Rs. " . number_format($item['price'], 2) . "</td>";
        $bill_html .= "<td>Rs. " . number_format($item['total'], 2) . "</td>";
        $bill_html .= "</tr>";
    }
    
    $bill_html .= "</tbody>";
    $bill_html .= "<tfoot>";
    $bill_html .= "<tr><td colspan='3'>Subtotal</td><td>Rs. " . number_format($sale['sub_total'], 2) . "</td></tr>";
    $bill_html .= "<tr><td colspan='3'>Discount</td><td>Rs. " . number_format($sale['discount'], 2) . "</td></tr>";
    $bill_html .= "<tr><td colspan='3'>VAT</td><td>Rs. " . number_format($sale['vat_amount'], 2) . "</td></tr>";
    $bill_html .= "<tr><td colspan='3'><strong>Net Total</strong></td><td><strong>Rs. " . number_format($sale['net_total'], 2) . "</strong></td></tr>";
    $bill_html .= "</tfoot>";
    $bill_html .= "</table>";
    $bill_html .= "</div>";

    echo json_encode([
        'success' => true,
        'bill_html' => $bill_html
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    $conn->close();
}
?>
