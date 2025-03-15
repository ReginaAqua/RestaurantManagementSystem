<?php

/*

// session start. Beginning of the php
session_start();

// connection between Form and MySql server
$con = mysqli_connect("localhost","root","","user");

//username and password is received from login page
$usertrim = trim($_POST['username']);
//eliminated space and special characters here
$userstrip = stripcslashes($usertrim);
$finaluser = htmlspecialchars($userstrip);

//similar for password space and special character elimination 

$passtrim = trim($_POST['password']);
//eliminated space and special characters here
$passstrip = stripcslashes($passtrim);
$finalpass = htmlspecialchars($passstrip);

//comparison between user input with database values
$sql = "SELECT * FROM user_tbl where username = '$finaluser' AND password = '$finalpass'";
//SQL result executed
$result = mysqli_query($con,$sql);

//if number of rows is greater than 0 then there is username and password match
//match is found else is not found

if(mysqli_num_rows($result)>=1)
{
    //username is stored to session and forwarded to next page
    $_SESSION["myuser"]= $finaluser;
    header("Location:newpage.html");
}
else {
    //error is shown in the same page or next page
    $_SESSION["error"]= "You are not a valid user";
    header("Location:error.html");
    
}
*/

session_start();

$con = mysqli_connect("localhost","root","","user");

$manager = "manager"; 

//$username = $_POST['username'];

$usertrim = trim($_POST['username']);
$userstrip = stripcslashes($usertrim);
$finaluser = htmlspecialchars($userstrip);

//$password = $_POST['password'];

$passtrim = trim($_POST['password']);
$passstrip = stripcslashes($passtrim);
$finalpass = htmlspecialchars($passstrip);


$sql = " SELECT * FROM users where username = '$finaluser' AND password = '$finalpass'";

$final = mysqli_query($con,$sql);
    
$user_data = mysqli_fetch_assoc($final);

$role = $user_data['role'];

if(mysqli_num_rows($final)>=1 AND $role == $manager )
{
    $_SESSION["myuser"]= $finaluser;
    header("Location:manager.html");
    //echo "The role is: ", $role;
}
else
{
    header("Location:staff.html");
   // echo "The role is: ", $role;
}

?>
