<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: user_management.php');
    exit();
}

$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$assigned_role = trim($_POST['assigned_role'] ?? '');
$allowed_roles = ['student', 'finance', 'library', 'ict', 'dean', 'registrar', 'admin'];

if ($user_id <= 0 || !in_array($assigned_role, $allowed_roles, true)) {
    $_SESSION['error'] = 'Invalid role assignment request.';
    header('Location: user_management.php');
    exit();
}

$check_user_sql = "SELECT id, full_name, role FROM users WHERE id = $user_id LIMIT 1";
$check_user_result = $conn->query($check_user_sql);

if (!$check_user_result || $check_user_result->num_rows === 0) {
    $_SESSION['error'] = 'User not found.';
    header('Location: user_management.php');
    exit();
}

$user = $check_user_result->fetch_assoc();

if ($user['role'] !== 'staff') {
    $_SESSION['error'] = 'Only pending staff accounts can be assigned from this button. Use Edit for other users.';
    header('Location: user_management.php');
    exit();
}

if ($user_id === (int)$_SESSION['user_id']) {
    $_SESSION['error'] = 'You cannot reassign your own account from this action.';
    header('Location: user_management.php');
    exit();
}

$safe_role = mysqli_real_escape_string($conn, $assigned_role);
$update_sql = "UPDATE users SET role = '$safe_role', is_active = 1 WHERE id = $user_id";

if ($conn->query($update_sql)) {
    $safe_name = mysqli_real_escape_string($conn, $user['full_name']);
    $log_sql = "INSERT INTO activity_logs (user_id, action, description, ip_address)
                VALUES ({$_SESSION['user_id']}, 'Role Assigned', 'Assigned role $safe_role to $safe_name', '{$_SERVER['REMOTE_ADDR']}')";
    $conn->query($log_sql);

    $_SESSION['success'] = "Role assigned successfully to {$user['full_name']}.";
} else {
    $_SESSION['error'] = 'Failed to assign role. Please try again.';
}

header('Location: user_management.php');
exit();
?>