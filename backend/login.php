<?php
include 'db.php';
session_start();

// Set JSON response
header('Content-Type: application/json');

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request"
    ]);
    exit;
}

// Collect POST data
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// Check required fields
if ($email === '' || $password === '') {
    echo json_encode([
        "status" => "error",
        "message" => "Email and password are required"
    ]);
    exit;
}

// Prepare statement to prevent SQL injection
$stmt = $conn->prepare("SELECT id, full_name, password, role FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Account not found"
    ]);
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Verify password using password_verify
if (!password_verify($password, $user['password'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Wrong password"
    ]);
    exit;
}

// Check role specifically for voter login
if ($user['role'] !== 'voter') {
    echo json_encode([
        "status" => "error",
        "message" => "This account is not a voter account"
    ]);
    exit;
}

// Set session variables
$_SESSION['user_id'] = $user['id'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['role'] = $user['role'];

// Return success
echo json_encode([
    "status" => "success",
    "message" => "Login successful",
    "user_id" => $user['id'],
    "full_name" => $user['full_name'],
    "role" => $user['role']
]);

$conn->close();
?>