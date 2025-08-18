<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config.php';

$pid = isset($_POST['pid']) ? preg_replace('/[^A-Za-z0-9\-_:]/', '', $_POST['pid']) : '';
$amt = isset($_POST['amt']) ? (float) $_POST['amt'] : 0;
$rid = isset($_POST['rid']) ? preg_replace('/[^A-Za-z0-9\-_:]/', '', $_POST['rid']) : '';

if (!$pid || $amt <= 0 || !$rid) {
    echo json_encode(['success' => false, 'message' => 'Missing pid/amt/rid']);
    exit;
}

$verifyParams = http_build_query([
    'amt' => number_format($amt, 2, '.', ''),
    'scd' => $merchantCode,
    'pid' => $pid,
    'rid' => $rid,
]);

$verifyUrl = $epayVerifyUrl . '?' . $verifyParams;
$ch = curl_init($verifyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$curlErr = curl_error($ch);
curl_close($ch);

if ($result && stripos($result, 'Success') !== false) {
    // Mark paid in tmp store for consistency with success.php
    $storeDir = __DIR__ . '/tmp_status';
    if (!is_dir($storeDir)) @mkdir($storeDir, 0777, true);
    @file_put_contents($storeDir . '/' . $pid . '.json', json_encode([
        'status' => 'paid',
        'refId' => $rid,
        'amount' => $amt,
        'time' => date('c'),
    ]));
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $result ?: $curlErr ?: 'verify failed']);
}

