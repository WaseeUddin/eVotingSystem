<?php
include 'db.php';
session_start();

header('Content-Type: application/json');

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

$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid data"
    ]);
    exit;
}

$name = trim($input['name'] ?? '');
$party = trim($input['party'] ?? '');
$statement = trim($input['campaign_statement'] ?? '');
$agendas = $input['agendas'] ?? [];

if ($name === '' || $party === '' || $statement === '') {
    echo json_encode([
        "status" => "error",
        "message" => "Name, party and statement are required"
    ]);
    exit;
}

if (!is_array($agendas) || count($agendas) === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Please add at least one agenda"
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT id 
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

$conn->begin_transaction();

try {
    $updateCandidateStmt = $conn->prepare(
        "UPDATE candidates 
         SET name = ?, party = ?, campaign_statement = ? 
         WHERE id = ?"
    );

    $updateCandidateStmt->bind_param(
        "sssi",
        $name,
        $party,
        $statement,
        $candidateId
    );

    $updateCandidateStmt->execute();
    $updateCandidateStmt->close();

    $updateUserStmt = $conn->prepare(
        "UPDATE users 
         SET full_name = ? 
         WHERE id = ?"
    );

    $updateUserStmt->bind_param("si", $name, $userId);
    $updateUserStmt->execute();
    $updateUserStmt->close();

    $deleteStmt = $conn->prepare(
        "DELETE FROM candidate_agendas 
         WHERE candidate_id = ?"
    );

    $deleteStmt->bind_param("i", $candidateId);
    $deleteStmt->execute();
    $deleteStmt->close();

    $insertStmt = $conn->prepare(
        "INSERT INTO candidate_agendas 
        (candidate_id, title, description) 
        VALUES (?, ?, ?)"
    );

    foreach ($agendas as $agenda) {
        $agendaTitle = trim($agenda['title'] ?? '');
        $agendaDescription = trim($agenda['description'] ?? '');

        if ($agendaTitle !== '' && $agendaDescription !== '') {
            $insertStmt->bind_param(
                "iss",
                $candidateId,
                $agendaTitle,
                $agendaDescription
            );

            $insertStmt->execute();
        }
    }

    $insertStmt->close();

    $conn->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Profile updated successfully"
    ]);
} catch (Exception $e) {
    $conn->rollback();

    echo json_encode([
        "status" => "error",
        "message" => "Profile update failed"
    ]);
}

$conn->close();
?>