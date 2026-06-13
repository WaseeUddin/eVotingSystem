<?php
include 'db.php';
session_start();

header('Content-Type: application/json');

/**
 * Only allow POST request
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request"
    ]);
    exit;
}

/**
 * Collect input
 */
$full_name = trim($_POST['full_name'] ?? '');
$nid = trim($_POST['nid'] ?? '');
$dob = trim($_POST['dob'] ?? '');
$gender = trim($_POST['gender'] ?? 'Other');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$passwordText = trim($_POST['password'] ?? '');
$role = trim($_POST['role'] ?? 'voter');
$party = trim($_POST['party'] ?? '');
$campaign_statement = trim($_POST['campaign_statement'] ?? '');

/**
 * Validate required fields
 */
if (
    $full_name === '' ||
    $nid === '' ||
    $dob === '' ||
    $email === '' ||
    $phone === '' ||
    $passwordText === ''
) {
    echo json_encode([
        "status" => "error",
        "message" => "Please fill all required fields"
    ]);
    exit;
}

/**
 * Validate role
 */
if (!in_array($role, ['voter', 'candidate', 'admin'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid role"
    ]);
    exit;
}

/**
 * Validate gender
 */
if (!in_array($gender, ['Male', 'Female', 'Other'])) {
    $gender = 'Other';
}

/**
 * Candidate extra validation
 */
if ($role === 'candidate' && $party === '') {
    echo json_encode([
        "status" => "error",
        "message" => "Party name is required for candidate"
    ]);
    exit;
}

/**
 * File upload (NID)
 */
$nidFilePath = '';

if (isset($_FILES['nidFile']) && $_FILES['nidFile']['error'] === 0) {

    $uploadDir = '../uploads/';

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . '_' . basename($_FILES['nidFile']['name']);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['nidFile']['tmp_name'], $targetFile)) {
        $nidFilePath = 'uploads/' . $fileName;
    }
}

/**
 * Hash password
 */
$password = password_hash($passwordText, PASSWORD_BCRYPT);

/**
 * Start transaction
 */
$conn->begin_transaction();

try {

    /**
     * Insert user
     */
    $stmt = $conn->prepare("
        INSERT INTO users
        (
            full_name,
            nid,
            dob,
            gender,
            email,
            phone,
            password,
            role,
            verified
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");

    $stmt->bind_param(
        "ssssssss",
        $full_name,
        $nid,
        $dob,
        $gender,
        $email,
        $phone,
        $password,
        $role
    );

    $stmt->execute();
    $userId = $stmt->insert_id;
    $stmt->close();

    /**
     * If candidate → insert candidate table
     */
    if ($role === 'candidate') {

        $candidateStmt = $conn->prepare("
            INSERT INTO candidates
            (
                user_id,
                name,
                party,
                campaign_statement,
                total_raised
            )
            VALUES (?, ?, ?, ?, 0)
        ");

        $candidateStmt->bind_param(
            "isss",
            $userId,
            $full_name,
            $party,
            $campaign_statement
        );

        $candidateStmt->execute();
        $candidateId = $candidateStmt->insert_id;
        $candidateStmt->close();

        /**
         * Default agenda
         */
        $agendaStmt = $conn->prepare("
            INSERT INTO candidate_agendas
            (
                candidate_id,
                title,
                description
            )
            VALUES (?, ?, ?)
        ");

        $agendaTitle = "My Main Agenda";
        $agendaDescription = "I will work for the people and community.";

        $agendaStmt->bind_param(
            "iss",
            $candidateId,
            $agendaTitle,
            $agendaDescription
        );

        $agendaStmt->execute();
        $agendaStmt->close();
    }

    $conn->commit();

    /**
     * Success response
     */
    echo json_encode([
        "status" => "success",
        "message" => "Registration successful",
        "role" => $role,
        "nid_file" => $nidFilePath
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "status" => "error",
        "message" => "Registration failed. Email or NID may already exist."
    ]);
}

$conn->close();
?>