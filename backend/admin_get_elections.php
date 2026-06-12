<?php
include 'db.php';
session_start();

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode([
        "status" => "error",
        "message" => "Please login as admin"
    ]);
    exit;
}

$candidates = [];

$candidateResult = $conn->query(
    "SELECT id, name, party 
     FROM candidates 
     ORDER BY name ASC"
);

if ($candidateResult) {
    while ($row = $candidateResult->fetch_assoc()) {
        $candidates[] = $row;
    }
}

$elections = [];

$sql = "
    SELECT 
        e.id,
        e.title,
        e.description,
        e.start_datetime,
        e.end_datetime,
        COALESCE(e.election_type, 'public') AS election_type,
        COUNT(DISTINCT ec.candidate_id) AS linked_candidate_count,
        COUNT(DISTINCT v.id) AS vote_count
    FROM elections e
    LEFT JOIN election_candidates ec ON e.id = ec.election_id
    LEFT JOIN votes v ON e.id = v.election_id
    GROUP BY e.id
    ORDER BY e.start_datetime DESC
";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $now = time();
    $start = strtotime($row['start_datetime']);
    $end = strtotime($row['end_datetime']);

    if ($now < $start) {
        $status = "Upcoming";
    } elseif ($now >= $start && $now <= $end) {
        $status = "Active";
    } else {
        $status = "Completed";
    }

    $candidateIds = [];

    $linkStmt = $conn->prepare(
        "SELECT candidate_id 
         FROM election_candidates 
         WHERE election_id = ?"
    );

    $linkStmt->bind_param("i", $row['id']);
    $linkStmt->execute();
    $linkResult = $linkStmt->get_result();

    while ($link = $linkResult->fetch_assoc()) {
        $candidateIds[] = intval($link['candidate_id']);
    }

    $linkStmt->close();

    $candidateCount = intval($row['linked_candidate_count']);

    if ($candidateCount === 0) {
        $candidateCount = count($candidates);
    }

    $row['status'] = $status;
    $row['candidate_count'] = $candidateCount;
    $row['candidate_ids'] = $candidateIds;
    $row['vote_count'] = intval($row['vote_count']);

    $elections[] = $row;
}

echo json_encode([
    "status" => "success",
    "candidates" => $candidates,
    "elections" => $elections
]);

$conn->close();
?>