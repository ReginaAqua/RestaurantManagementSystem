<?php
session_start();


$jsonFile = '../Data/PP_DB.json';

//json for users
$userFile = '../Data/users.json';
$userdata = file_get_contents($userFile,true);
$user_dec = json_decode($userdata,true);

//settigns for sepperatign manager options from regular staff
$userRole = '';

foreach ($user_dec as $user) {
  if (isset($_SESSION['usernm'])&& $user['username']===$_SESSION['usernm']) {
    $userRole = $user['role'] ?? '';
    break;
  }
}
// 1) AJAX endpoint to mark an order “served” (no deletion)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = $_POST['order_id'];
    if (file_exists($jsonFile)) {
        $data = json_decode(file_get_contents($jsonFile), true) ?: ["reservations"=>[], "orders"=>[]];
        foreach ($data['orders'] as $key => &$order) {
            if ((string)$order['orderID'] === (string)$orderId) {
                $order['status'] = 'served';
                file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
                echo json_encode(["success"=>true, "message"=>"Order #{$orderId} marked served."]);
                exit;
            }
        }
        echo json_encode(["error"=>"Order #{$orderId} not found."]);
    } else {
        echo json_encode(["error"=>"Database file not found."]);
    }
    exit;
}

// 2) Normal form submissions (new_order or update_order)
$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['order_id'])) {
    if (isset($_POST['new_order'])) {
        $tableNumber = $_POST['table_number'];
        $orderText   = $_POST['order'];
        $comments    = $_POST['comments'];
        $orderTime   = date("H:i:s");
        $data = file_exists($jsonFile)
              ? (json_decode(file_get_contents($jsonFile), true) ?: ["reservations"=>[], "orders"=>[]])
              : ["reservations"=>[], "orders"=>[]];
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
        $message = "Order #{$newOrderID} submitted successfully!";
    } elseif (isset($_POST['update_order'])) {
        $orderID      = $_POST['orderID'];
        $statusAction = $_POST['status_action'];
        $data = file_exists($jsonFile)
              ? (json_decode(file_get_contents($jsonFile), true) ?: ["reservations"=>[], "orders"=>[]])
              : ["reservations"=>[], "orders"=>[]];
        foreach ($data["orders"] as $i => &$o) {
            if ($o["orderID"] == $orderID) {
                if ($statusAction === "mark_ready" && $o["status"] === "pending") {
                    $o["status"] = "ready";
                    $message = "Order #{$orderID} marked ready.";
                } elseif ($statusAction === "remove" && $o["status"] === "ready") {
                    $o["status"] = "served";
                    $message = "Order #{$orderID} marked served.";
                }
                break;
            }
        }
        file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}

// 3) Read & filter for display
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
  <title>Orders Dashboard</title>
  <link rel="stylesheet" href="../cssFiles/orders.css">
  <link rel="stylesheet" href="../cssFiles/dash.css">
</head>
<body>
  <!-- Sidebar Navigation -->
  <div class="sidebar" id="sidebar">
    <a href="../phpfiles/dash.php"><span>Dashboard</span></a>
    <a href="../phpFiles/AccountManagement.php"><span>Account Management</span></a>
    <a href="#"><span>Analytics</span></a>
    <a href="../phpFiles/Schedule.php"><span>Schedule</span></a>
    <a href="../phpFiles/inventory.php"><span>Inventory</span></a>
    <a href="../phpFiles/orders.php"><span>Orders</span></a>
    <?php if ($userRole === 'manager'): ?>
    <a href="../phpFiles/StaffManagement.php"><span>Staff Management</span></a>
    <a href="../phpFiles/scheduleManager.php"><span>Schedule Management</span></a>
    <a href="../phpFiles/manage_reservations.php"><span>Reservations</span></a>
    <?php endif; ?>
    <a href="../phpFiles/PreviousOrders.php"><span>Previous Orders</span></a>
  </div>

  <div class="main" id="mainContent">
    <!-- Top Bar -->
    <div class="top-bar">
      <button class="toggle-btn" id="toggleSidebar">&#9776;</button>
      <div class="profile" id="profileBtn">
        <span class="profile-name"><?= htmlspecialchars($_SESSION['usernm'] ?? 'User') ?></span>
        <div class="dropdown" id="profileDropdown">
          <a href="../phpFiles/AccountManagement.php">Account Management</a>
          <a href="../htmlfiles/login.html">Log Out</a>
        </div>
      </div>
    </div>

    <!-- Page Content -->
    <div class="container">
      <h1>Active Orders</h1>

      <?php if ($message): ?>
        <p class="message" id="feedback"><?= htmlspecialchars($message) ?></p>
        <script>
          setTimeout(() => {
            const fb = document.getElementById('feedback');
            if (fb) fb.style.display = 'none';
          }, 3000);
        </script>
      <?php endif; ?>

      <?php if (count($displayOrders)): ?>
        <div class="table-section">
          <table>
            <thead>
              <tr>
                <th>OrderID</th>
                <th>Table #</th>
                <th>Order</th>
                <th>Comments</th>
                <th>Time</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($displayOrders as $order): ?>
              <tr>
                <td><?= $order["orderID"] ?></td>
                <td><?= htmlspecialchars($order["table_number"]) ?></td>
                <td><?= htmlspecialchars($order["order"]) ?></td>
                <td><?= htmlspecialchars($order["comments"]) ?></td>
                <td><?= htmlspecialchars($order["order_time"]) ?></td>
                <td><?= htmlspecialchars($order["status"]) ?></td>
                <td class="action-cell">
                  <?php if ($order["status"] === "pending"): ?>
                    <form method="post" class="update-form">
                      <input type="hidden" name="update_order" value="1">
                      <input type="hidden" name="orderID" value="<?= $order["orderID"] ?>">
                      <input type="hidden" name="status_action" value="mark_ready">
                      <input type="submit" class="small-button" value="Mark Ready">
                    </form>
                  <?php elseif ($order["status"] === "ready"): ?>
                    <button class="small-button"
                            onclick="removeOrder(<?= $order['orderID'] ?>)">
                      Served
                    </button>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="no-orders">No active orders.</p>
      <?php endif; ?>

      <div class="center">
        <button onclick="toggleOrderForm()">Add New Order</button>
      </div>

      <div id="orderFormContainer">
        <h2>Input Order</h2>
        <form method="post" id="orderForm">
          <input type="hidden" name="new_order" value="1">
          <label>Table Number:</label>
          <input type="number" name="table_number" required>
          <label>Order:</label>
          <input type="text" id="orderSearch" placeholder="Type to search food items">
          <div id="suggestions" class="suggestions"></div>
          <ul id="orderList"></ul>
          <input type="hidden" name="order" id="order">
          <label>Comments:</label>
          <textarea name="comments"></textarea>
          <input type="submit" value="Submit Order">
        </form>
      </div>
    </div> <!-- .container -->
  </div> <!-- .main -->

  <script src="../htmlfiles/dash.js"></script>
  <script>
    function toggleOrderForm() {
      const f = document.getElementById("orderFormContainer");
      f.style.display = f.style.display === "block" ? "none" : "block";
    }
    async function removeOrder(orderID) {
      try {
        const res = await fetch('orders.php', {
          method:'POST',
          headers:{'Content-Type':'application/x-www-form-urlencoded'},
          body:`order_id=${orderID}`
        });
        const json = await res.json();
        alert(json.message||json.error);
        if(json.success) window.location.reload();
      } catch {
        alert("Network error");
      }
    }
  </script>
  <script src="../htmlFiles/orders.js"></script>
  <!--logout script-->
<script>
function confirmLogout() {
    if (confirm("Are you sure you want to log out?")) {
        //  make a fetch request to logout.php
        fetch('logout.php')
            .then(response => {
                if (response.ok) {
                    // you can redirect after a successful fetch
                    window.location.href = "../htmlfiles/login.html";
                } else {
                    alert('Logout failed!');
                }
            })
    }
}
</script>
</body>
</html>