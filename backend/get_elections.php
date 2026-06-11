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
    "SELECT id, nid, role 
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
        "message" => "Only voter can view elections"
    ]);
    exit;
}

$voterNid = $user['nid'] ?? '';

$sql = "
    SELECT DISTINCT
        e.id,
        e.title,
        e.description,
        e.start_datetime,
        e.end_datetime,
        COALESCE(e.election_type, 'public') AS election_type,
        e.created_by
    FROM elections e
    LEFT JOIN election_voters ev ON e.id = ev.election_id
    WHERE 
        COALESCE(e.election_type, 'public') = 'public'
        OR e.created_by = ?
        OR ev.voter_id = ?
        OR ev.nid = ?
    ORDER BY e.start_datetime DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $userId, $userId, $voterNid);
$stmt->execute();
$result = $stmt->get_result();

$elections = [];

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

    $row['status'] = $status;
    $elections[] = $row;
}

$stmt->close();

echo json_encode([
    "status" => "success",
    "elections" => $elections
]);

$conn->close();
?>