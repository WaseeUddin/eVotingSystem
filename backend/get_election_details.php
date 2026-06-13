<?php

include 'db.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Election ID missing"
    ]);
    exit;
}

$electionId = intval($_GET['id']);

$electionQuery = "
SELECT *
FROM elections
WHERE id = $electionId
LIMIT 1
";

$electionResult = $conn->query($electionQuery);

if ($electionResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Election not found"
    ]);
    exit;
}

$election = $electionResult->fetch_assoc();

$candidatesQuery = "
SELECT
    c.id,
    c.name,
    c.party,
    c.campaign_statement,
    c.total_raised,

    (
        SELECT COUNT(*)
        FROM votes v
        WHERE v.candidate_id = c.id
        AND v.election_id = $electionId
    ) AS vote_count,

    (
        SELECT COALESCE(SUM(amount),0)
        FROM donations d
        WHERE d.candidate_id = c.id
    ) AS donation_total

FROM election_candidates ec
INNER JOIN candidates c
ON c.id = ec.candidate_id

WHERE ec.election_id = $electionId
";

$candidateResult = $conn->query($candidatesQuery);

$candidates = [];

while ($row = $candidateResult->fetch_assoc()) {
    $candidates[] = $row;
}

echo json_encode([
    "status" => "success",
    "election" => $election,
    "candidates" => $candidates
]);