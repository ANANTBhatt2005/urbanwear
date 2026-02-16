<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "urbanwear");
if ($conn->connect_error) {
  echo json_encode(["success" => false, "error" => "Connection failed"]);
  exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$cart = $data['cart'] ?? [];
$email = $data['email'] ?? '';
$address = $data['address'] ?? '';

if (empty($cart) || !$email || !$address) {
  echo json_encode(["success" => false, "error" => "Invalid data"]);
  exit;
}

$total = 0;
foreach ($cart as $item) {
  $total += $item['price'] * $item['quantity'];
}

$stmt = $conn->prepare("INSERT INTO orders (customer_email, total_amount, address, payment_mode) VALUES (?, ?, ?, 'COD')");
$stmt->bind_param("sds", $email, $total, $address);
if (!$stmt->execute()) {
  echo json_encode(["success" => false, "error" => "Failed to save order"]);
  exit;
}
$order_id = $conn->insert_id;

foreach ($cart as $item) {
  $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
  $stmt2->bind_param("issdi", $order_id, $item['product_id'], $item['product_name'], $item['price'], $item['quantity']);
  $stmt2->execute();
}

echo json_encode(["success" => true, "order_id" => $order_id]);
$conn->close();
?>
