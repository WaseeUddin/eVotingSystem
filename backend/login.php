<?php
include 'db.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request"
    ]);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$expectedRole = trim($_POST['expected_role'] ?? '');

if ($email === '' || $password === '') {
    echo json_encode([
        "status" => "error",
        "message" => "Email and password are required"
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT id, full_name, password, role
    FROM users
    WHERE email = ?
    LIMIT 1
");

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


// ===============================
// FIX: SUPPORT BOTH OLD + NEW PASSWORD SYSTEM
// ===============================

$loginSuccess = false;

// Case 1: New system (hashed password)
if (password_verify($password, $user['password'])) {
    $loginSuccess = true;
}
// Case 2: Old system (plain text password)
elseif ($password === $user['password']) {

    // auto upgrade password to hash
    $hashed = password_hash($password, PASSWORD_BCRYPT);

    $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update->bind_param("si", $hashed, $user['id']);
    $update->execute();
    $update->close();

    $loginSuccess = true;
}

// wrong password
if (!$loginSuccess) {
    echo json_encode([
        "status" => "error",
        "message" => "Wrong password"
    ]);
    exit;
}


// role check
if ($expectedRole !== '' && $user['role'] !== $expectedRole) {
    echo json_encode([
        "status" => "error",
        "message" => "This account is not a " . $expectedRole . " account"
    ]);
    exit;
}


// session set (IMPORTANT FIX FOR YOUR ELECTION ISSUE)
$_SESSION['user_id'] = $user['id'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['role'] = $user['role'];
$_SESSION['email'] = $email;

echo json_encode([
    "status" => "success",
    "message" => "Login successful",
    "user_id" => $user['id'],
    "full_name" => $user['full_name'],
    "role" => $user['role']
]);

$conn->close();
?>