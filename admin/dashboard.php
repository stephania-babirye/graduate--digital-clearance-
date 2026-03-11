<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php");
    exit();
}

$admin_name = $_SESSION['full_name'];

// Fetch statistics
$total_users_query = "SELECT COUNT(*) as count FROM users";
$total_users = $conn->query($total_users_query)->fetch_assoc()['count'];

$total_students_query = "SELECT COUNT(*) as count FROM users WHERE role = 'student'";
$total_students = $conn->query($total_students_query)->fetch_assoc()['count'];

$total_officers_query = "SELECT COUNT(*) as count FROM users WHERE role != 'student' AND role != 'admin'";
$total_officers = $conn->query($total_officers_query)->fetch_assoc()['count'];

$total_applications_query = "SELECT COUNT(*) as count FROM clearance_applications";
$total_applications = $conn->query($total_applications_query)->fetch_assoc()['count'];

$pending_applications_query = "SELECT COUNT(*) as count FROM clearance_applications WHERE registrar_status = 'pending'";
$pending_applications = $conn->query($pending_applications_query)->fetch_assoc()['count'];

// Fetch graduation settings
$settings_query = "SELECT * FROM system_settings WHERE id = 1";
$settings_result = $conn->query($settings_query);
$settings = ($settings_result && $settings_result->num_rows > 0) ? $settings_result->fetch_assoc() : null;

// Set defaults if settings not found or keys missing
$graduation_year = (isset($settings['graduation_year'])) ? $settings['graduation_year'] : 2026;
$clearance_open = (isset($settings['clearance_open'])) ? $settings['clearance_open'] : 1;

// Fetch recent activity
$recent_activity_query = "SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 10";
$recent_activity = $conn->query($recent_activity_query);

// Fetch users by role
$roles_query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
$roles_result = $conn->query($roles_query);
$role_counts = [];
while ($row = $roles_result->fetch_assoc()) {
    $role_counts[$row['role']] = $row['count'];
}
?>

<div class="container mt-4 mb-5">
    <!-- Header -->
    <div class="dashboard-card mb-4">
        <h2>System Administrator Dashboard</h2>
        <p class="mb-0">Welcome, <?php echo htmlspecialchars($admin_name); ?> | Complete system control and management</p>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card stat-card-maroon">
                <h3 class="stat-number stat-number-maroon"><?php echo $total_users; ?></h3>
                <p class="mb-0">Total Users</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-card-blue">
                <h3 class="stat-number stat-number-blue"><?php echo $total_students; ?></h3>
                <p class="mb-0">Students</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-card-green">
                <h3 class="stat-number stat-number-green"><?php echo $total_officers; ?></h3>
                <p class="mb-0">Officers</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-card-gold">
                <h3 class="stat-number stat-number-gold"><?php echo $pending_applications; ?></h3>
                <p class="mb-0">Pending Clearances</p>
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="card mb-4">
        <div class="card-header bg-maroon">
            <h5 class="mb-0">System Status</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Current Graduation Year:</strong> <?php echo $graduation_year; ?></p>
                    <p><strong>Clearance Status:</strong> 
                        <span class="badge <?php echo $clearance_open == '1' ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo $clearance_open == '1' ? 'OPEN' : 'CLOSED'; ?>
                        </span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Total Applications:</strong> <?php echo $total_applications; ?></p>
                    <p><strong>System Version:</strong> 1.0.0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-4">
        <div class="card-header bg-maroon">
            <h5 class="mb-0">User Management</h5>
        </div>
        <div class="card-body">
            <div class="row justify-content-center g-3">
                <div class="col-6 col-md-3">
                    <a href="user_management.php" class="btn btn-maroon user-action-btn">
                        <i class="bi bi-people"></i>
                        <span>Manage Users</span>
                    </a>
                    <small class="text-muted d-block mt-2 text-center">View, edit, deactivate</small>
                </div>
                <div class="col-6 col-md-3">
                    <a href="create_user.php" class="btn btn-success user-action-btn">
                        <i class="bi bi-person-plus"></i>
                        <span>Create User</span>
                    </a>
                    <small class="text-muted d-block mt-2 text-center">Add new accounts</small>
                </div>
                <div class="col-6 col-md-3">
                    <a href="password_generator.php" class="btn btn-warning user-action-btn">
                        <i class="bi bi-key"></i>
                        <span>Reset Passwords</span>
                    </a>
                    <small class="text-muted d-block mt-2 text-center">Reset credentials</small>
                </div>
                <div class="col-6 col-md-3">
                    <a href="user_management.php" class="btn btn-info user-action-btn">
                        <i class="bi bi-shield"></i>
                        <span>Role Management</span>
                    </a>
                    <small class="text-muted d-block mt-2 text-center">Manage permissions</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Users by Role -->
    <div class="card mb-4">
        <div class="card-header bg-maroon">
            <h5 class="mb-0">Users by Role (RBAC)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>User Count</th>
                            <th>Access Level</th>
                            <th>Dashboard</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Student</strong></td>
                            <td><?php echo $role_counts['student'] ?? 0; ?></td>
                            <td><span class="badge bg-secondary">User Level</span></td>
                            <td>Student Dashboard</td>
                        </tr>
                        <tr>
                            <td><strong>Finance Officer</strong></td>
                            <td><?php echo $role_counts['finance'] ?? 0; ?></td>
                            <td><span class="badge bg-primary">Department Level</span></td>
                            <td>Finance Dashboard</td>
                        </tr>
                        <tr>
                            <td><strong>Library Officer</strong></td>
                            <td><?php echo $role_counts['library'] ?? 0; ?></td>
                            <td><span class="badge bg-primary">Department Level</span></td>
                            <td>Library Dashboard</td>
                        </tr>
                        <tr>
                            <td><strong>ICT Officer</strong></td>
                            <td><?php echo $role_counts['ict'] ?? 0; ?></td>
                            <td><span class="badge bg-primary">Department Level</span></td>
                            <td>ICT Dashboard</td>
                        </tr>
                        <tr>
                            <td><strong>Faculty Dean</strong></td>
                            <td><?php echo $role_counts['dean'] ?? 0; ?></td>
                            <td><span class="badge bg-primary">Department Level</span></td>
                            <td>Dean Dashboard</td>
                        </tr>
                        <tr>
                            <td><strong>Registrar</strong></td>
                            <td><?php echo $role_counts['registrar'] ?? 0; ?></td>
                            <td><span class="badge bg-success">High Level</span></td>
                            <td>Registrar Dashboard</td>
                        </tr>
                        <tr>
                            <td><strong>Administrator</strong></td>
                            <td><?php echo $role_counts['admin'] ?? 0; ?></td>
                            <td><span class="badge bg-danger">Full Access</span></td>
                            <td>Admin Dashboard</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="alert alert-info mt-3">
                <strong>Access Rules:</strong> Each role has strict isolation. Finance cannot access Faculty records. Library cannot access Finance data. Only Administrators have full system access.
            </div>
        </div>
    </div>

    <!-- System Management -->
    <div class="card mb-4">
        <div class="card-header bg-maroon">
            <h5 class="mb-0">System Management</h5>
        </div>
        <div class="card-body">
            <div class="row justify-content-center g-3">
                <div class="col-6 col-md-3">
                    <a href="graduation_closure.php" class="btn btn-danger user-action-btn">
                        <i class="bi bi-lock"></i>
                        <span>Graduation Closure</span>
                    </a>
                    <small class="text-muted d-block mt-2 text-center">Close/Open system</small>
                </div>
                <div class="col-6 col-md-3">
                    <a href="system_settings.php" class="btn btn-secondary user-action-btn">
                        <i class="bi bi-gear"></i>
                        <span>System Settings</span>
                    </a>
                    <small class="text-muted d-block mt-2 text-center">Configure parameters</small>
                </div>
                <div class="col-6 col-md-3">
                    <a href="logs.php" class="btn btn-primary user-action-btn">
                        <i class="bi bi-file-text"></i>
                        <span>Activity Logs</span>
                    </a>
                    <small class="text-muted d-block mt-2 text-center">View audit trail</small>
                </div>
                <div class="col-6 col-md-3">
                    <a href="backup.php" class="btn btn-warning user-action-btn">
                        <i class="bi bi-database"></i>
                        <span>Backup Data</span>
                    </a>
                    <small class="text-muted d-block mt-2 text-center">Export database</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card mb-4">
        <div class="card-header bg-maroon">
            <h5 class="mb-0">Recent System Activity</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($recent_activity && $recent_activity->num_rows > 0):
                            while ($activity = $recent_activity->fetch_assoc()): 
                                $user_query = "SELECT full_name FROM users WHERE id = " . ($activity['user_id'] ?? 0);
                                $user_result = $conn->query($user_query);
                                $user_name = $user_result && $user_result->num_rows > 0 ? $user_result->fetch_assoc()['full_name'] : 'Unknown';
                        ?>
                        <tr>
                            <td><?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($user_name); ?></td>
                            <td><span class="badge bg-info"><?php echo htmlspecialchars($activity['action']); ?></span></td>
                            <td><?php echo htmlspecialchars($activity['description']); ?></td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="4" class="text-center">No recent activity</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Logout Button -->
    <div class="text-center mb-4">
        <a href="../login/logout.php" class="btn btn-outline-danger">Logout</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>