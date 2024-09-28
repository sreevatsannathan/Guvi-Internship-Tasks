<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require '../vendor/autoload.php';

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "user_management";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Connection to MySQL failed: " . $e->getMessage()]);
    exit();
}

try {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $collection = $client->user_management->profiles;
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Connection to MongoDB failed: " . $e->getMessage()]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['registerUsername'];
    $password = password_hash($_POST['registerPassword'], PASSWORD_BCRYPT);
    $email = $_POST['registerEmail'];

    $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
    $mysqlSuccess = $stmt->execute([$username, $password, $email]);

    $user_id = $conn->lastInsertId();

    try {
        $result = $collection->insertOne([
            'user_id' => $user_id,
            'name' => $username,
            'email' => $email
        ]);
        $mongoSuccess = true;
    } catch (Exception $e) {
        $mongoSuccess = false;
        $mongoError = $e->getMessage();
    }

    if ($mysqlSuccess && $mongoSuccess) {
        echo json_encode(["status" => "success", "message" => "Registration successful"]);
    } else {
        $errorMessage = "Registration failed";
        if (!$mysqlSuccess) {
            $errorMessage .= " (MySQL error)";
        }
        if (!$mongoSuccess) {
            $errorMessage .= " (MongoDB error: $mongoError)";
        }
        echo json_encode(["status" => "error", "message" => $errorMessage]);
    }
}