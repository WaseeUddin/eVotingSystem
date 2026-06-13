<?php
include 'db.php';

header('Content-Type: application/json');

/**
 * Only allow POST request
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
    ]);
    exit;
}

/**
 * Get and sanitize input
 */
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$start = trim($_POST['start_datetime'] ?? '');
$end = trim($_POST['end_datetime'] ?? '');

/**
 * Validate required fields
 */
if ($title === '' || $description === '' || $start === '' || $end === '') {
    echo json_encode([
        "status" => "error",
        "message" => "All fields are required"
    ]);
    exit;
}

/**
 * Validate date range
 */
if (strtotime($end) <= strtotime($start)) {
    echo json_encode([
        "status" => "error",
        "message" => "End date must be after start date"
    ]);
    exit;
}

/**
 * Generate unique election code
 */
$electionCode = strtoupper(substr(md5(uniqid()), 0, 8));

/**
 * Insert into database
 */
$stmt = $conn->prepare("
    INSERT INTO elections 
    (title, description, start_datetime, end_datetime, status, election_type, election_code)
    VALUES (?, ?, ?, ?, 'active', 'public', ?)
");

$stmt->bind_param(
    "sssss",
    $title,
    $description,
    $start,
    $end,
    $electionCode
);

/**
 * Execute query
 */
if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Election created successfully",
        "election_id" => $stmt->insert_id,
        "election_code" => $electionCode
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => $stmt->error
    ]);
}

/**
 * Close connection
 */
$stmt->close();
$conn->close();
?>