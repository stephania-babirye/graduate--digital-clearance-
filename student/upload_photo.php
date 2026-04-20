<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['photo'])) {
    $user_id = (int) $_SESSION['user_id'];
    $file = $_FILES['photo'];
    
    // Validate file
    $allowed_types = ['image/jpeg', 'image/png'];
    $max_size = 5242880; // 5MB

    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "Upload failed. Please try again.";
        header("Location: dashboard.php");
        exit();
    }

    // Do not trust browser-reported MIME type.
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $detected_type = $finfo ? finfo_file($finfo, $file['tmp_name']) : null;
    if ($finfo) {
        finfo_close($finfo);
    }
    
    if (!$detected_type || !in_array($detected_type, $allowed_types, true)) {
        $_SESSION['error'] = "Only JPG and PNG files are allowed!";
        header("Location: dashboard.php");
        exit();
    }
    
    if ($file['size'] > $max_size) {
        $_SESSION['error'] = "File size must be less than 5MB!";
        header("Location: dashboard.php");
        exit();
    }
    
    // Create upload directory if it does not exist.
    $upload_dir = __DIR__ . '/../uploads/photos/';
    if (!is_dir($upload_dir)) {
        @mkdir($upload_dir, 0775, true);
    }

    // Ensure upload directory is writable on shared/hosted environments.
    if (!is_writable($upload_dir)) {
        @chmod($upload_dir, 0775);
    }

    if (!is_writable($upload_dir)) {
        $_SESSION['error'] = "Upload directory is not writable. Please contact admin.";
        header("Location: dashboard.php");
        exit();
    }
    
    // Generate unique filename
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($extension === 'jpeg') {
        $extension = 'jpg';
    }

    if (!in_array($extension, ['jpg', 'png'], true)) {
        $_SESSION['error'] = "Invalid file extension. Use JPG or PNG.";
        header("Location: dashboard.php");
        exit();
    }

    $filename = 'student_' . $user_id . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Delete old photo if exists
    $old_photo_query = "SELECT photo_path FROM student_profiles WHERE user_id = $user_id";
    $old_photo_result = $conn->query($old_photo_query);
    if ($old_photo_result && $old_photo_result->num_rows > 0) {
        $old_photo = $old_photo_result->fetch_assoc()['photo_path'];
        if ($old_photo) {
            $old_photo_abs = __DIR__ . '/../' . ltrim($old_photo, '/');
            if (file_exists($old_photo_abs)) {
                @unlink($old_photo_abs);
            }
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