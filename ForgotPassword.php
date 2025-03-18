<?php
//Requirements to access phpemailer to send emails to gmail using composer.
require 'vendor/autoload.php'; // Adjust this if you're not using Composer

// Include PHPMailer files
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//starting session
session_start();
//connection with the form and sql server
$con = mysqli_connect("localhost","root","","user");
//initialise variables that will be the user's input and clear the contact variable from html if it was previously used.
$email = null;
$phone_num = null;
$username = null;

//Running a condition to check the input and assign it to the proper variable. Using trim function to remove whitespace from The beginning or the end of the input
//Check if it's an Email 
if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
    $email = trim($_POST['contact']);
   // echo "This is an email: " . $email;
}
// Check if it's a valid phone number (basic 10-digit phone number check) 
elseif (preg_match('/^[0-9]{10}$/', $contact)) {
    $phone_num = trim($_POST['contact']);
   // echo "This is a phone number: " . $username;
}
// Otherwise, treat it as a username
else {
    $username = trim($_POST['contact']);
   // echo "This is a username " . $phone_num;
}

//Command that selects all columns from users that have the same data as the input given. 
$sql = " SELECT * FROM users where email = '$email' OR telephone = '$phone_num' OR username = '$username'";
//Connecting into the query and stores the result.
$search = mysqli_query($con,$sql);


//Using fetching function to grab data associated with the input given for the designated user.
$user_data = mysqli_fetch_assoc($search);

//fetching name based on the input given to adress the recipient when sending the email.
$name = $user_data['name']; 
$surname = $user_data['surname'];
$full_name = $name . ' ' . $surname;  

//fetched email based on the input given which is then added to addAdress() function to send the email to the right recipient.
$fetched_email = $user_data['email'];

$mail = new PHPMailer(true);  // Passing `true` enables exceptions


try {
    //Server settings
    $mail->isSMTP();  // Setting mailer to use SMTP
    $mail->Host = 'smtp.gmail.com';  // Setting Gmail's SMTP server
    $mail->SMTPAuth = true;  // Enabling SMTP authentication
    $mail->Username = 'anastasiosdrog@Gmail.com';  // Your Gmail address
    $mail->Password = 'zgau morr ihfz qdmt';  // Your app-specific password (if 2FA is enabled)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Enable TLS encryption
    $mail->Port = 587;  // TCP port to connect to (587 for Gmail)

    //Recipients
    $mail->setFrom('anastasiosdrog@hotmail.com', 'sender');  // Sender's email
    $mail->addAddress($fetched_email, $full_name);  // Recipient's email

    
    $OTP = rand(100000, 999999); //creating a 6 digit code for authentication which will be sent to the user's email.
    $OTP_final = strval($OTP);
    $set_OTP = $con->prepare("UPDATE users SET otp_code = ? WHERE email = '$fetched_email'");
    $set_OTP->bind_param("s", $OTP_final);
    $mail->isHTML(true);  // Setting email format to HTML
    $mail->Subject = 'Request to re-enable your password.';
    $mail->Body = 'Please enter the 6 digit code provided to you, in the link sent, to re-enable your new password.<br>Code: '. $OTP;

    // Send the email
    if (mysqli_num_rows($search) >= 1 && ($email || $phone_num || $username)) {

        if ($set_OTP->execute()) {
            echo "OTP was send to the email: ". $email;
        } else {
            echo "Error updating record: " . $conn->error;
        }

        $mail->send();
    echo 'A 6 digit code has been sent to your email for re-enabling your password.';
    }
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}


?>
