<?php
session_start();
require 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;

require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$jsonFile = '../Data/PP_DB.json';
$managementEmail = 'oddandv@gmail.com';

if (!file_exists($jsonFile)) {
    die('Database not found.');
}

$db = json_decode(file_get_contents($jsonFile), true);
$inventory = $db['inventory'] ?? [];
$today = date('Y-m-d');

// --- Handle Snapshot Creation ---
if (!isset($db['inventory_snapshot']) || $db['inventory_snapshot']['date'] !== $today) {
    $db['inventory_snapshot'] = [
        'date' => $today,
        'inventory' => []
    ];
    foreach ($inventory as $item) {
        $db['inventory_snapshot']['inventory'][$item['item_name']] = $item['quantity'];
    }
    file_put_contents($jsonFile, json_encode($db, JSON_PRETTY_PRINT));
}

// --- Handle POST Requests ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['generate_report'])) {
        // Generate Daily Report
        $snapshot = $db['inventory_snapshot'];
        $html = "<h1>Daily Inventory Usage Report ({$today})</h1>";
        $html .= "<table border='1' cellpadding='5'>
                    <tr><th>Item Name</th><th>Starting Quantity (00:00)</th><th>Current Quantity</th><th>Consumed Today</th></tr>";

        foreach ($inventory as $item) {
            $startQty = $snapshot['inventory'][$item['item_name']] ?? 0;
            $currentQty = $item['quantity'];
            $consumed = $startQty - $currentQty;
            $consumed = $consumed < 0 ? 0 : $consumed;

            $html .= "<tr>
                        <td>{$item['item_name']}</td>
                        <td>{$startQty}</td>
                        <td>{$currentQty}</td>
                        <td>{$consumed}</td>
                      </tr>";
        }

        $html .= "</table>";

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('Daily_Inventory_Report.pdf');
        exit;
    }

    if (isset($_POST['add_item'])) {
        // Add Item
        $newItem = [
            "item_name" => $_POST['item_name'],
            "quantity" => floatval($_POST['quantity']),
            "reorder_level" => intval($_POST['reorder_level']),
            "supplier_info" => $_POST['supplier_info']
        ];
        $db['inventory'][] = $newItem;
        file_put_contents($jsonFile, json_encode($db, JSON_PRETTY_PRINT));
        header('Location: inventory.php');
        exit;
    }

    if (isset($_POST['edit_item'])) {
        // Edit Item
        $item_name = $_POST['item_name'];
        $supplier_info = $_POST['supplier_info'];
        $reorder_level = intval($_POST['reorder_level']);
        $quantity = floatval($_POST['quantity']);

        foreach ($db['inventory'] as &$item) {
            if ($item['item_name'] === $item_name) {
                $item['supplier_info'] = $supplier_info;
                $item['reorder_level'] = $reorder_level;
                $item['quantity'] = $quantity;
                break;
            }
        }
        unset($item);
        file_put_contents($jsonFile, json_encode($db, JSON_PRETTY_PRINT));
        header('Location: inventory.php');
        exit;
    }

    if (isset($_POST['item'])) {
        // Update Quantity
        $item_name = $_POST['item'];
        $quantity = floatval($_POST['quantity']);

        foreach ($db['inventory'] as &$item) {
            if ($item['item_name'] === $item_name) {
                $item['quantity'] = $quantity;

                if ($quantity <= $item['reorder_level']) {
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'anastasiosdrog@gmail.com';
                        $mail->Password = 'zgau morr ihfz qdmt';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        $mail->setFrom('anastasiosdrog@gmail.com', "Dragon's Pizzeria");
                        $mail->addAddress($managementEmail);

                        $mail->isHTML(true);
                        $mail->Subject = 'Low Inventory Alert - ' . $item['item_name'];
                        $mail->Body = "
                            <h2 style='color: red;'>⚠️ Low Stock Alert</h2>
                            <p><strong>Item:</strong> " . htmlspecialchars($item['item_name']) . "</p>
                            <p><strong>Quantity Remaining:</strong> <span style='color:red;'>" . htmlspecialchars($quantity) . "</span></p>
                            <p><strong>Supplier:</strong> " . htmlspecialchars($item['supplier_info']) . "</p>
                            <p><strong>Reorder Level:</strong> " . htmlspecialchars($item['reorder_level']) . "</p>
                        ";
                        $mail->send();
                    } catch (Exception $e) {
                        error_log("Mailer Error: {$mail->ErrorInfo}");
                    }
                }
                break;
            }
        }
        unset($item);
        file_put_contents($jsonFile, json_encode($db, JSON_PRETTY_PRINT));
        header('Location: inventory.php');
        exit;
    }
}

// Reload latest DB after updates
$db = json_decode(file_get_contents($jsonFile), true);
$inventory = $db['inventory'] ?? [];
?>

<!-- HTML starts below, same as you already had -->


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Management</title>
    <link rel="stylesheet" href="../cssFiles/inventory.css">
    <link rel="stylesheet" href="../cssFiles/dash.css">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <a href="../htmlfiles/dash.html"><span>Dashboard</span></a>
    <a href="../phpFiles/AccountManagement.php"><span>Account Management</span></a>
    <a href="../phpFiles/inventory.php"><span>Inventory</span></a>
    <a href="../phpFiles/manage_reservations.php"><span>Reservations</span></a>
    <a href="../phpFiles/orders.php"><span>Orders</span></a>
    <a href="../phpFiles/PreviousOrders.php"><span>Previous Orders</span></a>
</div>

<!-- Main Content -->
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

    <!-- Content Area -->
    <div class="container">
        <header>
            <button class="add-item-button" onclick="openAddForm()">Add Item</button>
        </header>

        <form method="POST" class="generate-report-form">
            <input type="hidden" name="generate_report" value="1">
            <button type="submit" class="generate-report-button">Generate Daily Report</button>
        </form>

        <!-- Hidden Forms -->
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

        <div id="editItemForm" class="add-item-form" style="display:none;">
            <form method="POST">
                <input type="hidden" name="edit_item" value="1">
                <input type="text" id="edit_item_name" name="item_name" placeholder="Item Name" readonly required>
                <input type="text" id="edit_supplier_info" name="supplier_info" placeholder="Supplier Info" required>
                <input type="number" id="edit_reorder_level" name="reorder_level" placeholder="Reorder Level" required>
                <input type="number" id="edit_quantity" name="quantity" placeholder="Quantity" step="0.01" required>
                <button type="submit" class="add-button">Save Changes</button>
                <button type="button" class="cancel-button" onclick="closeEditForm()">Cancel</button>
            </form>
        </div>

        <div class="inventory-container">
            <?php if (empty($inventory)): ?>
                <p class="no-inventory">No inventory items available.</p>
            <?php else: ?>
                <?php foreach ($inventory as $item): ?>
                    <div class="inventory-item <?= ($item['quantity'] <= $item['reorder_level']) ? 'low-stock' : '' ?>">
                        <h3><?= htmlspecialchars($item['item_name']) ?></h3>
                        <p>Reorder Level: <?= htmlspecialchars($item['reorder_level']) ?></p>
                        <form method="POST">
                            <input type="hidden" name="item" value="<?= htmlspecialchars($item['item_name']) ?>">
                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>" step="0.01" required>
                            <button type="submit" class="update-button">Update</button>
                        </form>
                        <p class="supplier-info">Supplier: <?= htmlspecialchars($item['supplier_info']) ?></p>
                        <button class="edit-button" onclick="openEditForm('<?= htmlspecialchars($item['item_name']) ?>', '<?= htmlspecialchars($item['supplier_info']) ?>', <?= $item['reorder_level'] ?>, <?= $item['quantity'] ?>)">Edit</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

</div>

<!-- JS -->
<script src="../htmlfiles/dash.js"></script>
<script>
function openAddForm() {
    document.getElementById('addItemForm').style.display = 'block';
}
function closeAddForm() {
    document.getElementById('addItemForm').style.display = 'none';
}
function openEditForm(itemName, supplierInfo, reorderLevel, quantity) {
    document.getElementById('editItemForm').style.display = 'block';
    document.getElementById('edit_item_name').value = itemName;
    document.getElementById('edit_supplier_info').value = supplierInfo;
    document.getElementById('edit_reorder_level').value = reorderLevel;
    document.getElementById('edit_quantity').value = quantity;
}
function closeEditForm() {
    document.getElementById('editItemForm').style.display = 'none';
}
</script>

</body>
</html>
