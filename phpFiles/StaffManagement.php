<?php
session_start(); //for cookies
//READING USERS.JSON
$json = '../Data/users.json';
//check if it exists
if (file_exists($json)) {
    $jsonData = file_get_contents($json);
    $users = json_decode($jsonData, true);
    //checking if save was pressed to store the new data in users.json
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save']))
    {
        $targetUsername = $_POST['target_username']; 
        $updatedEmail = $_POST['email'];
        $updatedPhone = $_POST['phone'];
        $updatedRole = $_POST['role'];

        foreach ($users as &$user) {
            if ($user['username'] === $targetUsername) {
                $user['email'] = $updatedEmail;
                $user['phone'] = $updatedPhone;
                $user['role'] = $updatedRole;
                break;
            }
        }
        $json_en = json_encode($users,JSON_PRETTY_PRINT);
        file_put_contents($json,$json_en);
        header("Location: StaffManagement.php#".urlencode($targetUsername));
        exit();
    }
   //creating  table row with table data to showcase users info: changeable and non changeable if editing or if not
   $Rows = '';
   foreach ($users as $user) {
       $Rows .= "<div class='user-block' id='" . htmlspecialchars($user['username']) . "'>"; // wrapper div to style each user block
       $Rows .= "<form method='POST' action='StaffManagement.php'>";
       $Rows .= "<table>";
   
       if (isset($_GET['edit']) && $_GET['edit'] === $user['username']) {
           $Rows .= "
               <tr><th>Name</th></tr>
               <tr><td><input type='text' name='name' value='" . htmlspecialchars($user['name']) . "' disabled></td></tr>
   
               <tr><th>Surname</th></tr>
               <tr><td><input type='text' name='surname' value='" . htmlspecialchars($user['surname']) . "' disabled></td></tr>
   
               <tr><th>Email</th></tr>
               <tr><td><input type='email' name='email' value='" . htmlspecialchars($user['email']) . "' required></td></tr>
   
               <tr><th>Phone Number</th></tr>
               <tr><td><input type='text' name='phone' value='" . htmlspecialchars($user['phone']) . "' required></td></tr>
   
               <tr><th>Role</th></tr>
               <tr><td><input type='text' name='role' value='" . htmlspecialchars($user['role']) . "' required></td></tr>
   
               <tr>
                   <td>
                       <input type='hidden' name='target_username' value='" . htmlspecialchars($user['username']) . "'>
                       <button type='submit' name='save' class='save-button'>Save</button>
                   </td>
               </tr>
           ";
       } else {
           $Rows .= "
               <tr><th>Name</th></tr>
               <tr><td>" . htmlspecialchars($user['name']) . "</td></tr>
   
               <tr><th>Surname</th></tr>
               <tr><td>" . htmlspecialchars($user['surname']) . "</td></tr>
   
               <tr><th>Email</th></tr>
               <tr><td>" . htmlspecialchars($user['email']) . "</td></tr>
   
               <tr><th>Phone Number</th></tr>
               <tr><td>" . htmlspecialchars($user['phone']) . "</td></tr>
   
               <tr><th>Role</th></tr>
               <tr><td>" . htmlspecialchars($user['role']) . "</td></tr>
   
               <tr>
                   <td>
                       <a href='StaffManagement.php?edit=" . urlencode($user['username']) . "' class='edit-button'>Edit</a>
                       <a href='../htmlfiles/OldPassword.html' class='change-password'>Change Password</a>
                   </td>
               </tr>
           ";
       }
   
       $Rows .= "</table>";
       $Rows .= "</form>";
       $Rows .= "</div>"; // close user block
   }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management</title>
    <link rel="stylesheet" href="../cssFiles/Management.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Staff Management</h1>
        </header>
        <div class="user-table">
            <?php echo $Rows; ?>
        </div>
    </div>
</body>
</html>
