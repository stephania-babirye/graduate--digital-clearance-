<?php
session_start();
include '../config/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php");
    exit();
}

// Clear all activity logs
$clear_query = "TRUNCATE TABLE activity_logs";

if ($conn->query($clear_query)) {
    // Log this action before clearing (it will be the only log)
    $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                 VALUES ({$_SESSION['user_id']}, 'Logs Cleared', 
                 'All activity logs were cleared', '{$_SERVER['REMOTE_ADDR']}')";
    $conn->query($log_query);
    
    $_SESSION['success'] = "All activity logs cleared successfully!";
} else {
    $_SESSION['error'] = "Failed to clear activity logs!";
}

header("Location: logs.php");
exit();
?>