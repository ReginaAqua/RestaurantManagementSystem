<?php

$jsonFile = '../Data/PP_DB.json';


$message = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    
    if (isset($_POST['new_order']) && $_POST['new_order'] === "1") {
        $tableNumber = $_POST['table_number'];
        $orderText   = $_POST['order'];
        $comments    = $_POST['comments'];
        $orderTime   = date("H:i:s");

        // Load or initialize JSON structure
        if (file_exists($jsonFile)) {
            $data = json_decode(file_get_contents($jsonFile), true) ?: ["reservations"=>[], "orders"=>[]];
        } else {
            $data = ["reservations"=>[], "orders"=>[]];
        }

        
        $ordersArray = $data["orders"];
        $newOrderID  = $ordersArray
                     ? max(array_column($ordersArray, "orderID")) + 1
                     : 1;

        
        $data["orders"][] = [
            "orderID"      => $newOrderID,
            "table_number" => $tableNumber,
            "order"        => $orderText,
            "comments"     => $comments,
            "order_time"   => $orderTime,
            "status"       => "pending"
        ];

        file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
        $message = "Order submitted successfully!";


    } elseif (isset($_POST['update_order'], $_POST['orderID'], $_POST['status_action'])) {
        $orderID      = $_POST['orderID'];
        $statusAction = $_POST['status_action'];

        
        if (file_exists($jsonFile)) {
            $data = json_decode(file_get_contents($jsonFile), true) ?: ["reservations"=>[], "orders"=>[]];
        } else {
            $data = ["reservations"=>[], "orders"=>[]];
        }

        // Process update
        foreach ($data["orders"] as $i => $o) {
            if ($o["orderID"] == $orderID) {
                if ($statusAction === "mark_ready" && $o["status"] === "pending") {
                    $data["orders"][$i]["status"] = "ready";
                    $message = "Order marked as ready.";
                }
                elseif ($statusAction === "remove" && $o["status"] === "ready") {
                    // Instead of deleting, mark served so it stays in DB but is hidden
                    $data["orders"][$i]["status"] = "served";
                    $message = "Order served.";
                }
                break;
            }
        }

        file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
    }
}


$data = file_exists($jsonFile)
      ? (json_decode(file_get_contents($jsonFile), true) ?: ["reservations"=>[], "orders"=>[]])
      : ["reservations"=>[], "orders"=>[]];


$displayOrders = array_filter($data["orders"], fn($o)=>
    in_array($o["status"], ["pending","ready"], true)
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Active Orders</title>
  <link rel="stylesheet" href="../cssFiles/orders.css">
  <script>
    function toggleOrderForm() {
      const f = document.getElementById("orderFormContainer");
      f.style.display = f.style.display === "block" ? "none" : "block";
    }
  </script>
</head>
<body>
  <h1>Active Orders</h1>
  <?php if ($message): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>

  <?php if (count($displayOrders)): ?>
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
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($displayOrders as $order): ?>
            <tr>
              <td><?= htmlspecialchars($order["orderID"]) ?></td>
              <td><?= htmlspecialchars($order["table_number"]) ?></td>
              <td><?= htmlspecialchars($order["order"]) ?></td>
              <td><?= htmlspecialchars($order["comments"]) ?></td>
              <td><?= htmlspecialchars($order["order_time"]) ?></td>
              <td><?= htmlspecialchars($order["status"]) ?></td>
              <td class="action-cell">
                <form method="post" class="update-form">
                  <input type="hidden" name="update_order" value="1">
                  <input type="hidden" name="orderID" value="<?= $order["orderID"] ?>">
                  <?php if ($order["status"] === "pending"): ?>
                    <input type="hidden" name="status_action" value="mark_ready">
                    <input type="submit" value="Mark Ready" class="small-button">
                  <?php elseif ($order["status"] === "ready"): ?>
                    <input type="hidden" name="status_action" value="remove">
                    <input type="submit" value="Served" class="small-button">
                  <?php endif; ?>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="no-orders">No orders to display.</p>
  <?php endif; ?>

  <div class="center">
    <button onclick="toggleOrderForm()">Add Order</button>
  </div>

  <div id="orderFormContainer">
    <h2>Input Order</h2>
    <form method="post" id="orderForm">
      <input type="hidden" name="new_order" value="1">
      <label>Table Number:</label>
      <input type="number" name="table_number" required>
      <label>Order (select items):</label>
      <input type="text" id="orderSearch" placeholder="Type to search food items">
      <div id="suggestions" class="suggestions"></div>
      <ul id="orderList"></ul>
      <input type="hidden" name="order" id="order">
      <label>Comments:</label>
      <textarea name="comments"></textarea>
      <input type="submit" value="Submit Order">
    </form>
  </div>

  <script src="../htmlFiles/orders.js"></script>
</body>
</html>
