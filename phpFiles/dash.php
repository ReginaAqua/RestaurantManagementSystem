<?php
session_start();

$usersFile = '../Data/users.json';
$usersData = file_get_contents($usersFile);
$user_dec = json_decode($usersData, true);

// Top bar settings
$loggedInUsername = $_SESSION['usernm'] ?? '';
$displayName = '';
$userRole = '';

foreach ($user_dec as $user) {
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Interactive Dashboard</title>
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
        <a href="../htmlfiles/login.html" onclick="return confirmLogout()">Log Out</a>
        </div>
      </div>
    </div>

    <!-- Dashboard Boxes -->
    <div class="dashboard-container">
      <a href="../phpFiles/AccountManagement.php" class="dashboard-box">Account Management</a>
      <a href="#" class="dashboard-box">Analytics</a>
      <a href="../phpFiles/Schedule.php" class="dashboard-box">Schedule</a>
      <a href="../phpFiles/inventory.php" class="dashboard-box">Inventory</a>
      <?php if ($userRole === 'manager'): ?>
        <a href="../phpFiles/manage_reservations.php" class="dashboard-box">Reservations Management</a>
        <a href="../phpFiles/StaffManagement.php" class="dashboard-box">Staff Management</a>
        <a href="../phpFiles/scheduleManager.php" class="dashboard-box">Schedule Management</a>
      <?php endif; ?>
      <a href="../phpFiles/orders.php" class="dashboard-box">Orders</a>
    </div>
  </div>

<script src="../htmlFiles/dash.js"></script>

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
