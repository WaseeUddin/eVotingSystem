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

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid election"
    ]);
    exit;
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("DELETE FROM votes WHERE election_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM election_candidates WHERE election_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM election_voters WHERE election_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM elections WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Election deleted successfully"
    ]);
} catch (Exception $e) {
    $conn->rollback();

    echo json_encode([
        "status" => "error",
        "message" => "Delete failed: " . $e->getMessage()
    ]);
}

$conn->close();
?>