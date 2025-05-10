<?php
session_start();
//read json
$json = '../Data/users.json';
// role setignig
$roles = ['manager', 'customer', 'waitstaff', 'kitchenstaff'];

if (file_exists($json)) {
  $jsonData = file_get_contents($json);
  $users = json_decode($jsonData, true);
  //saves users detail changes 
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
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
    $json_en = json_encode($users, JSON_PRETTY_PRINT);
    file_put_contents($json, $json_en);
    header("Location: StaffManagement.php#" . urlencode($targetUsername));
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_new_user'])) {
    // Create a new user entry
    $newUser = [
        'username' => $_POST['username'],
        'name' => $_POST['name'],
        'surname' => $_POST['surname'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'role' => $_POST['role']
    ];

    // Append to the user list
    $users[] = $newUser;
    // Save back to JSON
    $json_en = json_encode($users, JSON_PRETTY_PRINT);
    file_put_contents($json, $json_en); 
    // Redirect back to the new user's section
    header("Location: StaffManagement.php#" . urlencode($newUser['username']));
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $deleteUsername = $_POST['delete_username'];

    // Remove the user from the array
    $users = array_filter($users, function ($user) use ($deleteUsername) {
        return $user['username'] !== $deleteUsername;
    });

    //  save in database 
    $users = array_values($users);
     // Save back to JSON
    $json_en = json_encode($users, JSON_PRETTY_PRINT);
    file_put_contents($json, $json_en);        
    header("Location: StaffManagement.php");
    exit();
}

    // Build rows for display
    $Rows = '';
    foreach ($users as $user) {
        // Skip customers entirely
        if ($user['role'] === 'customer') {
            continue;
        }

        $Rows .= "<div class='user-block' id='" . htmlspecialchars($user['username']) . "'>";
        $Rows .= "<form method='POST' action='StaffManagement.php'>";
        $Rows .= "<table>";

        if (isset($_GET['edit']) && $_GET['edit'] === $user['username']) {
            // Editable fields
          $Rows .= "<tr><th>Name</th></tr>";
          $Rows .= "<tr><td><input type='text' name='name' value='" . htmlspecialchars($user['name']) . "' disabled></td></tr>";

          $Rows .= "<tr><th>Surname</th></tr>";
          $Rows .= "<tr><td><input type='text' name='surname' value='" . htmlspecialchars($user['surname']) . "' disabled></td></tr>";

          $Rows .= "<tr><th>Email</th></tr>";
          $Rows .= "<tr><td><input type='email' name='email' value='" . htmlspecialchars($user['email']) . "' required></td></tr>";

          $Rows .= "<tr><th>Phone Number</th></tr>";
          $Rows .= "<tr><td><input type='text' name='phone' value='" . htmlspecialchars($user['phone']) . "' required></td></tr>";

          // Role dropdown
          $Rows .= "<tr><th>Role</th></tr>";
          $Rows .= "<tr><td><select name='role' required>";
          foreach ($roles as $r) {
              $sel = ($user['role'] === $r) ? ' selected' : '';
              $Rows .= "<option value=\"{$r}\"{$sel}>" . ucfirst($r) . "</option>";
          }
          $Rows .= "</select></td></tr>";

          $Rows .= "<tr>";
          $Rows .= "<td>";
          $Rows .= "<input type='hidden' name='target_username' value='" . htmlspecialchars($user['username']) . "'>";
          $Rows .= "<button type='submit' name='save' class='save-button'>Save</button>";
          $Rows .= "</td>";
          $Rows .= "</tr>";
      } else {
          // Read-only display
          $Rows .= "<tr><th>Name</th></tr>";
          $Rows .= "<tr><td>" . htmlspecialchars($user['name']) . "</td></tr>";

          $Rows .= "<tr><th>Surname</th></tr>";
          $Rows .= "<tr><td>" . htmlspecialchars($user['surname']) . "</td></tr>";

          $Rows .= "<tr><th>Email</th></tr>";
          $Rows .= "<tr><td>" . htmlspecialchars($user['email']) . "</td></tr>";

          $Rows .= "<tr><th>Phone Number</th></tr>";
          $Rows .= "<tr><td>" . htmlspecialchars($user['phone']) . "</td></tr>";

          $Rows .= "<tr><th>Role</th></tr>";
          $Rows .= "<tr><td>" . htmlspecialchars($user['role']) . "</td></tr>";

          $Rows .= "<tr>";
          $Rows .= "<td>";
          $Rows .= "<a href='StaffManagement.php?edit=" . urlencode($user['username']) . "' class='edit-button'>Edit</a>";
          $Rows .= "<a href='../htmlfiles/OldPassword.html' class='change-password'>Change Password</a>";
          $Rows .= "</td>";
          $Rows .= "</tr>";
        }

        $Rows .= "</table>";
        $Rows .= "</form>";
        $Rows .= "</div>";
  }
} 
//side bar settigns for sepperatign manager options from regular staff
$userRole = '';

foreach ($users as $user) {
  if (isset($_SESSION['usernm'])&& $user['username']===$_SESSION['usernm']) {
    $userRole = $user['role'] ?? '';
    break;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Staff Management</title>
  <link rel="stylesheet" href="../cssfiles/dash.css"> <!-- Use your dashboard layout CSS -->
  <link rel="stylesheet" href="../cssfiles/Management.css"> <!-- Your original styling -->
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
        <span class="profile-name">User Name</span>
        <div class="dropdown" id="profileDropdown">
          <a href="../phpFiles/AccountManagement.php">Account Management</a>
          <a href="../htmlfiles/login.html">Log Out</a>
        </div>
      </div>
    </div>

    <!-- Staff Management Content -->
    <div class="container">
      <h1 style="text-align: center">Staff Management</h1>
      <?php echo $Rows; ?>
      <button id="addRowBtn" class="add-button">Add</button> 
      <div id="newUserContainer" class="new-user-block"></div>
    </div>
  </div>

  <!-- dashboard JS -->
  <script src="../htmlfiles/dash.js"></script>

  <!--  add && delete -->
  <script>
    document.getElementById('addRowBtn').addEventListener('click', function () {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = 'StaffManagement.php';

      const table = document.createElement('table');
      table.classList.add('horizontal-table', 'new-user-table');

      table.innerHTML = `
        <tr><th>Name</th></tr>
        <tr><td><input type='text' name='name' required></td></tr>

        <tr><th>Surname</th></tr>
        <tr><td><input type='text' name='surname' required></td></tr>

        <tr><th>Email</th></tr>
        <tr><td><input type='email' name='email' required></td></tr>

        <tr><th>Phone Number</th></tr>
        <tr><td><input type='text' name='phone' required></td></tr>

        <tr><th>Role</th></tr>
        <tr><td><input type='text' name='role' required></td></tr>

        <tr><td><input type='hidden' name='username' value='user_${Date.now()}'></td></tr>
      `;

      const saveBtn = document.createElement('button');
      saveBtn.type = 'submit';
      saveBtn.name = 'add_new_user';
      saveBtn.innerText = 'Save';
      saveBtn.classList.add('edit-button');

      const deleteBtn = document.createElement('button');
      deleteBtn.type = 'button';
      deleteBtn.innerText = 'Delete';
      deleteBtn.classList.add('delete-button');
      deleteBtn.style.marginLeft = '10px';
      deleteBtn.addEventListener('click', function () {
        wrapper.remove(); // delete the whole form block
      });

      const wrapper = document.createElement('div');
      wrapper.classList.add('user-block');
      form.appendChild(table);
      form.appendChild(saveBtn);
      form.appendChild(deleteBtn);
      wrapper.appendChild(form);
      document.getElementById('newUserContainer').appendChild(wrapper);
    });
  </script>

  <script>
    function toggleDetails(header) {
      const details = header.nextElementSibling;
      const isVisible = details.style.display === "block";
      details.style.display = isVisible ? "none" : "block";
      header.innerHTML = (isVisible ? "▶" : "▼") + " " + header.textContent.slice(2);
    }
  </script>
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
