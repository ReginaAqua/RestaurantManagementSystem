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
    //creating a unique id for each user signing in
    $randomNumber = mt_rand(1000000, 9999999);
    // Adding a prefix "PE" to differentiate
    $user_id = 'PE' . $randomNumber;
    //Establish connection to the json file
    $json_file = '../Data/users.json';
    $json_data = file_get_contents($json_file);
    $dec_data = json_decode($json_data, true);
    //making sure the id made is unique. if its not it will keep making new ones until its unique.
    $existing_ids = array_column($dec_data, 'user_id');
    do {
       $randomNumber = mt_rand(1000000, 9999999);
       $user_id = 'PE' . $randomNumber;
    } while (in_array($user_id, $existing_ids));


    // Prepare user data to be returned as JSON (without inserting to database)
    $user_data = [
        "username" => $username,
        "user_id" => $user_id,
        "name" => $name,
        "surname" => $surname,
        "birthdate" => $birthdate,
        "email" => $email,
        "phone" => $phone,
        "password" => $hashed_password, 
        "role" => "customer",
        "OTP" => NULL
    ];

    // Check for duplicate email, phone, or username
    foreach ($dec_data as $user) {
        if ($user['email'] === $email) {
            $response = [
                "status" => "error",
                "message" => "Email already exists."
            ];
            echo json_encode($response);
            exit();
        }

        if ($user['phone'] === $phone) {
            $response = [
                "status" => "error",
                "message" => "Phone number already exists."
            ];
            echo json_encode($response);
            exit();
        }

        if ($user['username'] === $username) {
            $response = [
                "status" => "error",
                "message" => "Username already exists."
            ];
            echo json_encode($response);
            exit();
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
}
?>
