<?php
session_start(); // for cookies
// READING USERS.JSON
$json = '../Data/users.json';

if (file_exists($json)) {
    // Load existing users
    $jsonData = file_get_contents($json);
    $users = json_decode($jsonData, true);

    // role setignig
    $roles = ['manager', 'customer', 'waitstaff', 'kitchenstaff'];

    if (isset($_GET['search'])) {
        $query = strtolower(trim($_GET['search']));
        $filePath = '../Data/users.json';
    
        if (file_exists($filePath)) {
            $jsonData = file_get_contents($filePath);
            $users = json_decode($jsonData, true);
    
            $results = array_filter($users, function ($user) use ($query) {
                return (stripos($user['name'], $query) !== false || stripos($user['surname'], $query) !== false);
            });
    
            header('Content-Type: application/json');
            echo json_encode(array_values($results));
            exit;
        } else {
            echo json_encode([]);
            exit;
        }

    // Handle save action
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
        $targetUsername = $_POST['target_username'];
        $updatedEmail    = $_POST['email'];
        $updatedPhone    = $_POST['phone'];
        $updatedRole     = $_POST['role'];

        // Update the matching user
        foreach ($users as &$user) {
            if ($user['username'] === $targetUsername) {
                $user['email'] = $updatedEmail;
                $user['phone'] = $updatedPhone;
                $user['role']  = $updatedRole;
                break;
            }
        }
        unset($user);

        // Save back to JSON file
        $json_en = json_encode($users, JSON_PRETTY_PRINT);
        file_put_contents($json, $json_en);

        // Redirect back to the edited user
        header("Location: StaffManagement.php#" . urlencode($targetUsername));
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
        <input type="text" id="searchInput" placeholder="Search by name or surname">
        <button onclick="searchUsers()">Search</button>

       <div id="results"></div>
        <div class="user-table">
            <?php echo $Rows; ?>
        </div>
    </div>
    <script>
        function searchUsers() {
        const query = document.getElementById("searchInput").value.trim();

        if (query === "") {
            document.getElementById("results").innerHTML = "Please enter a name or surname.";
            return;
        }

        fetch("StaffManagement.php?search=" + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                const resultsDiv = document.getElementById("results");
                if (data.length > 0) {
                    resultsDiv.innerHTML = data.map(user => `<p>${user.name} ${user.surname}</p>`).join("");
                } else {
                    resultsDiv.innerHTML = "No matches found.";
                }
            })
            .catch(error => {
                document.getElementById("results").innerHTML = "Error occurred: " + error;
            });
            }
        </script>
</body>
</html>
