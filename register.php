<?php
// File: register.php

// === CORS HEADERS ===
$allowed_origins = [
    'http://localhost:3000',
    'https://attendance-app-six-kappa.vercel.app/'
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include your DB connection (adjust this path if needed)
require 'connection.php';

// Parse incoming JSON
$data = json_decode(file_get_contents("php://input"));

// Validate required fields
if (!isset($data->username) || !isset($data->email) || !isset($data->password)) {
    http_response_code(400); // Bad Request
    echo json_encode([
        'success' => false,
        'message' => 'Incomplete data. Please provide username, email, and password.'
    ]);
    exit();
}

// Sanitize input
$username = htmlspecialchars(strip_tags($data->username));
$email = htmlspecialchars(strip_tags($data->email));
$password = password_hash($data->password, PASSWORD_BCRYPT); // Securely hash password
$role = 'user'; // Force default role

try {
    // Check if email already exists
    $checkQuery = "SELECT id FROM users WHERE email = :email";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->bindParam(':email', $email);
    $checkStmt->execute();

    if ($checkStmt->rowCount() > 0) {
        http_response_code(409); // Conflict
        echo json_encode([
            'success' => false,
            'message' => 'This email is already registered.'
        ]);
        exit();
    }

    // Insert new user
    $query = "INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':role', $role);

    if ($stmt->execute()) {
        http_response_code(201); // Created
        echo json_encode([
            'success' => true,
            'message' => 'User registered successfully.'
        ]);
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode([
            'success' => false,
            'message' => 'Unable to register user. Please try again later.'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
