<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

// Check if user is logged in and is a library officer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'library') {
    header("Location: ../login/index.php");
    exit();
}

// Fetch all clearance applications where finance is approved
$applications_query = "SELECT ca.*, u.full_name, u.registration_number, u.email, u.phone, 
                       sp.program_level, sp.course, sp.campus
                       FROM clearance_applications ca
                       JOIN users u ON ca.user_id = u.id
                       JOIN student_profiles sp ON u.id = sp.user_id
                       WHERE ca.finance_status = 'approved'
                       ORDER BY ca.applied_at DESC";
$applications_result = $conn->query($applications_query);

// Get statistics
$pending_count = $conn->query("SELECT COUNT(*) as count FROM clearance_applications WHERE library_status = 'pending' AND finance_status = 'approved'")->fetch_assoc()['count'];
$approved_count = $conn->query("SELECT COUNT(*) as count FROM clearance_applications WHERE library_status = 'approved'")->fetch_assoc()['count'];
$rejected_count = $conn->query("SELECT COUNT(*) as count FROM clearance_applications WHERE library_status = 'rejected'")->fetch_assoc()['count'];
?>

<div class="container mt-4 mb-5">
    <div class="dashboard-card mb-4">
        <h2>Library Officer Dashboard</h2>
        <p class="mb-0">Manage student library clearance applications</p>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-warning">⏳</div>
                <div class="stat-details">
                    <h3><?php echo $pending_count; ?></h3>
                    <p>Pending Applications</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-success">✅</div>
                <div class="stat-details">
                    <h3><?php echo $approved_count; ?></h3>
                    <p>Approved</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-danger">❌</div>
                <div class="stat-details">
                    <h3><?php echo $rejected_count; ?></h3>
                    <p>Rejected</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="card">
        <div class="card-header bg-maroon d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Clearance Applications</h5>
            <a href="activity_log.php" class="btn btn-gold btn-sm">View Activity Log</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Reg. No.</th>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>Campus</th>
                            <th>Finance Status</th>
                            <th>Library Status</th>
                            <th>Applied Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($app = $applications_result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($app['registration_number']); ?></strong></td>
                            <td><?php echo htmlspecialchars($app['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($app['course'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($app['campus'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge bg-success">Approved</span>
                            </td>
                            <td>
                                <?php if ($app['library_status'] == 'pending'): ?>
                                    <span class="badge badge-pending">Pending</span>
                                <?php elseif ($app['library_status'] == 'approved'): ?>
                                    <span class="badge badge-approved">Approved</span>
                                <?php else: ?>
                                    <span class="badge badge-rejected">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($app['applied_at'])); ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="viewDetails(<?php echo $app['id']; ?>, '<?php echo htmlspecialchars($app['full_name']); ?>', '<?php echo htmlspecialchars($app['registration_number']); ?>', '<?php echo htmlspecialchars($app['email']); ?>', '<?php echo htmlspecialchars($app['phone'] ?? ''); ?>', '<?php echo htmlspecialchars($app['course'] ?? 'N/A'); ?>', '<?php echo htmlspecialchars($app['campus'] ?? 'N/A'); ?>')">
                                    View Details
                                </button>
                                <?php if ($app['library_status'] == 'pending'): ?>
                                    <button class="btn btn-sm btn-success" onclick="approveApplication(<?php echo $app['id']; ?>)">
                                        Approve
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="showRejectModal(<?php echo $app['id']; ?>)">
                                        Reject
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-maroon">
                <h5 class="modal-title">Student Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Full Name:</strong>
                        <p id="modalName"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Registration Number:</strong>
                        <p id="modalRegNo"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Email:</strong>
                        <p id="modalEmail"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Phone:</strong>
                        <p id="modalPhone"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Course:</strong>
                        <p id="modalCourse"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Campus:</strong>
                        <p id="modalCampus"></p>
                    </div>
                </div>
                <hr>
                <h6 class="text-maroon">Library Checklist</h6>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="booksReturned">
                    <label class="form-check-label" for="booksReturned">
                        All books returned
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="finesCleared">
                    <label class="form-check-label" for="finesCleared">
                        All fines cleared
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="process_action.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="application_id" id="rejectAppId">
                    <div class="mb-3">
                        <label class="form-label">Reason for Rejection *</label>
                        <textarea class="form-control" name="reason" rows="4" required 
                                  placeholder="Enter detailed reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Application</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewDetails(id, name, regNo, email, phone, course, campus) {
    document.getElementById('modalName').textContent = name;
    document.getElementById('modalRegNo').textContent = regNo;
    document.getElementById('modalEmail').textContent = email;
    document.getElementById('modalPhone').textContent = phone;
    document.getElementById('modalCourse').textContent = course;
    document.getElementById('modalCampus').textContent = campus;
    
    var modal = new bootstrap.Modal(document.getElementById('detailsModal'));
    modal.show();
}

function approveApplication(appId) {
    if (confirm('Approve this library clearance application?')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = 'process_action.php';
        
        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'approve';
        
        var appIdInput = document.createElement('input');
        appIdInput.type = 'hidden';
        appIdInput.name = 'application_id';
        appIdInput.value = appId;
        
        form.appendChild(actionInput);
        form.appendChild(appIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function showRejectModal(appId) {
    document.getElementById('rejectAppId').value = appId;
    var modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}
</script>

<?php include '../includes/footer.php'; ?>