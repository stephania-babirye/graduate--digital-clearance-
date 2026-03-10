<?php
session_start();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Query user
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Route to appropriate dashboard based on role
            switch ($user['role']) {
                case 'student':
                    header("Location: ../student/dashboard.php");
                    break;
                case 'finance':
                    header("Location: ../finance/dashboard.php");
                    break;
                case 'library':
                    header("Location: ../library/dashboard.php");
                    break;
                case 'ict':
                    header("Location: ../ict/dashboard.php");
                    break;
                case 'dean':
                    header("Location: ../dean/dashboard.php");
                    break;
                case 'registrar':
                    header("Location: ../registrar/dashboard.php");
                    break;
                case 'admin':
                    header("Location: ../admin/dashboard.php");
                    break;
                default:
                    $_SESSION['error'] = "Invalid role assigned!";
                    header("Location: index.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "Invalid email or password!";
            header("Location: index.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Invalid email or password!";
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>