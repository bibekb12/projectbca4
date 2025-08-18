<?php
// eSewa failure/cancel callback
require_once __DIR__ . '/config.php';

$orderId = isset($_GET['oid']) ? $_GET['oid'] : (isset($_GET['pid']) ? $_GET['pid'] : '');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
</head>
<body>
    <!-- Show basic failure message and order reference -->
    <h2>Payment Failed or Cancelled</h2>
    <?php if (!empty($orderId)): ?>
      <p>Order ID: <?php echo htmlspecialchars($orderId); ?></p>
    <?php endif; ?>
    <p>Please try again or choose a different payment method.</p>
</body>
</html>


