<?php
include 'db.php';
session_start();

header('Content-Type: application/json');

/**
 * Check login session
 */
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Please login"
    ]);
    exit;
}

$userId = intval($_SESSION['user_id']);

/**
 * Fetch elections based on:
 * - Public elections
 * - Elections created by user
 * - Elections where user is voter
 */
$sql = "
SELECT DISTINCT
    e.id,
    e.title,
    e.description,
    e.start_datetime,
    e.end_datetime,
    e.election_type,
    e.created_by
FROM elections e
LEFT JOIN election_voters ev ON e.id = ev.election_id
WHERE 
    e.election_type = 'public'
    OR e.created_by = ?
    OR ev.voter_id = ?
ORDER BY e.start_datetime DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $userId, $userId);

$stmt->execute();
$result = $stmt->get_result();

$elections = [];

/**
 * Process each election
 */
while ($row = $result->fetch_assoc()) {

    $now = time();
    $start = strtotime($row['start_datetime']);
    $end = strtotime($row['end_datetime']);

    /**
     * Determine status
     */
    if ($now < $start) {
        $row['status'] = "Upcoming";
    } elseif ($now >= $start && $now <= $end) {
        $row['status'] = "Active";
    } else {
        $row['status'] = "Completed";
    }

    $elections[] = $row;
}

/**
 * Final response
 */
echo json_encode([
    "status" => "success",
    "elections" => $elections
]);

$stmt->close();
$conn->close();
?>