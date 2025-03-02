<?php
session_start();
header('Content-Type: text/html');

// Include database connection
include('db.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo '<tr><td colspan="6">Unauthorized access</td></tr>';
    exit;
}

try {
    // Fetch recent sales with more comprehensive data
    $recent_sales = $conn->query("SELECT s.*, 
        s.customer_contact, 
        u.username AS sale_user 
        FROM sales s 
        LEFT JOIN customers c ON s.customer_id = c.id 
        LEFT JOIN users u ON s.user_id = u.id
        ORDER BY s.sale_date DESC 
        LIMIT 10");
    
    // Check if query was successful
    if ($recent_sales === false) {
        echo "<tr><td colspan='6'>Error fetching transactions: " . $conn->error . "</td></tr>";
    } elseif ($recent_sales->num_rows === 0) {
        echo "<tr><td colspan='6'>No recent transactions found.</td></tr>";
    } else {
        while ($sale = $recent_sales->fetch_assoc()) {
            // Determine payment method display
            $payment_method = !empty($sale['payment_method']) ? 
                ucfirst(strtolower($sale['payment_method'])) : 
                'Cash';

            // Determine bill file status
            $bill_status = !empty($sale['bill_file']) ? 
                '<span class="badge badge-success">Generated</span>' : 
                '<span class="badge badge-warning">Pending</span>';

            echo "<tr data-sale-id='{$sale['id']}'>
                <td class='invoice-number'>" . str_pad($sale['id'], 6, '0', STR_PAD_LEFT) . "</td>
                <td>
                    <div class='customer-cell'>
                        <span class='customer-name'>" . htmlspecialchars($sale['customer_name']) . "</span>
                        <span class='customer-contact'>" . 
                            (!empty($sale['customer_contact']) ? 
                            htmlspecialchars($sale['customer_contact']) : 
                            'N/A') . 
                        "</span>
                    </div>
                </td>
                <td class='amount'>Rs. " . number_format($sale['net_total'], 2) . "</td>
                <td class='payment-method'>" . htmlspecialchars($payment_method) . "</td>
                <td class='time'>" . date('h:i A', strtotime($sale['sale_date'])) . "</td>
                <td class='actions'>
                    <div class='btn-group'>
                        <button type='button' class='btn-primary btn-sm' onclick='showBillOptions(" . $sale['id'] . ")'>
                            <i class='fa fa-file-text'></i> View/Reprint Bill
                        </button>
                    </div>
                </td>
            </tr>";
        }
    }
} catch (Exception $e) {
    echo "<tr><td colspan='6'>An error occurred: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
} finally {
    $conn->close();
}
?>
