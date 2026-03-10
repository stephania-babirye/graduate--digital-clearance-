<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['photo'])) {
    $user_id = $_SESSION['user_id'];
    $file = $_FILES['photo'];
    
    // Validate file
    $allowed_types = ['image/jpeg', 'image/png'];
    $max_size = 2097152; // 2MB
    
    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['error'] = "Only JPG and PNG files are allowed!";
        header("Location: dashboard.php");
        exit();
    }
    
    if ($file['size'] > $max_size) {
        $_SESSION['error'] = "File size must be less than 2MB!";
        header("Location: dashboard.php");
        exit();
    }
    
    // Create upload directory if not exists
    $upload_dir = '../uploads/photos/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'student_' . $user_id . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Delete old photo if exists
    $old_photo_query = "SELECT photo_path FROM student_profiles WHERE user_id = $user_id";
    $old_photo_result = $conn->query($old_photo_query);
    if ($old_photo_result && $old_photo_result->num_rows > 0) {
        $old_photo = $old_photo_result->fetch_assoc()['photo_path'];
        if ($old_photo && file_exists('../' . $old_photo)) {
            unlink('../' . $old_photo);
        }
    }
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $relative_path = 'uploads/photos/' . $filename;
        
        // Update database
        $update_query = "UPDATE student_profiles SET photo_path = '$relative_path' WHERE user_id = $user_id";
        
        if ($conn->query($update_query)) {
            $_SESSION['success'] = "Photo uploaded successfully!";
        } else {
            $_SESSION['error'] = "Failed to update database!";
        }
    } else {
        $_SESSION['error'] = "Failed to upload photo!";
    }
} else {
    $_SESSION['error'] = "Invalid request!";
}

header("Location: dashboard.php");
exit();
?>