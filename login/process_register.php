<?php
session_start();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $reg_number = mysqli_real_escape_string($conn, $_POST['reg_number']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $specific_program = mysqli_real_escape_string($conn, $_POST['specific_program']);
    $campus = mysqli_real_escape_string($conn, $_POST['campus']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Store full program in program_level field
    $program_level = $specific_program;

    // Validate passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
        header("Location: register.php");
        exit();
    }

    // Validate student email contains @stud
    if (strpos($email, '@stud') === false) {
        $_SESSION['error'] = "Student email must contain '@stud' (e.g., yourname@stud.umu.ac.ug)";
        header("Location: register.php");
        exit();
    }

    // Check if email already exists
    $check_email = "SELECT id FROM users WHERE email = '$email'";
    $result = $conn->query($check_email);
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email already registered!";
        header("Location: register.php");
        exit();
    }

    // Check if registration number already exists
    $check_reg = "SELECT id FROM users WHERE registration_number = '$reg_number'";
    $result = $conn->query($check_reg);
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Registration number already exists!";
        header("Location: register.php");
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $sql = "INSERT INTO users (full_name, registration_number, email, phone, password, role, created_at) 
            VALUES ('$full_name', '$reg_number', '$email', '$phone', '$hashed_password', 'student', NOW())";

    if ($conn->query($sql) === TRUE) {
        $user_id = $conn->insert_id;

        // Insert student profile
        $profile_sql = "INSERT INTO student_profiles (user_id, program_level, campus, created_at) 
                       VALUES ('$user_id', '$program_level', '$campus', NOW())";
        $conn->query($profile_sql);

        $_SESSION['success'] = "Registration successful! Please login.";
        header("Location: index.php");
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: register.php");
    }
}
?>