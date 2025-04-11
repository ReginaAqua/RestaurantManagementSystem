$ cat get_user_data.php
<?php
session_start();

if (!isset($_SESSION["username"])) {
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

$username = $_SESSION["username"];

// Adjust the file path if necessary
$users = json_decode(file_get_contents("users.json"), true);

foreach ($users as $user) {
    if ($user["username"] === $username) {
        // Check using "Role" (capital R) as defined in users.json
        if (isset($user["role"]) && $user["role"] === "customer") {
            echo json_encode($user);
        } else {
            echo json_encode(["error" => "User is not a customer"]);
        }
        exit;
    }
}
echo json_encode(["error" => "User not found"]);
?>
