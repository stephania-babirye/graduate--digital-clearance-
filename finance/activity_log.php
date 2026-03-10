<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

// Check if user is logged in and is a finance officer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'finance') {
    header("Location: ../login/index.php");
    exit();
}

$officer_id = $_SESSION['user_id'];
$officer_name = $_SESSION['full_name'];

// Fetch all activity logs for this officer
$activity_query = "SELECT ah.*, u.full_name as student_name, u.registration_number
                   FROM approval_history ah
                   JOIN clearance_applications ca ON ah.application_id = ca.id
                   JOIN users u ON ca.user_id = u.id
                   WHERE ah.department = 'finance' AND ah.officer_id = $officer_id
                   ORDER BY ah.action_date DESC";
$activity_result = $conn->query($activity_query);
?>

<div class="container mt-4 mb-5">
    <!-- Header -->
    <div class="dashboard-card mb-4">
        <h2>Activity Log - Finance Department</h2>
        <p class="mb-0">Complete history of your clearance decisions</p>
    </div>

    <div class="card">
        <div class="card-header bg-maroon">
            <h5 class="mb-0">Your Activity History</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date & Time</th>
                            <th>Student Name</th>
                            <th>Registration No.</th>
                            <th>Action</th>
                            <th>Reason/Notes</th>
                            <th>Officer Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 1;
                        if ($activity_result && $activity_result->num_rows > 0):
                            while ($activity = $activity_result->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><?php echo $count++; ?></td>
                            <td><?php echo date('M j, Y<\b\r>g:i A', strtotime($activity['action_date'])); ?></td>
                            <td><strong><?php echo htmlspecialchars($activity['student_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($activity['registration_number']); ?></td>
                            <td>
                                <span class="badge <?php echo $activity['action'] == 'approved' ? 'badge-approved' : 'badge-rejected'; ?>">
                                    <?php echo ucfirst($activity['action']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($activity['reason']); ?></td>
                            <td><?php echo htmlspecialchars($activity['officer_name']); ?></td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="7" class="text-center">No activity recorded yet</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-center">
            <a href="dashboard.php" class="btn btn-maroon">Back to Dashboard</a>
            <button onclick="window.print()" class="btn btn-secondary">Print Log</button>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>