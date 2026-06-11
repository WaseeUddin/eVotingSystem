<?php
include 'db.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
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

$title = trim($input['title'] ?? '');
$description = trim($input['description'] ?? '');
$startDatetime = trim($input['start_datetime'] ?? '');
$endDatetime = trim($input['end_datetime'] ?? '');

$existingCandidates = $input['existing_candidates'] ?? [];
$newCandidate = $input['new_candidate'] ?? null;
$voterNids = $input['voter_nids'] ?? [];

/*
  Testing er jonno user_id na thakle 1 dhora hocche.
  Login/session properly connect korar por ?? 1 remove kore dite parba.
*/
$createdBy = $_SESSION['user_id'] ?? 1;

if ($title === '' || $description === '' || $startDatetime === '' || $endDatetime === '') {
    echo json_encode([
        "status" => "error",
        "message" => "Please fill election title, description, start time and end time"
    ]);
    exit;
}

$startDatetime = str_replace("T", " ", $startDatetime) . ":00";
$endDatetime = str_replace("T", " ", $endDatetime) . ":00";

if (strtotime($endDatetime) <= strtotime($startDatetime)) {
    echo json_encode([
        "status" => "error",
        "message" => "End date must be after start date"
    ]);
    exit;
}

$hasExistingCandidate = is_array($existingCandidates) && count($existingCandidates) > 0;
$hasNewCandidate = is_array($newCandidate)
    && trim($newCandidate['name'] ?? '') !== ''
    && trim($newCandidate['party'] ?? '') !== '';

if (!$hasExistingCandidate && !$hasNewCandidate) {
    echo json_encode([
        "status" => "error",
        "message" => "Please select or add at least one candidate"
    ]);
    exit;
}

if (!is_array($voterNids) || count($voterNids) === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Please select or enter at least one eligible voter NID"
    ]);
    exit;
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare(
        "INSERT INTO elections 
        (title, description, start_datetime, end_datetime, status, election_type, created_by) 
        VALUES (?, ?, ?, ?, 'active', 'private', ?)"
    );

    $stmt->bind_param(
        "ssssi",
        $title,
        $description,
        $startDatetime,
        $endDatetime,
        $createdBy
    );

    $stmt->execute();
    $electionId = $stmt->insert_id;
    $stmt->close();

    if ($hasExistingCandidate) {
        $candidateStmt = $conn->prepare(
            "INSERT IGNORE INTO election_candidates (election_id, candidate_id) VALUES (?, ?)"
        );

        foreach ($existingCandidates as $candidateId) {
            $candidateId = intval($candidateId);

            if ($candidateId > 0) {
                $candidateStmt->bind_param("ii", $electionId, $candidateId);
                $candidateStmt->execute();
            }
        }

        $candidateStmt->close();
    }

    if ($hasNewCandidate) {
        $newName = trim($newCandidate['name']);
        $newParty = trim($newCandidate['party']);
        $newStatement = trim($newCandidate['statement'] ?? '');

        $newCandidateStmt = $conn->prepare(
            "INSERT INTO candidates (name, party, campaign_statement) VALUES (?, ?, ?)"
        );

        $newCandidateStmt->bind_param("sss", $newName, $newParty, $newStatement);
        $newCandidateStmt->execute();

        $newCandidateId = $newCandidateStmt->insert_id;
        $newCandidateStmt->close();

        $linkStmt = $conn->prepare(
            "INSERT INTO election_candidates (election_id, candidate_id) VALUES (?, ?)"
        );

        $linkStmt->bind_param("ii", $electionId, $newCandidateId);
        $linkStmt->execute();
        $linkStmt->close();
    }

    $voterStmt = $conn->prepare(
        "INSERT IGNORE INTO election_voters (election_id, voter_id, nid) VALUES (?, ?, ?)"
    );

    $findVoterStmt = $conn->prepare(
        "SELECT id FROM users WHERE nid = ? AND role = 'voter' LIMIT 1"
    );

    foreach ($voterNids as $nid) {
        $nid = trim($nid);

        if ($nid === '') {
            continue;
        }

        $voterId = null;

        $findVoterStmt->bind_param("s", $nid);
        $findVoterStmt->execute();

        $result = $findVoterStmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $voterId = intval($row['id']);
        }

        $voterStmt->bind_param("iis", $electionId, $voterId, $nid);
        $voterStmt->execute();
    }

    $findVoterStmt->close();
    $voterStmt->close();

    $conn->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Private election created successfully",
        "election_id" => $electionId
    ]);
} catch (Exception $e) {
    $conn->rollback();

    echo json_encode([
        "status" => "error",
        "message" => "Election creation failed: " . $e->getMessage()
    ]);
}

$conn->close();
?>