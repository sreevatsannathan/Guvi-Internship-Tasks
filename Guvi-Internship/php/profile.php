<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Headers: ngrok-skip-browser-warning, Content-Type");

require '../vendor/autoload.php';

try {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $collection = $client->user_management->profiles;
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Failed to connect to MongoDB: " . $e->getMessage()]);
    exit();
}

try {
    $redis = new Predis\Client([
        'scheme' => 'tcp',
        'host' => '127.0.0.1',
        'port' => 6379,
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Failed to connect to Redis: " . $e->getMessage()]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $email = $_GET['email'];

    try {
        $profile = $collection->findOne(['email' => $email]);

        if ($profile) {
            echo json_encode([
                "status" => "success",
                "profile" => [
                    "name" => $profile['name'] ?? '',
                    "phone" => $profile['phone'] ?? '',
                    "email" => $profile['email'] ?? '',
                    "dob" => $profile['dob'] ?? '',
                    "age" => $profile['age'] ?? '',
                    "address" => $profile['address'] ?? '',
                    "country" => $profile['country'] ?? '',
                    "state" => $profile['state'] ?? ''
                ]
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Profile not found"]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Failed to fetch profile: " . $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $user_id = $redis->get("session:$token");

    if ($user_id) {
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $dob = $_POST['dob'];
        $age = $_POST['age'];
        $address = $_POST['address'];
        $country = $_POST['country'];
        $state = $_POST['state'];

        try {
            $result = $collection->updateOne(
                ['user_id' => $user_id],
                [
                    '$set' => [
                        'name' => $name,
                        'phone' => $phone,
                        'email' => $email,
                        'dob' => $dob,
                        'age' => $age,
                        'address' => $address,
                        'country' => $country,
                        'state' => $state
                    ]
                ],
                ['upsert' => true]
            );
            echo json_encode(["status" => "success", "message" => "Profile updated"]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Failed to update profile: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid session"]);
    }
}
?>