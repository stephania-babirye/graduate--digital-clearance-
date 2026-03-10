<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Check if already applied
    $check_query = "SELECT id FROM clearance_applications WHERE user_id = $user_id";
    $check_result = $conn->query($check_query);
    
    if (!$check_result) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: dashboard.php");
        exit();
    }
    
    if ($check_result->num_rows > 0) {
        $_SESSION['error'] = "You have already applied for clearance!";
        header("Location: dashboard.php");
        exit();
    }
    
    // Check profile completion
    $profile_query = "SELECT * FROM student_profiles WHERE user_id = $user_id";
    $profile_result = $conn->query($profile_query);
    
    if (!$profile_result) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: dashboard.php");
        exit();
    }
    
    $profile = $profile_result->fetch_assoc();
    
    if (!$profile || !$profile['program_level'] || !$profile['campus'] || !$profile['date_of_birth'] || 
        !$profile['year_of_intake'] || !$profile['photo_path']) {
        $_SESSION['error'] = "Please complete your profile before applying for clearance!";
        header("Location: dashboard.php");
        exit();
    }
    
    // Insert clearance application
    $insert_query = "INSERT INTO clearance_applications (user_id, application_status, applied_at) 
                    VALUES ($user_id, 'pending', NOW())";
    
    if ($conn->query($insert_query)) {
        $application_id = $conn->insert_id;
        
        // Create department approval records
        $departments = ['finance', 'library', 'ict', 'faculty', 'registrar'];
        $all_success = true;
        foreach ($departments as $dept) {
            $dept_query = "INSERT INTO department_approvals (application_id, department, status) 
                          VALUES ($application_id, '$dept', 'pending')";
            if (!$conn->query($dept_query)) {
                $all_success = false;
                $_SESSION['error'] = "Error creating department approvals: " . $conn->error;
                break;
            }
        }
        
        if ($all_success) {
            $_SESSION['success'] = "Clearance application submitted successfully! Your application is now pending review by all departments.";
        }
    } else {
        $_SESSION['error'] = "Failed to submit clearance application: " . $conn->error;
    }
} else {
    $_SESSION['error'] = "Invalid request!";
}

header("Location: dashboard.php");
exit();
?>