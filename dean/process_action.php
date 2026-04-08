<?php
session_start();
include '../config/db.php';

// Check if user is logged in and is a dean
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dean') {
    header("Location: ../login/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $application_id = (int)$_POST['application_id'];
    $faculty_name = isset($_POST['faculty_name']) ? mysqli_real_escape_string($conn, $_POST['faculty_name']) : '';
    $results_confirmed = isset($_POST['results_confirmed']) ? mysqli_real_escape_string($conn, $_POST['results_confirmed']) : 'pending';
    $dissertation_approved = isset($_POST['dissertation_approved']) ? mysqli_real_escape_string($conn, $_POST['dissertation_approved']) : 'pending';
    $notes = isset($_POST['notes']) ? mysqli_real_escape_string($conn, $_POST['notes']) : '';
    
    if ($action == 'approve') {
        // Update faculty status to approved
        $update_query = "UPDATE clearance_applications 
                        SET faculty_status = 'approved', updated_at = NOW()
                        WHERE id = $application_id";
        
        if ($conn->query($update_query)) {
            // Get application details
            $app_query = "SELECT ca.*, u.full_name 
                         FROM clearance_applications ca
                         JOIN users u ON ca.user_id = u.id
                         WHERE ca.id = $application_id";
            $app = $conn->query($app_query)->fetch_assoc();
            
            // Store faculty approval details in department_approvals
            $check_approval = "SELECT id FROM department_approvals WHERE application_id = $application_id AND department = 'faculty'";
            if ($conn->query($check_approval)->num_rows > 0) {
                $approval_update = "UPDATE department_approvals 
                                   SET status = 'approved', 
                                       rejection_reason = NULL,
                                       faculty_name = '$faculty_name', 
                                       results_confirmed = '$results_confirmed', 
                                       dissertation_approved = '$dissertation_approved',
                                       notes = '$notes',
                                       approved_by = {$_SESSION['user_id']},
                                       approved_at = NOW(),
                                       updated_at = NOW()
                                   WHERE application_id = $application_id AND department = 'faculty'";
                $conn->query($approval_update);
            } else {
                $approval_insert = "INSERT INTO department_approvals 
                                   (application_id, department, status, faculty_name, results_confirmed, 
                                    dissertation_approved, notes, approved_by, approved_at) 
                                   VALUES ($application_id, 'faculty', 'approved', '$faculty_name', '$results_confirmed', 
                                           '$dissertation_approved', '$notes', {$_SESSION['user_id']}, NOW())";
                $conn->query($approval_insert);
            }
            
            // Log in approval history
            $history_query = "INSERT INTO approval_history (application_id, department, action, officer_id, officer_name) 
                             VALUES ($application_id, 'faculty', 'approved', {$_SESSION['user_id']}, '{$_SESSION['full_name']}')";
            $conn->query($history_query);
            
            // Log activity with faculty details
            $faculty_info = "Results: $results_confirmed, Dissertation: $dissertation_approved, Faculty: $faculty_name";
            
            $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                         VALUES ({$_SESSION['user_id']}, 'Faculty Clearance Approved', 
                         'Approved faculty clearance for {$app['full_name']} ($faculty_info)', '{$_SERVER['REMOTE_ADDR']}')";
            $conn->query($log_query);
            
            $_SESSION['success'] = "Application approved successfully!";
        } else {
            $_SESSION['error'] = "Failed to approve application!";
        }
        
    } elseif ($action == 'reject') {
        $reason = mysqli_real_escape_string($conn, $_POST['reason']);
        $rejection_type = isset($_POST['rejection_type']) ? mysqli_real_escape_string($conn, $_POST['rejection_type']) : '';
        
        // Update faculty status to rejected
        $update_query = "UPDATE clearance_applications 
                        SET faculty_status = 'rejected', updated_at = NOW()
                        WHERE id = $application_id";
        
        if ($conn->query($update_query)) {
            // Get application details
            $app_query = "SELECT ca.*, u.full_name 
                         FROM clearance_applications ca
                         JOIN users u ON ca.user_id = u.id
                         WHERE ca.id = $application_id";
            $app = $conn->query($app_query)->fetch_assoc();
            
            // Build rejection reason with type
            $full_reason = $reason;
            if (!empty($rejection_type)) {
                $rejection_labels = [
                    'results_not_cleared' => 'Results Not Cleared',
                    'dissertation_not_approved' => 'Dissertation Not Approved',
                    'both' => 'Results Not Cleared & Dissertation Not Approved',
                    'other' => 'Other'
                ];
                $full_reason = "[" . $rejection_labels[$rejection_type] . "] " . $reason;
            }
            
            // Store rejection reason and faculty details in department_approvals
            $check_approval = "SELECT id FROM department_approvals WHERE application_id = $application_id AND department = 'faculty'";
            if ($conn->query($check_approval)->num_rows > 0) {
                $approval_update = "UPDATE department_approvals 
                                   SET status = 'rejected', 
                                       rejection_reason = '$full_reason',
                                       results_confirmed = '$results_confirmed', 
                                       dissertation_approved = '$dissertation_approved',
                                       updated_at = NOW()
                                   WHERE application_id = $application_id AND department = 'faculty'";
                $conn->query($approval_update);
            } else {
                $approval_insert = "INSERT INTO department_approvals 
                                   (application_id, department, status, rejection_reason, results_confirmed, 
                                    dissertation_approved) 
                                   VALUES ($application_id, 'faculty', 'rejected', '$full_reason', '$results_confirmed', 
                                           '$dissertation_approved')";
                $conn->query($approval_insert);
            }
            
            // Log in approval history
            $history_query = "INSERT INTO approval_history (application_id, department, action, officer_id, officer_name, reason) 
                             VALUES ($application_id, 'faculty', 'rejected', {$_SESSION['user_id']}, '{$_SESSION['full_name']}', '$full_reason')";
            $conn->query($history_query);
            
            // Log activity with faculty details
            $faculty_info = "Results: $results_confirmed, Dissertation: $dissertation_approved";
            
            $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                         VALUES ({$_SESSION['user_id']}, 'Faculty Clearance Rejected', 
                         'Rejected faculty clearance for {$app['full_name']} - Reason: $full_reason ($faculty_info)', '{$_SERVER['REMOTE_ADDR']}')";
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