<?php
include 'db.php';
session_start();

$email = 'dalal@test.com';

$stmt = $conn->prepare(
    "SELECT id, full_name, role 
     FROM users 
     WHERE email = ? AND role = 'voter' 
     LIMIT 1"
);

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "Dalal Shehub voter account not found.";
    exit;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['role'] = $user['role'];

header("Location: ../VoterDashboard.html");
exit;
?>