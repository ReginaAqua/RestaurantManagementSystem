<?php
// Specify the path to the JSON file
$filePath = '../Data/PP_DB.json';

// Check if the file exists
if (file_exists($filePath)) {
    // Get the contents of the JSON file
    $orderData = json_decode(file_get_contents($filePath), true);

    // Get the order_id from the request
    $orderId = $_POST['order_id'];

    // Find the order by order_id and change its status
    foreach ($orderData['orders'] as &$order) {
        if ($order['order_id'] == $orderId) {
            $order['status'] = 'ready to serve';  // Change status to ready to serve
            $order['status_changed_at'] = date('Y-m-d\TH:i:s');  // Update timestamp for status change
            break;
        }
    }

    // Save the updated orders back to the JSON file
    file_put_contents($filePath, json_encode($orderData, JSON_PRETTY_PRINT));

    echo json_encode(["success" => true, "message" => "Order status updated."]);
} else {
    echo json_encode(["error" => "Database file not found."]);
}
?>