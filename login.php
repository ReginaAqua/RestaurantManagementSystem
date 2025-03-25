<?php

// Define the path to the JSON file
$json_file = 'Data/users.json';

// Read the contents of the JSON file
$json_data = file_get_contents($json_file);

// Decode the JSON data into a PHP array
$users = json_decode($json_data, true);

// Initialize variables (from POST request)
$usertrim = trim($_POST['username']);
$userstrip = stripcslashes($usertrim);
$finaluser = htmlspecialchars($userstrip);

$passtrim = trim($_POST['password']);
$passstrip = stripcslashes($passtrim);
$finalpass = htmlspecialchars($passstrip);

// Search for the user in the JSON data
$found_user = null;

foreach ($users as $user) {
    if ($user['username'] == $finaluser && password_verify($finalpass, $user['password'])) {
        $found_user = $user;
        break;
    }
}

// Check if user was found and if the role is 'manager'
// Check if user was found and if the role is 'manager'
if ($found_user && $found_user['role'] == 'manager') {
    $_SESSION['usernm'] = $finaluser;
    //session_write_close(); 
    header("Location: AccountManagement.php");
    exit(); 
} elseif ($found_user) {
    // If the user exists but is not a manager, redirect to staff page
    header("Location: AccountManagement.php");
    exit();
} else {
    // If no matching user is found
     echo "Invalid username or password.";
     exit();
}
?>
