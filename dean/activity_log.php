<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

// Check if user is logged in and is a dean
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dean') {
    header("Location: ../login/index.php");
    exit();
}

// Fetch activity logs for faculty department
$logs_query = "SELECT ah.*, u.full_name as student_name, u.registration_number 
               FROM approval_history ah
               JOIN clearance_applications ca ON ah.application_id = ca.id
               JOIN users u ON ca.user_id = u.id
               WHERE ah.department = 'faculty'
               ORDER BY ah.action_date DESC";
$logs_result = $conn->query($logs_query);
?>

<div class="container mt-4 mb-5">
    <div class="dashboard-card mb-4">
        <h2>Faculty Activity Log</h2>
        <p class="mb-0">Complete history of all faculty clearance actions with officer details, date, and time</p>
    </div>

    <div class="card">
        <div class="card-header bg-maroon">
            <h5 class="mb-0">Activity History</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Officer Name</th>
                            <th>Student Name</th>
                            <th>Reg. Number</th>
                            <th>Action</th>
                            <th>Reason/Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($log = $logs_result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo date('M j, Y', strtotime($log['action_date'])); ?></strong></td>
                            <td><?php echo date('g:i A', strtotime($log['action_date'])); ?></td>
                            <td><span class="badge bg-info"><?php echo htmlspecialchars($log['officer_name']); ?></span></td>
                            <td><?php echo htmlspecialchars($log['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($log['registration_number']); ?></td>
                            <td>
                                <?php if ($log['action'] == 'approved'): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Rejected</span>
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