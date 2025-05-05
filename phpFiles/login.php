<?php
session_start(); // for cookies

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_file = '../Data/users.json';

    $json_data = file_get_contents($json_file);
    $users = json_decode($json_data, true);

    $usertrim = trim($_POST['username']);
    $userstrip = stripcslashes($usertrim);
    $finaluser = htmlspecialchars($userstrip);

    $passtrim = trim($_POST['password']);
    $passstrip = stripcslashes($passtrim);
    $finalpass = htmlspecialchars($passstrip);

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
        $role = isset($found_user['role']) && $found_user['role'] !== null ? strtolower(trim($found_user['role'])) : '';
        $_SESSION['role'] = $role;

        if ($role === "customer") {
            header("Location: ../htmlFiles/customer.html");
            exit();
        } else {
            header("Location: dash.php");
            exit();
        }
    } else {
        echo "Invalid username or password.";
        exit();
    }
} else {
    echo "Please submit the form.";
    exit();
}
?>