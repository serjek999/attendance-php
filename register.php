<?php
// File: register.php

// Set headers for CORS and content type
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include your database connection file
require 'connection.php';

// Get the data sent from the frontend
$data = json_decode(file_get_contents("php://input"));
// Get the data sent from the frontend
$data = json_decode(file_get_contents("php://input"));

// Validate the incoming data
if (!isset($data->username) || !isset($data->email) || !isset($data->password)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Incomplete data. Please provide username, email, and password.']);
    exit();
}

// Sanitize and prepare data
$username = htmlspecialchars(strip_tags($data->username));
$email = htmlspecialchars(strip_tags($data->email));
$password = password_hash($data->password, PASSWORD_BCRYPT); // Securely hash the password

try {
    // Check if the email already exists to avoid duplicates
    $checkQuery = "SELECT id FROM users WHERE email = :email";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->bindParam(':email', $email);
    $checkStmt->execute();

    if ($checkStmt->rowCount() > 0) {
        http_response_code(409); // Conflict
        echo json_encode(['success' => false, 'message' => 'This email is already registered.']);
        exit();
    }

    // Prepare the SQL query to insert a new user
    $query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
    $stmt = $pdo->prepare($query);

    // Bind parameters
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);

    // Execute the statement
    if ($stmt->execute()) {
        http_response_code(201); // Created
        echo json_encode(['success' => true, 'message' => 'User was successfully registered.']);
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(['success' => false, 'message' => 'Unable to register user.']);
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>