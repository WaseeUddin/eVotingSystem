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

$stats = [
    "total_elections" => 0,
    "active_elections" => 0,
    "total_votes" => 0,
    "candidates" => 0,
    "total_anomalies" => 0,
    "unresolved_anomalies" => 0
];

$result = $conn->query("SELECT COUNT(*) AS total FROM elections");
if ($result && $row = $result->fetch_assoc()) {
    $stats["total_elections"] = intval($row["total"]);
}

$result = $conn->query(
    "SELECT COUNT(*) AS total 
     FROM elections 
     WHERE NOW() BETWEEN start_datetime AND end_datetime"
);
if ($result && $row = $result->fetch_assoc()) {
    $stats["active_elections"] = intval($row["total"]);
}

$result = $conn->query("SELECT COUNT(*) AS total FROM votes");
if ($result && $row = $result->fetch_assoc()) {
    $stats["total_votes"] = intval($row["total"]);
}

/*
  Admin candidate count always comes from candidates table.
  Donation page and candidate dashboard also use this table.
*/
$result = $conn->query("SELECT COUNT(*) AS total FROM candidates");
if ($result && $row = $result->fetch_assoc()) {
    $stats["candidates"] = intval($row["total"]);
}

$result = $conn->query("SELECT COUNT(*) AS total FROM anomalies");
if ($result && $row = $result->fetch_assoc()) {
    $stats["total_anomalies"] = intval($row["total"]);
}

$hasStatusColumn = false;
$columnCheck = $conn->query("SHOW COLUMNS FROM anomalies LIKE 'status'");

if ($columnCheck && $columnCheck->num_rows > 0) {
    $hasStatusColumn = true;
}

if ($hasStatusColumn) {
    $result = $conn->query(
        "SELECT COUNT(*) AS total 
         FROM anomalies 
         WHERE status IS NULL OR status != 'resolved'"
    );
} else {
    $result = $conn->query("SELECT COUNT(*) AS total FROM anomalies");
}

if ($result && $row = $result->fetch_assoc()) {
    $stats["unresolved_anomalies"] = intval($row["total"]);
}

$activeElections = [];

$activeResult = $conn->query(
    "SELECT 
        e.id,
        e.title,
        e.description,
        e.start_datetime,
        e.end_datetime,
        COUNT(v.id) AS vote_count
     FROM elections e
     LEFT JOIN votes v ON e.id = v.election_id
     WHERE NOW() BETWEEN e.start_datetime AND e.end_datetime
     GROUP BY e.id
     ORDER BY e.end_datetime ASC
     LIMIT 5"
);

if ($activeResult) {
    while ($row = $activeResult->fetch_assoc()) {
        $endTime = strtotime($row["end_datetime"]);
        $hoursRemaining = 0;

        if ($endTime > time()) {
            $hoursRemaining = ceil(($endTime - time()) / 3600);
        }

        $row["hours_remaining"] = $hoursRemaining;
        $activeElections[] = $row;
    }
}

$anomalies = [];

if ($hasStatusColumn) {
    $anomalyResult = $conn->query(
        "SELECT 
            a.id,
            a.type,
            a.description,
            a.reported_at,
            e.title AS election_title
         FROM anomalies a
         LEFT JOIN elections e ON a.election_id = e.id
         WHERE a.status IS NULL OR a.status != 'resolved'
         ORDER BY a.reported_at DESC
         LIMIT 5"
    );
} else {
    $anomalyResult = $conn->query(
        "SELECT 
            a.id,
            a.type,
            a.description,
            a.reported_at,
            e.title AS election_title
         FROM anomalies a
         LEFT JOIN elections e ON a.election_id = e.id
         ORDER BY a.reported_at DESC
         LIMIT 5"
    );
}

if ($anomalyResult) {
    while ($row = $anomalyResult->fetch_assoc()) {
        $anomalies[] = $row;
    }
}

echo json_encode([
    "status" => "success",
    "stats" => $stats,
    "active_elections" => $activeElections,
    "anomalies" => $anomalies
]);

$conn->close();
?>