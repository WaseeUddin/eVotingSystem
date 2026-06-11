<?php
include 'db.php';
session_start();

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$goal = 100000;

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Please login as candidate"
    ]);
    exit;
}

$userId = intval($_SESSION['user_id']);

$userStmt = $conn->prepare(
    "SELECT id, role 
     FROM users 
     WHERE id = ? 
     LIMIT 1"
);

$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();
$userStmt->close();

if (!$user || $user['role'] !== 'candidate') {
    echo json_encode([
        "status" => "error",
        "message" => "This page is only for candidate accounts"
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, name, party, total_raised 
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
    echo json_encode([
        "status" => "error",
        "message" => "Candidate profile not found"
    ]);
    exit;
}

$candidateId = intval($candidate['id']);

$summaryStmt = $conn->prepare(
    "SELECT 
        COUNT(DISTINCT voter_id) AS total_donors,
        COALESCE(AVG(amount), 0) AS avg_donation
     FROM donations 
     WHERE candidate_id = ?"
);

$summaryStmt->bind_param("i", $candidateId);
$summaryStmt->execute();
$summary = $summaryStmt->get_result()->fetch_assoc();
$summaryStmt->close();

$monthStmt = $conn->prepare(
    "SELECT COALESCE(SUM(amount), 0) AS month_total 
     FROM donations 
     WHERE candidate_id = ?
     AND MONTH(donation_datetime) = MONTH(CURRENT_DATE())
     AND YEAR(donation_datetime) = YEAR(CURRENT_DATE())"
);

$monthStmt->bind_param("i", $candidateId);
$monthStmt->execute();
$monthRow = $monthStmt->get_result()->fetch_assoc();
$monthStmt->close();

$recentStmt = $conn->prepare(
    "SELECT 
        d.amount,
        d.donation_datetime,
        u.full_name AS donor_name
     FROM donations d
     LEFT JOIN users u ON d.voter_id = u.id
     WHERE d.candidate_id = ?
     ORDER BY d.donation_datetime DESC
     LIMIT 5"
);

$recentStmt->bind_param("i", $candidateId);
$recentStmt->execute();
$recentResult = $recentStmt->get_result();

$recentDonations = [];

while ($row = $recentResult->fetch_assoc()) {
    $recentDonations[] = $row;
}

$recentStmt->close();

$totalRaised = floatval($candidate['total_raised']);
$totalDonors = intval($summary['total_donors'] ?? 0);
$avgDonation = floatval($summary['avg_donation'] ?? 0);
$thisMonth = floatval($monthRow['month_total'] ?? 0);
$remaining = max(0, $goal - $totalRaised);
$progress = 0;

if ($goal > 0) {
    $progress = min(100, ($totalRaised / $goal) * 100);
}

echo json_encode([
    "status" => "success",
    "candidate" => $candidate,
    "campaign" => [
        "goal" => $goal,
        "total_raised" => $totalRaised,
        "total_donors" => $totalDonors,
        "avg_donation" => $avgDonation,
        "this_month" => $thisMonth,
        "remaining" => $remaining,
        "progress" => $progress,
        "recent_donations" => $recentDonations
    ]
]);

$conn->close();
?>