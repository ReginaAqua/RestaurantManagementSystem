<?php
session_start(); //for cookies
// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Grab the 6 digits and combine them
    $otp = $_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'] . $_POST['otp5'] . $_POST['otp6'];

    // Load user data from JSON
    $json_file = '../Data/users.json';
    $json_data = file_get_contents($json_file);
    $json_dec = json_decode($json_data, true);

    $matchFound = false;

    // Check if OTP matches any user
    foreach ($json_dec as $index => $user) {
        if (isset($user['OTP']) && $user['OTP'] == $otp && $_SESSION['email'] == $user['email']) {
            $matchFound = true;
            $json_dec[$index]['OTP'] = null;
            break;
        }
    }
    //updating the users.json 
    $json_en = json_encode($json_dec, JSON_PRETTY_PRINT);
    file_put_contents($json_file, $json_en);
    if ($matchFound){
        header ('Location: ../phpfiles/Re-enablePassword.php');
        //delete otp 
        exit();
    } else {
        header ('Location: ../htmlfiles/OTP.html?msg=invalid');
        exit(); 
    }
}
?>