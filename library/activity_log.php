<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

// Check if user is logged in and is a library officer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'library') {
    header("Location: ../login/index.php");
    exit();
}

// Fetch activity logs for library department
$logs_query = "SELECT ah.*, u.full_name as student_name, u.registration_number 
               FROM approval_history ah
               JOIN clearance_applications ca ON ah.application_id = ca.id
               JOIN users u ON ca.user_id = u.id
               WHERE ah.department = 'library'
               ORDER BY ah.action_date DESC";
$logs_result = $conn->query($logs_query);
?>

<div class="container mt-4 mb-5">
    <div class="dashboard-card mb-4">
        <h2>Library Activity Log</h2>
        <p class="mb-0">View all library clearance actions</p>
    </div>

    <div class="card">
        <div class="card-header bg-maroon">
            <h5 class="mb-0">Recent Activities</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Student Name</th>
                            <th>Reg. Number</th>
                            <th>Action</th>
                            <th>Officer</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($log = $logs_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('M j, Y g:i A', strtotime($log['action_date'])); ?></td>
                            <td><strong><?php echo htmlspecialchars($log['student_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($log['registration_number']); ?></td>
                            <td>
                                <?php if ($log['action'] == 'approved'): ?>
                                    <span class="badge bg-success"><i class="fas fa-check-circle"></i> Approved</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($log['officer_name']); ?></td>
                            <td><?php echo htmlspecialchars($log['reason'] ?? '-'); ?></td>
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