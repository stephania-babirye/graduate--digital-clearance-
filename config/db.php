<?php
// Use getenv to read environment variables, with a fallback for local development
$host = getenv('DB_HOST') ?: 'localhost';
$db   = getenv('DB_DATABASE') ?: 'graduation_clearance';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$port = getenv('DB_PORT') ?: '3307';

$conn = new mysqli($host, $user, $pass, $db,$port);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
?>