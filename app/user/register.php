<?php
// debugger ENABLE ONLY IN DEVELOPEMENT
error_reporting(E_ALL); ini_set('display_errors', '1');

//require_once realpath(__DIR__ . '/vendor/autoload.php');

require_once('connect.php');

// Looing for .env at the root directory
//$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
//$dotenv->load();

// Checking if the inputs are filled
$fields = [
    'username',
    'password',
    'email',
    'firstname',
    'lastname'
];
foreach($fields as $field){
    if(!$_POST[$field]){
        echo 'missing field: ' . $field;
        return http_response_code(400);
    }
}

// Checking if the user exists
$sql = "SELECT count(*) FROM users WHERE user_name = ? AND user_email = ?";
$stmt = $mysqli->prepare($sql);

$stmt->bind_param("ss", $_POST['username'],  $_POST['email']);
$stmt->execute();

$lines = $stmt->affected_rows;
echo $lines;

if($lines > 0){
    echo 'User as alias already exists!';
    return http_response_code(409);
}

else{
    $stmt->close();
    $mysqli->next_result();

    $sql = "INSERT INTO users (
        user_id,
        user_name,
        user_pass,
        user_email,
        user_first_name,
        user_last_name,
        user_permission_level
    ) VALUES (?,?,?,?,?,?,?)";
    $stmt = $mysqli->prepare($sql);
    
    require("./assets/uuid.php");
    $id = gen_uuid();
    $default = "default";
    echo $id;
    $stmt->bind_param("sssssss", 
    $id,
    $_POST['username'],
    $_POST['password'],
    $_POST['email'],
    $_POST['firstname'],
    $_POST['lastname'],
    $default
    );
    $stmt->execute();

    $lines = $stmt->num_rows;


    echo $lines;

    if( mysqli_affected_rows($mysqli) <= 0){
        $stmt->close();
        $mysqli->next_result();
        echo 'there was some issue while registering!';
    }
    else{
        $stmt->close();
        $mysqli->next_result();

        echo 'Succesful register!';
        return http_response_code(200);
    }
}
