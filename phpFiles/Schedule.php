<?php
session_start(); // cookies passing

// File paths
$jsonFile = __DIR__ . '/../Data/PP_DB.json';
$usersFile = __DIR__ . '/../Data/users.json';

// Create files if missing
if (!file_exists($jsonFile)) {
    file_put_contents($jsonFile, json_encode(new stdClass(), JSON_PRETTY_PRINT));
}
if (!file_exists($usersFile)) {
    file_put_contents($usersFile, json_encode([]));
}

// Load data
$jsonData = json_decode(file_get_contents($jsonFile), true);
$usersData = json_decode(file_get_contents($usersFile), true);

// Safeguards
if (!is_array($jsonData)) $jsonData = [];
if (!isset($jsonData['schedule_req'])) $jsonData['schedule_req'] = [];
if (!isset($jsonData['schedule'])) {
    $jsonData['schedule'] = [
        'Sunday' => [], 'Monday' => [], 'Tuesday' => [],
        'Wednesday' => [], 'Thursday' => [], 'Friday' => [], 'Saturday' => []
    ];
}

// Handle request form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_text'])) {
    $requestText = trim($_POST['request_text']);
    if (!empty($requestText)) {
        if (isset($_SESSION['usernm'])) {
            $loggedInUsername = $_SESSION['usernm'];

            // Find user in users.json
            $foundUser = null;
            foreach ($usersData as $user) {
                if (isset($user['username']) && $user['username'] === $loggedInUsername) {
                    $foundUser = $user;
                    break;
                }
            }

            if ($foundUser !== null) {
                // Insert under username key
                if (!isset($jsonData['schedule_req'][$loggedInUsername])) {
                    $jsonData['schedule_req'][$loggedInUsername] = [];
                }
                $jsonData['schedule_req'][$loggedInUsername][] = $requestText;

                // Save
                file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT));
                $successMessage = "✅ Request saved successfully!";
            } else {
                $errorMessage = "⚠️ User not found.";
            }
        } else {
            $errorMessage = "⚠️ No user logged in.";
        }
    } else {
        $errorMessage = "⚠️ Request cannot be empty.";
    }
}

// Always save updated structure
file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT));

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

$thisToday = new DateTime(); // today's full date
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo "$monthName $year Calendar"; ?></title>
    <link rel="stylesheet" href="../cssFiles/schedule.css">
</head>
<body>

<?php if (!empty($successMessage)) { echo "<p style='color: green;'>$successMessage</p>"; } ?>
<?php if (!empty($errorMessage)) { echo "<p style='color: red;'>$errorMessage</p>"; } ?>

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
            // Date range: today to today + 27 days
            $startDate = new DateTime();
            $endDate = (clone $startDate)->modify('+27 days');

            $currentDay = 1;
            $dayOfWeek = date('w', $firstDayOfMonth);

            for ($i = 0; $i < $dayOfWeek; $i++) {
                echo "<td></td>";
            }

            while ($currentDay <= $numberDays) {
                if ($dayOfWeek == 7) {
                    $dayOfWeek = 0;
                    echo "</tr><tr>";
                }

                $cellDate = new DateTime("$year-$month-$currentDay");
                $isToday = ($cellDate->format('Y-m-d') == $thisToday->format('Y-m-d'));
                $class = $isToday ? 'today' : '';

                $dayName = $daysOfWeek[$dayOfWeek];
                $events = [];

                if ($cellDate >= $startDate && $cellDate <= $endDate) {
                    $events = $jsonData['schedule'][$dayName] ?? [];
                }

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

    <button id="submitRequestBtn" onclick="showRequestBox()">Submit Request</button>

    <form id="requestForm" method="POST" style="display: none; margin-top: 20px;">
        <h3>Please write your request:</h3>
        <textarea name="request_text" rows="4" cols="50" required></textarea><br>
        <button type="submit">Send Request</button>
    </form>

    <script>
    function showRequestBox() {
        document.getElementById('requestForm').style.display = 'block';
    }
    </script>

</div>

<!-- Modal -->
<div id="modal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h3><strong>Name:</strong> <span id="modal-name">---</span></h3>
        <p><strong>Shift Start:</strong> <span id="modal-start">--:--</span></p>
        <p><strong>Shift End:</strong> <span id="modal-end">--:--</span></p>
    </div>
</div>

<script>
const scheduleData = <?php echo json_encode($jsonData['schedule']); ?>;

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

</body>
</html>
