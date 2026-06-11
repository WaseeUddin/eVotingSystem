<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start = $_POST['start_datetime'];
    $end = $_POST['end_datetime'];

    $stmt = $conn->prepare("INSERT INTO elections (title,description,start_datetime,end_datetime) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss", $title, $description, $start, $end);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: ".$stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>