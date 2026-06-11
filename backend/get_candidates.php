<?php
include 'db.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$sql = "
    SELECT 
        c.id,
        c.name,
        c.party,
        c.campaign_statement,
        c.total_raised,
        COUNT(ca.id) AS agenda_count
    FROM candidates c
    LEFT JOIN candidate_agendas ca ON c.id = ca.candidate_id
    GROUP BY c.id
    ORDER BY c.id DESC
";

$result = $conn->query($sql);

$candidates = [];

while ($row = $result->fetch_assoc()) {
    $candidates[] = [
        "id" => $row["id"],
        "name" => $row["name"],
        "party" => $row["party"],
        "campaign_statement" => $row["campaign_statement"],
        "total_raised" => $row["total_raised"],
        "agenda_count" => $row["agenda_count"]
    ];
}

echo json_encode([
    "status" => "success",
    "candidates" => $candidates
]);

$conn->close();
?>