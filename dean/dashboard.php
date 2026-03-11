<?php
session_start();
include '../config/db.php';
include '../config/officer_departments.php';
include '../includes/header.php';

// Check if user is logged in and is a dean
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dean') {
    header("Location: ../login/index.php");
    exit();
}

$officer_id = $_SESSION['user_id'];
$officer_name = $_SESSION['full_name'];
$officer_email = $_SESSION['email'];

// Get dean's faculty/department from configuration or session
$dean_faculty = get_dean_faculty($officer_email, $officer_id);
$_SESSION['department_name'] = $dean_faculty; // Store in session for reuse

// Fetch all clearance applications where ICT is approved
$applications_query = "SELECT ca.*, u.full_name, u.registration_number, 
                       sp.program_level
                       FROM clearance_applications ca
                       JOIN users u ON ca.user_id = u.id
                       JOIN student_profiles sp ON u.id = sp.user_id
                       WHERE ca.ict_status = 'approved'
                       ORDER BY ca.applied_at DESC";
$applications_result = $conn->query($applications_query);

// Get statistics
$pending_count = $conn->query("SELECT COUNT(*) as count FROM clearance_applications WHERE faculty_status = 'pending' AND ict_status = 'approved'")->fetch_assoc()['count'];
$approved_count = $conn->query("SELECT COUNT(*) as count FROM clearance_applications WHERE faculty_status = 'approved'")->fetch_assoc()['count'];
$rejected_count = $conn->query("SELECT COUNT(*) as count FROM clearance_applications WHERE faculty_status = 'rejected'")->fetch_assoc()['count'];
?>

<div class="container mt-4 mb-5">
    <div class="dashboard-card mb-4">
        <h2>Faculty Dean Dashboard</h2>
        <p class="mb-0">Welcome, <?php echo htmlspecialchars($officer_name); ?> | <?php echo htmlspecialchars($dean_faculty); ?></p>
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
            <div class="stat-card stat-card-gold">
                <div class="stat-icon bg-warning"><i class="fas fa-clock"></i></div>
                <div class="stat-details">
                    <h3 class="stat-number-gold"><?php echo $pending_count; ?></h3>
                    <p>Pending Applications</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-card-green">
                <div class="stat-icon bg-success"><i class="fas fa-check-circle"></i></div>
                <div class="stat-details">
                    <h3 class="stat-number-green"><?php echo $approved_count; ?></h3>
                    <p>Approved</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-card-blue">
                <div class="stat-icon bg-danger"><i class="fas fa-times-circle"></i></div>
                <div class="stat-details">
                    <h3 class="stat-number-blue"><?php echo $rejected_count; ?></h3>
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
                            <th>Registration Number</th>
                            <th>Student Name</th>
                            <th>Programme</th>
                            <th>Faculty Name</th>
                            <th>Results Confirmed</th>
                            <th>Dissertation Approved</th>
                            <th>Faculty Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($app = $applications_result->fetch_assoc()): 
                            // Fetch faculty approval details for this application
                            $faculty_details_query = "SELECT results_confirmed, dissertation_approved, faculty_name 
                                                     FROM department_approvals 
                                                     WHERE application_id = {$app['id']} AND department = 'faculty' 
                                                     LIMIT 1";
                            $faculty_details_result = $conn->query($faculty_details_query);
                            $faculty_details = $faculty_details_result && $faculty_details_result->num_rows > 0 
                                              ? $faculty_details_result->fetch_assoc() 
                                              : ['results_confirmed' => 'pending', 'dissertation_approved' => 'pending', 'faculty_name' => ''];
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($app['registration_number']); ?></strong></td>
                            <td><?php echo htmlspecialchars($app['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($app['program_level'] ?? 'N/A'); ?></td>
                            <td>
                                <?php 
                                // Display faculty name if available from department approvals
                                $facultyDisplay = !empty($faculty_details['faculty_name']) ? $faculty_details['faculty_name'] : 'Not Set';
                                echo htmlspecialchars($facultyDisplay);
                                ?>
                            </td>
                            <td>
                                <?php 
                                if ($faculty_details['results_confirmed'] == 'yes') {
                                    echo '<span class="badge bg-success">Yes</span>';
                                } elseif ($faculty_details['results_confirmed'] == 'no') {
                                    echo '<span class="badge bg-danger">No</span>';
                                } else {
                                    echo '<span class="badge bg-secondary"><i class="fas fa-clock"></i> Pending</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                if ($faculty_details['dissertation_approved'] == 'yes') {
                                    echo '<span class="badge bg-success">Yes</span>';
                                } elseif ($faculty_details['dissertation_approved'] == 'no') {
                                    echo '<span class="badge bg-danger">No</span>';
                                } else {
                                    echo '<span class="badge bg-secondary"><i class="fas fa-clock"></i> Pending</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($app['faculty_status'] == 'pending'): ?>
                                    <span class="badge badge-pending"><i class="fas fa-clock"></i> Pending</span>
                                <?php elseif ($app['faculty_status'] == 'approved'): ?>
                                    <span class="badge badge-approved"><i class="fas fa-check-circle"></i> Approved</span>
                                <?php else: ?>
                                    <span class="badge badge-rejected"><i class="fas fa-times-circle"></i> Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($app['faculty_status'] == 'pending'): ?>
                                    <button class="btn btn-sm btn-success mb-1" onclick="showApproveModal(<?php echo $app['id']; ?>, '<?php echo htmlspecialchars($app['full_name']); ?>', '<?php echo htmlspecialchars($app['registration_number']); ?>', '<?php echo htmlspecialchars($app['program_level'] ?? 'N/A'); ?>')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="btn btn-sm btn-danger mb-1" onclick="showRejectModal(<?php echo $app['id']; ?>, '<?php echo htmlspecialchars($app['full_name']); ?>', '<?php echo htmlspecialchars($app['registration_number']); ?>')">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-info mb-1" onclick="viewDetails(<?php echo $app['id']; ?>, '<?php echo htmlspecialchars($app['full_name']); ?>', '<?php echo htmlspecialchars($app['registration_number']); ?>', '<?php echo htmlspecialchars($app['program_level'] ?? 'N/A'); ?>')">
                                        <i class="bi bi-eye"></i> View
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-maroon">
                <h5 class="modal-title">Faculty Approval Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Student Name:</strong>
                    <p id="modalName"></p>
                </div>
                <div class="mb-3">
                    <strong>Registration Number:</strong>
                    <p id="modalRegNo"></p>
                </div>
                <div class="mb-3">
                    <strong>Programme:</strong>
                    <p id="modalCourse"></p>
                </div>
                <hr>
                <h6 class="text-maroon">Faculty Approval Status</h6>
                <p><strong>Results Confirmed:</strong> <span id="modalResults"><i class="fas fa-clock"></i> Pending</span></p>
                <p><strong>Dissertation Approved:</strong> <span id="modalDissertation"><i class="fas fa-clock"></i> Pending</span></p>
                <div id="facultyNameDiv">
                    <strong>Faculty Name:</strong>
                    <p id="modalFacultyName" class="text-muted"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Approve Faculty Clearance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="process_action.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="application_id" id="approveAppId">
                    
                    <div class="alert alert-info">
                        <strong>Student:</strong> <span id="approveStudentName"></span><br>
                        <strong>Reg. No:</strong> <span id="approveRegNo"></span><br>
                        <strong>Programme:</strong> <span id="approveCourse"></span>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Faculty Name *</label>
                        <input type="text" class="form-control" name="faculty_name" 
                               value="<?php echo htmlspecialchars($dean_faculty); ?>" readonly
                               style="background-color: #f8f9fa;">
                        <small class="text-muted">Auto-populated based on your faculty assignment</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirm Results? *</label>
                        <select class="form-control" name="results_confirmed" required>
                            <option value="">-- Select --</option>
                            <option value="yes">Yes - Results Confirmed</option>
                            <option value="no">No - Results Not Confirmed</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Approve Dissertation? *</label>
                        <select class="form-control" name="dissertation_approved" required>
                            <option value="">-- Select --</option>
                            <option value="yes">Yes - Dissertation Approved</option>
                            <option value="no">No - Dissertation Not Approved</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Additional Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="2" 
                                  placeholder="Any additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">✅ Approve Clearance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Faculty Clearance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="process_action.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="application_id" id="rejectAppId">
                    
                    <div class="alert alert-warning">
                        <strong>Student:</strong> <span id="rejectStudentName"></span><br>
                        <strong>Reg. No:</strong> <span id="rejectRegNo"></span>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirm Results?</label>
                        <select class="form-control" name="results_confirmed">
                            <option value="pending">Pending</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Approve Dissertation?</label>
                        <select class="form-control" name="dissertation_approved">
                            <option value="pending">Pending</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Reason for Rejection *</label>
                        <select class="form-control" name="rejection_type" id="rejectionTypeSelect" required onchange="updateReasonField()">
                            <option value="">-- Select Reason --</option>
                            <option value="results_not_cleared">Results Not Cleared</option>
                            <option value="dissertation_not_approved">Dissertation Not Approved</option>
                            <option value="both">Both - Results Not Cleared & Dissertation Not Approved</option>
                            <option value="other">Other Reason</option>
                        </select>
                        <small class="text-muted">Select the primary reason for rejection</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Additional Details *</label>
                        <textarea class="form-control" name="reason" id="reasonField" rows="4" required 
                                  placeholder="Provide detailed explanation for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">❌ Reject Application</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewDetails(id, name, regNo, course) {
    // Fetch faculty approval details via AJAX
    fetch('get_faculty_details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modalName').textContent = name;
            document.getElementById('modalRegNo').textContent = regNo;
            document.getElementById('modalCourse').textContent = course;
            
            // Results confirmed status
            if (data.results_confirmed == 'yes') {
                document.getElementById('modalResults').innerHTML = '<span class="badge bg-success">Yes</span>';
            } else if (data.results_confirmed == 'no') {
                document.getElementById('modalResults').innerHTML = '<span class="badge bg-danger">No</span>';
            } else {
                document.getElementById('modalResults').innerHTML = '<span class="badge bg-secondary"><i class="fas fa-clock"></i> Pending</span>';
            }
            
            // Dissertation approved status
            if (data.dissertation_approved == 'yes') {
                document.getElementById('modalDissertation').innerHTML = '<span class="badge bg-success">Yes</span>';
            } else if (data.dissertation_approved == 'no') {
                document.getElementById('modalDissertation').innerHTML = '<span class="badge bg-danger">No</span>';
            } else {
                document.getElementById('modalDissertation').innerHTML = '<span class="badge bg-secondary"><i class="fas fa-clock"></i> Pending</span>';
            }
            
            // Faculty name
            if (data.faculty_name) {
                document.getElementById('modalFacultyName').textContent = data.faculty_name;
            } else {
                document.getElementById('modalFacultyName').textContent = 'Not specified';
            }
            
            var modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            modal.show();
        });
}

function showApproveModal(appId, name, regNo, course) {
    document.getElementById('approveAppId').value = appId;
    document.getElementById('approveStudentName').textContent = name;
    document.getElementById('approveRegNo').textContent = regNo;
    document.getElementById('approveCourse').textContent = course;
    
    var modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
}

function showRejectModal(appId, name, regNo) {
    document.getElementById('rejectAppId').value = appId;
    document.getElementById('rejectStudentName').textContent = name;
    document.getElementById('rejectRegNo').textContent = regNo;
    
    var modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

function updateReasonField() {
    var select = document.getElementById('rejectionTypeSelect');
    var field = document.getElementById('reasonField');
    
    switch(select.value) {
        case 'results_not_cleared':
            field.placeholder = 'Explain why results are not cleared...';
            break;
        case 'dissertation_not_approved':
            field.placeholder = 'Explain why dissertation is not approved...';
            break;
        case 'both':
            field.placeholder = 'Explain both issues (results and dissertation)...';
            break;
        case 'other':
            field.placeholder = 'Provide detailed explanation...';
            break;
        default:
            field.placeholder = 'Provide detailed explanation for rejection...';
    }
}
</script>

<?php include '../includes/footer.php'; ?>