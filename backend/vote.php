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

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'voter') {
    echo json_encode([
        "status" => "error",
        "message" => "Please login as voter"
    ]);
    exit;
}

$voterId = intval($_SESSION['user_id']);
$electionId = intval($_POST['election_id'] ?? 0);
$candidateId = intval($_POST['candidate_id'] ?? 0);

if ($electionId <= 0 || $candidateId <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid vote data"
    ]);
    exit;
}

$electionStmt = $conn->prepare(
    "SELECT id, start_datetime, end_datetime 
     FROM elections 
     WHERE id = ? 
     LIMIT 1"
);

$electionStmt->bind_param("i", $electionId);
$electionStmt->execute();
$electionResult = $electionStmt->get_result();
$election = $electionResult->fetch_assoc();
$electionStmt->close();

if (!$election) {
    echo json_encode([
        "status" => "error",
        "message" => "Election not found"
    ]);
    exit;
}

$now = time();
$start = strtotime($election['start_datetime']);
$end = strtotime($election['end_datetime']);

if ($now < $start) {
    echo json_encode([
        "status" => "error",
        "message" => "Election is not open yet"
    ]);
    exit;
}

if ($now > $end) {
    echo json_encode([
        "status" => "error",
        "message" => "Election has ended"
    ]);
    exit;
}

$candidateStmt = $conn->prepare(
    "SELECT id 
     FROM candidates 
     WHERE id = ? 
     LIMIT 1"
);

$candidateStmt->bind_param("i", $candidateId);
$candidateStmt->execute();
$candidateResult = $candidateStmt->get_result();

if ($candidateResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Candidate not found"
    ]);
    exit;
}

$candidateStmt->close();

$checkStmt = $conn->prepare(
    "SELECT id 
     FROM votes 
     WHERE voter_id = ? AND election_id = ? 
     LIMIT 1"
);

$checkStmt->bind_param("ii", $voterId, $electionId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    echo json_encode([
        "status" => "error",
        "message" => "You have already voted in this election"
    ]);
    exit;
}

$checkStmt->close();

$insertStmt = $conn->prepare(
    "INSERT INTO votes 
    (voter_id, candidate_id, election_id) 
    VALUES (?, ?, ?)"
);

$insertStmt->bind_param("iii", $voterId, $candidateId, $electionId);

if ($insertStmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Vote submitted successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Vote failed"
    ]);
}

$insertStmt->close();
$conn->close();
?>