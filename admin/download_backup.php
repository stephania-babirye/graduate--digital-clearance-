<?php
session_start();
include '../config/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php");
    exit();
}

if (isset($_GET['file'])) {
    $file = basename($_GET['file']); // Sanitize filename
    $filepath = "../backups/$file";
    
    if (file_exists($filepath)) {
        // Set headers for download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$file.'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        
        // Read file and output
        readfile($filepath);
        
        // Log activity
        $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                     VALUES ({$_SESSION['user_id']}, 'Backup Downloaded', 
                     'Downloaded backup file: $file', '{$_SERVER['REMOTE_ADDR']}')";
        $conn->query($log_query);
        
        exit;
    } else {
        $_SESSION['error'] = "Backup file not found!";
        header("Location: backup.php");
        exit();
    }
} else {
    header("Location: backup.php");
    exit();
}
?>