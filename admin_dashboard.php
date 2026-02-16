<?php
session_start();
$conn = new mysqli("localhost", "root", "", "urbanwear");
if ($conn->connect_error) {
  die("Connection failed");
}

$orders = [];
$result = $conn->query("SELECT o.id, o.customer_email, o.total_amount, o.address, o.created_at, GROUP_CONCAT(oi.product_name SEPARATOR ', ') as products, SUM(oi.quantity) as total_qty FROM orders o LEFT JOIN order_items oi ON o.id = oi.order_id GROUP BY o.id ORDER BY o.created_at DESC");
if ($result) {
  $orders = $result->fetch_all(MYSQLI_ASSOC);
}

$newOrders = array_filter($orders, function($o) {
  return strtotime($o['created_at']) > time() - 900;
});
$newOrderCount = count($newOrders);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .notification { color: red; font-weight: bold; }
  </style>
</head>
<body>
  <h1>Admin Dashboard</h1>
  <?php if ($newOrderCount > 0): ?>
    <div class="notification">New Orders: <?php echo $newOrderCount; ?></div>
  <?php endif; ?>
  <h2>Orders</h2>
  <table>
    <tr>
      <th>ID</th>
      <th>Email</th>
      <th>Products</th>
      <th>Quantities</th>
      <th>Total</th>
      <th>Status</th>
      <th>Date</th>
    </tr>
    <?php foreach ($orders as $order): ?>
      <tr>
        <td><?php echo $order['id']; ?></td>
        <td><?php echo $order['customer_email']; ?></td>
        <td><?php echo $order['products']; ?></td>
        <td><?php echo $order['total_qty']; ?></td>
        <td>₹<?php echo number_format($order['total_amount']); ?></td>
        <td>Confirmed</td>
        <td><?php echo $order['created_at']; ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>

<?php $conn->close(); ?>
