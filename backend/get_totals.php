<?php
include 'db.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$result = $conn->query("SELECT id, total_raised FROM candidates ORDER BY id");

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        "id" => $row["id"],
        "total_raised" => $row["total_raised"]
    ];
}

echo json_encode($data);
?>