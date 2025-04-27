<?php
session_start();

$jsonFile = __DIR__ . '/../Data/PP_DB.json';
if (file_exists($jsonFile)) {
    $jsonData = json_decode(file_get_contents($jsonFile), true);
} else {
    $jsonData = [];
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo "$monthName $year Calendar"; ?></title>
    <link rel="stylesheet" href="../cssFiles/schedule.css">
    <link rel="stylesheet" href="../cssFiles/requests.css"> <!-- External CSS for requests -->
</head>
<body>

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

    <button id="viewRequestsBtn" onclick="openModal()">View Requests</button>
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
                            <?php foreach ($userRequests as $request): ?>
                                <div class="request-item">
                                    - <?php echo htmlspecialchars($request); ?>
                                    <button class="approve-btn" title="Approve">✔️</button>
                                    <button class="deny-btn" title="Deny" onclick="denyRequest(this)">❌</button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="request-item">
                                - <?php echo htmlspecialchars($userRequests); ?>
                                <button class="approve-btn" title="Approve">✔️</button>
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
    if (requestItem) {
        requestItem.remove();
    }
}

window.onclick = function(event) {
    const modal = document.getElementById('requestsModal');
    if (event.target === modal) {
        modal.style.display = "none";
    }
}
</script>

</body>
</html>
