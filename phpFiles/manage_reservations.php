<?php
session_start();//cookies always to keep the data for instant use
require '../vendor/autoload.php'; //Requirements to access phpemailer to send emails to gmail using composer.
// Include PHPMailer files
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$json = '../Data/PP_DB.json'; // file route of database
$json_users = '../Data/users.json'; // file route for users
//initiliasing
$Rows = '';
$editingReservation = isset($_GET['edit']) ? $_GET['edit'] : null;
//EMAIL SETUP:
$mail = new PHPMailer(true);  // Passing `true` enables exceptions

//Server settings
$mail->isSMTP();  // Setting mailer to use SMTP
$mail->Host = 'smtp.gmail.com';  // Setting Gmail's SMTP server
$mail->SMTPAuth = true;  // Enabling SMTP authentication
$mail->Username = 'anastasiosdrog@gmail.com';  // Your Gmail address
$mail->Password = 'zgau morr ihfz qdmt';  // Your app-specific password (if 2FA is enabled)
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Enable TLS encryption
$mail->Port = 587;  // TCP port to connect to (587 for Gmail)

//initiliase information for email and other
$name=null;
$email=null;
$user_id=null;
$table=null;
$time=null;
$date=null;

//function that turns hours to minutes
function timeToMinutes($timeStr) {
    [$hour, $minute] = explode(':', $timeStr);
    return ((int)$hour * 60) + (int)$minute;
} 

//check if files exist
if (file_exists($json) && file_exists($json_users)) {
    $jsonData = file_get_contents($json);
    $jsonUsers = file_get_contents($json_users);
    $data = json_decode($jsonData, true);
    $dec_users=json_decode($jsonUsers,true);
    $reservations = $data['reservations'];
    //sorting reservations based on date/unix timestamp
    usort($reservations, function($a, $b) {
        return strtotime($a['reservation_date']) - strtotime($b['reservation_date']);//this function sorts dates by converting them in Unix timestamp and comparing them.
    });

    // The Delete Post and send email 
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete']) && isset($_POST['reservation_id'])) {
        $targetId = $_POST['reservation_id'];
        foreach ($reservations as $res) {
        if ($res['reservation_id'] === $targetId) {
            $name = $res['name'];
            $user_id = $res['user_id'];
            $table=$res['table'];
            $time = $res['reservation_time'];
            $date = $res['reservation_date'];
            break;
        }
    }
        $reservations = array_filter($reservations, function ($res) {
            return $res['reservation_id'] !== $_POST['reservation_id'];
        });
        $data['reservations'] = array_values($reservations);//re-indexes after deleting the previous array element
        file_put_contents($json, json_encode($data, JSON_PRETTY_PRINT));
       foreach($dec_users as $user){
        if($user_id === $user['user_id'] && $name===$user['name'])
        {
            $email=$user['email'];
            //Recipients
            $mail->setFrom('anastasiosdrog@gmail.com', "Dragon's Pizzeria");// Sender's email
            $mail->addAddress($email);  // Recipient's email
            $mail->isHTML(true);  // Setting email format to HTML
            $mail->Subject = "Dragon's Pizzeria";//Subject of the email
            $mail->Body = 'Dear ' . $name . ', your reservation for
            table ' . $table . 
            ' at ' . $time .
            ' on ' . $date .
            " has been cancelled. If you have any questions or need further assistance, 
            please don't hesitate to contact us. We apologize for the inconvenience,
            and thank you for your understanding.";//Body of the email
            // Sending the email
            try {
                $mail->send();
                header("Location: manage_reservations.php");
                exit();
            } catch (Exception $e) {
                echo "Mailer Error: " . $mail->ErrorInfo;
                exit(); //no redirect
            }
            break;
        }
      }
    }

    // The Save Post
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save']) && isset($_POST['reservation_id'])) {
        foreach ($reservations as &$res) {
            if ($res['reservation_id'] === $_POST['reservation_id']) {
                //bool to check for conflicts in time for the table
                $isconflict=false;
                if ($res['reservation_time'] !== $_POST['reservation_time'] ||
                $res['reservation_date'] !== $_POST['reservation_date']){
                  // Convert requested time to hour 
                  $requested_minutes = timeToMinutes($_POST['reservation_time']);
                  //CHEKING all reservations again to find all the tables matching and check for time
                  foreach($reservations as $reserv)
                  {
                     //Skipping the current reservation
                      if ($reserv['reservation_id'] === $_POST['reservation_id']) {
                      continue;
                    }
                    if($_POST['reservation_date']=== $reserv['reservation_date'] &&
                    $_POST['table']===$reserv['table'])
                    {
                        $existing_minutes = timeToMinutes($reserv['reservation_time']);
                        // checks if the reservation is too close (less than 2 hours apart in minutes)
                       if (abs($existing_minutes - $requested_minutes) < 120) {
                        $isconflict=true;
                        break;
                      }
                    } 
                }
                if ($isconflict) {
                    die("ERROR CONFLICT");
                }
                
                else{
                $res['name'] = $_POST['name'];
                $res['num_people'] = $_POST['num_people'];
                $res['reservation_time'] = $_POST['reservation_time'];
                $res['reservation_date'] = $_POST['reservation_date'];
                break;
                }
            }
        }
        $data['reservations'] = $reservations;
        file_put_contents($json, json_encode($data, JSON_PRETTY_PRINT));
        header("Location: manage_reservations.php#" . urlencode($_POST['reservation_id']));
        exit();
    }
 }

    // Generate output HTML.
    foreach ($reservations as $res) {
        $resId = htmlspecialchars($res['reservation_id']);
        $Rows .= "<div class='user-block' id='$resId'>";
        if ($editingReservation === $res['reservation_id']) {
            // Edit Mode
            $Rows .= "<form method='POST' action='manage_reservations.php'>";
            $Rows .= "<div class='user-header'>📝 Editing " . htmlspecialchars($res['table']) . "</div>";
            $Rows .= "<div class='user-details' style='display:block'>";
            $Rows .= "<table class='horizontal-table'>";
            $Rows .= "<tr><th>Name</th></tr><tr><td><input type='text' name='name' value='" . htmlspecialchars($res['name']) . "' required></td></tr>";
            $Rows .= "<tr><th>Number of People</th></tr><tr><td><input type='number' name='num_people' value='" . htmlspecialchars($res['num_people']) . "' required></td></tr>";
            $Rows .= "<tr><th>Reservation Time</th></tr><tr><td><input type='time' name='reservation_time' value='" . htmlspecialchars($res['reservation_time']) . "' required></td></tr>";
            $Rows .= "<tr><th>Reservation Date</th></tr><tr><td><input type='date' name='reservation_date' value='" . htmlspecialchars($res['reservation_date']) . "' required></td></tr>";
            $Rows .= "<tr><th>Table</th></tr><tr><td><input type='text' name='table' value='" . htmlspecialchars($res['table']) . "' readonly></td></tr>";
            $Rows .= "<input type='hidden' name='reservation_id' value='" . $resId . "'>";
            $Rows .= "<tr><td><button type='submit' name='save' class='save-button'>Save</button></td></tr>";
            $Rows .= "</table></div></form>";
        } else {
            // View Mode
            $Rows .= "<div class='user-header' onclick='toggleDetails(this)'>▶ " . htmlspecialchars($res['reservation_date']) . "</div>";
            $Rows .= "<div class='user-details'>";
            $Rows .= "<table class='horizontal-table'>";
            $Rows .= "<tr><th>Name</th></tr><tr><td>" . htmlspecialchars($res['name']) . "</td></tr>";
            $Rows .= "<tr><th>Number of People</th></tr><tr><td>" . htmlspecialchars($res['num_people']) . "</td></tr>";
            $Rows .= "<tr><th>Reservation Time</th></tr><tr><td>" . htmlspecialchars($res['reservation_time']) . "</td></tr>";
            $Rows .= "<tr><th>Table</th></tr><tr><td>" . htmlspecialchars($res['table']) . "</td></tr>";
            $Rows .= "<tr><td>
                <a href='?edit=" . urlencode($res['reservation_id']) . "' class='edit-button'>Edit</a>
                <form method='POST' action='manage_reservations.php' style='display:inline; margin-left: 10px;'>
                <input type='hidden' name='reservation_id' value='" . $resId . "'>
                <button type='submit' name='delete' class='delete-button' onclick='return confirm(\"Delete this reservation?\")'>Delete</button>
                </form>
            </td></tr>";
            $Rows .= "</table></div>";
        }

        $Rows .= "</div>";
    }
  
} 
 else {
    $Rows = "<p>No reservation data found.</p>";
}
//side bar settigns for sepperatign manager options from regular staff
$userRole = '';

foreach ($dec_users as $user) {
  if (isset($_SESSION['usernm'])&& $user['username']===$_SESSION['usernm']) {
    $userRole = $user['role'] ?? '';
    break;
  }
}

// Top bar settings
$loggedInUsername = $_SESSION['usernm'] ?? '';
$displayName = '';
$userRole = '';

foreach ($dec_users as $user) {
  if ($user['username'] === $loggedInUsername) {
    $displayName = htmlspecialchars($user['name'] . ' ' . $user['surname']);
    $userRole = $user['role'] ?? '';
    break;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Reservations</title>
    <link rel="stylesheet" href="../cssfiles/Management.css">
    <link rel="stylesheet" href="../cssFiles/dash.css">
</head>
<body>
    <!-- Sidebar Navigation -->
  <div class="sidebar" id="sidebar">
    <a href="../phpfiles/dash.php"><span>Dashboard</span></a>
    <a href="../phpFiles/AccountManagement.php"><span>Account Management</span></a>
    <a href="../phpFiles/Schedule.php"><span>Schedule</span></a>
    <a href="../phpFiles/inventory.php"><span>Inventory</span></a>
    <a href="../phpFiles/orders.php"><span>Orders</span></a>
    <?php if ($userRole === 'manager'): ?>
    <a href="../phpFiles/StaffManagement.php"><span>Staff Management</span></a>
    <a href="../phpFiles/scheduleManager.php"><span>Schedule Management</span></a>
    <a href="../phpFiles/manage_reservations.php"><span>Reservations</span></a>
    <?php endif;?>
  </div>

<!-- Main Content Area -->
<div class="main" id="mainContent">
  <!-- Top Bar -->
  <div class="top-bar">
    <button class="toggle-btn" id="toggleSidebar">&#9776;</button>
    <div class="profile" id="profileBtn">
      <span class="profile-name"><?php echo$displayName?></span>
      <div class="dropdown" id="profileDropdown">
        <a href="../phpFiles/AccountManagement.php">Account Management</a>
        <a href="../htmlfiles/login.html">Log Out</a>
      </div>
    </div>
  </div>
<h1 style="text-align: center;">Table Reservations</h1>
<div class="container">
    <?php echo $Rows; ?>
</div>

<script>
function toggleDetails(header) {
    const details = header.nextElementSibling;
    const isVisible = details.style.display === "block";
    details.style.display = isVisible ? "none" : "block";
    header.textContent = (isVisible ? "▶ " : "▼ ") + header.textContent.slice(2);
}
</script>
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
