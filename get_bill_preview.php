<?php
session_start();
include 'db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access");
}

// Validate sale ID
$sale_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($sale_id <= 0) {
    die("Invalid sale ID");
}

// Check database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch sale details with username
$sale_query = "SELECT 
    s.id, 
    s.sale_date, 
    s.customer_name,
    u.username as sale_user
    FROM sales s
    LEFT JOIN users u ON s.user_id = u.id
    WHERE s.id = $sale_id";
$sale_result = $conn->query($sale_query);

if (!$sale_result) {
    die("Sale query failed: " . $conn->error);
}

if ($sale_result->num_rows === 0) {
    die("Sale not found");
}

$sale = $sale_result->fetch_assoc();

// Fetch sale items with calculation
$items_query = "SELECT 
    COALESCE(i.itemname, 'Unknown Item') as item_name, 
    si.quantity, 
    si.price, 
    si.total 
    FROM sale_items si
    LEFT JOIN items i ON si.item_id = i.id
    WHERE si.sale_id = $sale_id";
$items_result = $conn->query($items_query);

if (!$items_result) {
    die("Items query failed: " . $conn->error);
}

// Calculate totals
$total_amount = 0;
$vat_amount = 0;
$items = [];

while ($item = $items_result->fetch_assoc()) {
    $total_amount += $item['total'];
    $items[] = $item;
}

// Estimate VAT (assuming 13% if not stored separately)
$vat_amount = $total_amount * 0.13;
$net_total = $total_amount + $vat_amount;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sale Invoice</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 600px; 
            margin: 0 auto; 
            padding: 20px;
            line-height: 1.6;
        }
        .invoice-container {
            border: 1px solid #ddd;
            padding: 20px;
        }
        .invoice-header {
            text-align: center;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px;
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
        }
        .invoice-footer {
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <h1>Sales Invoice</h1>
            <p>Invoice #<?php echo str_pad($sale_id, 6, '0', STR_PAD_LEFT); ?></p>
        </div>

        <div class="invoice-details">
            <div>
                <strong>Customer:</strong> <?php echo htmlspecialchars($sale['customer_name']); ?>
            </div>
            <div>
                <strong>Date:</strong> <?php echo htmlspecialchars($sale['sale_date']); ?>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (count($items) > 0) {
                    foreach ($items as $item) {
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                    <td>Rs. <?php echo number_format($item['total'], 2); ?></td>
                </tr>
                <?php 
                    } 
                } else { 
                ?>
                <tr>
                    <td colspan="4">No items found for this sale</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="invoice-footer">
            <div>
                <strong>Subtotal:</strong> Rs. <?php echo number_format($total_amount, 2); ?><br>
                <strong>VAT (13%):</strong> Rs. <?php echo number_format($vat_amount, 2); ?><br>
                <strong>Net Total:</strong> Rs. <?php echo number_format($net_total, 2); ?>
            </div>
            <div>
                <strong>Prepared by:</strong> <?php echo htmlspecialchars(isset($sale['sale_user']) ? $sale['sale_user'] : 'Unknown User'); ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// Close connection
$conn->close();
?>
