<?php
session_start();
include '../config/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $user_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    // Don't allow deactivating yourself
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = "Cannot modify your own account!";
        header("Location: user_management.php");
        exit();
    }
    
    $status = ($action == 'activate') ? 1 : 0;
    $status_text = ($action == 'activate') ? 'activated' : 'deactivated';
    
    $update_query = "UPDATE users SET is_active = $status WHERE id = $user_id";
    
    if ($conn->query($update_query)) {
        // Get user details for logging
        $user_query = "SELECT full_name, role FROM users WHERE id = $user_id";
        $user_result = $conn->query($user_query);
        $user = $user_result->fetch_assoc();
        
        // Log activity
        $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                     VALUES ({$_SESSION['user_id']}, 'User " . ucfirst($action) . "d', 
                     'User {$user['full_name']} ({$user['role']}) was $status_text', '{$_SERVER['REMOTE_ADDR']}')";
        $conn->query($log_query);
        
        $_SESSION['success'] = "User $status_text successfully!";
    } else {
        $_SESSION['error'] = "Failed to $action user!";
    }
} else {
    $_SESSION['error'] = "Invalid request!";
}

header("Location: user_management.php");
exit();
?>