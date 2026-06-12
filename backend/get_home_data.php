<?php
include 'db.php';

header('Content-Type: application/json');

$stats = [
    "voters" => 0,
    "elections" => 0,
    "active_elections" => 0
];

$result = $conn->query("SELECT COUNT(*) total FROM users WHERE role='voter'");
if ($result && $row = $result->fetch_assoc()) {
    $stats["voters"] = (int)$row["total"];
}

$result = $conn->query("SELECT COUNT(*) total FROM elections");
if ($result && $row = $result->fetch_assoc()) {
    $stats["elections"] = (int)$row["total"];
}

$result = $conn->query("
    SELECT COUNT(*) total
    FROM elections
    WHERE NOW() BETWEEN start_datetime AND end_datetime
");
if ($result && $row = $result->fetch_assoc()) {
    $stats["active_elections"] = (int)$row["total"];
}

$activeElections = [];

$sql = "
SELECT
e.id,
e.title,
e.description,
e.start_datetime,
e.end_datetime,
COALESCE(e.election_type,'public') election_type
FROM elections e
WHERE
COALESCE(e.election_type,'public')='public'
AND NOW() BETWEEN e.start_datetime AND e.end_datetime
ORDER BY e.end_datetime ASC
LIMIT 3
";

$result = $conn->query($sql);

while ($election = $result->fetch_assoc()) {

    $electionId = (int)$election["id"];

    $candidateCount = 0;
    $voteCount = 0;

    $candidateResult = $conn->query("
        SELECT COUNT(*) total
        FROM election_candidates
        WHERE election_id=$electionId
    ");

    if ($candidateResult && $row = $candidateResult->fetch_assoc()) {
        $candidateCount = (int)$row["total"];
    }

    $voteResult = $conn->query("
        SELECT COUNT(*) total
        FROM votes
        WHERE election_id=$electionId
    ");

    if ($voteResult && $row = $voteResult->fetch_assoc()) {
        $voteCount = (int)$row["total"];
    }

    $election["candidate_count"] = $candidateCount;
    $election["vote_count"] = $voteCount;

    $activeElections[] = $election;
}

echo json_encode([
    "status" => "success",
    "stats" => $stats,
    "active_elections" => $activeElections
]);

$conn->close();
?>