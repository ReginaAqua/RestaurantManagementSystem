<?php
session_start();

$json = 'Data/users.json';

if (file_exists($json)) {
    $jsonData = file_get_contents($json);
    $users = json_decode($jsonData, true);

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

    $userRows = '';
    foreach ($users as $user) {
        if (isset($_GET['edit']) && isset($user['username']) && $_SESSION['usernm'] == $user['username']) {
            $userRows .= "<tr>
                <form method='POST' action='AccountManagement.php'>
                    <td><input type='text' name='name' value='" . htmlspecialchars($user['name']) . "' disabled></td>
                    <td><input type='text' name='surname' value='" . htmlspecialchars($user['surname']) . "' disabled></td>
                    <td><input type='email' name='email' value='" . htmlspecialchars($user['email']) . "' required></td>
                    <td><input type='text' name='phone' value='" . htmlspecialchars($user['phone']) . "' required></td>
                    <td>
                        <button type='submit' name='save' class='save-button'>Save</button>
                    </td>
                </form>
            </tr>";
        } else {
            $userRows .= "<tr>
                <td>" . htmlspecialchars($user['name']) . "</td>
                <td>" . htmlspecialchars($user['surname']) . "</td>
                <td>" . htmlspecialchars($user['email']) . "</td>
                <td>" . htmlspecialchars($user['phone']) . "</td>
                <td><a href='AccountManagement.php?edit=1' class='edit-button'>Edit</a></td>
            </tr>";
        }
    }
} else {
    $userRows = "<tr><td colspan='5'>Error: User data file not found.</td></tr>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Management</title>
    <link rel="stylesheet" href="AccountManagement.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Account Management</h1>
        </header>
        <div class="user-table">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Surname</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo $userRows; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
