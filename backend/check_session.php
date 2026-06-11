<?php
session_start();

header('Content-Type: application/json');

echo json_encode([
    "user_id" => $_SESSION['user_id'] ?? null,
    "full_name" => $_SESSION['full_name'] ?? null,
    "role" => $_SESSION['role'] ?? null
]);
?>