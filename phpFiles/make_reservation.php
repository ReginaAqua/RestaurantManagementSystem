<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: ../htmlFiles/customer.html");
    exit();
}

// Set the path to the JSON database file (adjusting for folder structure)
$dbFile = "../Data/PP_DB.json";

// Retrieve form data
$num_people       = $_POST['num_people'];
$reservation_time = $_POST['reservation_time'];
$reservation_date = $_POST['reservation_date'];
// You can get the username either from the hidden field or from the session; here we use the session
$username         = $_SESSION['username'];
$user_id          = $_SESSION['user_id'];

// Read existing data from the JSON database
if (!file_exists($dbFile)) {
    // Initialize the database if the file doesn't exist
    $data = array("reservations" => array());
} else {
    $json = file_get_contents($dbFile);
    $data = json_decode($json, true);
    if ($data === null) {
        // If decoding fails, initialize an empty structure
        $data = array("reservations" => array());
    }
}

// Create a new reservation entry (including the username)
$newReservation = array(
    "reservation_id"   => uniqid(), // Generates a unique ID
    "user_id"          => $user_id,
    "username"         => $username,
    "num_people"       => $num_people,
    "reservation_time" => $reservation_time,
    "reservation_date" => $reservation_date
);

// Append the new reservation to the data array
$data['reservations'][] = $newReservation;

// Save the updated data back to the JSON file
file_put_contents($dbFile, json_encode($data, JSON_PRETTY_PRINT));

// Redirect back to the homepage with a success flag
header("Location: ../htmlFiles/customer.html?reservation=success");
exit();
?>
