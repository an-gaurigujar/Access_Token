<?php
include 'db.php';
header("Content-Type: application/json");

$config = new Config();
$conn = $config->conn;

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"),true);
    $email = $data->email;
    $password = $data->password;
} elseif ($method == 'GET') {
    $email = $_GET['email'] ?? '';
    $password = $_GET['password'] ?? '';
} else {
    http_response_code(405); 
    echo json_encode(["message" => "Method not allowed"]);
    exit();
}

if (!$email || !$password) {
    echo json_encode(["message" => "Email and password are required"]);
    exit();
}

$stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && $password == $user['password']) {
    $token = bin2hex(random_bytes(32));

    $stmt = $conn->prepare("UPDATE users SET token = ?, token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?");
    $stmt->bind_param("si", $token, $user['id']);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["token" => $token, "message" => "Login successful"]);
    } else {
        echo json_encode(["message" => "Failed to update token"]);
    }

    $stmt->close();
} else {
    echo json_encode(["message" => "Invalid credentials"]);
}

$stmt->close();
$conn->close();
?>
