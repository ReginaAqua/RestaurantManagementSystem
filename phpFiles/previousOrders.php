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
//READING USERS.JSON
$json = '../Data/users.json';
$jsonData = file_get_contents($json);
$users = json_decode($jsonData, true);
//side bar settigns for sepperatign manager options from regular staff
$userRole = '';

foreach ($users as $user) {
  if (isset($_SESSION['usernm'])&& $user['username']===$_SESSION['usernm']) {
    $userRole = $user['role'] ?? '';
    break;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Previous Orders</title>
  <link rel="stylesheet" href="../cssFiles/orders.css">
</head>
<body>
      <!-- Sidebar Navigation -->
  <div class="sidebar" id="sidebar">
    <a href="../phpfiles/dash.php"><span>Dashboard</span></a>
    <a href="../phpFiles/AccountManagement.php"><span>Account Management</span></a>
    <a href="../phpFiles/Schedule.php"><span>Schedule</span></a>
    <a href="../phpFiles/inventory.php"><span>Inventory</span></a>
    <a href="../phpFiles/orders.php"><span>Orders</span></a>
    <?php if ($userRole === 'manager'): ?>
    <a href="../phpFiles/StaffManagement.php"><span>Staff Management</span></a>
    <a href="../phpFiles/scheduleManager.php"><span>Schedule Management</span></a>
    <a href="../phpFiles/manage_reservations.php"><span>Reservations</span></a>
    <?php endif;?>
    <a href="../phpFiles/PreviousOrders.php"><span>Previous Orders</span></a>
  </div>
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