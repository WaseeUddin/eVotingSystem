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

$selectedElectionId = intval($_GET['election_id'] ?? 0);

$elections = [];

$electionResult = $conn->query(
    "SELECT 
        id,
        title,
        description,
        start_datetime,
        end_datetime,
        COALESCE(election_type, 'public') AS election_type
     FROM elections
     ORDER BY start_datetime DESC"
);

while ($row = $electionResult->fetch_assoc()) {
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

    $row['status'] = $status;
    $elections[] = $row;
}

if (count($elections) === 0) {
    echo json_encode([
        "status" => "success",
        "elections" => [],
        "election" => null,
        "total_votes" => 0,
        "leading_candidate" => null,
        "results" => []
    ]);
    exit;
}

$selectedElection = null;

foreach ($elections as $election) {
    if ($selectedElectionId > 0 && intval($election['id']) === $selectedElectionId) {
        $selectedElection = $election;
        break;
    }
}

if (!$selectedElection) {
    foreach ($elections as $election) {
        if ($election['status'] === "Active") {
            $selectedElection = $election;
            break;
        }
    }
}

if (!$selectedElection) {
    $selectedElection = $elections[0];
}

$electionId = intval($selectedElection['id']);

$linkedCount = 0;

$countStmt = $conn->prepare(
    "SELECT COUNT(*) AS total 
     FROM election_candidates 
     WHERE election_id = ?"
);

$countStmt->bind_param("i", $electionId);
$countStmt->execute();
$countResult = $countStmt->get_result();

if ($countRow = $countResult->fetch_assoc()) {
    $linkedCount = intval($countRow['total']);
}

$countStmt->close();

$results = [];

if ($linkedCount > 0) {
    $stmt = $conn->prepare(
        "SELECT 
            c.id,
            c.name,
            c.party,
            COUNT(v.id) AS vote_count
         FROM election_candidates ec
         JOIN candidates c ON ec.candidate_id = c.id
         LEFT JOIN votes v 
            ON v.candidate_id = c.id 
            AND v.election_id = ec.election_id
         WHERE ec.election_id = ?
         GROUP BY c.id
         ORDER BY vote_count DESC, c.name ASC"
    );

    $stmt->bind_param("i", $electionId);
} else {
    $stmt = $conn->prepare(
        "SELECT 
            c.id,
            c.name,
            c.party,
            COUNT(v.id) AS vote_count
         FROM candidates c
         LEFT JOIN votes v 
            ON v.candidate_id = c.id 
            AND v.election_id = ?
         GROUP BY c.id
         ORDER BY vote_count DESC, c.name ASC"
    );

    $stmt->bind_param("i", $electionId);
}

$stmt->execute();
$result = $stmt->get_result();

$totalVotes = 0;

while ($row = $result->fetch_assoc()) {
    $row['vote_count'] = intval($row['vote_count']);
    $totalVotes += $row['vote_count'];
    $results[] = $row;
}

$stmt->close();

foreach ($results as $index => $row) {
    $percent = 0;

    if ($totalVotes > 0) {
        $percent = round(($row['vote_count'] / $totalVotes) * 100, 1);
    }

    $results[$index]['percent'] = $percent;
}

$leadingCandidate = null;

if (count($results) > 0 && $totalVotes > 0) {
    $leadingCandidate = $results[0];
}

echo json_encode([
    "status" => "success",
    "elections" => $elections,
    "election" => $selectedElection,
    "total_votes" => $totalVotes,
    "leading_candidate" => $leadingCandidate,
    "results" => $results
]);

$conn->close();
?>