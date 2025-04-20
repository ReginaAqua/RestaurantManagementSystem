<?php
session_start();
require '../vendor/autoload.php'; //Requirements to access phpemailer to send emails to gmail using composer.
// Include PHPMailer files
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//EMAIL SETUP:
$mail = new PHPMailer(true);  // Passing `true` enables exceptions

//Server settings
$mail->isSMTP();  // Setting mailer to use SMTP
$mail->Host = 'smtp.gmail.com';  // Setting Gmail's SMTP server
$mail->SMTPAuth = true;  // Enabling SMTP authentication
$mail->Username = 'anastasiosdrog@gmail.com';  // Your Gmail address
$mail->Password = 'zgau morr ihfz qdmt';  // Your app-specific password (if 2FA is enabled)
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Enable TLS encryption
$mail->Port = 587;  // TCP port to connect to (587 for Gmail)

// Check if user session exists
if (!isset($_SESSION['usernm'])) {
    header("Location: ../htmlFiles/login.html");
    exit();
}

// Paths to DB files
$dbFile = __DIR__ . '/../Data/PP_DB.json';
$userFile = '../Data/users.json';

// Get form data
$num_people       = trim($_POST['num_people'] ?? '');
$reservation_time = trim($_POST['reservation_time'] ?? '');
$reservation_date = trim($_POST['reservation_date'] ?? '');

//function that turns hours to minutes
function timeToMinutes($timeStr) {
    [$hour, $minute] = explode(':', $timeStr);
    return ((int)$hour * 60) + (int)$minute;
}

// Load existing DB
if (!file_exists($dbFile)) {
    $data = ['reservations' => []];
} else {
    $json = file_get_contents($dbFile);
    $data = json_decode($json, true);
    if (!is_array($data) || !isset($data['reservations'])) {
        $data = ['reservations' => []];
    }
}
//initialise just in case
$email=null;
$name=null;
$user_id=null;

// Load user details
$user_file = file_get_contents($userFile);
$users = json_decode($user_file, true);
foreach ($users as $user) {
    if ($user['username'] === $_SESSION['usernm']) {
        $name = $user['name'];
        $user_id = $user['user_id'];
        $email= $user['email'];
        break;
    }
}

// all tables
$tables = [
    "VIP_1",
    "VIP_2",
    "Window_1",
    "Window_2",
    "Booth_1",
    "Booth_2",
];
// Convert requested time to hour 
$requested_minutes = timeToMinutes($reservation_time);
$fin_table=null;
// checks for conflicting reservations within 2-hour window for each table 
foreach($tables as $checktable) {
    $isavailable=false;
    foreach ($data['reservations'] as $existing) {
        if ($existing['reservation_date'] === $reservation_date &&
          isset($existing['table']) &&
          $existing['table'] === $checktable)
            {
          $existing_minutes = timeToMinutes($existing['reservation_time']);
          // checks if the reservation is too close (less than 2 hours apart in minutes)
          if (abs($existing_minutes - $requested_minutes) < 120) {
              $isavailable=true;
              break;
        }
    }
 }
 if(!$isavailable)
 {
    $fin_table = $checktable;
    break;
 }
}
if(!$fin_table)
{
    header("Location: ../phpFiles/customer.php?reservation=no_available_tables_for_that_time");
    exit();
}
// new reservation being created
$newReservation = [
    "reservation_id"   => uniqid(),
    "user_id"          => $user_id,
    "name"             => $name,
    "num_people"       => $num_people,
    "reservation_time" => $reservation_time,
    "reservation_date" => $reservation_date,
    "table"            => $fin_table
];

//check if reservation date is empty
if(empty($newReservation['reservation_date']))
{
    header("Location: ../phpFiles/customer.php?status=null_date_error");
    exit();
}
//after the successfull reservation was made we need to email the success and the details to the user
$mail->setFrom('anastasiosdrog@gmail.com', "Dragon's Pizzeria");// Sender's email
$mail->addAddress($email);  // Recipient's email
$mail->isHTML(true);  // Setting email format to HTML
$mail->Subject = "Dragon's Pizzeria";//Subject of the email
$mail->Body = 'Dear ' . $name . ', your reservation has been successfully made for
table ' . $fin_table .
' at ' . $reservation_time .
' on ' . $reservation_date . 
". If you have any questions or need further assistance, please don't hesitate to contact us. 
We look forward to serving you!"; // body of the email

$data['reservations'][] = $newReservation;
file_put_contents($dbFile, json_encode($data, JSON_PRETTY_PRINT));

// Redirect to customer page THIS NEEDS TO CHANGE AND INSTEAD SAY THAT YOU HAVE MADE A "RESERVATION SUCCESSFULLLY AND AN EMAIL WAS SENT!"
try {
    $mail->send();
    header("Location: customer.php?status=success");//update the status for message in customer.php
    exit();
} catch (Exception $e) {
    echo "Mailer Error: " . $mail->ErrorInfo;
    exit(); //no redirect
}
?>
