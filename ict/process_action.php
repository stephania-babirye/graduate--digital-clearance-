<?php
session_start();
include '../config/db.php';

// Check if user is logged in and is an ICT officer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ict') {
    header("Location: ../login/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $application_id = (int)$_POST['application_id'];
    $laptop_returned = isset($_POST['laptop_returned']) ? mysqli_real_escape_string($conn, $_POST['laptop_returned']) : 'pending';
    $equipment_damaged = isset($_POST['equipment_damaged']) ? mysqli_real_escape_string($conn, $_POST['equipment_damaged']) : 'pending';
    $damage_description = isset($_POST['damage_description']) ? mysqli_real_escape_string($conn, $_POST['damage_description']) : '';
    $equipment_notes = isset($_POST['equipment_notes']) ? mysqli_real_escape_string($conn, $_POST['equipment_notes']) : '';
    
    if ($action == 'approve') {
        // Update ICT status to approved
        $update_query = "UPDATE clearance_applications 
                        SET ict_status = 'approved', updated_at = NOW()
                        WHERE id = $application_id";
        
        if ($conn->query($update_query)) {
            // Get application details
            $app_query = "SELECT ca.*, u.full_name 
                         FROM clearance_applications ca
                         JOIN users u ON ca.user_id = u.id
                         WHERE ca.id = $application_id";
            $app = $conn->query($app_query)->fetch_assoc();
            
            // Store ICT equipment details in department_approvals
            $check_approval = "SELECT id FROM department_approvals WHERE application_id = $application_id AND department = 'ict'";
            if ($conn->query($check_approval)->num_rows > 0) {
                $approval_update = "UPDATE department_approvals 
                                   SET status = 'approved', 
                                       rejection_reason = NULL,
                                       laptop_returned = '$laptop_returned', 
                                       equipment_damaged = '$equipment_damaged', 
                                       damage_description = '$damage_description',
                                       equipment_notes = '$equipment_notes',
                                       approved_by = {$_SESSION['user_id']},
                                       approved_at = NOW(),
                                       updated_at = NOW()
                                   WHERE application_id = $application_id AND department = 'ict'";
                $conn->query($approval_update);
            } else {
                $approval_insert = "INSERT INTO department_approvals 
                                   (application_id, department, status, laptop_returned, equipment_damaged, 
                                    damage_description, equipment_notes, approved_by, approved_at) 
                                   VALUES ($application_id, 'ict', 'approved', '$laptop_returned', '$equipment_damaged', 
                                           '$damage_description', '$equipment_notes', {$_SESSION['user_id']}, NOW())";
                $conn->query($approval_insert);
            }
            
            // Log in approval history
            $history_query = "INSERT INTO approval_history (application_id, department, action, officer_id, officer_name) 
                             VALUES ($application_id, 'ict', 'approved', {$_SESSION['user_id']}, '{$_SESSION['full_name']}')";
            $conn->query($history_query);
            
            // Log activity with equipment details
            $equipment_info = "Laptop: $laptop_returned, Equipment Damaged: $equipment_damaged";
            if ($equipment_damaged == 'yes' && !empty($damage_description)) {
                $equipment_info .= ", Damage: " . substr($damage_description, 0, 100);
            }
            
            $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                         VALUES ({$_SESSION['user_id']}, 'ICT Clearance Approved', 
                         'Approved ICT clearance for {$app['full_name']} ($equipment_info)', '{$_SERVER['REMOTE_ADDR']}')";
            $conn->query($log_query);
            
            $_SESSION['success'] = "Application approved successfully!";
        } else {
            $_SESSION['error'] = "Failed to approve application!";
        }
        
    } elseif ($action == 'reject') {
        $reason = mysqli_real_escape_string($conn, $_POST['reason']);
        
        // Update ICT status to rejected
        $update_query = "UPDATE clearance_applications 
                        SET ict_status = 'rejected', updated_at = NOW()
                        WHERE id = $application_id";
        
        if ($conn->query($update_query)) {
            // Get application details
            $app_query = "SELECT ca.*, u.full_name 
                         FROM clearance_applications ca
                         JOIN users u ON ca.user_id = u.id
                         WHERE ca.id = $application_id";
            $app = $conn->query($app_query)->fetch_assoc();
            
            // Store rejection reason and equipment details in department_approvals
            $check_approval = "SELECT id FROM department_approvals WHERE application_id = $application_id AND department = 'ict'";
            if ($conn->query($check_approval)->num_rows > 0) {
                $approval_update = "UPDATE department_approvals 
                                   SET status = 'rejected', 
                                       rejection_reason = '$reason',
                                       laptop_returned = '$laptop_returned', 
                                       equipment_damaged = '$equipment_damaged', 
                                       damage_description = '$damage_description',
                                       updated_at = NOW()
                                   WHERE application_id = $application_id AND department = 'ict'";
                $conn->query($approval_update);
            } else {
                $approval_insert = "INSERT INTO department_approvals 
                                   (application_id, department, status, rejection_reason, laptop_returned, 
                                    equipment_damaged, damage_description) 
                                   VALUES ($application_id, 'ict', 'rejected', '$reason', '$laptop_returned', 
                                           '$equipment_damaged', '$damage_description')";
                $conn->query($approval_insert);
            }
            
            // Log in approval history
            $history_query = "INSERT INTO approval_history (application_id, department, action, officer_id, officer_name, reason) 
                             VALUES ($application_id, 'ict', 'rejected', {$_SESSION['user_id']}, '{$_SESSION['full_name']}', '$reason')";
            $conn->query($history_query);
            
            // Log activity with equipment details
            $equipment_info = "Laptop: $laptop_returned, Equipment Damaged: $equipment_damaged";
            if ($equipment_damaged == 'yes' && !empty($damage_description)) {
                $equipment_info .= ", Damage: " . substr($damage_description, 0, 100);
            }
            
            $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                         VALUES ({$_SESSION['user_id']}, 'ICT Clearance Rejected', 
                         'Rejected ICT clearance for {$app['full_name']} - Reason: $reason ($equipment_info)', '{$_SERVER['REMOTE_ADDR']}')";
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