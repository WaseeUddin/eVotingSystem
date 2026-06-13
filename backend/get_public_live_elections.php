<?php

include 'db.php';

header('Content-Type: application/json');

date_default_timezone_set('Asia/Dhaka');

$sql = "
SELECT
    id,
    title,
    description,
    start_datetime,
    end_datetime,
    election_type
FROM elections
WHERE election_type = 'public'
ORDER BY start_datetime DESC
";

$result = $conn->query($sql);

$elections = [];

while ($row = $result->fetch_assoc()) {

    $now = time();
    $start = strtotime($row['start_datetime']);
    $end = strtotime($row['end_datetime']);

    if ($now < $start) {
        $status = "Upcoming";
    }
    elseif ($now >= $start && $now <= $end) {
        $status = "Active";
    }
    else {
        $status = "Completed";
    }

    $row['status'] = $status;

    $elections[] = $row;
}

echo json_encode([
    "status" => "success",
    "elections" => $elections
]);

$conn->close();
?>