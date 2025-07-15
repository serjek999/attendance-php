<?php
$host = 'localhost';
$db = 'dbattendance';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Hide sensitive error details
    http_response_code(500); // Internal Server Error
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed.'
    ]));
}
