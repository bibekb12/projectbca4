<?php
// eSewa success callback
// Expected GET params: amt, refId (or referenceId), oid/pid, etc.

require_once __DIR__ . '/config.php';

// Capture params returned by eSewa
$amount = isset($_GET['amt']) ? (float) $_GET['amt'] : 0;
$refId = isset($_GET['refId']) ? $_GET['refId'] : (isset($_GET['referenceId']) ? $_GET['referenceId'] : '');
$orderId = isset($_GET['oid']) ? $_GET['oid'] : (isset($_GET['pid']) ? $_GET['pid'] : '');

// Verify transaction with eSewa server-to-server
$verifyParams = http_build_query([
    'amt' => number_format($amount, 2, '.', ''),
    'scd' => $merchantCode,
    'pid' => $orderId,
    'rid' => $refId,
]);

$verifyUrl = $epayVerifyUrl . '?' . $verifyParams;

$ch = curl_init($verifyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$curlErr = curl_error($ch);
curl_close($ch);

$verified = false;
if ($result && stripos($result, 'Success') !== false) {
    $verified = true;
}

// Simple HTML response for now; in app you should mark sale as paid
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eSewa Payment Status</title>
</head>
<body>
<?php if ($verified): ?>
    <!-- Payment verified successfully -->
    <h2>Payment Success</h2>
    <p>Order ID: <?php echo htmlspecialchars($orderId); ?></p>
    <p>Reference ID: <?php echo htmlspecialchars($refId); ?></p>
    <p>Amount: <?php echo htmlspecialchars(number_format($amount, 2)); ?></p>
    <p>Thank you for your payment.</p>
    <?php
      // Store status to a temp file so frontend can poll via status.php
      $storeDir = __DIR__ . '/tmp_status';
      if (!is_dir($storeDir)) {
          @mkdir($storeDir, 0777, true);
      }
      @file_put_contents($storeDir . '/' . preg_replace('/[^A-Za-z0-9\-_:]/', '', $orderId) . '.json', json_encode([
          'status' => 'paid',
          'refId' => $refId,
          'amount' => $amount,
          'time' => date('c'),
      ]));
    ?>
<?php else: ?>
    <!-- Payment not verified; show error -->
    <h2>Payment Verification Failed</h2>
    <p>We could not verify your payment with eSewa.</p>
    <pre><?php echo htmlspecialchars($result ?: $curlErr ?: 'No response'); ?></pre>
<?php endif; ?>
</body>
</html>


