<?php
// eSewa configuration
// TODO: Replace EPAYTEST and base URL for production

$isSandbox = true; // Set to false for production

// Merchant/service code (test merchant is usually 'EPAYTEST')
$merchantCode = 'EPAYTEST'; // <-- CHANGE THIS TO YOUR LIVE MERCHANT CODE

// Your app base URL. Ensure this matches your local/prod base including folder path
// e.g., local XAMPP path
$appBaseUrl = 'http://localhost/projectbca4';

// eSewa endpoints
$epayBase = $isSandbox ? 'https://uat.esewa.com.np' : 'https://epay.esewa.com.np';
$epayMainUrl = $epayBase . '/epay/main';
$epayVerifyUrl = $epayBase . '/epay/transrec';

// Callback URLs
$successUrl = $appBaseUrl . '/esewa/success.php';
$failureUrl = $appBaseUrl . '/esewa/failure.php';

?>

