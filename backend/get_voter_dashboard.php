<?php
include 'db.php';
session_start();

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Please login as voter"
    ]);
    exit;
}

$userId = intval($_SESSION['user_id']);

$userStmt = $conn->prepare(
    "SELECT id, full_name, nid, role 
     FROM users 
     WHERE id = ? 
     LIMIT 1"
);

$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();
$userStmt->close();

if (!$user || $user['role'] !== 'voter') {
    echo json_encode([
        "status" => "error",
        "message" => "This dashboard is only for voter accounts"
    ]);
    exit;
}

$voterNid = $user['nid'] ?? '';

$candidateCount = 0;
$candidateResult = $conn->query("SELECT COUNT(*) AS total FROM candidates");

if ($candidateRow = $candidateResult->fetch_assoc()) {
    $candidateCount = intval($candidateRow['total']);
}

$votesCast = 0;

$voteStmt = $conn->prepare(
    "SELECT COUNT(*) AS total 
     FROM votes 
     WHERE voter_id = ?"
);

$voteStmt->bind_param("i", $userId);
$voteStmt->execute();
$voteResult = $voteStmt->get_result();

if ($voteRow = $voteResult->fetch_assoc()) {
    $votesCast = intval($voteRow['total']);
}

$voteStmt->close();

$electionStmt = $conn->prepare(
    "SELECT DISTINCT
        e.id,
        e.title,
        e.description,
        e.start_datetime,
        e.end_datetime,
        COALESCE(e.election_type, 'public') AS election_type
     FROM elections e
     LEFT JOIN election_voters ev ON e.id = ev.election_id
     WHERE 
        (
            COALESCE(e.election_type, 'public') = 'public'
            OR e.created_by = ?
            OR ev.voter_id = ?
            OR ev.nid = ?
        )
        AND NOW() BETWEEN e.start_datetime AND e.end_datetime
     ORDER BY e.end_datetime ASC"
);

$electionStmt->bind_param("iis", $userId, $userId, $voterNid);
$electionStmt->execute();
$electionResult = $electionStmt->get_result();

$activeElections = [];

while ($row = $electionResult->fetch_assoc()) {
    $activeElections[] = $row;
}

$electionStmt->close();

echo json_encode([
    "status" => "success",
    "voter" => [
        "id" => $user['id'],
        "full_name" => $user['full_name'],
        "role" => $user['role']
    ],
    "stats" => [
        "active_elections" => count($activeElections),
        "votes_cast" => $votesCast,
        "candidates" => $candidateCount
    ],
    "active_elections" => $activeElections
]);

$conn->close();
?>