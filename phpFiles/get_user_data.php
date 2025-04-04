<?php
session_start();

if (!isset($_SESSION["usernm"])) {
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

$username = $_SESSION["usernm"];
$users = json_decode(file_get_contents("users.json"), true);

foreach ($users as $user) {
    if ($user["username"] === $username) {
        // Check using "Role" (capital R) as defined in users.json
        if (isset($user["Role"]) && $user["Role"] === "customer") {
            echo json_encode($user);
        } else {
            echo json_encode(["error" => "User is not a customer"]);
        }
        exit;
    }
}
echo json_encode(["error" => "User not found"]);
?>
