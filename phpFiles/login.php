<?php
session_start(); // for cookies

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Define the path to the JSON file
    $json_file = '../Data/users.json';

    // Read and decode user data
    $json_data = file_get_contents($json_file);
    $users = json_decode($json_data, true);

    // Sanitize POST input
    $usertrim = trim($_POST['username']);
    $userstrip = stripcslashes($usertrim);
    $finaluser = htmlspecialchars($userstrip);

    $passtrim = trim($_POST['password']);
    $passstrip = stripcslashes($passtrim);
    $finalpass = htmlspecialchars($passstrip);

    // Search for matching user
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
        header("Location: ../htmlfiles/dash.html");
        exit();
    } else {
        echo "Invalid username or password.";
        exit();
    }
} else {
    echo "Please submit the form.";
    exit();
}
?>