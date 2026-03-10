<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $specific_program = mysqli_real_escape_string($conn, $_POST['specific_program']);
    
    // Store the full program in program_level field
    $program_level = $specific_program;
    
    $campus = mysqli_real_escape_string($conn, $_POST['campus']);
    $date_of_birth = mysqli_real_escape_string($conn, $_POST['date_of_birth']);
    $year_of_intake = mysqli_real_escape_string($conn, $_POST['year_of_intake']);
    
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