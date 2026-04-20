<?php
session_start();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $account_type = isset($_POST['account_type']) ? mysqli_real_escape_string($conn, $_POST['account_type']) : 'student';
    if (!in_array($account_type, ['student', 'staff'], true)) {
        $account_type = 'student';
    }
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name'] ?? ''));
    $email = strtolower(trim($_POST['email'] ?? ''));
    $email = mysqli_real_escape_string($conn, $email);
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
        header("Location: register.php");
        exit();
    }

    if (empty($full_name) || empty($email) || empty($phone)) {
        $_SESSION['error'] = "Please fill all required fields!";
        header("Location: register.php");
        exit();
    }

    if (!preg_match('/^\d+$/', $phone)) {
        $_SESSION['error'] = "Phone number must contain digits only.";
        header("Location: register.php");
        exit();
    }

    // Ensure database supports staff role and profiles for existing deployments.
    $conn->query("ALTER TABLE users MODIFY role ENUM('student', 'staff', 'finance', 'library', 'ict', 'dean', 'registrar', 'admin') NOT NULL");
    $conn->query("CREATE TABLE IF NOT EXISTS staff_profiles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(100) NOT NULL,
        campus ENUM('Nkozi', 'Rubaga', 'Masaka', 'Ngetta', 'Fortportal') NOT NULL,
        staff_id VARCHAR(50) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    $role = 'student';
    $reg_number = '';
    $campus = '';
    $program_level = '';
    $staff_title = '';

    if ($account_type === 'staff') {
        $role = 'staff';
        $staff_title = mysqli_real_escape_string($conn, trim($_POST['staff_title'] ?? ''));
        $campus = mysqli_real_escape_string($conn, trim($_POST['campus'] ?? ''));
        $staff_id = mysqli_real_escape_string($conn, trim($_POST['staff_id'] ?? ''));

        if (empty($staff_title) || empty($campus) || empty($staff_id)) {
            $_SESSION['error'] = "Please fill all required staff details!";
            header("Location: register.php");
            exit();
        }

        // Store staff ID in registration_number for compatibility with existing features.
        $reg_number = $staff_id;

        if (!preg_match('/@umu\.ac\.ug$/i', $email)) {
            $_SESSION['error'] = "Staff/office email must end with '@umu.ac.ug' (e.g., yourname@umu.ac.ug)";
            header("Location: register.php");
            exit();
        }
    } else {
        $role = 'student';
        $reg_number = mysqli_real_escape_string($conn, trim($_POST['reg_number'] ?? ''));
        $specific_program = mysqli_real_escape_string($conn, trim($_POST['specific_program'] ?? ''));
        $campus = mysqli_real_escape_string($conn, trim($_POST['campus'] ?? ''));

        // Store full program in program_level field
        $program_level = $specific_program;

        if (empty($reg_number) || empty($specific_program) || empty($campus)) {
            $_SESSION['error'] = "Please fill all required student details!";
            header("Location: register.php");
            exit();
        }

        // Validate student email contains @stud
        if (!preg_match('/@stud\.umu\.ac\.ug$/i', $email)) {
            $_SESSION['error'] = "Student email must end with '@stud.umu.ac.ug' (e.g., yourname@stud.umu.ac.ug)";
            header("Location: register.php");
            exit();
        }
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
            VALUES ('$full_name', '$reg_number', '$email', '$phone', '$hashed_password', '$role', NOW())";

    if ($conn->query($sql) === TRUE) {
        $user_id = $conn->insert_id;

        if ($role === 'student') {
            // Insert student profile
            $profile_sql = "INSERT INTO student_profiles (user_id, program_level, campus, created_at) 
                           VALUES ('$user_id', '$program_level', '$campus', NOW())";
            $conn->query($profile_sql);
        } else {
            // Insert staff profile
            $staff_profile_sql = "INSERT INTO staff_profiles (user_id, title, campus, staff_id) 
                                  VALUES ($user_id, '$staff_title', '$campus', '$reg_number')";
            $conn->query($staff_profile_sql);
        }

        if ($role === 'staff') {
            $safeName = mysqli_real_escape_string($conn, $full_name);
            $safeEmail = mysqli_real_escape_string($conn, $email);
            $requestNote = "New staff/officer registration pending role assignment: {$safeName} ({$safeEmail})";
            $adminNotifyLog = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                               VALUES ($user_id, 'Role Assignment Request', '$requestNote', '{$_SERVER['REMOTE_ADDR']}')";
            $conn->query($adminNotifyLog);

            $_SESSION['success'] = "Registration submitted. Your account is pending System Admin approval and role assignment.";
        } else {
            $_SESSION['success'] = "Registration successful! Please login.";
        }
        header("Location: index.php");
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: register.php");
    }
}
?>