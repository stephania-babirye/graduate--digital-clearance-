<?php
session_start();
include '../config/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php");
    exit();
}

if (isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    
    // Don't allow deleting yourself
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = "Cannot delete your own account!";
        header("Location: user_management.php");
        exit();
    }
    
    // Get user details before deletion
    $user_query = "SELECT full_name, role FROM users WHERE id = $user_id";
    $user_result = $conn->query($user_query);
    $user = $user_result->fetch_assoc();
    
    // Delete related records first
    $conn->query("DELETE FROM student_profiles WHERE user_id = $user_id");
    $conn->query("DELETE FROM clearance_applications WHERE user_id = $user_id");
    $conn->query("DELETE FROM department_approvals WHERE user_id = $user_id");
    $conn->query("DELETE FROM approval_history WHERE user_id = $user_id");
    $conn->query("DELETE FROM graduation_list WHERE user_id = $user_id");
    $conn->query("DELETE FROM activity_logs WHERE user_id = $user_id");
    
    // Delete user
    $delete_query = "DELETE FROM users WHERE id = $user_id";
    
    if ($conn->query($delete_query)) {
        // Log activity
        $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                     VALUES ({$_SESSION['user_id']}, 'User Deleted', 
                     'Deleted user: {$user['full_name']} ({$user['role']})', '{$_SERVER['REMOTE_ADDR']}')";
        $conn->query($log_query);
        
        $_SESSION['success'] = "User deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete user!";
    }
} else {
    $_SESSION['error'] = "Invalid request!";
}

header("Location: user_management.php");
exit();
?>