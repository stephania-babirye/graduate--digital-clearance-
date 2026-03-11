<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $password = $_POST['password'];
    $registration_number = mysqli_real_escape_string($conn, $_POST['registration_number']);
    
    // Validate student email contains @stud
    if ($role == 'student' && strpos($email, '@stud') === false) {
        $_SESSION['error'] = "Student email must contain '@stud'!";
    }
    // Check if email exists
    elseif ($conn->query("SELECT id FROM users WHERE email = '$email'")->num_rows > 0) {
        $_SESSION['error'] = "Email already exists!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $insert_user = "INSERT INTO users (full_name, email, phone, password, role, registration_number, is_active) 
                       VALUES ('$full_name', '$email', '$phone', '$hashed_password', '$role', " . 
                       ($registration_number ? "'$registration_number'" : "NULL") . ", 1)";
        
        if ($conn->query($insert_user)) {
            $user_id = $conn->insert_id;
            
            // If student, create profile
            if ($role == 'student' && !empty($registration_number)) {
                $course = mysqli_real_escape_string($conn, $_POST['course']);
                $campus = mysqli_real_escape_string($conn, $_POST['campus']);
                
                $insert_profile = "INSERT INTO student_profiles (user_id, course, campus) 
                                  VALUES ($user_id, '$course', '$campus')";
                $conn->query($insert_profile);
            }
            
            // Log activity
            $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                         VALUES ({$_SESSION['user_id']}, 'User Created', 'Created user: $full_name ($role)', '{$_SERVER['REMOTE_ADDR']}')";
            $conn->query($log_query);
            
            $role_names = [
                'student' => 'Student',
                'finance' => 'Finance Officer',
                'library' => 'Library Officer',
                'ict' => 'ICT Officer',
                'dean' => 'Faculty Dean',
                'registrar' => 'Academic Registrar',
                'admin' => 'System Administrator'
            ];
            
            $_SESSION['success'] = "<i class='fas fa-check-circle'></i> " . $role_names[$role] . " account created successfully for " . $full_name . "!";
            header("Location: user_management.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to create user!";
        }
    }
}
?>

<div class="container mt-4 mb-5">
    <div class="dashboard-card mb-4">
        <h2>Create New User</h2>
        <p class="mb-0">
            Add any type of user to the system - Students, Officers, or Administrators
            <a href="ADMIN_USER_CREATION_GUIDE.md" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                <i class="fas fa-book"></i> View Creation Guide
            </a>
        </p>
    </div>

    <!-- User Type Info Alert -->
    <div class="alert alert-info alert-dismissible fade show">
        <h6 class="alert-heading"><strong><i class="fas fa-users"></i> Available User Types:</strong></h6>
        <div class="row mt-3">
            <div class="col-md-6">
                <ul class="mb-0">
                    <li><strong>Student</strong> - Apply for clearance, track status</li>
                    <li><strong>Finance Officer</strong> - Approve/reject financial clearance</li>
                    <li><strong>Library Officer</strong> - Manage library clearances</li>
                    <li><strong>ICT Officer</strong> - Handle equipment clearances</li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="mb-0">
                    <li><strong>Faculty Dean</strong> - Academic approvals & dissertations</li>
                    <li><strong>Academic Registrar</strong> - Final approval & graduation list</li>
                    <li><strong>Administrator</strong> - Full system access & user management</li>
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-maroon">
            <h5 class="mb-0">User Information - All Roles Available</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email Address *</label>
                        <input type="email" class="form-control" name="email" required>
                        <small class="text-muted"><i class="fas fa-exclamation-triangle"></i> <strong>Students only:</strong> Email must contain '@stud'</small><br>
                        <small class="text-muted"><i class="fas fa-envelope"></i> <strong>Officers/Admins:</strong> Any valid email address</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" name="phone">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><strong>User Role *</strong></label>
                        <select class="form-control" name="role" id="roleSelect" required onchange="toggleStudentFields()">
                            <option value="">-- Select User Role --</option>
                            <optgroup label="Student Role">
                                <option value="student">Student</option>
                            </optgroup>
                            <optgroup label="Officer Roles">
                                <option value="finance">Finance Officer</option>
                                <option value="library">Library Officer</option>
                                <option value="ict">ICT Officer</option>
                                <option value="dean">Faculty Dean</option>
                                <option value="registrar">Academic Registrar</option>
                            </optgroup>
                            <optgroup label="Administrator Role">
                                <option value="admin">System Administrator</option>
                            </optgroup>
                        </select>
                        <small class="text-muted" id="roleDescription">Select a role to see description</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Registration Number</label>
                        <input type="text" class="form-control" name="registration_number" id="regNumber">
                        <small class="text-muted"><strong>Required for students only</strong></small>
                    </div>
                </div>

                <div id="studentFields" style="display: none;">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Course/Programme</label>
                            <input type="text" class="form-control" name="course">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Campus</label>
                            <select class="form-control" name="campus">
                                <option value="">Select Campus</option>
                                <option value="Nkozi">Nkozi</option>
                                <option value="Rubaga">Rubaga</option>
                                <option value="Masaka">Masaka</option>
                                <option value="Ngetta">Ngetta</option>
                                <option value="Fortportal">Fort Portal</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-maroon btn-lg">
                    <i class="bi bi-person-plus"></i> Create User Account
                </button>
                <a href="user_management.php" class="btn btn-secondary btn-lg">Cancel</a>
            </form>
        </div>
    </div>

    <!-- Quick Reference Card -->
    <div class="card mt-3 border-info">
        <div class="card-body">
            <h6 class="card-title text-info"><strong><i class="fas fa-lightbulb"></i> Quick Reference - Default Credentials</strong></h6>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Officer Accounts:</strong> Default password = <code>officer123</code></p>
                    <p class="mb-1"><strong>Student Accounts:</strong> Custom password (user should change on first login)</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Email Format:</strong></p>
                    <ul class="mb-0">
                        <li>Students: <code>name@stud.umu.ac.ug</code> <i class="fas fa-check text-success"></i></li>
                        <li>Others: <code>name@umu.ac.ug</code> or any valid email <i class="fas fa-check text-success"></i></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleStudentFields() {
    const role = document.getElementById('roleSelect').value;
    const studentFields = document.getElementById('studentFields');
    const roleDescription = document.getElementById('roleDescription');
    const regNumber = document.getElementById('regNumber');
    
    // Role descriptions
    const descriptions = {
        'student': '<i class="fas fa-user-graduate"></i> Student accounts can apply for clearance and track their graduation status',
        'finance': '<i class="fas fa-dollar-sign"></i> Finance officers approve/reject financial clearances',
        'library': '<i class="fas fa-book"></i> Library officers manage book returns and library clearances',
        'ict': '<i class="fas fa-laptop"></i> ICT officers handle equipment returns and technical clearances',
        'dean': '<i class="fas fa-graduation-cap"></i> Faculty deans approve academic results and dissertations',
        'registrar': '<i class="fas fa-clipboard-list"></i> Academic Registrar gives final approval and manages graduation list',
        'admin': '<i class="fas fa-cog"></i> System administrators have full access to all system functions'
    };
    
    // Update description
    if (role && descriptions[role]) {
        roleDescription.innerHTML = '<strong class="text-success"><i class="fas fa-check"></i></strong> ' + descriptions[role];
    } else {
        roleDescription.textContent = 'Select a role to see description';
    }
    
    // Toggle student-specific fields
    if (role === 'student') {
        studentFields.style.display = 'block';
        regNumber.required = true;
    } else {
        studentFields.style.display = 'none';
        regNumber.required = false;
    }
}
</script>

<?php include '../includes/footer.php'; ?>