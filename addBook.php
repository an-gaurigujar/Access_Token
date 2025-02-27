<?php
include 'db.php';
header("Content-Type: application/json");

$config = new Config();
$conn = $config->conn;

$headers = getallheaders();


$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';

if (!$authHeader) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(["message" => "Unauthorized: Token missing"]);
    exit(); 
}

$stmt = $conn->prepare("SELECT id, name, email FROM users WHERE token = ?");

if (!$stmt) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(["message" => "Database error: " . $conn->error]);
    exit();
}

$stmt->bind_param("s", $authHeader); 
$stmt->execute();
$result = $stmt->get_result();

$user = $result->fetch_assoc();

if (!$user) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(["message" => "Unauthorized: Invalid or expired token"]);
    exit();
}

  $data = json_decode(file_get_contents("php://input"), true);

  if (!isset($data['title']) || !isset($data['author'])) {
      echo json_encode(["message" => "Title and Author are required"]);
      exit();
  }

  $title = mysqli_real_escape_string($conn, $data['title']);
  $author = mysqli_real_escape_string($conn, $data['author']);

  $stmt = $conn->prepare("INSERT INTO books (title, author, user_id) VALUES (?, ?, ?)");
  $stmt->bind_param("ssi", $title, $author, $user['id']);

  if ($stmt->execute()) {
      echo json_encode(["message" => "Book added successfully"]);
  } else {
      echo json_encode(["message" => "Failed to add book"]);
  }

  $stmt->close();

$conn->close();
?>
