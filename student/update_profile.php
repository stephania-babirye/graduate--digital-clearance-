<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $phone_raw = trim($_POST['phone'] ?? '');
    $specific_program = mysqli_real_escape_string($conn, $_POST['specific_program']);
    
    // Store the full program in program_level field
    $program_level = $specific_program;
    
    $campus = mysqli_real_escape_string($conn, $_POST['campus']);
    $date_of_birth_raw = trim($_POST['date_of_birth'] ?? '');
    $year_of_intake_raw = trim($_POST['year_of_intake'] ?? '');

    // Validate phone: digits only.
    if ($phone_raw === '' || !preg_match('/^\d+$/', $phone_raw)) {
        $_SESSION['error'] = 'Phone number must contain digits only.';
        header('Location: dashboard.php');
        exit();
    }

    // Validate date of birth format and minimum age (17 years).
    $dob = DateTime::createFromFormat('Y-m-d', $date_of_birth_raw);
    $dob_errors = DateTime::getLastErrors();
    $dob_warning_count = is_array($dob_errors) ? (int) $dob_errors['warning_count'] : 0;
    $dob_error_count = is_array($dob_errors) ? (int) $dob_errors['error_count'] : 0;
    if (!$dob || $dob_warning_count > 0 || $dob_error_count > 0 || $dob->format('Y-m-d') !== $date_of_birth_raw) {
        $_SESSION['error'] = 'Invalid date of birth format.';
        header('Location: dashboard.php');
        exit();
    }

    $today = new DateTime('today');
    $age = $dob->diff($today)->y;
    if ($dob > $today || $age < 17) {
        $_SESSION['error'] = 'You must be at least 17 years old.';
        header('Location: dashboard.php');
        exit();
    }

    // Validate year of intake: digits only, between current year and current year - 10.
    if (!preg_match('/^\d{4}$/', $year_of_intake_raw)) {
        $_SESSION['error'] = 'Year of intake must be a 4-digit year.';
        header('Location: dashboard.php');
        exit();
    }

    $current_year = (int) date('Y');
    $min_year = $current_year - 10;
    $year_of_intake_int = (int) $year_of_intake_raw;
    if ($year_of_intake_int < $min_year || $year_of_intake_int > $current_year) {
        $_SESSION['error'] = 'Year of intake must be between ' . $min_year . ' and ' . $current_year . '.';
        header('Location: dashboard.php');
        exit();
    }

    $phone = mysqli_real_escape_string($conn, $phone_raw);
    $date_of_birth = mysqli_real_escape_string($conn, $date_of_birth_raw);
    $year_of_intake = mysqli_real_escape_string($conn, (string) $year_of_intake_int);
    
    // Update users table
    $update_user = "UPDATE users SET phone = '$phone' WHERE id = $user_id";
    $conn->query($update_user);
    
    // Check if student profile exists
    $check_profile = "SELECT id FROM student_profiles WHERE user_id = $user_id";
    $check_result = $conn->query($check_profile);
    
    if ($check_result && $check_result->num_rows > 0) {
        // Update existing profile
        $update_profile = "UPDATE student_profiles SET 
                          program_level = '$program_level',
                          campus = '$campus',
                          date_of_birth = '$date_of_birth',
                          year_of_intake = '$year_of_intake',
                          profile_completed = 1
                          WHERE user_id = $user_id";
        
        if ($conn->query($update_profile)) {
            $_SESSION['success'] = "Profile updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update profile: " . $conn->error;
        }
    } else {
        // Create new profile
        $insert_profile = "INSERT INTO student_profiles (user_id, program_level, campus, date_of_birth, year_of_intake, profile_completed) 
                          VALUES ($user_id, '$program_level', '$campus', '$date_of_birth', '$year_of_intake', 1)";
        
        if ($conn->query($insert_profile)) {
            $_SESSION['success'] = "Profile created successfully!";
        } else {
            $_SESSION['error'] = "Failed to create profile: " . $conn->error;
        }
    }
} else {
    $_SESSION['error'] = "Invalid request!";
}

header("Location: dashboard.php");
exit();
?>