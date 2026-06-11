<?php
include 'db.php';
session_start();

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request"
    ]);
    exit;
}

$candidate_id = intval($_POST['candidate_id'] ?? 0);
$amount = floatval($_POST['amount'] ?? 0);

if ($candidate_id <= 0 || $amount <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid donation amount"
    ]);
    exit;
}

/*
  Real login thakle session voter use korbe.
  Local testing e session na thakle dalal@test.com voter use korbe.
*/
$voter_id = 0;

if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'voter') {
    $voter_id = intval($_SESSION['user_id']);
} else {
    $testEmail = 'dalal@test.com';

    $voterStmt = $conn->prepare(
        "SELECT id FROM users WHERE email = ? AND role = 'voter' LIMIT 1"
    );
    $voterStmt->bind_param("s", $testEmail);
    $voterStmt->execute();
    $voterResult = $voterStmt->get_result();

    if ($voterRow = $voterResult->fetch_assoc()) {
        $voter_id = intval($voterRow['id']);
    }

    $voterStmt->close();
}

if ($voter_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Voter account not found. Please create dalal@test.com voter first."
    ]);
    exit;
}

$checkStmt = $conn->prepare("SELECT id FROM candidates WHERE id = ? LIMIT 1");
$checkStmt->bind_param("i", $candidate_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Candidate not found"
    ]);
    exit;
}

$checkStmt->close();

$conn->begin_transaction();

try {
    $stmt = $conn->prepare(
        "INSERT INTO donations 
        (voter_id, candidate_id, amount) 
        VALUES (?, ?, ?)"
    );

    $stmt->bind_param("iid", $voter_id, $candidate_id, $amount);
    $stmt->execute();
    $stmt->close();

    $updateStmt = $conn->prepare(
        "UPDATE candidates 
         SET total_raised = total_raised + ? 
         WHERE id = ?"
    );

    $updateStmt->bind_param("di", $amount, $candidate_id);
    $updateStmt->execute();
    $updateStmt->close();

    $totalStmt = $conn->prepare(
        "SELECT total_raised 
         FROM candidates 
         WHERE id = ?"
    );

    $totalStmt->bind_param("i", $candidate_id);
    $totalStmt->execute();
    $totalResult = $totalStmt->get_result();
    $row = $totalResult->fetch_assoc();
    $totalStmt->close();

    $conn->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Donation successful",
        "total_raised" => $row['total_raised']
    ]);
} catch (Exception $e) {
    $conn->rollback();

    echo json_encode([
        "status" => "error",
        "message" => "Donation failed"
    ]);
}

$conn->close();
?>