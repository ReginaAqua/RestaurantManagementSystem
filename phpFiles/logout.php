<?php
session_start(); 
session_destroy();
header("Location: ../htmlfiles/login.html"); 
exit;
?>