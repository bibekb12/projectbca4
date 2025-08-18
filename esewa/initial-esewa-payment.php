<?php
header('Content-Type: application/json');

$amount = $_POST['amount'];
$orderId = $_POST['orderId'];

// Replace with your eSewa merchant info
$merchantCode = 'YOUR_MERCHANT_CODE';
$successUrl = 'https://yourdomain.com/payment-success';
$failureUrl = 'https://yourdomain.com/payment-failure';

$paymentUrl = "https://epay.esewa.com.np/epay/main" .
    "?amt={$amount}" .
    "&pdc=0&psc=0&txAmt=0" .
    "&tAmt={$amount}" .
    "&pid={$orderId}" .
    "&scd={$merchantCode}" .
    "&su=" . urlencode($successUrl) .
    "&fu=" . urlencode($failureUrl);

echo json_encode([
    'success' => true,
    'paymentUrl' => $paymentUrl
]);
