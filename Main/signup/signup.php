<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars($_POST["username"]);
    $password = $_POST["password"]; // No need to sanitize, it's hashed later
    $name = htmlspecialchars($_POST["name"]);
    $surname = htmlspecialchars($_POST["surname"]);
    $birthdate = $_POST["birthdate"];
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars($_POST["phone"]);

    // Basic validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Database connection
    $conn = new mysqli("localhost", "root", "", "users_db");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Insert user into database
    $stmt = $conn->prepare("INSERT INTO users (username, password, name, surname, birthdate, email, phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $username, $hashed_password, $name, $surname, $birthdate, $email, $phone);

    if ($stmt->execute()) {
        echo "<script>alert('Sign up successful!'); window.location.href='signup.html';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close database connection
    $stmt->close();
    $conn->close();
}
?>
