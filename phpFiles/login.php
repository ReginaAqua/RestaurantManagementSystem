<?php
ob_start(); // Starts output buffering
session_start(); // Start session

// Define the path to the JSON file
$json_file = '../Data/users.json';

// Read the contents of the JSON file
$json_data = file_get_contents($json_file);

// Decode the JSON data into a PHP array
$users = json_decode($json_data, true);

// Sanitize inputs
$finaluser = htmlspecialchars(stripcslashes(trim($_POST['username'])));
$finalpass = htmlspecialchars(stripcslashes(trim($_POST['password'])));

// Search for the user in the JSON data
$found_user = null;
foreach ($users as $user) {
    if ($user['username'] == $finaluser && password_verify($finalpass, $user['password'])) {
        $found_user = $user;
        $_SESSION['email'] = $user['email'];
        break;
    }
}

if ($found_user) {
    $_SESSION['usernm'] = $finaluser;
    $_SESSION['user_id'] = isset($found_user['id']) ? $found_user['id'] : null;
    // Use the correct JSON key "Role"
    $role = isset($found_user['role']) && $found_user['role'] !== null ? strtolower(trim($found_user['role'])) : '';
    $_SESSION['role'] = $role;
    if ($role === "customer") {
        header("Location: ../htmlFiles/customer.html");
        exit();
    } else {
        header("Location: ../htmlFiles/dash.html");
        exit();
    }
} else {
    echo "Invalid username or password.";
    exit();
}
ob_end_flush(); // Sends the output at the end
?>
