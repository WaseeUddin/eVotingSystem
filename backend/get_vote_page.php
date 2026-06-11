<?php
include 'db.php';
session_start();

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'voter') {
    echo json_encode([
        "status" => "error",
        "message" => "Please login as voter"
    ]);
    exit;
}

$voterId = intval($_SESSION['user_id']);
$electionId = intval($_GET['election_id'] ?? 0);

if ($electionId <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid election"
    ]);
    exit;
}

$electionStmt = $conn->prepare(
    "SELECT 
        id, 
        title, 
        description, 
        start_datetime, 
        end_datetime,
        COALESCE(election_type, 'public') AS election_type
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
        "message" => "This election is not open yet"
    ]);
    exit;
}

if ($now > $end) {
    echo json_encode([
        "status" => "error",
        "message" => "This election has ended"
    ]);
    exit;
}

$checkVoteStmt = $conn->prepare(
    "SELECT id 
     FROM votes 
     WHERE voter_id = ? AND election_id = ? 
     LIMIT 1"
);

$checkVoteStmt->bind_param("ii", $voterId, $electionId);
$checkVoteStmt->execute();
$checkVoteResult = $checkVoteStmt->get_result();
$alreadyVoted = $checkVoteResult->num_rows > 0;
$checkVoteStmt->close();

/*
  যদি election_candidates table-এ এই election-এর candidate linked থাকে,
  তাহলে ওই candidate গুলো দেখাবে।
  যদি link না থাকে, তাহলে public election-এর জন্য সব candidate দেখাবে।
*/
$countStmt = $conn->prepare(
    "SELECT COUNT(*) AS total 
     FROM election_candidates 
     WHERE election_id = ?"
);

$countStmt->bind_param("i", $electionId);
$countStmt->execute();
$countResult = $countStmt->get_result();
$countRow = $countResult->fetch_assoc();
$linkedCount = intval($countRow['total'] ?? 0);
$countStmt->close();

$candidates = [];

if ($linkedCount > 0) {
    $candidateStmt = $conn->prepare(
        "SELECT 
            c.id,
            c.name,
            c.party,
            c.campaign_statement
         FROM election_candidates ec
         JOIN candidates c ON ec.candidate_id = c.id
         WHERE ec.election_id = ?
         ORDER BY c.name ASC"
    );

    $candidateStmt->bind_param("i", $electionId);
} else {
    $candidateStmt = $conn->prepare(
        "SELECT 
            id,
            name,
            party,
            campaign_statement
         FROM candidates
         ORDER BY name ASC"
    );
}

$candidateStmt->execute();
$candidateResult = $candidateStmt->get_result();

while ($row = $candidateResult->fetch_assoc()) {
    $candidates[] = $row;
}

$candidateStmt->close();

echo json_encode([
    "status" => "success",
    "election" => $election,
    "already_voted" => $alreadyVoted,
    "candidates" => $candidates
]);

$conn->close();
?>