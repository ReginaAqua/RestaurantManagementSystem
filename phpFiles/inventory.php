<?php
session_start();
require_once 'dompdf/autoload.inc.php'; // Make sure you have installed Dompdf!
use Dompdf\Dompdf;

// --- SETTINGS ---
$jsonFile = '../Data/PP_DB.json';
$managementEmail = 'manager@example.com'; // Update to your real management email

//json for users
$userFile = '../Data/users.json';
$userdata = file_get_contents($userFile,true);
$user_dec = json_decode($userdata,true);

//settigns for sepperatign manager options from regular staff
$userRole = '';

foreach ($user_dec as $user) {
  if (isset($_SESSION['usernm'])&& $user['username']===$_SESSION['usernm']) {
    $userRole = $user['role'] ?? '';
    break;
  }
}

// top bar settings
$loggedInUsername = $_SESSION['usernm'] ?? '';
$displayName = '';

foreach ($user_dec as $user) {
  if ($user['username'] === $loggedInUsername) {
    $displayName = htmlspecialchars($user['name'] . ' ' . $user['surname']);
    break;
  }
}

// --- HANDLE POST REQUEST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = json_decode(file_get_contents($jsonFile), true);

    if (isset($_POST['generate_report'])) {
        // Generate PDF Report
        $inventory = $db['inventory'] ?? [];

        $html = "<h1>Daily Inventory Report</h1><table border='1' cellpadding='5' cellspacing='0'><tr><th>Item Name</th><th>Quantity</th><th>Reorder Level</th><th>Supplier Info</th></tr>";

        foreach ($inventory as $item) {
            $html .= "<tr>
                <td>{$item['item_name']}</td>
                <td>{$item['quantity']}</td>
                <td>{$item['reorder_level']}</td>
                <td>{$item['supplier_info']}</td>
            </tr>";
        }

        $html .= "</table>";

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('Daily_Inventory_Report.pdf');
        exit;
    } elseif (isset($_POST['add_item'])) {
        // Adding a new item
        $newItem = [
            "item_name" => $_POST['item_name'],
            "quantity" => floatval($_POST['quantity']),
            "reorder_level" => intval($_POST['reorder_level']),
            "supplier_info" => $_POST['supplier_info']
        ];
        if (!isset($db['inventory'])) {
            $db['inventory'] = [];
        }
        $db['inventory'][] = $newItem;
        file_put_contents($jsonFile, json_encode($db, JSON_PRETTY_PRINT));
        header('Location: inventory.php');
        exit;
    } else {
        // Update quantity
        $item_name = $_POST['item'];
        $quantity = floatval($_POST['quantity']);

        if (isset($db['inventory']) && is_array($db['inventory'])) {
            foreach ($db['inventory'] as &$item) {
                if ($item['item_name'] === $item_name) {
                    $item['quantity'] = $quantity;

                    if ($quantity <= $item['reorder_level']) {
                        $subject = "Low Inventory Alert: " . $item['item_name'];
                        $message = "The inventory item '" . $item['item_name'] . "' is low on stock. Only " . $quantity . " units remaining.";
                        $headers = "From: inventory-system@example.com";

                        mail($managementEmail, $subject, $message, $headers);
                    }
                    break;
                }
            }
            unset($item);
            file_put_contents($jsonFile, json_encode($db, JSON_PRETTY_PRINT));
        }
        header('Location: inventory.php');
        exit;
    }
}

// --- LOAD INVENTORY ---
$db = json_decode(file_get_contents($jsonFile), true);
$inventory = $db['inventory'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Management</title>
    <link rel="stylesheet" href="../cssFiles/inventory.css">
    <link rel="stylesheet" href="../cssfiles/dash.css"> <!-- Use your dashboard layout CSS -->
</head>
<body>
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

<div class="main" id="mainContent">
  <div class="top-bar">
    <button class="toggle-btn" id="toggleSidebar">&#9776;</button>
    <div class="profile" id="profileBtn">
      <span class="profile-name"><?php echo $displayName; ?></span>
      <div class="dropdown" id="profileDropdown">
      <a href="../htmlFiles/login.html">Log Out</a>
      </div>
    </div>
  </div>
  
<header>
<div class="header-container">
        <button class="add-item-button" onclick="openAddForm()">Add Item</button>
    </div>
</header>
<main>
    <h1>Current Inventory</h1>

    <form method="POST" class="generate-report-form">
        <input type="hidden" name="generate_report" value="1">
        <button type="submit" class="generate-report-button">Generate Daily Report</button>
    </form>

    <!-- Hidden Add Item Form -->
    <div id="addItemForm" class="add-item-form" style="display:none;">
        <form method="POST">
            <input type="hidden" name="add_item" value="1">
            <input type="text" name="item_name" placeholder="Item Name" required>
            <input type="text" name="supplier_info" placeholder="Supplier Info" required>
            <input type="number" name="reorder_level" placeholder="Reorder Level" required>
            <input type="number" name="quantity" placeholder="Quantity" step="0.01" required>
            <button type="submit" class="add-button">Add Item</button>
            <button type="button" class="cancel-button" onclick="closeAddForm()">Cancel</button>
        </form>
    </div>

    <div class="inventory-container">
        <?php if (empty($inventory)): ?>
            <p class="no-inventory">No inventory items available.</p>
        <?php else: ?>
            <?php foreach ($inventory as $item): ?>
                <div class="inventory-item">
                    <h3><?= htmlspecialchars($item['item_name']) ?></h3>
                    <p>Supplier: <?= htmlspecialchars($item['supplier_info']) ?></p>
                    <p>Reorder Level: <?= htmlspecialchars($item['reorder_level']) ?></p>
                    <form method="POST">
                        <input type="hidden" name="item" value="<?= htmlspecialchars($item['item_name']) ?>">
                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" step="0.01" required>
                        <button type="submit" class="update-button">Update</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<script>
function openAddForm() {
    document.getElementById('addItemForm').style.display = 'block';
}
function closeAddForm() {
    document.getElementById('addItemForm').style.display = 'none';
}
</script>
 <script src="../htmlfiles/dash.js"></script>
</div> <!-- end of .main -->
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
