<?php
session_start();//cookies passing
// File path to your JSON database
$jsonFile = __DIR__ . '/../Data/PP_DB.json';

// If the file doesn't exist yet, create it with empty object
if (!file_exists($jsonFile)) {
    file_put_contents($jsonFile, json_encode(new stdClass(), JSON_PRETTY_PRINT));
}

// Load the JSON data
$jsonData = json_decode(file_get_contents($jsonFile), true);

// Make sure it's an array, not null or something broken
if (!is_array($jsonData)) {
    $jsonData = [];
}

// If "schedule" key doesn't exist, create it
if (!isset($jsonData['schedule'])) {
    $jsonData['schedule'] = [
        'Sunday'    => [],
        'Monday'    => [],
        'Tuesday'   => [],
        'Wednesday' => [],
        'Thursday'  => [],
        'Friday'    => [],
        'Saturday'  => []
    ];
}

    // If "schedule_req" key doesn't exist, create it
if (!isset($jsonData['schedule_req'])) {
    $jsonData['schedule_req'] = [];
}

    // Save the updated JSON back to the file
    file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT));

// Get current or selected month/year
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// preventing overflow  of months
if ($month < 1) {
    $month = 12;
    $year--;
} elseif ($month > 12) {
    $month = 1;
    $year++;
}

$daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year); // timestamp for the first day of this month  
$numberDays = date('t', $firstDayOfMonth); // how many dates are in this month
$dayOfWeek = date('w', $firstDayOfMonth); // day of the week in numbers from 0-6 0=sunday, 1=monday...
$monthName = date('F', $firstDayOfMonth); // gives the full name of the month
$currentDay = 1;

// For highlighting today's date
$today = date('j');
$thisMonth = date('n');
$thisYear = date('Y');

// Previous and next month
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
                for ($i = 0; $i < $dayOfWeek; $i++) {
                    echo "<td></td>";
                }

                while ($currentDay <= $numberDays) {
                    if ($dayOfWeek == 7) {
                        $dayOfWeek = 0;
                        echo "</tr><tr>";
                    }

                    $isToday = ($currentDay == $today && $month == $thisMonth && $year == $thisYear);
                    $class = $isToday ? 'today' : '';

                    // Get the day of the week for this date
                   $dayName = $daysOfWeek[$dayOfWeek % 7]; // ensure 0-6 range

                  // Check if there are items for this day in the schedule
                  $events = isset($jsonData['schedule'][$dayName]) ? $jsonData['schedule'][$dayName] : [];

                  // Convert event strings to HTML list items
                  $eventHtml = '';
                    foreach ($events as $event) {
                        if (is_array($event)) {
                            $eventHtml .= "<div class='event'>{$event['name']}</div>";
                        } else {
                            $eventHtml .= "<div class='event'>$event</div>"; // fallback for simple strings
                        }
                    }

                  // Format the full date for use in JS (YYYY-MM-DD)
                  $fullDate = sprintf('%04d-%02d-%02d', $year, $month, $currentDay);

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
        <button id="request-button">Request</button>
    </div>
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
    </script>
    <script>
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
