<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
 
    $username = htmlspecialchars($_POST["username"]);
    $password = $_POST["password"]; 
    $name = htmlspecialchars($_POST["name"]);
    $surname = htmlspecialchars($_POST["surname"]);
    $birthdate = $_POST["birthdate"];
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars($_POST["phone"]);

    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response = [
            "status" => "error",
            "message" => "Invalid email format."
        ];
        echo json_encode($response);
        exit;
    }

    // Hash the password (for the sake of this transformation, we don't store the password in the response)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare user data to be returned as JSON (without inserting to database)
    $user_data = [
        "username" => $username,
        "name" => $name,
        "surname" => $surname,
        "birthdate" => $birthdate,
        "email" => $email,
        "phone" => $phone,
        "password" => $hashed_password,  // Usually you don't send passwords in response, this is just for demonstration
        "Role" => NULL,
        "OTP" => NULL
    ];
   //Establish connection to the json file
    $json_file = '../Data/users.json';
    $json_data = file_get_contents($json_file);
    $dec_data = json_decode($json_data, true);

    // Check for duplicate email, phone, or username
    foreach ($dec_data as $user) {
        if ($user['email'] === $email) {
            $response = [
                "status" => "error",
                "message" => "Email already exists."
            ];
            echo json_encode($response);
            exit;
        }

        if ($user['phone'] === $phone) {
            $response = [
                "status" => "error",
                "message" => "Phone number already exists."
            ];
            echo json_encode($response);
            exit;
        }

        if ($user['username'] === $username) {
            $response = [
                "status" => "error",
                "message" => "Username already exists."
            ];
            echo json_encode($response);
            exit;
        }
    }

   //assign the data to json
    $dec_data[] = $user_data;
    $enc_data = json_encode($dec_data, JSON_PRETTY_PRINT);

    if (file_put_contents($json_file, $enc_data)) {
        $response = [
            "status" => "success",
            "message" => "User data added to the JSON file successfully."
        ];
        header("Location: ../htmlFiles/login.html"); //Redirect to login page
    } else {
        $response = [
            "status" => "error",
            "message" => "Failed to write to the JSON file."
        ];
    }

    // Return the response as JSON
    echo json_encode($response);
}
?>
