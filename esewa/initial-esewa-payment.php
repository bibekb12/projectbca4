<?php
header('Content-Type: application/json');

// Basic validation and sanitization
$amount = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;
$orderId = isset($_POST['orderId']) ? preg_replace('/[^A-Za-z0-9\-_:]/', '', $_POST['orderId']) : '';

if ($amount <= 0 || empty($orderId)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid amount or order id'
    ]);
    exit;
}

require_once __DIR__ . '/config.php';

// Build eSewa URL
$amt = number_format($amount, 2, '.', '');
$taxAmount = 0; // Adjust if you want to break out VAT for eSewa separately
$serviceCharge = 0; // pdc
$productDeliveryCharge = 0; // psc
$totalAmount = $amt; // tAmt must equal sum

$query = http_build_query([
    'amt' => $amt,
    'pdc' => $productDeliveryCharge,
    'psc' => $serviceCharge,
    'txAmt' => $taxAmount,
    'tAmt' => $totalAmount,
    'pid' => $orderId,
    'scd' => $merchantCode,
    'su' => $successUrl,
    'fu' => $failureUrl,
]);

$paymentUrl = $epayMainUrl . '?' . $query;

echo json_encode([
    'success' => true,
    'paymentUrl' => $paymentUrl,
]);
