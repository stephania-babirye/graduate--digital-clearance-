<?php
session_start();
include '../config/db.php';

// Check if user is logged in and is a finance officer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'finance') {
    header("Location: ../login/index.php");
    exit();
}

include '../includes/header.php';

$officer_id = $_SESSION['user_id'];
$officer_name = $_SESSION['full_name'];

// Fetch all clearance applications
$applications_query = "SELECT ca.id, ca.user_id, ca.finance_status, ca.applied_at,
                       u.full_name, u.registration_number, u.email,
                       sp.program_level, sp.course, sp.campus
                       FROM clearance_applications ca
                       JOIN users u ON ca.user_id = u.id
                       JOIN student_profiles sp ON u.id = sp.user_id
                       ORDER BY ca.applied_at DESC";
$applications_result = $conn->query($applications_query);

// Fetch recent activity logs for this officer
$activity_query = "SELECT * FROM approval_history 
                   WHERE department = 'finance' AND officer_id = $officer_id 
                   ORDER BY action_date DESC LIMIT 10";
$activity_result = $conn->query($activity_query);
?>

<div class="container mt-4 mb-5">
    <!-- Header -->
    <div class="dashboard-card mb-4">
        <h2>Finance Officer Dashboard</h2>
        <p class="mb-0">Welcome, <?php echo htmlspecialchars($officer_name); ?> | Review and approve student clearances</p>
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
        <?php
        $pending_count = 0;
        $approved_count = 0;
        $rejected_count = 0;
        
        $stats_query = "SELECT finance_status, COUNT(*) as count FROM clearance_applications GROUP BY finance_status";
        $stats_result = $conn->query($stats_query);
        while ($stat = $stats_result->fetch_assoc()) {
            if ($stat['finance_status'] == 'pending') $pending_count = $stat['count'];
            if ($stat['finance_status'] == 'approved') $approved_count = $stat['count'];
            if ($stat['finance_status'] == 'rejected') $rejected_count = $stat['count'];
        }
        ?>
        <div class="col-md-4">
            <div class="stat-card stat-card-gold">
                <h3 class="stat-number stat-number-gold"><?php echo $pending_count; ?></h3>
                <p class="mb-0">Pending Review</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-card-green">
                <h3 class="stat-number stat-number-green"><?php echo $approved_count; ?></h3>
                <p class="mb-0">Approved</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-card-blue">
                <h3 class="stat-number stat-number-blue"><?php echo $rejected_count; ?></h3>
                <p class="mb-0">Rejected</p>
            </div>
        </div>
    </div>

    <!-- Student Applications Table -->
    <div class="card mb-4">
        <div class="card-header bg-maroon">
            <h5 class="mb-0">Student Clearance Applications</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Registration No.</th>
                            <th>Course</th>
                            <th>Campus</th>
                            <th>Applied Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 1;
                        if ($applications_result && $applications_result->num_rows > 0):
                            while ($app = $applications_result->fetch_assoc()): 
                                $status_class = '';
                                switch($app['finance_status']) {
                                    case 'approved': $status_class = 'badge-approved'; break;
                                    case 'rejected': $status_class = 'badge-rejected'; break;
                                    default: $status_class = 'badge-pending';
                                }
                        ?>
                        <tr>
                            <td><?php echo $count++; ?></td>
                            <td><strong><?php echo htmlspecialchars($app['full_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($app['registration_number']); ?></td>
                            <td><?php echo htmlspecialchars($app['course'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($app['campus'] ?? 'N/A'); ?></td>
                            <td><?php echo date('M j, Y', strtotime($app['applied_at'])); ?></td>
                            <td>
                                <span class="badge <?php echo $status_class; ?>">
                                    <?php 
                                    if ($app['finance_status'] == 'approved') {
                                        echo '<i class="fas fa-check-circle"></i> Approved';
                                    } elseif ($app['finance_status'] == 'rejected') {
                                        echo '<i class="fas fa-times-circle"></i> Rejected';
                                    } else {
                                        echo '<i class="fas fa-clock"></i> Pending';
                                    }
                                    ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="viewDetails(<?php echo $app['id']; ?>, '<?php echo htmlspecialchars($app['full_name']); ?>', '<?php echo htmlspecialchars($app['registration_number']); ?>')">
                                    View Details
                                </button>
                                <?php if ($app['finance_status'] != 'approved'): ?>
                                <button class="btn btn-sm btn-success" onclick="approveApplication(<?php echo $app['id']; ?>, '<?php echo htmlspecialchars($app['full_name']); ?>')">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <?php endif; ?>
                                <?php if ($app['finance_status'] == 'pending'): ?>
                                <button class="btn btn-sm btn-danger" onclick="showRejectModal(<?php echo $app['id']; ?>, '<?php echo htmlspecialchars($app['full_name']); ?>')">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="8" class="text-center">No applications found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Activity Log -->
    <div class="card mb-4">
        <div class="card-header bg-maroon d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Your Recent Activity</h5>
            <a href="activity_log.php" class="btn btn-sm btn-gold">View Full Log</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Application ID</th>
                            <th>Action</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($activity_result && $activity_result->num_rows > 0):
                            while ($activity = $activity_result->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><?php echo date('M j, Y g:i A', strtotime($activity['action_date'])); ?></td>
                            <td>#<?php echo $activity['application_id']; ?></td>
                            <td>
                                <span class="badge <?php echo $activity['action'] == 'approved' ? 'badge-approved' : 'badge-rejected'; ?>">
                                    <?php echo ucfirst($activity['action']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($activity['reason'] ?? '-'); ?></td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="4" class="text-center">No activity yet</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Logout Button -->
        <!-- Duplicate Logout Button Removed -->
</div>

<!-- Student Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-maroon text-white">
                <h5 class="modal-title">Student Fee Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="studentDetails">
                    <!-- Details loaded via JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Reason Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Application</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" action="process_action.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="application_id" id="reject_app_id">
                    
                    <p>You are about to reject the clearance for: <strong id="reject_student_name"></strong></p>
                    
                    <div class="mb-3">
                        <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                        <select class="form-control mb-2" id="rejection_reason_select" onchange="handleReasonSelect()">
                            <option value="">Select a reason...</option>
                            <option value="Outstanding tuition balance">Outstanding tuition balance</option>
                            <option value="Graduation fee not paid">Graduation fee not paid</option>
                            <option value="Pending accommodation fees">Pending accommodation fees</option>
                            <option value="Outstanding library fines">Outstanding library fines</option>
                            <option value="Other">Other (specify below)</option>
                        </select>
                        
                        <textarea class="form-control" name="rejection_reason" id="rejection_reason" rows="3" 
                                  placeholder="Enter detailed reason for rejection..." required></textarea>
                        <small class="text-muted">This reason will be visible to the student.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Bootstrap modals
let detailsModal, rejectModal;

document.addEventListener('DOMContentLoaded', function() {
    detailsModal = new bootstrap.Modal(document.getElementById('detailsModal'));
    rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
});

function viewDetails(appId, studentName, regNumber) {
    document.getElementById('studentDetails').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-maroon" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    detailsModal.show();
    
    // Simulate fee details (in real system, this would be fetched from database)
    setTimeout(() => {
        document.getElementById('studentDetails').innerHTML = `
            <h5 class="text-maroon">${studentName}</h5>
            <p class="text-muted">Registration No: ${regNumber}</p>
            
            <div class="card mt-3">
                <div class="card-header">
                    <strong>Fee Payment Status</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Tuition Fees:</strong></td>
                                <td><span class="badge bg-success">Paid</span></td>
                                <td>UGX 3,000,000</td>
                            </tr>
                            <tr>
                                <td><strong>Graduation Fee:</strong></td>
                                <td><span class="badge bg-success">Paid</span></td>
                                <td>UGX 300,000</td>
                            </tr>
                            <tr>
                                <td><strong>Library Fines:</strong></td>
                                <td><span class="badge bg-success">Cleared</span></td>
                                <td>UGX 0</td>
                            </tr>
                            <tr>
                                <td><strong>Accommodation:</strong></td>
                                <td><span class="badge bg-success">Paid</span></td>
                                <td>UGX 500,000</td>
                            </tr>
                        </table>
                    </div>
                    <div class="alert alert-success mt-3">
                        <strong>✓ All fees cleared</strong>
                    </div>
                    <p class="text-muted mb-0"><small>Note: In production, this would show actual payment records from the finance system.</small></p>
                </div>
            </div>
            
            <div class="mt-3">
                <button class="btn btn-primary" onclick="window.print()">Print Receipt</button>
            </div>
        `;
    }, 500);
}

function approveApplication(appId, studentName) {
    if (confirm(`Are you sure you want to APPROVE the clearance for ${studentName}?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'process_action.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'approve';
        
        const appIdInput = document.createElement('input');
        appIdInput.type = 'hidden';
        appIdInput.name = 'application_id';
        appIdInput.value = appId;
        
        form.appendChild(actionInput);
        form.appendChild(appIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function showRejectModal(appId, studentName) {
    document.getElementById('reject_app_id').value = appId;
    document.getElementById('reject_student_name').textContent = studentName;
    document.getElementById('rejection_reason').value = '';
    document.getElementById('rejection_reason_select').value = '';
    rejectModal.show();
}

function handleReasonSelect() {
    const select = document.getElementById('rejection_reason_select');
    const textarea = document.getElementById('rejection_reason');
    
    if (select.value && select.value !== 'Other') {
        textarea.value = select.value;
    } else if (select.value === 'Other') {
        textarea.value = '';
        textarea.focus();
    }
}

// Form validation
document.getElementById('rejectForm').addEventListener('submit', function(e) {
    const reason = document.getElementById('rejection_reason').value.trim();
    if (!reason) {
        e.preventDefault();
        alert('Please provide a reason for rejection!');
        return false;
    }
});
</script>

<?php include '../includes/footer.php'; ?>