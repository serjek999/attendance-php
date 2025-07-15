<?php
// File: check_in.php

// Allow from local frontend
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

// Respond immediately to preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require 'connection.php';


$data = json_decode(file_get_contents("php://input"));

if (!isset($data->user_id, $data->date, $data->check_in)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

$user_id = $data->user_id;
$date = $data->date;
$check_in = $data->check_in;

try {
    // Check if a record already exists for this user and date
    $query = "SELECT id FROM attendance WHERE user_id = :user_id AND date = :date";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $user_id, 'date' => $date]);

    if ($stmt->rowCount() > 0) {
        // Update existing record
        $update = "UPDATE attendance SET check_in = :check_in WHERE user_id = :user_id AND date = :date";
        $stmt = $pdo->prepare($update);
        $stmt->execute(['check_in' => $check_in, 'user_id' => $user_id, 'date' => $date]);
    } else {
        // Insert new record
        $insert = "INSERT INTO attendance (user_id, date, check_in) VALUES (:user_id, :date, :check_in)";
        $stmt = $pdo->prepare($insert);
        $stmt->execute(['user_id' => $user_id, 'date' => $date, 'check_in' => $check_in]);
    }

    echo json_encode(['success' => true, 'message' => 'Check-in recorded successfully.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
