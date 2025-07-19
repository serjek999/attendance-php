<?php
// File: check_in.php
$allowed_origins = [
    'http://localhost:3000',
    'https://attendance-app-pearl-three.vercel.app'
];
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

if (!isset($data->user_id, $data->date, $data->check_out)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

$user_id = $data->user_id;
$date = $data->date;
$check_out = $data->check_out;

try {
    $update = "UPDATE attendance SET check_out = :check_out WHERE user_id = :user_id AND date = :date";
    $stmt = $pdo->prepare($update);
    $stmt->execute(['check_out' => $check_out, 'user_id' => $user_id, 'date' => $date]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Attendance record not found.']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Check-out recorded successfully.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
