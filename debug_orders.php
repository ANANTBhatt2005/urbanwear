<?php
require_once 'api-helper.php';
session_start();
header('Content-Type: application/json');

$token = getAuthToken();
if (!$token) {
    echo json_encode(['error' => 'No token found']);
    exit;
}

$response = $API->get('/api/v1/admin/orders', $token);
echo json_encode([
    'raw_response_keys' => array_keys($response),
    'success' => $response['success'] ?? false,
    'orders_count' => isset($response['orders']) ? count($response['orders']) : null,
    'data_count' => isset($response['data']) ? count($response['data']) : null,
    'first_order_sample' => isset($response['orders'][0]) ? $response['orders'][0] : (isset($response['data'][0]) ? $response['data'][0] : null)
], JSON_PRETTY_PRINT);
