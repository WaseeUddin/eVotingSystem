<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $election_id = $_POST['election_id'];
    $type = $_POST['type'];
    $description = $_POST['description'];
    $reported_by = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO anomalies (election_id,type,description,reported_by) VALUES (?,?,?,?)");
    $stmt->bind_param("issi", $election_id, $type, $description, $reported_by);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: ".$stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>