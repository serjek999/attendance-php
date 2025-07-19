<?php
// File: fetch_attendance.php
$allowed_origins = [
    'http://localhost:3000',
    'https://attendance-app-pearl-three.vercel.app'
];
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'connection.php';

$data = json_decode(file_get_contents("php://input"));
if (!isset($data->user_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID required.']);
    exit();
}

$user_id = $data->user_id;

try {
    $stmt = $pdo->prepare("SELECT id, date, check_in, check_out FROM attendance WHERE user_id = :user_id ORDER BY date DESC");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'records' => $records]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
