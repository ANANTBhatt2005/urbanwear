<?php
session_start();
require_once 'api-helper.php';
$login = $API->post('/api/v1/auth/login', ['email'=>'anant@gmail.com','password'=>'123456']);
$token = $login['data']['token'] ?? $login['token'] ?? null;
if (!$token) {
    echo "Login failed: ";
    print_r($login);
    exit;
}
$orders = $API->get('/api/v1/admin/orders', $token);
echo "ADMIN ORDERS: " . json_encode($orders);
$user_orders = $API->get('/api/v1/orders', $token);
echo "\nUSER ORDERS: " . json_encode($user_orders);
