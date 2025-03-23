<?php

//include('ForgotPassword.php');

// Initializing a message variable
$message = "";
//connecting to MySQL database
$con = mysqli_connect("localhost","root","","user");

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieving the password from the user's input
    $newPassword = $_POST['new-password'];
    $confirmPassword = $_POST['confirm-password'];

    // Check if the passwords match
    if ($newPassword === $confirmPassword) {
        $message = "<span style='color: green;'> Passwords match!";
    } else {        
        $message = "<span style='color: red;'>Passwords do not match. Please try again.</span>";
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="../htmlFiles/Re-enablePassword.css">
</head>
<body>

    <div class="container">
        <img src="foto/eyespy.png" alt="Image description" class="image">
        <p class="description">Don't mind me, just investigating your password huehuehue.</p>
    </div>
    
    <div class="form-container">
        <h2>Change Your Password</h2>

        <!-- Display the message here if it's set -->
        <?php if (!empty($message)) : ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>

        <!-- The form to change the password -->
        <form id="passwordForm" action="Re-enablePassword.php" method="POST">
            <label for="new-password">New Password</label>
            <input type="password" id="new-password" name="new-password" required>

            <label for="confirm-password">Confirm New Password</label>
            <input type="password" id="confirm-password" name="confirm-password" required>

            <button type="submit">Change Password</button>
        </form>

    </div>

</body>
</html>
