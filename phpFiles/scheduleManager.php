<?php
session_start();

$jsonFile = __DIR__ . '/../Data/PP_DB.json';
$usersFile ='../Data/users.json';

if (file_exists($jsonFile)) {
    $jsonData = json_decode(file_get_contents($jsonFile), true);
} else {
    $jsonData = [];
}

$usersData = file_get_contents($usersFile);
$user_dec = json_decode($usersData, true);

//droplist of users when adding them in shifts
$userOptionsHTML = '';
if (!empty($user_dec)) {
    foreach ($user_dec as $user) {
        $name = htmlspecialchars($user['name']." ".$user['surname']);
        $userOptionsHTML .= "<option value=\"$name\">$name</option>";
    }
}
//settigns for sepperatign manager options from regular staff
$userRole = '';

foreach ($user_dec as $user) {
  if (isset($_SESSION['usernm'])&& $user['username']===$_SESSION['usernm']) {
    $userRole = $user['role'] ?? '';
    break;
  }
}
//approve or deny requests hadnling
if (isset($_GET['deny']) || isset($_GET['approve'])) {
    $actionRequest = isset($_GET['deny']) ? $_GET['deny'] : $_GET['approve'];
    $newStatus = isset($_GET['deny']) ? false : true;

    if (isset($jsonData['schedule_req'])) {
        foreach ($jsonData['schedule_req'] as $username => &$dates) {
            foreach ($dates as $date => &$requests) {
                foreach ($requests as &$req) {
                    if ($req['request'] === $actionRequest) {
                        $req['status'] = $newStatus; // approve or deny
                    }
                }
            }
        }
    }

    // saving and updating the JSON
    file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT));

    // Redirecting again to clear the GET from the bar
    header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}
//with get we delete shifts for individual user
if (isset($_GET['deleteShift'], $_GET['day'], $_GET['name'])) {
    $day = $_GET['day'];
    $nameToDelete = $_GET['name'];

    if (isset($jsonData['schedule'][$day])) {
        $jsonData['schedule'][$day] = array_filter(
            $jsonData['schedule'][$day],
            fn($shift) => $shift['name'] !== $nameToDelete
        );
        $jsonData['schedule'][$day] = array_values($jsonData['schedule'][$day]);
        // Save the updated data
        file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT));
    }

    header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}
//again with get we read and update the shift for the user for start and end time 
if (isset($_GET['updateShift'], $_GET['day'], $_GET['name'], $_GET['start'], $_GET['end'])) {
    $day = $_GET['day'];
    $nameToUpdate = $_GET['name'];
    $newStart = $_GET['start'];
    $newEnd = $_GET['end'];

    if (isset($jsonData['schedule'][$day])) {
        foreach ($jsonData['schedule'][$day] as &$shift) {
            if ($shift['name'] === $nameToUpdate) {
                $shift['start'] = $newStart;
                $shift['end'] = $newEnd;
                break;
            }
        }
        // Save changes
        file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT));
    }

    header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}
//for addign shifts when pressing add button on shifts modal
if (isset($_GET['addShift'], $_GET['day'], $_GET['name'], $_GET['start'], $_GET['end'])) {
    $day = $_GET['day'];
    $name = $_GET['name'];
    $start = $_GET['start'];
    $end = $_GET['end'];

    $newShift = ['name' => $name, 'start' => $start, 'end' => $end];

    if (!isset($jsonData['schedule'][$day])) {
        $jsonData['schedule'][$day] = [];
    }

    $jsonData['schedule'][$day][] = $newShift;

    file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT));
    header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}
$scheduleRequests = $jsonData['schedule_req'] ?? [];
$scheduleData = $jsonData['schedule'] ?? [
    'Sunday' => [], 'Monday' => [], 'Tuesday' => [],
    'Wednesday' => [], 'Thursday' => [], 'Friday' => [], 'Saturday' => []
];

$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

if ($month < 1) { $month = 12; $year--; }
elseif ($month > 12) { $month = 1; $year++; }

$daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
$numberDays = date('t', $firstDayOfMonth);
$dayOfWeek = date('w', $firstDayOfMonth);
$monthName = date('F', $firstDayOfMonth);

$todayDate = new DateTime(); // Today full date
$startDate = clone $todayDate;
$endDate = (clone $startDate)->modify('+27 days'); // Only next 28 days window

$prevMonth = $month - 1; $prevYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
$nextMonth = $month + 1; $nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

// top bar settings
$loggedInUsername = $_SESSION['usernm'] ?? '';
$displayName = '';

foreach ($user_dec as $user) {
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
    <title><?php echo "$monthName $year Calendar"; ?></title>
    <link rel="stylesheet" href="../cssFiles/schedule.css">
    <link rel="stylesheet" href="../cssFiles/dash.css">
</head>
<body>

<div class="top-bar">
    <button class="toggle-btn" id="toggleSidebar">&#9776;</button>
    <div class="profile" id="profileBtn">
        <span class="profile-name">Welcome</span>
        <div class="dropdown" id="profileDropdown">
        <a href="../htmlFiles/login.html">Log Out</a>
        </div>
    </div>
</div>



<div class="main">
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
<div class="top-bar">
  <button class="toggle-btn" id="toggleSidebar">&#9776;</button>
  <div class="profile" id="profileBtn">
    <span class="profile-name"><?php echo $displayName; ?></span>
    <div class="dropdown" id="profileDropdown">
      <a href="../phpFiles/logout.php">Log Out</a>
    </div>
  </div>
</div>
<div class="calendar-container">
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
            $currentDay = 1;
            for ($i = 0; $i < $dayOfWeek; $i++) { echo "<td></td>"; }

            while ($currentDay <= $numberDays) {
                if ($dayOfWeek == 7) {
                    $dayOfWeek = 0;
                    echo "</tr><tr>";
                }

                $cellDate = new DateTime("$year-$month-$currentDay");
                $isToday = ($cellDate->format('Y-m-d') === $todayDate->format('Y-m-d'));
                $class = $isToday ? 'today' : '';

                $dayName = $daysOfWeek[$dayOfWeek % 7];
                $events = [];

                if ($cellDate >= $startDate && $cellDate <= $endDate) {
                    $events = $scheduleData[$dayName] ?? [];
                }

                $eventHtml = '';
                foreach ($events as $event) {
                    $eventHtml .= "<div class='event'>" . (is_array($event) ? $event['name'] : htmlspecialchars($event)) . "</div>";
                }

                $fullDate = $cellDate->format('Y-m-d');
                $hasShifts = count($events) > 0 ? 'has-shift' : '';

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

<div class="button-group">
    <button id="viewRequestsBtn" onclick="openModal()">View Requests</button>
    <button id="viewShiftsBtn" onclick="openShifts()">Shifts</button>
    </div>
</div>
<!-- calendar checking for shifts time and name -->
<div id="modal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-btn" onclick="closeShiftModal()">&times;</span>
        <h3 id="modal-title">Name: ---</h3> 
        <div id="modal-shift-details">
        </div>
    </div>
</div>
<!-- Popup Modal -->
<div id="requestsModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2>Requests List</h2>
        <div id="requestsList">
            <?php if (!empty($scheduleRequests)): ?>
                <?php $i = 0; ?>
                <?php foreach ($scheduleRequests as $username => $userRequests): ?>
                    <?php $requestId = 'user-requests-' . $i++; ?>
                    <div class="username" onclick="toggleRequests('<?php echo $requestId; ?>')">
                        <?php echo htmlspecialchars($username); ?> ▼
                    </div>
                    <div id="<?php echo $requestId; ?>" class="request-items" style="display: none;">
                        <?php if (is_array($userRequests)): ?>
                            <?php foreach ($userRequests as $date => $requestsOnDate): ?>
                            <?php foreach ($requestsOnDate as $req): ?>
                                <?php
                                    // Determine the status of the request and then highlight it with css
                                    $statusClass = '';
                                    if (isset($req['status'])) {
                                        if ($req['status'] === true) {
                                            $statusClass = 'approved';
                                        } elseif ($req['status'] === false) {
                                            $statusClass = 'denied';
                                        }
                                    }
                                ?>
                                <div class="request-item <?php echo $statusClass; ?>">
                                - <?php echo htmlspecialchars($req['request']); ?>
                                <?php if (!isset($req['status'])): ?>
                                    <button class="approve-btn" title="Approve" onclick="approveRequest(this)">✔️</button>
                                    <button class="deny-btn" title="Deny" onclick="denyRequest(this)">❌</button>
                                <?php elseif ($req['status'] === true): ?>
                                    <span class="status-text approved-text">✔️ Approved</span>
                                <?php elseif ($req['status'] === false): ?>
                                    <span class="status-text denied-text">❌ Denied</span>
                                <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        <?php else: ?>
                            <div class="request-item">
                                - <?php echo htmlspecialchars($userRequests); ?>
                                <button class="approve-btn" title="Approve" onclick="approveRequest(this)">✔️</button>
                                <button class="deny-btn" title="Deny" onclick="denyRequest(this)">❌</button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No requests available.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- Shifts Modal -->
<div id="shiftsModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeShifts()">&times;</span>
        <h2>Shift Schedule</h2>
        <div id="shiftsList">
    <!-- Shift days and shifts will be inserted dynamically -->
        </div>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('requestsModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('requestsModal').style.display = 'none';
}

function toggleRequests(id) {
    const box = document.getElementById(id);
    box.style.display = (box.style.display === 'none' || box.style.display === '') ? 'block' : 'none';
}

function denyRequest(button) {
    const requestItem = button.closest('.request-item');
    const requestText = requestItem.textContent.trim().split(' ')[1];
    if (requestText) {
        // get method which jumps to the php and executes the change in the json to false
        window.location.href = window.location.pathname + '?deny=' + encodeURIComponent(requestText);
    }
}

function approveRequest(button) {
    const requestItem = button.closest('.request-item');
    const requestText = requestItem.textContent.trim().split(' ')[1];

    if (requestText) {
        // get method which jumps to the php and executes the change in the json to true
        window.location.href = window.location.pathname + '?approve=' + encodeURIComponent(requestText);
    }
}

window.onclick = function(event) {
    const requestsModal = document.getElementById('requestsModal');
    const shiftsModal = document.getElementById('shiftsModal');

    if (event.target === requestsModal) {
        requestsModal.style.display = "none";
    }
    if (event.target === shiftsModal) {
        shiftsModal.style.display = "none";
    }
}
</script>
<!--for shifts-->
<script>
    const userDropdownHTML = `<select class="new-name"><?php echo $userOptionsHTML; ?></select>`;
    let counter=1;
  function openShifts() {
    document.getElementById('shiftsModal').style.display = 'block';
    loadShifts(); 
}

function closeShifts() {
    document.getElementById('shiftsModal').style.display = 'none';
}
function enableShiftEdit(button) {
    const container = button.closest('.shift-item');
    const inputs = container.querySelectorAll('.time-input');
    const currentlyDisabled = inputs[0].disabled;

    inputs.forEach(input => {
        input.disabled = !currentlyDisabled;
    });
}

function deleteShift(day, name) {
    if (confirm(`Are you sure you want to delete ${name}'s shift on ${day}?`)) {
        // Send a GET request to the server to trigger deletion
        const url = `${window.location.pathname}?deleteShift=1&day=${encodeURIComponent(day)}&name=${encodeURIComponent(name)}`;
        window.location.href = url;
    }
}
function saveShift(button) {
    const container = button.closest('.shift-item');
    const startInput = container.querySelector('input[data-type="start"]');
    const endInput = container.querySelector('input[data-type="end"]');
    const day = startInput.dataset.day;
    const name = startInput.dataset.name;
    const newStart = startInput.value;
    const newEnd = endInput.value;

    // Redirect with GET to update
    const url = `${window.location.pathname}?updateShift=1&day=${encodeURIComponent(day)}&name=${encodeURIComponent(name)}&start=${encodeURIComponent(newStart)}&end=${encodeURIComponent(newEnd)}`;
    window.location.href = url;
}
// Function to load shifts dynamically
function loadShifts() {
    const shiftsList = document.getElementById('shiftsList');
    shiftsList.innerHTML = ''; // Clear previous content

    const scheduleData = <?php echo json_encode($scheduleData); ?>; // Get shifts from PHP

    for (const day in scheduleData) {
        const dayId = day + "-shifts";
        let output = `
                    <div class="shift-day" onclick="toggleDay('${dayId}')">
                        <span>${day} ▼</span>
                    </div>`;
        output += `<div id="${dayId}" class="shift-items" style="display: none;">`;

        if (scheduleData[day] && scheduleData[day].length > 0) {
            scheduleData[day].forEach(shift => {
                output += `
                    <div class="shift-item">
                        <strong>${shift.name}</strong>
                        <button class="edit-btn" onclick="enableShiftEdit(this)">Edit</button>
                        <button class="delete-btn" onclick="deleteShift('${day}', '${shift.name}')">Delete</button><br>
                        Start: <input type="time" value="${shift.start}" class="time-input" data-day="${day}" data-name="${shift.name}" data-type="start" disabled>
                        End: <input type="time" value="${shift.end}" class="time-input" data-day="${day}" data-name="${shift.name}" data-type="end" disabled>
                        <button class="save-btn" onclick="saveShift(this)">Save</button>
                        </div>
                        `;
            });
        } else {
            output += `<div class="shift-item">No shifts available</div>`;
        }

        output += `</div>`;

        shiftsList.innerHTML += output;
    }
}

// Function to toggle days and add new shifts 
function toggleDay(id) {
    // Remove any existing add buttons and forms
    document.querySelectorAll('.Add').forEach(btn => btn.remove());
    document.querySelectorAll('.add-shift-form').forEach(form => form.remove());

    const box = document.getElementById(id);
    box.style.display = (box.style.display === 'none' || box.style.display === '') ? 'block' : 'none';

    const dayTitle = document.querySelector(`.shift-day[onclick="toggleDay('${id}')"]`);

    if (box.style.display === 'block') {
        const btn = document.createElement('button');
        btn.textContent = '+';
        btn.className = 'Add';
        btn.title = 'Add new shift';

        btn.onclick = function (e) {
            e.stopPropagation(); // Don't collapse the day

            const formDiv = document.createElement('div');
            formDiv.className = 'add-shift-form';
            formDiv.innerHTML = `
            ${userDropdownHTML}
            <input type="time" class="new-start">
            <input type="time" class="new-end">
            <button onclick="submitNewShift('${id}', this)">Save</button>
        `;

            box.prepend(formDiv);
        };

        dayTitle.appendChild(btn);
    }
}

function submitNewShift(dayId, button) {
    const form = button.closest('.add-shift-form');
    const name = form.querySelector('.new-name').value.trim();
    const start = form.querySelector('.new-start').value;
    const end = form.querySelector('.new-end').value;
    const day = dayId.replace('-shifts', '');

    if (!name || !start || !end) {
        alert('Please fill all fields.');
        return;
    }

    const url = `${window.location.pathname}?addShift=1&day=${encodeURIComponent(day)}&name=${encodeURIComponent(name)}&start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}`;
    window.location.href = url;
}

</script>
<!--for checking cells for shifts in calendar-->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cells = document.querySelectorAll('.calendar-cell.has-shift');
    const modal = document.getElementById('modal');
    const scheduleData = <?php echo json_encode($scheduleData); ?>;

    cells.forEach(cell => {
        cell.addEventListener('click', function () {
            const date = this.getAttribute('data-date');
            modal.style.display = 'block';

            const shiftDetailsDiv = document.getElementById('modal-shift-details');
            const modalTitle = document.getElementById('modal-title');

            shiftDetailsDiv.innerHTML = ''; // Clear previous
            modalTitle.textContent = '';    // Reset title

            const clickedDate = new Date(date);
            const clickedDayName = clickedDate.toLocaleDateString('en-US', { weekday: 'long' });

            if (scheduleData[clickedDayName] && scheduleData[clickedDayName].length > 0) {
                const shift = scheduleData[clickedDayName][0]; // Only show first shift
                modalTitle.textContent = `Name: ${shift.name}`;
                shiftDetailsDiv.innerHTML = `
                    <p><strong>Shift Start:</strong> ${shift.start}</p>
                    <p><strong>Shift End:</strong> ${shift.end}</p>
                `;
            } else {
                modalTitle.textContent = "No shift";
                shiftDetailsDiv.innerHTML = '<p>No shifts for this day.</p>';
            }
        });
    });
});

function closeShiftModal() {
    document.getElementById('modal').style.display = 'none';
}

</script>
<script src="../htmlfiles/dash.js"></script>
</div>
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
