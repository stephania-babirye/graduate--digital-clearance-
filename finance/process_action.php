<?php
session_start();
include '../config/db.php';

// Check if user is logged in and is a finance officer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'finance') {
    header("Location: ../login/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $officer_id = $_SESSION['user_id'];
    $officer_name = $_SESSION['full_name'];
    $action = mysqli_real_escape_string($conn, $_POST['action']);
    $application_id = intval($_POST['application_id']);
    
    // Get student info for the application
    $student_query = "SELECT u.full_name, u.id as user_id 
                     FROM clearance_applications ca 
                     JOIN users u ON ca.user_id = u.id 
                     WHERE ca.id = $application_id";
    $student_result = $conn->query($student_query);
    $student = $student_result->fetch_assoc();
    
    if (!$student) {
        $_SESSION['error'] = "Application not found!";
        header("Location: dashboard.php");
        exit();
    }
    
    if ($action == 'approve') {
        // Update clearance application
        $update_clearance = "UPDATE clearance_applications 
                            SET finance_status = 'approved' 
                            WHERE id = $application_id";
        
        // Update department approvals
        $update_dept = "UPDATE department_approvals 
                       SET status = 'approved', 
                           approved_by = $officer_id,
                           rejection_reason = NULL,
                           approved_at = NOW()
                       WHERE application_id = $application_id 
                       AND department = 'finance'";
        
        if ($conn->query($update_clearance) && $conn->query($update_dept)) {
            // Log activity
            $log_query = "INSERT INTO approval_history 
                         (application_id, department, action, officer_id, officer_name, reason, action_date)
                         VALUES 
                         ($application_id, 'finance', 'approved', $officer_id, '$officer_name', 'All fees cleared', NOW())";
            $conn->query($log_query);
            
            // Log to activity logs table
            $activity_log = "INSERT INTO activity_logs 
                            (user_id, action, description, ip_address, created_at)
                            VALUES 
                            ($officer_id, 'Finance Approval', 'Approved clearance for {$student['full_name']}', '{$_SERVER['REMOTE_ADDR']}', NOW())";
            $conn->query($activity_log);
            
            $_SESSION['success'] = "Successfully approved clearance for {$student['full_name']}!";
        } else {
            $_SESSION['error'] = "Failed to approve clearance!";
        }
        
    } elseif ($action == 'reject') {
        $rejection_reason = mysqli_real_escape_string($conn, $_POST['rejection_reason']);
        
        if (empty($rejection_reason)) {
            $_SESSION['error'] = "Rejection reason is required!";
            header("Location: dashboard.php");
            exit();
        }
        
        // Update clearance application
        $update_clearance = "UPDATE clearance_applications 
                            SET finance_status = 'rejected' 
                            WHERE id = $application_id";
        
        // Update department approvals
        $update_dept = "UPDATE department_approvals 
                       SET status = 'rejected', 
                           approved_by = $officer_id,
                           rejection_reason = '$rejection_reason',
                           approved_at = NOW()
                       WHERE application_id = $application_id 
                       AND department = 'finance'";
        
        if ($conn->query($update_clearance) && $conn->query($update_dept)) {
            // Log activity
            $log_query = "INSERT INTO approval_history 
                         (application_id, department, action, officer_id, officer_name, reason, action_date)
                         VALUES 
                         ($application_id, 'finance', 'rejected', $officer_id, '$officer_name', '$rejection_reason', NOW())";
            $conn->query($log_query);
            
            // Log to activity logs table
            $activity_log = "INSERT INTO activity_logs 
                            (user_id, action, description, ip_address, created_at)
                            VALUES 
                            ($officer_id, 'Finance Rejection', 'Rejected clearance for {$student['full_name']}: $rejection_reason', '{$_SERVER['REMOTE_ADDR']}', NOW())";
            $conn->query($activity_log);
            
            $_SESSION['success'] = "Successfully rejected clearance for {$student['full_name']}!";
        } else {
            $_SESSION['error'] = "Failed to reject clearance!";
        }
    } else {
        $_SESSION['error'] = "Invalid action!";
    }
} else {
    $_SESSION['error'] = "Invalid request method!";
}

header("Location: dashboard.php");
exit();
?>