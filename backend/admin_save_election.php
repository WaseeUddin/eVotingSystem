<?php
include 'db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode([
        "status" => "error",
        "message" => "Please login as admin"
    ]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

$id = intval($input['id'] ?? 0);
$title = trim($input['title'] ?? '');
$description = trim($input['description'] ?? '');
$startDatetime = trim($input['start_datetime'] ?? '');
$endDatetime = trim($input['end_datetime'] ?? '');
$electionType = trim($input['election_type'] ?? 'public');
$candidateIds = $input['candidate_ids'] ?? [];

if ($title === '' || $description === '' || $startDatetime === '' || $endDatetime === '') {
    echo json_encode([
        "status" => "error",
        "message" => "Please fill all required fields"
    ]);
    exit;
}

$startDatetime = str_replace("T", " ", $startDatetime) . ":00";
$endDatetime = str_replace("T", " ", $endDatetime) . ":00";

if (strtotime($endDatetime) <= strtotime($startDatetime)) {
    echo json_encode([
        "status" => "error",
        "message" => "End time must be after start time"
    ]);
    exit;
}

if ($electionType !== 'public' && $electionType !== 'private') {
    $electionType = 'public';
}

$conn->begin_transaction();

try {
    if ($id > 0) {
        $stmt = $conn->prepare(
            "UPDATE elections
             SET title = ?, description = ?, start_datetime = ?, end_datetime = ?, election_type = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "sssssi",
            $title,
            $description,
            $startDatetime,
            $endDatetime,
            $electionType,
            $id
        );

        $stmt->execute();
        $stmt->close();

        $electionId = $id;
    } else {
        $createdBy = intval($_SESSION['user_id']);

        $stmt = $conn->prepare(
            "INSERT INTO elections
            (title, description, start_datetime, end_datetime, status, election_type, created_by)
            VALUES (?, ?, ?, ?, 'active', ?, ?)"
        );

        $stmt->bind_param(
            "sssssi",
            $title,
            $description,
            $startDatetime,
            $endDatetime,
            $electionType,
            $createdBy
        );

        $stmt->execute();
        $electionId = $stmt->insert_id;
        $stmt->close();
    }

    $deleteLinks = $conn->prepare(
        "DELETE FROM election_candidates WHERE election_id = ?"
    );

    $deleteLinks->bind_param("i", $electionId);
    $deleteLinks->execute();
    $deleteLinks->close();

    if (is_array($candidateIds) && count($candidateIds) > 0) {
        $linkStmt = $conn->prepare(
            "INSERT IGNORE INTO election_candidates (election_id, candidate_id)
             VALUES (?, ?)"
        );

        foreach ($candidateIds as $candidateId) {
            $candidateId = intval($candidateId);

            if ($candidateId > 0) {
                $linkStmt->bind_param("ii", $electionId, $candidateId);
                $linkStmt->execute();
            }
        }

        $linkStmt->close();
    }

    $conn->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Election saved successfully"
    ]);
} catch (Exception $e) {
    $conn->rollback();

    echo json_encode([
        "status" => "error",
        "message" => "Election save failed: " . $e->getMessage()
    ]);
}

$conn->close();
?>