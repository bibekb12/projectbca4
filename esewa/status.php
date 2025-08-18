<?php
header('Content-Type: application/json');

// Simple file-based status store for demo purposes.
// In production, store payment status in DB against your sale/order.

$pid = isset($_GET['pid']) ? preg_replace('/[^A-Za-z0-9\-_:]/', '', $_GET['pid']) : '';
if (!$pid) {
    echo json_encode(['status' => 'unknown', 'message' => 'missing pid']);
    exit;
}

$storeDir = __DIR__ . '/tmp_status';
if (!is_dir($storeDir)) {
    @mkdir($storeDir, 0777, true);
}

$file = $storeDir . '/' . $pid . '.json';
if (!file_exists($file)) {
    echo json_encode(['status' => 'pending']);
    exit;
}

$json = @file_get_contents($file);
$data = json_decode($json, true);
if (!$data) {
    echo json_encode(['status' => 'pending']);
    exit;
}

echo json_encode(['status' => $data['status'] ?? 'pending']);

