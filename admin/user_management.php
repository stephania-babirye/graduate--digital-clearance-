<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php");
    exit();
}

// Fetch all users
$users_query = "SELECT u.*, sp.program_level, sp.campus 
                FROM users u 
                LEFT JOIN student_profiles sp ON u.id = sp.user_id 
                ORDER BY CASE WHEN u.role = 'staff' THEN 0 ELSE 1 END, u.created_at DESC";
$users_result = $conn->query($users_query);
?>

<div class="container mt-4 mb-5">
    <div class="dashboard-card mb-4">
        <h2>User Management</h2>
        <p class="mb-0">Manage all system users - Students, Officers, and Administrators</p>
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

    <!-- Quick Action Buttons -->
    <div class="card mb-4 bg-light">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-2"><i class="fas fa-users"></i> Add New Users to the System</h5>
                    <p class="mb-0 text-muted">Create accounts for Students, Officers (Finance, Library, ICT, Dean, Registrar), or Administrators</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="create_user.php" class="btn btn-maroon btn-lg">
                        <i class="bi bi-person-plus"></i> Create New User
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-maroon d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All System Users</h5>
            <span class="badge bg-light text-dark">Total: <?php echo $users_result->num_rows; ?> users</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Identifier</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge <?php 
                                    echo $user['role'] == 'staff' ? 'bg-warning text-dark' :
                                        ($user['role'] == 'admin' ? 'bg-danger' : 
                                        ($user['role'] == 'registrar' ? 'bg-success' : 
                                        ($user['role'] == 'student' ? 'bg-secondary' : 'bg-primary')));
                                ?>">
                                    <?php echo $user['role'] === 'staff' ? 'Pending Assignment' : ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $identifier = trim((string)($user['registration_number'] ?? ''));
                                if ($identifier === '') {
                                    echo '-';
                                } elseif ($user['role'] === 'student') {
                                    echo 'REG: ' . htmlspecialchars($identifier);
                                } else {
                                    echo 'STAFF ID: ' . htmlspecialchars($identifier);
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                <?php if ($user['role'] === 'staff'): ?>
                                    <form method="POST" action="assign_role.php" class="d-inline-flex align-items-center ms-1">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                        <select name="assigned_role" class="form-select form-select-sm me-1 role-assign-select" style="border: 2px solid #800000 !important; background: #fff2e6 !important;" required>
                                            <option value="">Select Role</option>
                                            <option value="finance">Finance</option>
                                            <option value="library">Library</option>
                                            <option value="ict">ICT</option>
                                            <option value="dean">Dean</option>
                                            <option value="registrar">Registrar</option>
                                            <option value="admin">System Admin</option>
                                            <option value="student">Student</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-success">Assign</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <?php if ($user['is_active']): ?>
                                        <a href="toggle_user_status.php?id=<?php echo $user['id']; ?>&action=deactivate" 
                                           class="btn btn-sm btn-warning" 
                                           onclick="return confirm('Deactivate this user?')">Deactivate</a>
                                    <?php else: ?>
                                        <a href="toggle_user_status.php?id=<?php echo $user['id']; ?>&action=activate" 
                                           class="btn btn-sm btn-success">Activate</a>
                                    <?php endif; ?>
                                    <a href="delete_user.php?id=<?php echo $user['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Delete this user permanently?')">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-center">
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>