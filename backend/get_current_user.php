<?php
include 'db.php';
session_start();

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "No user logged in"
    ]);
    exit;
}

$userId = intval($_SESSION['user_id']);

$stmt = $conn->prepare(
    "SELECT id, full_name, role 
     FROM users 
     WHERE id = ? 
     LIMIT 1"
);

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode([
        "status" => "error",
        "message" => "User not found"
    ]);
    exit;
}

echo json_encode([
    "status" => "success",
    "user" => [
        "id" => $user["id"],
        "full_name" => $user["full_name"],
        "role" => $user["role"]
    ]
]);

$conn->close();
?>