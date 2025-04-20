<?php
session_start(); //for cookies
// Define the path to the JSON file
$json_file = '../Data/users.json';

// Read the contents of the JSON file
$json_data = file_get_contents($json_file);
$json_dec = json_decode($json_data,true);

// Checking if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    foreach ($json_dec as $user)
    {
        //var_dump($_SESSION['email'], $_SESSION['usernm']);
       //var_dump($user['email'], $user['username']);
        if($_SESSION['email'] == $user['email'] && $_SESSION['usernm'] == $user['username'])
        {
        $hashpassword = $user['password'];
          if(password_verify($password,$hashpassword))
          {
            header('Location: ../phpfiles/Re-enablePassword.php');
            exit();
          }
          else
          {
            echo "<script>alert('Incorrect password.\\nPlease try again.'); window.history.back();</script>";
            exit();
          }
        }
    }
}
?>