<?php
// backend/get_home_data.php
include 'db.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$stats = [
    "voters" => 0,
    "elections" => 0,
    "active_elections" => 0
];

// Total voters
$result = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'voter'");
if ($result && $row = $result->fetch_assoc()) {
    $stats["voters"] = intval($row["total"]);
}

// Total elections
$result = $conn->query("SELECT COUNT(*) AS total FROM elections");
if ($result && $row = $result->fetch_assoc()) {
    $stats["elections"] = intval($row["total"]);
}

// Active elections
$result = $conn->query(
    "SELECT COUNT(*) AS total 
     FROM elections 
     WHERE NOW() BETWEEN start_datetime AND end_datetime"
);
if ($result && $row = $result->fetch_assoc()) {
    $stats["active_elections"] = intval($row["total"]);
}

// Active public elections data
$activeElections = [];

$electionResult = $conn->query(
    "SELECT 
        id,
        title,
        description,
        start_datetime,
        end_datetime,
        COALESCE(election_type, 'public') AS election_type
     FROM elections
     WHERE NOW() BETWEEN start_datetime AND end_datetime AND is_public=1
     ORDER BY end_datetime ASC"
);

if ($electionResult) {
    while ($election = $electionResult->fetch_assoc()) {
        $electionId = intval($election["id"]);

        $candidateCount = 0;
        $voteCount = 0;

        // Count candidates linked to this election
        $candidateStmt = $conn->prepare(
            "SELECT COUNT(*) AS total FROM election_candidates WHERE election_id = ?"
        );
        $candidateStmt->bind_param("i", $electionId);
        $candidateStmt->execute();
        $candidateResult = $candidateStmt->get_result();
        if ($candidateRow = $candidateResult->fetch_assoc()) {
            $candidateCount = intval($candidateRow["total"]);
        }
        $candidateStmt->close();

        // Count votes for this election
        $voteStmt = $conn->prepare(
            "SELECT COUNT(*) AS total FROM votes WHERE election_id = ?"
        );
        $voteStmt->bind_param("i", $electionId);
        $voteStmt->execute();
        $voteResult = $voteStmt->get_result();
        if ($voteRow = $voteResult->fetch_assoc()) {
            $voteCount = intval($voteRow["total"]);
        }
        $voteStmt->close();

        $election["candidate_count"] = $candidateCount;
        $election["vote_count"] = $voteCount;

        $activeElections[] = $election;
    }
}

echo json_encode([
    "status" => "success",
    "stats" => $stats,
    "active_elections" => $activeElections
]);

$conn->close();
?>