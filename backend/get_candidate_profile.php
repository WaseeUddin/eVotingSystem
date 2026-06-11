<?php
include 'db.php';
session_start();

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Please login as candidate"
    ]);
    exit;
}

$userId = intval($_SESSION['user_id']);

$userStmt = $conn->prepare(
    "SELECT id, role 
     FROM users 
     WHERE id = ? 
     LIMIT 1"
);

$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();
$userStmt->close();

if (!$user || $user['role'] !== 'candidate') {
    echo json_encode([
        "status" => "error",
        "message" => "This page is only for candidate accounts"
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT 
        id, 
        name, 
        party, 
        campaign_statement, 
        total_raised 
     FROM candidates 
     WHERE user_id = ? 
     LIMIT 1"
);

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$candidate = $result->fetch_assoc();
$stmt->close();

if (!$candidate) {
    echo json_encode([
        "status" => "error",
        "message" => "Candidate profile not found"
    ]);
    exit;
}

$candidateId = intval($candidate['id']);

$agendaStmt = $conn->prepare(
    "SELECT 
        id, 
        title, 
        description 
     FROM candidate_agendas 
     WHERE candidate_id = ? 
     ORDER BY id ASC"
);

$agendaStmt->bind_param("i", $candidateId);
$agendaStmt->execute();
$agendaResult = $agendaStmt->get_result();

$agendas = [];

while ($row = $agendaResult->fetch_assoc()) {
    $agendas[] = $row;
}

$agendaStmt->close();

echo json_encode([
    "status" => "success",
    "candidate" => $candidate,
    "agendas" => $agendas
]);

$conn->close();
?>