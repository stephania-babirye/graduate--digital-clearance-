<?php
session_start();
include '../config/db.php';

// Check if user is logged in and is a library officer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'library') {
    header("Location: ../login/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $application_id = (int)$_POST['application_id'];
    
    if ($action == 'approve') {
        // Update library status to approved
        $update_query = "UPDATE clearance_applications 
                        SET library_status = 'approved', updated_at = NOW()
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
                             VALUES ($application_id, 'library', 'approved', {$_SESSION['user_id']}, '{$_SESSION['full_name']}')";
            $conn->query($history_query);
            
            // Log activity
            $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                         VALUES ({$_SESSION['user_id']}, 'Library Clearance Approved', 
                         'Approved library clearance for {$app['full_name']}', '{$_SERVER['REMOTE_ADDR']}')";
            $conn->query($log_query);
            
            $_SESSION['success'] = "Application approved successfully!";
        } else {
            $_SESSION['error'] = "Failed to approve application!";
        }
        
    } elseif ($action == 'reject') {
        $reason = mysqli_real_escape_string($conn, $_POST['reason']);
        
        // Update library status to rejected
        $update_query = "UPDATE clearance_applications 
                        SET library_status = 'rejected', updated_at = NOW()
                        WHERE id = $application_id";
        
        if ($conn->query($update_query)) {
            // Get application details
            $app_query = "SELECT ca.*, u.full_name 
                         FROM clearance_applications ca
                         JOIN users u ON ca.user_id = u.id
                         WHERE ca.id = $application_id";
            $app = $conn->query($app_query)->fetch_assoc();
            
            // Log in approval history
            $history_query = "INSERT INTO approval_history (application_id, department, action, officer_id, officer_name, reason) 
                             VALUES ($application_id, 'library', 'rejected', {$_SESSION['user_id']}, '{$_SESSION['full_name']}', '$reason')";
            $conn->query($history_query);
            
            // Store rejection reason in department_approvals
            $check_approval = "SELECT id FROM department_approvals WHERE application_id = $application_id AND department = 'library'";
            if ($conn->query($check_approval)->num_rows > 0) {
                $conn->query("UPDATE department_approvals SET status = 'rejected', rejection_reason = '$reason' WHERE application_id = $application_id AND department = 'library'");
            } else {
                $conn->query("INSERT INTO department_approvals (application_id, department, status, rejection_reason) VALUES ($application_id, 'library', 'rejected', '$reason')");
            }
            
            // Log activity
            $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                         VALUES ({$_SESSION['user_id']}, 'Library Clearance Rejected', 
                         'Rejected library clearance for {$app['full_name']}: $reason', '{$_SERVER['REMOTE_ADDR']}')";
            $conn->query($log_query);
            
            $_SESSION['success'] = "Application rejected successfully!";
        } else {
            $_SESSION['error'] = "Failed to reject application!";
        }
    }
}

header("Location: dashboard.php");
exit();
?>