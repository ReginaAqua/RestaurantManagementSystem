<?php
session_start(); //for cookies
//Load users json Data
$json_file = "../Data/users.json";
$json_data = file_get_contents($json_file);
$json_dec = json_decode($json_data, true);

// Checking if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieving the password from the user's input
    $newPassword = $_POST['new-password'];
    $confirmPassword = $_POST['confirm-password'];
    $pass=false;
    // Check if the passwords match and then changing the user's password in the json file. 
    if ($newPassword === $confirmPassword) {
        $message = "<span style='color: green;'> Passwords match!";
        foreach($json_dec as $index => $user)
        {
            if($_SESSION['email'] == $user['email'])
            {
                $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);
                $json_dec[$index]['password'] = $hashed_password;
                $pass=true;
                break;
            }
        }   
    } 
    else {        
        $message = "<span style='color: red;'>Passwords do not match. Please try again.</span>";
    }
    if($pass)
    {
        $pass=false;
        $json_en = json_encode($json_dec, JSON_PRETTY_PRINT);
        file_put_contents($json_file, $json_en);
        header("Location: ../htmlfiles/login.html");
    }

}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="../cssFiles/Re-enablePassword.css">
</head>
<body>

    <div class="container">
        <img src="../foto/eyespy.png" alt="Image description" class="image">
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
            <i class="fas fa-eye" onclick="see()"></i>
            <button type="submit">Change Password</button>
        </form>

    </div>

</body>
</html>
