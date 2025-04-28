<?php
// previousOrders.php
// Shows all orders marked "served" (previous/removed orders).


$jsonFile = '../Data/PP_DB.json';


if (file_exists($jsonFile)) {
    $allData = json_decode(file_get_contents($jsonFile), true);
    if (!is_array($allData)) {
        $allData = ["reservations" => [], "orders" => []];
    }
} else {
    $allData = ["reservations" => [], "orders" => []];
}


$previousOrders = array_filter(
    $allData["orders"],
    fn($o) => isset($o["status"]) && $o["status"] === "served"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Previous Orders</title>
  <link rel="stylesheet" href="../cssFiles/orders.css">
</head>
<body>
  <h1>Previous Orders</h1>

  <?php if (count($previousOrders) > 0): ?>
    <div class="table-section">
      <table>
        <thead>
          <tr>
            <th>OrderID</th>
            <th>Table Number</th>
            <th>Order</th>
            <th>Comments</th>
            <th>Order Time</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($previousOrders as $order): ?>
            <tr>
              <td><?= htmlspecialchars($order["orderID"]) ?></td>
              <td><?= htmlspecialchars($order["table_number"]) ?></td>
              <td><?= htmlspecialchars($order["order"]) ?></td>
              <td><?= htmlspecialchars($order["comments"]) ?></td>
              <td><?= htmlspecialchars($order["order_time"]) ?></td>
              <td><?= htmlspecialchars($order["status"]) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="no-orders">No previous (served) orders found.</p>
  <?php endif; ?>

  <div class="center">
    <a href="orders.php"><button>Back to Active Orders</button></a>
  </div>
</body>
</html>