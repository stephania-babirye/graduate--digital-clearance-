<?php
session_start();
include '../config/db.php';

// Check if user is logged in and is a registrar
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registrar') {
    header("Location: ../login/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $application_id = (int)$_POST['application_id'];
    $graduation_year = isset($_POST['graduation_year']) ? (int)$_POST['graduation_year'] : 2026;
    $notes = isset($_POST['notes']) ? mysqli_real_escape_string($conn, $_POST['notes']) : '';
    
    // Get application details
    $app_query = "SELECT ca.*, u.id as user_id, u.full_name, u.registration_number 
                 FROM clearance_applications ca
                 JOIN users u ON ca.user_id = u.id
                 WHERE ca.id = $application_id AND ca.registrar_status = 'approved' 
                 AND ca.finance_status = 'approved' AND ca.library_status = 'approved' 
                 AND ca.ict_status = 'approved' AND ca.faculty_status = 'approved'";
    $app = $conn->query($app_query)->fetch_assoc();
    
    if ($app) {
        // Check if already in graduation list
        $check_grad = "SELECT id FROM graduation_list WHERE application_id = $application_id";
        if ($conn->query($check_grad)->num_rows == 0) {
            // Add to graduation list with notes
            $insert_query = "INSERT INTO graduation_list (user_id, application_id, confirmed_by, confirmation_status, notes, confirmed_at) 
                           VALUES ({$app['user_id']}, $application_id, {$_SESSION['user_id']}, 'confirmed', '$notes', NOW())";
            
            if ($conn->query($insert_query)) {
                // Log activity
                $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                             VALUES ({$_SESSION['user_id']}, 'Added to Graduation List', 
                             'Added {$app['full_name']} (Reg: {$app['registration_number']}) to graduation list for year $graduation_year', '{$_SERVER['REMOTE_ADDR']}')";
                $conn->query($log_query);
                
                $_SESSION['success'] = "✅ Student successfully added to graduation list for year $graduation_year!";
            } else {
                $_SESSION['error'] = "Failed to add to graduation list!";
            }
        } else {
            $_SESSION['error'] = "Student already in graduation list!";
        }
    } else {
        $_SESSION['error'] = "Application not found or not fully approved!";
    }
}

header("Location: dashboard.php");
exit();
?>