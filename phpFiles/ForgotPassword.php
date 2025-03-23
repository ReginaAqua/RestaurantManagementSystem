<?php
//Requirements to access phpemailer to send emails to gmail using composer.
require 'vendor/autoload.php'; // Adjust this if you're not using Composer

// Include PHPMailer files
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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


//Reading the json file data to find the user that matches the input given. 
$json_file = '../Data/users.json';
$json_data = file_get_contents($json_file);
$dec_data = json_decode($json_data, true);

//in case the person didnt input an email, we will grab it using the username or phone number from json.
if($email == null)
{
    foreach ($dec_data as $user)
    {
 
      if($user['username'] === $username || $user['phone'] === $phone_num)
      {
        $email = $user['email'];
        break;
      }
      else{
        $response = [
            "status" => "error",
            "message" => "User does not exist with that input!"
        ];
        echo json_encode($response);
        exit;
      }

    }

}

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
    $mail->addAddress($email);  // Recipient's email

    $OTP = rand(100000, 999999); //creating a 6 digit code for authentication which will be sent to the user's email.
    $OTP_str = strval($OTP);
   foreach ($dec_data as $user)
    {
        if($user['email'] === $email)
        {
            $user['OTP'] = $OTP_str;
            break;
        }
        
    }
    $mail->isHTML(true);  // Setting email format to HTML
    $mail->Subject = 'Request to re-enable your password.';
    $mail->Body = 'Please enter the 6 digit code provided to you, in the link sent, to re-enable your new password.<br>Code: '. $OTP;

    // Send the email
    $mail->send();
    echo 'A 6 digit code has been sent to your email for re-enabling your password.';
    //updating the json file data
    $new_json_data = json_encode($dec_data, JSON_PRETTY_PRINT);
    file_put_contents($json_file, $new_json_data);
    

} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}


?>
