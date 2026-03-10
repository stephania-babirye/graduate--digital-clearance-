<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php");
    exit();
}

// Fetch all activity logs
$logs_query = "SELECT al.*, u.full_name, u.role, u.email 
               FROM activity_logs al 
               LEFT JOIN users u ON al.user_id = u.id 
               ORDER BY al.created_at DESC 
               LIMIT 100";
$logs_result = $conn->query($logs_query);
?>

<div class="container mt-4 mb-5">
    <div class="dashboard-card mb-4">
        <h2>System Activity Logs</h2>
        <p class="mb-0">View all system activities and user actions</p>
    </div>

    <div class="card">
        <div class="card-header bg-maroon d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Activity (Last 100 Records)</h5>
            <a href="clear_logs.php" class="btn btn-danger btn-sm" onclick="return confirm('Clear all activity logs?')">Clear All Logs</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date & Time</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($log = $logs_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $log['id']; ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?></td>
                            <td><strong><?php echo htmlspecialchars($log['full_name'] ?? 'System'); ?></strong></td>
                            <td>
                                <span class="badge <?php 
                                    echo $log['role'] == 'admin' ? 'bg-danger' : 
                                        ($log['role'] == 'registrar' ? 'bg-success' : 
                                        ($log['role'] == 'student' ? 'bg-secondary' : 'bg-primary'));
                                ?>">
                                    <?php echo ucfirst($log['role'] ?? 'N/A'); ?>
                                </span>
                            </td>
                            <td><strong><?php echo htmlspecialchars($log['action']); ?></strong></td>
                            <td><?php echo htmlspecialchars($log['description']); ?></td>
                            <td><code><?php echo htmlspecialchars($log['ip_address']); ?></code></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>