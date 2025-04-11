<?php
// Path to the JSON file that contains both reservations and orders.
$jsonFile = '../Data/PP_DB.json';

// INITIALIZE A FEEDBACK MESSAGE VARIABLE
$message = "";

// PROCESS FORM SUBMISSIONS
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // NEW ORDER SUBMISSION
    if (isset($_POST['new_order']) && $_POST['new_order'] === "1") {
        // Get order form data from the new order form
        $tableNumber = $_POST['table_number'];
        // The order text is now generated from the hidden input that stores selected items.
        $orderText   = $_POST['order'];
        $comments    = $_POST['comments'];

        // Capture live order time (current server time)
        $orderTime = date("H:i:s");

        // Read the existing JSON file structure.
        if (file_exists($jsonFile)) {
            $jsonData = file_get_contents($jsonFile);
            $data = json_decode($jsonData, true);
            // If the file exists but is empty or invalid, initialize keys.
            if (!is_array($data)) {
                $data = ["reservations" => [], "orders" => []];
            }
        } else {
            // If the file doesn't exist yet, initialize it with both keys.
            $data = ["reservations" => [], "orders" => []];
        }

        // Determine new OrderID by scanning the existing orders.
        $ordersArray = isset($data["orders"]) ? $data["orders"] : [];
        $newOrderID = 1;
        if (count($ordersArray) > 0) {
            $maxId = 0;
            foreach ($ordersArray as $order) {
                if (isset($order["orderID"]) && $order["orderID"] > $maxId) {
                    $maxId = $order["orderID"];
                }
            }
            $newOrderID = $maxId + 1;
        }

        // Create a new order array with the generated OrderID and default "pending" status.
        $newOrder = [
            "orderID"      => $newOrderID,
            "table_number" => $tableNumber,
            "order"        => $orderText,
            "comments"     => $comments,
            "order_time"   => $orderTime,
            "status"       => "pending"
        ];

        // Append the new order to the "orders" key.
        $data["orders"][] = $newOrder;

        // Save the updated data back to the JSON file.
        file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));

        // Set a feedback message.
        $message = "Order submitted successfully!";

    // UPDATE ORDER ACTION (mark ready or remove)
    } elseif (isset($_POST['update_order']) && $_POST['update_order'] === "1") {
        // Get update action parameters.
        $orderID = $_POST['orderID'];
        $statusAction = $_POST['status_action']; // either "mark_ready" or "remove"

        // Load current data.
        if (file_exists($jsonFile)) {
            $jsonData = file_get_contents($jsonFile);
            $data = json_decode($jsonData, true);
            if (!is_array($data)) {
                $data = ["reservations" => [], "orders" => []];
            }
        } else {
            $data = ["reservations" => [], "orders" => []];
        }

        // Process each action.
        if (isset($data["orders"]) && is_array($data["orders"])) {
            foreach ($data["orders"] as $key => $order) {
                if (isset($order["orderID"]) && $order["orderID"] == $orderID) {
                    if ($statusAction === "mark_ready" && $order["status"] === "pending") {
                        // Update the order status to "ready".
                        $data["orders"][$key]["status"] = "ready";
                        $message = "Order marked as ready.";
                    } elseif ($statusAction === "remove" && $order["status"] === "ready") {
                        // Remove the order from the list.
                        unset($data["orders"][$key]);
                        $message = "Order removed successfully.";
                    }
                    break;
                }
            }
            // Re-index the orders array after a deletion.
            $data["orders"] = array_values($data["orders"]);
            // Save the updated data back to the JSON file.
            file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
        }
    }
}

// READ THE JSON FILE TO OBTAIN THE LATEST ORDERS FOR DISPLAY.
// Now we want to display orders that are either "pending" or "ready", so the waiter can update them.
if (file_exists($jsonFile)) {
    $jsonData = file_get_contents($jsonFile);
    $data = json_decode($jsonData, true);
    if (!is_array($data)) {
        $data = ["reservations" => [], "orders" => []];
    }
} else {
    $data = ["reservations" => [], "orders" => []];
}

// Filter orders that are either pending or ready.
$displayOrders = array_filter($data["orders"], function($order) {
    return isset($order["status"]) && ($order["status"] === "pending" || $order["status"] === "ready");
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Waiter Dashboard - Orders</title>
    <!-- Link to the external CSS file -->
    <link rel="stylesheet" href="../cssFiles/orders.css">
    <script>
      // Function to toggle the order form visibility
      function toggleOrderForm() {
          var form = document.getElementById("orderFormContainer");
          if (form.style.display === "none" || form.style.display === "") {
              form.style.display = "block";
          } else {
              form.style.display = "none";
          }
      }
    </script>
</head>
<body>
    <h1>Orders Dashboard</h1>
    <?php if (!empty($message)) { echo "<p class='message'>$message</p>"; } ?>

    <?php if (count($displayOrders) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>OrderID</th>
                <th>Table Number</th>
                <th>Order</th>
                <th>Comments</th>
                <th>Order Time</th>
                <th>Status</th>
                <th>Action</th> <!-- New column for action buttons -->
            </tr>
        </thead>
        <tbody>
            <?php foreach ($displayOrders as $order): ?>
            <tr>
                <td><?php echo htmlspecialchars($order["orderID"]); ?></td>
                <td><?php echo htmlspecialchars($order["table_number"]); ?></td>
                <td><?php echo htmlspecialchars($order["order"]); ?></td>
                <td><?php echo htmlspecialchars($order["comments"]); ?></td>
                <td><?php echo htmlspecialchars($order["order_time"]); ?></td>
                <td><?php echo htmlspecialchars($order["status"]); ?></td>
                <td>
                    <!-- Inline form for updating the order -->
                    <form action="orders.php" method="post" style="margin:0;">
                        <input type="hidden" name="update_order" value="1">
                        <input type="hidden" name="orderID" value="<?php echo htmlspecialchars($order["orderID"]); ?>">
                        <?php if ($order["status"] === "pending"): ?>
                            <input type="hidden" name="status_action" value="mark_ready">
                            <input type="submit" value="Mark Ready" style="color: black; background-color: yellow;">
                        <?php elseif ($order["status"] === "ready"): ?>
                            <input type="hidden" name="status_action" value="remove">
                            <input type="submit" value="Remove Order" class="small-button">
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p class="no-orders">No orders to display.</p>
    <?php endif; ?>

    <br>
    <!-- Button to toggle the integrated new order form -->
    <div class="center">
        <button onclick="toggleOrderForm()">Add Order</button>
    </div>

    <!-- Integrated New Order Form with Search-Based Order Input -->
    <div id="orderFormContainer">
        <h2>Input Order</h2>
        <form action="orders.php" method="post" id="orderForm">
            <!-- Hidden input to indicate this is a new order -->
            <input type="hidden" name="new_order" value="1">
            <label for="table_number">Table Number:</label>
            <input type="number" id="table_number" name="table_number" required>
            
            <!-- Search-based order input -->
            <label for="orderSearch">Order (select items):</label>
            <input type="text" id="orderSearch" placeholder="Type to search food items">
            <!-- Div for suggestions -->
            <div id="suggestions" class="suggestions" style="display:none;"></div>
            <!-- List that shows selected order items -->
            <ul id="orderList"></ul>
            <!-- Hidden input that stores the comma-separated list of order items -->
            <input type="hidden" id="order" name="order">
            
            <label for="comments">Comments:</label>
            <textarea id="comments" name="comments" rows="2"></textarea>
            
            <input type="submit" value="Submit Order">
        </form>
    </div>
    
    <!-- Include the modified orders.js script for the search functionality -->
    <script src="../htmlFiles/orders.js"></script>
</body>
</html>
