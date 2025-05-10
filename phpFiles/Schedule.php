<?php
session_start(); // cookies passing

/*$_SESSION['usernm']="banana"; THIS IS FOR DEBUGGING THE FEEDBACK MESSAGES*/

$success = ''; //feedback messages
$error = ''; //feedback messages

// File paths
$jsonFile = __DIR__ . '/../Data/PP_DB.json';
$usersFile = __DIR__ . '/../Data/users.json';

// Load data properly
$jsonData = file_get_contents($jsonFile);
$usersData = file_get_contents($usersFile);
$json_dec = json_decode($jsonData, true);
$user_dec = json_decode($usersData, true);

//settigns for sepperatign manager options from regular staff
$userRole = '';

foreach ($user_dec as $user) {
  if (isset($_SESSION['usernm'])&& $user['username']===$_SESSION['usernm']) {
    $userRole = $user['role'] ?? '';
    break;
  }
}

// Safeguards
if (!is_array($json_dec)) $json_dec = [];
if (!isset($json_dec['schedule_req'])) $json_dec['schedule_req'] = [];
if (!isset($json_dec['schedule'])) {
    $json_dec['schedule'] = [
        'Sunday' => [], 'Monday' => [], 'Tuesday' => [],
        'Wednesday' => [], 'Thursday' => [], 'Friday' => [], 'Saturday' => []
    ];
}

//settings for top bar to work and read your name 
$loggedInUsername = $_SESSION['usernm'] ?? ''; // fallback if not logged in
$displayName = '';

foreach ($user_dec as $user) {
  if ($user['username'] === $loggedInUsername) {
    // You can show full name or just username
    $displayName = htmlspecialchars($user['name'] . ' ' . $user['surname']);
    break;
  }
}


// Handle request saving
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usernm'])) {
    $requestString = $_POST['request_text'];
    if (strlen($requestString) > 300) {
        $error = "Your request is too long. Please limit it to 300 characters."; //word limit for request sumbition
    } else {
            $currentDate = date('Y-m-d');
            $name=null;
            $found=false;
            if (isset($_SESSION['usernm'])) {
                foreach ($user_dec as $user) {
                    if ($_SESSION['usernm']===$user['username']) {
                        $name= $user['name'] . " " . $user['surname'];
                        $found=true;
                        break;
                    }
                }
                if (!isset($json_dec['schedule_req'][$name]) && $found===true) {
                    $json_dec['schedule_req'][$name] = [];
                }
                if (!isset($json_dec['schedule_req'][$name][$currentDate]) && $found===true) {
                    $json_dec['schedule_req'][$name][$currentDate] = [];
                }
                if($found===true){
                $json_dec['schedule_req'][$name][$currentDate][] = [
                    'request' => $requestString,
                    'status' => null
                ];
                $success = "Request saved successfully!";
            }
            else if($found===false)
            {
                $error="User not found. Weird... how did you get on this site.";
            }
            }
            else if (!isset($_SESSION['usernm']))
            {
                $error="User not logged in. Please log in";
            }
        }
}
// save updated structure
$json_encoded = json_encode($json_dec, JSON_PRETTY_PRINT);
file_put_contents($jsonFile, $json_encoded);

// Calendar setup
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

if ($month < 1) {
    $month = 12;
    $year--;
} elseif ($month > 12) {
    $month = 1;
    $year++;
}

$daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
$numberDays = date('t', $firstDayOfMonth);
$monthName = date('F', $firstDayOfMonth);

$thisToday = new DateTime();
$thisMonth = (int)date('n');
$thisYear = (int)date('Y');

$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

$fullName = '';
$isCookie=null;
$exist=null;
    if (isset($_SESSION['usernm'])) {
        $isCookie=true;
        $exist=null;
        foreach ($user_dec as $user) {
            if ($_SESSION['usernm'] === $user['username']) {
                $fullName = $user['name'] . " " . $user['surname'];
                $exist=true;
                break;
            }
            else {
                $exist=FALSE;
            }
        }
    }
    else if (!isset($_SESSION['usernm'])){
        $isCookie=false;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo "$monthName $year Calendar"; ?></title>
    <link rel="stylesheet" href="../cssFiles/schedule.css">
    <link rel="stylesheet" href="../cssFiles/dash.css">
</head>
<body>
    <div class="main">

        <?php if (!empty($success)) { echo "<p style='color: green;'>$success</p>"; } ?>
        <?php if (!empty($error)) { echo "<p style='color: red;'>$error</p>"; } ?>
        <!-- Top Bar -->
        <div class="top-bar">
            <button class="toggle-btn" id="toggleSidebar">&#9776;</button>
            <div class="profile" id="profileBtn">
                <span class="profile-name"><?php echo $displayName; ?></span>
                <div class="dropdown" id="profileDropdown">
                <a href="../htmlFiles/login.html">Log Out</a>
                </div>
            </div>
            </div>

        <div class="layout-wrapper">
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

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Calendar Container -->
            <div class="calendar-container" style="margin: 30px; max-width: 1000px;">
            <div class="calendar-nav">
                <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>">&lt; Prev</a>
                <h2><?php echo "$monthName $year"; ?></h2>
                <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>">Next &gt;</a>
            </div>

            <table class="calendar">
                <tr>
                <?php foreach ($daysOfWeek as $day): ?>
                    <th><?php echo $day; ?></th>
                <?php endforeach; ?>
                </tr>
                <tr>
                <?php
                $startDate = new DateTime();
                $endDate = (clone $startDate)->modify('+30 days');
                $currentDay = 1;
                $dayOfWeek = date('w', $firstDayOfMonth);

                for ($i = 0; $i < $dayOfWeek; $i++) echo "<td></td>";

                while ($currentDay <= $numberDays) {
                    if ($dayOfWeek == 7) {
                    $dayOfWeek = 0;
                    echo "</tr><tr>";
                    }

                    $cellDate = new DateTime("$year-$month-$currentDay");
                    $isToday = ($cellDate->format('Y-m-d') == $thisToday->format('Y-m-d'));
                    $class = $isToday ? 'today' : '';
                    $dayName = $daysOfWeek[$dayOfWeek];
                    $events = $cellDate >= $startDate && $cellDate <= $endDate ? ($json_dec['schedule'][$dayName] ?? []) : [];

                    $eventHtml = '';
                    foreach ($events as $event) {
                    $eventHtml .= "<div class='event'>{$event['name']}</div>";
                    }

                    $hasShifts = count($events) > 0 ? 'has-shift' : '';
                    $fullDate = $cellDate->format('Y-m-d');

                    echo "<td class='$class calendar-cell $hasShifts' data-date='$fullDate'>
                            <div class='day-number'>$currentDay</div>
                            $eventHtml
                        </td>";

                    $currentDay++;
                    $dayOfWeek++;
                }

                while ($dayOfWeek < 7) {
                    echo "<td></td>";
                    $dayOfWeek++;
                }
                ?>
                </tr>
            </table>

            <div class="button-group" style="margin-top: 20px;">
                <button id="submitRequestBtn" type="button" onclick="showRequestBox()">Submit Request</button>
                <button id="viewRequestsBtn" type="button">View Requests</button>
            </div>
        </div>
    </div>    
</div>

<script>
function showRequestBox() {
    document.getElementById('requestForm').style.display = 'block';
}
</script>
<!--script for modal shift-->
<script>
const scheduleData = <?php echo json_encode($json_dec['schedule']); ?>;
document.addEventListener('DOMContentLoaded', function () {
    const cells = document.querySelectorAll('.calendar-cell.has-shift');
    const modal = document.getElementById('modal');
    const closeBtn = document.querySelector('.close-btn');

    cells.forEach(cell => {
        cell.addEventListener('click', function () {
            const date = new Date(this.getAttribute('data-date'));
            const dayName = date.toLocaleDateString('en-US', { weekday: 'long' });

            const shifts = scheduleData[dayName];

            if (shifts && shifts.length > 0) {
                const shift = shifts[0];

                document.getElementById('modal-name').innerText = shift.name;
                document.getElementById('modal-start').innerText = shift.start;
                document.getElementById('modal-end').innerText = shift.end;

                modal.style.display = "block";
            }
        });
    });

    closeBtn.addEventListener('click', () => {
        modal.style.display = "none";
    });

    window.addEventListener('click', (e) => {
        if (e.target == modal) {
            modal.style.display = "none";
        }
    });
});
</script>
<!--script for modal request-->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const requestModal = document.getElementById('requestModal');
    const viewRequestsModal = document.getElementById('viewRequestsModal');

    // Open request modal when button clicked
    document.getElementById('submitRequestBtn').addEventListener('click', function () {
        requestModal.style.display = 'block';
    });

    // Close request modal when cancel button clicked
    document.getElementById('cancelRequestBtn').addEventListener('click', function () {
        requestModal.style.display = 'none';
    });

    //Button to open View Requests Modal
    document.getElementById('viewRequestsBtn').addEventListener('click', function () {
        viewRequestsModal.style.display = 'block';
        loadRequests(); // loads all previous requests when opening
    });
    //Button to close View Requests Modal
    document.getElementById('closeViewRequestsBtn').addEventListener('click', function () {
        viewRequestsModal.style.display = 'none';
    });
    // Also close modal if clicking outside modal content
    window.addEventListener('click', function(event) {
        if (event.target == requestModal) {
            requestModal.style.display = "none";
        }
        if (event.target == viewRequestsModal) {
            viewRequestsModal.style.display = "none";
        }
    });
});

//Function to load previous requests from PHP into the modal
function loadRequests() {
    const requestsList = document.getElementById('requestsList');
    requestsList.innerHTML = '';
    const scheduleRequests = <?php echo json_encode($json_dec['schedule_req'] ?? []); ?>;
    const username = "<?php echo isset($_SESSION['usernm']) ? $_SESSION['usernm'] : ''; ?>";
    const fullName = "<?php echo $fullName; ?>";
    const exist = <?php echo $exist ? 'true' : 'false'; ?>;
    const isCookie = <?php echo $isCookie ? 'true' : 'false'; ?>;

    if (fullName && scheduleRequests[fullName]) {
    let output = '';
    for (const date in scheduleRequests[fullName]) {
        output += `<div class="request-card">`;
        output += `<div class="request-date">${date}</div>`;
        scheduleRequests[fullName][date].forEach(req => {
        const statusText = req.status === null ? "Undecided" : (req.status === true ? "Approved" : "Denied");
        let badgeClass = "undecided-badge"; 

        if (req.status === true) {
            badgeClass = "approved-badge";
        } else if (req.status === false) {
            badgeClass = "denied-badge";
        }

        output += `- ${req.request} <span class="status-badge ${badgeClass}">${statusText}</span><br>`;
    });
        output += `</div>`;
    }
    requestsList.innerHTML = output;
    }
    if (!exist) {
    alert("Error: user doesn't exist in the database!");
    } 
    if (!isCookie)
    {
        alert("Error: Can't view requests. User hasn't logged in properly!");
    }
    else if (!fullName || !scheduleRequests[fullName]) {
        requestsList.innerHTML = "<p>No requests found for your account.</p>";
    }
}
</script>
<!--Word limit js to notify instantly the user-->
<script>
        const textarea = document.getElementById('request_textarea');
        const charCount = document.getElementById('charCount');
        const maxLength = 300;

        textarea.addEventListener('input', function() {
            const currentLength = textarea.value.length;
            charCount.textContent = (maxLength - currentLength) + " characters remaining";

            if (currentLength >= maxLength) {
                alert("You have reached the maximum allowed characters (300)!");
            }
        });
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
<!-- Submit Request Modal -->
<div id="requestModal" class="modal" style="display:none;">
  <div class="modal-content">
    <h3>Submit Request</h3>
    <form id="requestForm" method="post" action="">
      <textarea id="request_textarea" name="request_text" maxlength="300" placeholder="Enter your request..." required></textarea>
      <p id="charCount">300 characters remaining</p>
      <div class="button-group">
        <button type="submit">Submit</button>
        <button type="button" id="cancelRequestBtn">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- View Requests Modal -->
<div id="viewRequestsModal" class="modal" style="display:none;">
  <div class="modal-content">
    <h3>Your Requests</h3>
    <div id="requestsList"></div>
    <div class="button-group">
      <button type="button" id="closeViewRequestsBtn">Close</button>
    </div>
  </div>
</div>
</body>
</html>
