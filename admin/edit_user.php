<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: user_management.php");
    exit();
}

$user_id = (int)$_GET['id'];

// Get user details
$user_query = "SELECT u.*, sp.program_level, sp.campus 
               FROM users u 
               LEFT JOIN student_profiles sp ON u.id = sp.user_id 
               WHERE u.id = $user_id";
$user_result = $conn->query($user_query);
$user = $user_result->fetch_assoc();

if (!$user) {
    header("Location: user_management.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $allowed_roles = ['student', 'finance', 'library', 'ict', 'dean', 'registrar', 'admin'];
    $submitted_role = $_POST['role'] ?? $user['role'];
    $role = in_array($submitted_role, $allowed_roles, true) ? $submitted_role : $user['role'];
    $role = mysqli_real_escape_string($conn, $role);
    $registration_number = mysqli_real_escape_string($conn, $_POST['registration_number']);
    
    // Check if email exists for other users
    $check_email = "SELECT id FROM users WHERE email = '$email' AND id != $user_id";
    if ($conn->query($check_email)->num_rows > 0) {
        $_SESSION['error'] = "Email already exists!";
    } else {
        $update_user = "UPDATE users SET 
                       full_name = '$full_name', 
                       email = '$email', 
                       phone = '$phone', 
                       role = '$role', 
                       registration_number = " . ($registration_number ? "'$registration_number'" : "NULL") . " 
                       WHERE id = $user_id";
        
        if ($conn->query($update_user)) {
            // Update student profile if exists
            if ($role == 'student' && !empty($registration_number)) {
                $course = mysqli_real_escape_string($conn, $_POST['course']);
                $campus = mysqli_real_escape_string($conn, $_POST['campus']);
                
                $check_profile = "SELECT user_id FROM student_profiles WHERE user_id = $user_id";
                if ($conn->query($check_profile)->num_rows > 0) {
                    $update_profile = "UPDATE student_profiles SET course = '$course', campus = '$campus' WHERE user_id = $user_id";
                    $conn->query($update_profile);
                } else {
                    $insert_profile = "INSERT INTO student_profiles (user_id, course, campus) VALUES ($user_id, '$course', '$campus')";
                    $conn->query($insert_profile);
                }
            }
            
            // Log activity
            $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                         VALUES ({$_SESSION['user_id']}, 'User Updated', 'Updated user: $full_name ($role)', '{$_SERVER['REMOTE_ADDR']}')";
            $conn->query($log_query);
            
            $_SESSION['success'] = "User updated successfully!";
            header("Location: user_management.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to update user!";
        }
    }
}
?>

<div class="container mt-4 mb-5">
    <div class="dashboard-card mb-4">
        <h2>Edit User</h2>
        <p class="mb-0">Modify user information</p>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-maroon">
            <h5 class="mb-0">User Information</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email Address *</label>
                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Role *</label>
                        <select class="form-control" name="role" id="roleSelect" required onchange="toggleStudentFields()" <?php echo ($user_id == $_SESSION['user_id']) ? 'disabled' : ''; ?>>
                            <option value="student" <?php echo $user['role'] == 'student' ? 'selected' : ''; ?>>Student</option>
                            <option value="finance" <?php echo $user['role'] == 'finance' ? 'selected' : ''; ?>>Finance Officer</option>
                            <option value="library" <?php echo $user['role'] == 'library' ? 'selected' : ''; ?>>Library Officer</option>
                            <option value="ict" <?php echo $user['role'] == 'ict' ? 'selected' : ''; ?>>ICT Officer</option>
                            <option value="dean" <?php echo $user['role'] == 'dean' ? 'selected' : ''; ?>>Faculty Dean</option>
                            <option value="registrar" <?php echo $user['role'] == 'registrar' ? 'selected' : ''; ?>>Academic Registrar</option>
                            <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Administrator</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Registration Number</label>
                        <input type="text" class="form-control" name="registration_number" value="<?php echo htmlspecialchars($user['registration_number'] ?? ''); ?>" id="regNumber">
                        <small class="text-muted">Required for students</small>
                    </div>
                </div>

                <div id="studentFields" style="display: <?php echo $user['role'] == 'student' ? 'block' : 'none'; ?>;">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Course/Programme</label>
                            <input type="text" class="form-control" name="course" value="<?php echo htmlspecialchars($user['course'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Campus</label>
                            <select class="form-control" name="campus">
                                <option value="">Select Campus</option>
                                <option value="Nkozi" <?php echo $user['campus'] == 'Nkozi' ? 'selected' : ''; ?>>Nkozi</option>
                                <option value="Rubaga" <?php echo $user['campus'] == 'Rubaga' ? 'selected' : ''; ?>>Rubaga</option>
                                <option value="Masaka" <?php echo $user['campus'] == 'Masaka' ? 'selected' : ''; ?>>Masaka</option>
                                <option value="Ngetta" <?php echo $user['campus'] == 'Ngetta' ? 'selected' : ''; ?>>Ngetta</option>
                                <option value="Fortportal" <?php echo $user['campus'] == 'Fortportal' ? 'selected' : ''; ?>>Fort Portal</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-maroon">Update User</button>
                <a href="user_management.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script>
function toggleStudentFields() {
    const role = document.getElementById('roleSelect').value;
    const studentFields = document.getElementById('studentFields');
    
    if (role === 'student') {
        studentFields.style.display = 'block';
    } else {
        studentFields.style.display = 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?>