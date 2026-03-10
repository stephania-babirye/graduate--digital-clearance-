<?php
session_start();
include '../config/db.php';

// Check if user is logged in and is a registrar
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registrar') {
    header("Location: ../login/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'approve') {
    $application_id = (int)$_POST['application_id'];
    
    // Verify all departments approved
    $check_query = "SELECT * FROM clearance_applications WHERE id = $application_id";
    $app = $conn->query($check_query)->fetch_assoc();
    
    if ($app['finance_status'] == 'approved' && 
        $app['library_status'] == 'approved' && 
        $app['ict_status'] == 'approved' && 
        $app['faculty_status'] == 'approved') {
        
        // Update registrar status
        $update_query = "UPDATE clearance_applications 
                        SET registrar_status = 'approved', updated_at = NOW()
                        WHERE id = $application_id";
        
        if ($conn->query($update_query)) {
            // Get application details
            $app_query = "SELECT ca.*, u.full_name 
                         FROM clearance_applications ca
                         JOIN users u ON ca.user_id = u.id
                         WHERE ca.id = $application_id";
            $app = $conn->query($app_query)->fetch_assoc();
            
            // Log in approval history
            $history_query = "INSERT INTO approval_history (application_id, department, action, officer_id, officer_name) 
                             VALUES ($application_id, 'registrar', 'approved', {$_SESSION['user_id']}, '{$_SESSION['full_name']}')";
            $conn->query($history_query);
            
            // Log activity
            $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                         VALUES ({$_SESSION['user_id']}, 'Final Clearance Approved', 
                         'Gave final approval for {$app['full_name']}', '{$_SERVER['REMOTE_ADDR']}')";
            $conn->query($log_query);
            
            $_SESSION['success'] = "Application given final approval successfully!";
        } else {
            $_SESSION['error'] = "Failed to approve application!";
        }
    } else {
        $_SESSION['error'] = "Cannot approve: Not all departments have approved!";
    }
}

header("Location: dashboard.php");
exit();
?>