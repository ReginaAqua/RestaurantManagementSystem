<?php
session_start(); //for cookies
//READING USERS.JSON
$json = '../Data/users.json';
//check if it exists
if (file_exists($json)) {
    $jsonData = file_get_contents($json);
    $users = json_decode($jsonData, true);
    //checking if save was pressed to store the new data in users.json
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
        $updatedEmail = $_POST['email'];
        $updatedPhone = $_POST['phone'];

        foreach ($users as &$user) {
            if ($user['username'] === $_SESSION['usernm']) {
                $user['email'] = $updatedEmail;
                $user['phone'] = $updatedPhone;
                break;
            }
        }
        $json_en = json_encode($users,JSON_PRETTY_PRINT);
        file_put_contents($json,$json_en);
        header("Location: AccountManagement.php");
        exit();
    }
   //creating  table row with table data to showcase users info: changeable and non changeable if editing or if not
    $Rows = '';
    foreach ($users as $user) {
        if (isset($_GET['edit']) && isset($user['username']) && $_SESSION['usernm'] == $user['username']) {
            $Rows .= "
                <form method='POST' action='AccountManagement.php'>
                    <tr><th>Name</th></tr>
                    <tr>
                    <td><input type='text' name='name' value='" . htmlspecialchars($user['name']) . "' disabled></td>
                    </tr>
                     <tr><th>Surname</th></tr>
                    <tr> 
                    <td><input type='text' name='surname' value='" . htmlspecialchars($user['surname']) . "' disabled></td>
                    </tr>
                     <tr><th>Email</th></tr>
                    <tr>
                    <td><input type='email' name='email' value='" . htmlspecialchars($user['email']) . "' required></td>
                    </tr>
                     <tr><th>Phone Number</th></tr>
                    <tr>
                    <td><input type='text' name='phone' value='" . htmlspecialchars($user['phone']) . "' required></td>
                    </tr>
                     <tr><th>Role</th></tr>
                    <tr>
                    <td><input type='text' name='role' value='" . htmlspecialchars($user['role']) . "' disabled></td>
                    </tr>
                    <tr>
                    <td>
                        <button type='submit' name='save' class='save-button'>Save</button>
                    </td>
                </form>
            </tr>";
        } else if ($_SESSION['usernm'] == $user['username']){
            $Rows .= "<tr>
                <tr><th>Name</th></tr>
                <td>" . htmlspecialchars($user['name']) . "</td>
                </tr>
                <tr><th>Surname</th></tr>
                <tr>
                <td>" . htmlspecialchars($user['surname']) . "</td>
                </tr>
                <tr><th>Email</th></tr>
                <tr>
                <td>" . htmlspecialchars($user['email']) . "</td>
                </tr>
                <tr><th>Phone Number</th></tr>
                <tr>
                <td>" . htmlspecialchars($user['phone']) . "</td>
                </tr>
                <tr><th>Role</th></tr>
                <tr>
                <td>".htmlspecialchars($user['role'])."</td>
                </tr>
                <tr>
                <td><a href='AccountManagement.php?edit=1' class='edit-button'>Edit</a>
                <a href='../htmlfiles/OldPassword.html' class='change-password'>Change Password</a></td>
            </tr>";
        }
    }
} else {
    $Rows = "<tr><td colspan='5'>Error: User data file not found.</td></tr>";
}
//side bar settigns for sepperatign manager options from regular staff
$userRole = '';

foreach ($users as $user) {
  if (isset($_SESSION['usernm'])&& $user['username']===$_SESSION['usernm']) {
    $userRole = $user['role'] ?? '';
    break;
  }
}

// top bar settings
$loggedInUsername = $_SESSION['usernm'] ?? '';
$displayName = '';

foreach ($users as $user) {
  if ($user['username'] === $loggedInUsername) {
    $displayName = htmlspecialchars($user['name'] . ' ' . $user['surname']);
    break;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Management</title>
    <link rel="stylesheet" href="../cssFiles/Management.css">
    <link rel="stylesheet" href="../cssFiles/dash.css">
</head>
<body>
      <!-- Sidebar Navigation -->
  <div class="sidebar" id="sidebar">
    <a href="../phpfiles/dash.php"><span>Dashboard</span></a>
    <a href="../phpFiles/AccountManagement.php"><span>Account Management</span></a>
    <a href=""><span>Analytics</span></a>
    <a href="../phpFiles/Schedule.php"><span>Schedule</span></a>
    <a href="../phpFiles/inventory.php"><span>Inventory</span></a>
    <a href="../phpFiles/orders.php"><span>Orders</span></a>
    <?php if ($userRole === 'manager'): ?>
    <a href="../phpFiles/StaffManagement.php"><span>Staff Management</span></a>
    <a href="../phpFiles/scheduleManager.php"><span>Schedule Management</span></a>
    <a href="../phpFiles/manage_reservations.php"><span>Reservations</span></a>
    <?php endif;?>
    <a href="../phpFiles/PreviousOrders.php"><span>Previous Orders</span></a>
  </div>

<!-- Main Content Area -->
<div class="main" id="mainContent">
  <!-- Top Bar -->
  <div class="top-bar">
    <button class="toggle-btn" id="toggleSidebar">&#9776;</button>
    <div class="profile" id="profileBtn">
      <span class="profile-name"><?php echo $displayName; ?></span>
      <div class="dropdown" id="profileDropdown">
        <a href="../phpFiles/AccountManagement.php">Account Management</a>
        <a href="../htmlfiles/login.html">Log Out</a>
      </div>
    </div>
  </div>
  
  <!--  Move your content INSIDE this div -->
  <div class="container">
    <header>
      <h1>Account Management</h1>
    </header>
    <div class="user-table">
      <table>
        <thead>
        </thead>
        <tbody>
          <?php echo $Rows; ?>
        </tbody>
      </table>
    </div>  
  </div>
</div> <!-- closing the .main div here -->

<!-- JS file should stay right before </body> -->
<script src="../htmlfiles/dash.js"></script>
<!--logout script-->
<script>
function confirmLogout() {
    if (confirm("Are you sure you want to log out?")) {
        //  make a fetch request to logout.php
        fetch('logout.php')
            .then(response => {
                if (response.ok) {
                    // you can redirect after a successful fetch
                    window.location.href = "../htmlfiles/login.html";
                } else {
                    alert('Logout failed!');
                }
            })
    }
}
</script>
</body>
</html>
