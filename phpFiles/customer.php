<?php
session_start();

if (!isset($_SESSION['usernm'])) {
  // Redirect if not logged in
  header("Location: ../htmlfiles/login.html");
  exit();
}

$username = $_SESSION['usernm'];
//successful message after receiving the success status at the end of the make_resarvations.php
$successMessage = '';
if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $successMessage = "Your reservation was successfully made and a confirmation has been sent to your email!";
}
//failed message if user didnt input a date.
$errorMessage = '';
if (isset($_GET['status']) && $_GET['status'] === 'null_date_error') {
    $errorMessage = "ERROR. Please input a date!";
}
//and a failed message if there are no available tables for the time and date given by the user
$failedMessage = '';
if (isset($_GET['reservation']) && $_GET['reservation'] === 'no_available_tables_for_that_time') {
    $failedMessage = "Unfortunately, all tables are fully booked for the time you selected. Please try a different time or date. We'd love to accommodate you!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Pizza Pellini's - Customer Dashboard</title>
  <link rel="stylesheet" href="../cssFiles/customer.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
</head>
<body>

  <!-- Header Section -->
  <section class="header">
    <nav>
      <a href="#"><img src="../foto/Company_Logo.png" alt="Company Logo" /></a>
      <div class="user-menu">
        <span class="username">
          <?php echo htmlspecialchars($username); ?> <i class="fa fa-caret-down"></i>
        </span>
        <div class="dropdown-content">
          <a href="../phpFiles/reservations.php">See Reservations</a>
          <a href="../phpFiles/account_settings.php">Account Settings</a>
          <a href="../phpFiles/logout.php">Log Out</a>
        </div>
      </div>
    </nav>
    <div class="text-box">
      <h1>Dragon's Pizzeria</h1>
      <p>Where Tradition Meets Flavor</p>
      <a href="../foto/fast-food-restaurant-menu.avif" class="hero-btn">Menu</a>
    </div>
    <div class="pizza">
      <img src="../foto/pizzanum1.jpg" alt="Pizza" />
    </div>
  </section>

  <!-- Reservation Form Section -->
  <section class="reservation">
    <h2>Make a Reservation</h2>
    <?php if (!empty($successMessage)): ?>
    <div class="reservation-success">
    <?php echo $successMessage; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($errorMessage)): ?>
    <div class="reservation-error">
    <?php echo $errorMessage; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($failedMessage)): ?>
    <div class="reservation-failed">
    <?php echo $failedMessage; ?>
    </div>
    <?php endif; ?>

    <form action="make_reservation.php" method="post">
      <!-- Hidden username field populated by PHP -->
      <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>" />

      <label for="num_people">Number of People:</label>
      <select id="num_people" name="num_people">
        <?php
        for ($i = 1; $i <= 15; $i++) {
          echo "<option value=\"$i\">$i</option>";
        }
        ?>
      </select>

      <label for="reservation_time">Time:</label>
      <select id="reservation_time" name="reservation_time">
        <option value="10:30">10:30</option>
        <option value="11:00">11:00</option>
        <option value="11:30">11:30</option>
        <option value="12:00">12:00</option>
        <option value="12:30">12:30</option>
        <option value="13:00">13:00</option>
        <option value="13:30">13:30</option>
        <option value="14:00">14:00</option>
      </select>

      <label for="reservation_date">Date:</label>
      <input type="date" id="reservation_date" name="reservation_date" />

      <button type="submit">Book Table</button>
    </form>
  </section>

  <!-- Dropdown JS -->
  <script>
    document.querySelector('.username').addEventListener('click', function () {
      document.querySelector('.dropdown-content').classList.toggle('show');
    });

    window.onclick = function (event) {
      if (!event.target.matches('.username')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        for (var i = 0; i < dropdowns.length; i++) {
          var openDropdown = dropdowns[i];
          if (openDropdown.classList.contains('show')) {
            openDropdown.classList.remove('show');
          }
        }
      }
    };
  </script>
</body>
</html>