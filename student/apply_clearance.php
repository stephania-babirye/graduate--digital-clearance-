<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = (int) $_SESSION['user_id'];
    
    // Check if already applied
    $check_query = "SELECT * FROM clearance_applications WHERE user_id = $user_id ORDER BY applied_at DESC LIMIT 1";
    $check_result = $conn->query($check_query);
    
    if (!$check_result) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: dashboard.php");
        exit();
    }
    
    $existing_application = $check_result->num_rows > 0 ? $check_result->fetch_assoc() : null;
    
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
    
    // If already approved, do not allow re-application.
    if ($existing_application &&
        $existing_application['finance_status'] === 'approved' &&
        $existing_application['library_status'] === 'approved' &&
        $existing_application['ict_status'] === 'approved' &&
        $existing_application['faculty_status'] === 'approved' &&
        $existing_application['registrar_status'] === 'approved') {
        $_SESSION['error'] = "Your clearance is already fully approved.";
        header("Location: dashboard.php");
        exit();
    }

    // Allow re-application only when there is at least one rejection.
    if ($existing_application) {
        $has_rejection = (
            $existing_application['finance_status'] === 'rejected' ||
            $existing_application['library_status'] === 'rejected' ||
            $existing_application['ict_status'] === 'rejected' ||
            $existing_application['faculty_status'] === 'rejected' ||
            $existing_application['registrar_status'] === 'rejected'
        );

        if (!$has_rejection) {
            $_SESSION['error'] = "You already have an active clearance application.";
            header("Location: dashboard.php");
            exit();
        }

        $application_id = (int) $existing_application['id'];

        // Reset the existing application to pending so departments can review again.
        $reset_application = "UPDATE clearance_applications
                              SET application_status = 'pending',
                                  finance_status = 'pending',
                                  library_status = 'pending',
                                  ict_status = 'pending',
                                  faculty_status = 'pending',
                                  registrar_status = 'pending',
                                  applied_at = NOW(),
                                  updated_at = NOW()
                              WHERE id = $application_id";

        if (!$conn->query($reset_application)) {
            $_SESSION['error'] = "Failed to reset your application: " . $conn->error;
            header("Location: dashboard.php");
            exit();
        }

        // Reset all department approval rows and clear rejection reasons.
        $reset_departments = "UPDATE department_approvals
                              SET status = 'pending',
                                  rejection_reason = NULL,
                                  approved_by = NULL,
                                  approved_at = NULL,
                                  updated_at = NOW()
                              WHERE application_id = $application_id";

        if (!$conn->query($reset_departments)) {
            $_SESSION['error'] = "Failed to reset department approvals: " . $conn->error;
            header("Location: dashboard.php");
            exit();
        }

        $_SESSION['success'] = "Clearance application re-submitted successfully. All departments have been reset to pending review.";
    } else {
        // First-time application
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
    }
} else {
    $_SESSION['error'] = "Invalid request!";
}

header("Location: dashboard.php");
exit();
?>