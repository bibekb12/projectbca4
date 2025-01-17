<?php
session_start();
include 'db.php';

if (!isset($_GET['bill_no'])) {
    die('Bill number not provided');
}

$bill_no = $_GET['bill_no'];

// Get bill details
$stmt = $conn->prepare("
    SELECT s.*, c.name as customer_name, c.contact as customer_contact, u.username
    FROM sales s
    JOIN customers c ON s.customer_id = c.id
    JOIN users u ON s.user_id = u.id
    WHERE s.bill_no = ?
");
$stmt->bind_param("s", $bill_no);
$stmt->execute();
$bill = $stmt->get_result()->fetch_assoc();

// Get bill items
$stmt = $conn->prepare("
    SELECT si.*, i.name as item_name
    FROM sales_items si
    JOIN items i ON si.item_id = i.id
    WHERE si.sale_id = ?
");
$stmt->bind_param("i", $bill['id']);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill #<?php echo $bill_no; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .bill-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .bill-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .company-address {
            margin-bottom: 15px;
        }
        .bill-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .customer-info, .bill-details {
            flex: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .total-section {
            text-align: right;
            margin-top: 20px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 14px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
            .bill-container {
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="bill-container">
        <div class="bill-header">
            <div class="company-name">Your Company Name</div>
            <div class="company-address">
                123 Business Street<br>
                City, Country<br>
                Phone: (123) 456-7890
            </div>
        </div>

        <div class="bill-info">
            <div class="customer-info">
                <h3>Bill To:</h3>
                <p>
                    <strong>Name:</strong> <?php echo htmlspecialchars($bill['customer_name']); ?><br>
                    <strong>Contact:</strong> <?php echo htmlspecialchars($bill['customer_contact']); ?>
                </p>
            </div>
            <div class="bill-details">
                <h3>Bill Details:</h3>
                <p>
                    <strong>Bill No:</strong> <?php echo htmlspecialchars($bill_no); ?><br>
                    <strong>Date:</strong> <?php echo date('d-m-Y H:i', strtotime($bill['sale_date'])); ?><br>
                    <strong>Cashier:</strong> <?php echo htmlspecialchars($bill['username']); ?>
                </p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>SN</th>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $sn = 1;
                foreach ($items as $item): 
                ?>
                <tr>
                    <td><?php echo $sn++; ?></td>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                    <td>$<?php echo number_format($item['total'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align: right;"><strong>Grand Total:</strong></td>
                    <td><strong>$<?php echo number_format($bill['total_amount'], 2); ?></strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            Thank you for your business!<br>
            Please visit again.
        </div>

        <div class="no-print" style="margin-top: 20px; text-align: center;">
            <button onclick="window.print();" style="padding: 10px 20px; cursor: pointer;">
                Print Bill
            </button>
            <button onclick="window.location.href='sale.php'" style="padding: 10px 20px; margin-left: 10px; cursor: pointer;">
                Back to Sales
            </button>
        </div>
    </div>
</body>
</html> 