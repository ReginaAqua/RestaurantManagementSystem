$ cat make_reservation.php
<?php
session_start();

if (!isset($_SESSION["usernm"])) {
    header("Location: ../phpFiles/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../htmlFiles/customer.html");
    exit;
}

$username = $_SESSION["usernm"];
$num_people = $_POST["num_people"];
$reservation_time = $_POST["reservation_time"];
$reservation_date = $_POST["reservation_date"];

$new_reservation = [
    "username" => $username,
    "num_people" => $num_people,
    "reservation_time" => $reservation_time,
    "reservation_date" => $reservation_date,
    "status" => "active"
];

$db_file = "../Data/PP_DB.json";

if (!file_exists($db_file)) {
    $data = ["reservations" => []];
} else {
    $json_data = file_get_contents($db_file);
    $data = json_decode($json_data, true);
    if ($data === null) {
        $data = ["reservations" => []];
    }
}

$data["reservations"][] = $new_reservation;

if (file_put_contents($db_file, json_encode($data, JSON_PRETTY_PRINT))) {
    echo "Reservation successfully saved.";
} else {
    echo "Error saving reservation.";
}
?>
