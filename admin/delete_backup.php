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
        if (unlink($filepath)) {
            // Log activity
            $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                         VALUES ({$_SESSION['user_id']}, 'Backup Deleted', 
                         'Deleted backup file: $file', '{$_SERVER['REMOTE_ADDR']}')";
            $conn->query($log_query);
            
            $_SESSION['success'] = "Backup deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete backup file!";
        }
    } else {
        $_SESSION['error'] = "Backup file not found!";
    }
} else {
    $_SESSION['error'] = "Invalid request!";
}

header("Location: backup.php");
exit();
?>