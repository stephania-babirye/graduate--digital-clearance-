<?php
// Database connection settings
$host = 'localhost';
$db   = 'graduation_clearance';
$user = 'root';
$pass = '';
$port = '3307';

$conn = new mysqli($host, $user, $pass, $db,$port);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
?>