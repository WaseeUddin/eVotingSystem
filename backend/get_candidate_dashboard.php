<?php
include 'db.php';
session_start();

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$userId = $_SESSION['user_id'] ?? 4;

$stmt = $conn->prepare(
    "SELECT id, name, party, campaign_statement, total_raised 
     FROM candidates 
     WHERE user_id = ? 
     LIMIT 1"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$candidate = $result->fetch_assoc();
$stmt->close();

if (!$candidate) {
    $fallbackId = 4;

    $stmt = $conn->prepare(
        "SELECT id, name, party, campaign_statement, total_raised 
         FROM candidates 
         WHERE id = ? 
         LIMIT 1"
    );
    $stmt->bind_param("i", $fallbackId);
    $stmt->execute();
    $result = $stmt->get_result();
    $candidate = $result->fetch_assoc();
    $stmt->close();
}

if (!$candidate) {
    echo json_encode([
        "status" => "error",
        "message" => "Candidate not found"
    ]);
    exit;
}

$candidateId = intval($candidate['id']);

$votes = 0;
$voteStmt = $conn->prepare("SELECT COUNT(*) AS total_votes FROM votes WHERE candidate_id = ?");
$voteStmt->bind_param("i", $candidateId);
$voteStmt->execute();
$voteRow = $voteStmt->get_result()->fetch_assoc();
$votes = intval($voteRow['total_votes'] ?? 0);
$voteStmt->close();

$supporters = 0;
$donorStmt = $conn->prepare("SELECT COUNT(DISTINCT voter_id) AS total_donors FROM donations WHERE candidate_id = ?");
$donorStmt->bind_param("i", $candidateId);
$donorStmt->execute();
$donorRow = $donorStmt->get_result()->fetch_assoc();
$supporters = intval($donorRow['total_donors'] ?? 0);
$donorStmt->close();

$activeCampaigns = 0;
$campaignStmt = $conn->prepare(
    "SELECT COUNT(DISTINCT election_id) AS total_campaigns 
     FROM election_candidates 
     WHERE candidate_id = ?"
);
$campaignStmt->bind_param("i", $candidateId);
$campaignStmt->execute();
$campaignRow = $campaignStmt->get_result()->fetch_assoc();
$activeCampaigns = intval($campaignRow['total_campaigns'] ?? 0);
$campaignStmt->close();

$agendaStmt = $conn->prepare(
    "SELECT title, description 
     FROM candidate_agendas 
     WHERE candidate_id = ? 
     ORDER BY id ASC 
     LIMIT 7"
);
$agendaStmt->bind_param("i", $candidateId);
$agendaStmt->execute();
$agendaResult = $agendaStmt->get_result();

$agendas = [];

while ($row = $agendaResult->fetch_assoc()) {
    $agendas[] = $row;
}

$agendaStmt->close();

$performanceStmt = $conn->prepare(
    "SELECT 
        e.id,
        e.title,
        e.description,
        e.start_datetime,
        e.end_datetime,
        COUNT(v.id) AS total_votes,
        SUM(CASE WHEN v.candidate_id = ? THEN 1 ELSE 0 END) AS your_votes
     FROM election_candidates ec
     JOIN elections e ON ec.election_id = e.id
     LEFT JOIN votes v ON v.election_id = e.id
     WHERE ec.candidate_id = ?
     GROUP BY e.id
     ORDER BY e.start_datetime DESC"
);
$performanceStmt->bind_param("ii", $candidateId, $candidateId);
$performanceStmt->execute();
$performanceResult = $performanceStmt->get_result();

$campaignPerformance = [];

while ($row = $performanceResult->fetch_assoc()) {
    $campaignPerformance[] = $row;
}

$performanceStmt->close();

echo json_encode([
    "status" => "success",
    "candidate" => $candidate,
    "stats" => [
        "total_donations" => floatval($candidate['total_raised']),
        "votes_received" => $votes,
        "active_campaigns" => $activeCampaigns,
        "supporters" => $supporters
    ],
    "agendas" => $agendas,
    "campaign_performance" => $campaignPerformance
]);

$conn->close();
?>