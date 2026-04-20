<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

// Check if user is logged in and is a registrar
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registrar') {
    header("Location: ../login/index.php");
    exit();
}

// Fetch all clearance applications
$applications_query = "SELECT ca.*, u.full_name, u.registration_number, u.email, u.phone, 
                       sp.program_level, sp.campus
                       FROM clearance_applications ca
                       JOIN users u ON ca.user_id = u.id
                       JOIN student_profiles sp ON u.id = sp.user_id
                       ORDER BY ca.applied_at DESC";
$applications_result = $conn->query($applications_query);

// Get statistics
$total_applications = $conn->query("SELECT COUNT(*) as count FROM clearance_applications")->fetch_assoc()['count'];
$ready_for_approval = $conn->query("SELECT COUNT(*) as count FROM clearance_applications WHERE finance_status = 'approved' AND library_status = 'approved' AND ict_status = 'approved' AND faculty_status = 'approved' AND registrar_status = 'pending'")->fetch_assoc()['count'];
$final_approved = $conn->query("SELECT COUNT(*) as count FROM clearance_applications WHERE registrar_status = 'approved' AND finance_status = 'approved' AND library_status = 'approved' AND ict_status = 'approved' AND faculty_status = 'approved'")->fetch_assoc()['count'];
$graduation_list_count = $conn->query("SELECT COUNT(*) as count FROM graduation_list")->fetch_assoc()['count'];
?>

<div class="container mt-4 mb-5">
    <div class="dashboard-card mb-4">
        <h2>Academic Registrar Dashboard</h2>
        <p class="mb-0">Master clearance management and graduation list</p>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card stat-card-maroon">
                <div class="stat-icon bg-primary"><i class="fas fa-chart-bar"></i></div>
                <div class="stat-details">
                    <h3 class="stat-number-maroon"><?php echo $total_applications; ?></h3>
                    <p>Total Applications</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-card-gold">
                <div class="stat-icon bg-warning"><i class="fas fa-clock"></i></div>
                <div class="stat-details">
                    <h3 class="stat-number-gold"><?php echo $ready_for_approval; ?></h3>
                    <p>Ready for Approval</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-card-green">
                <div class="stat-icon bg-success"><i class="fas fa-check-circle"></i></div>
                <div class="stat-details">
                    <h3 class="stat-number-green"><?php echo $final_approved; ?></h3>
                    <p>Final Approved</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-card-blue">
                <div class="stat-icon bg-info"><i class="fas fa-graduation-cap"></i></div>
                <div class="stat-details">
                    <h3 class="stat-number-blue"><?php echo $graduation_list_count; ?></h3>
                    <p>Graduation List</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <a href="graduation_list.php" class="btn btn-maroon"><i class="fas fa-clipboard-list"></i> View Graduation List</a>
        <a href="generate_graduation_pdf.php" class="btn btn-gold"><i class="fas fa-file-pdf"></i> Generate PDF</a>
    </div>

    <!-- Applications Table -->
    <div class="card">
        <div class="card-header bg-maroon d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Master Clearance Approval Table</h5>
            <small class="text-white">
                <span class="badge bg-success"><i class="fas fa-check-circle"></i> Approved</span>
                <span class="badge badge-pending"><i class="fas fa-clock"></i> Pending</span>
                <span class="badge badge-rejected"><i class="fas fa-times-circle"></i> Rejected</span>
            </small>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-sm">
                    <thead>
                        <tr>
                            <th rowspan="2" class="align-middle text-center">Student<br><small>(Name / Reg)</small></th>
                            <th colspan="3" class="text-center">Department Approvals</th>
                            <th rowspan="2" class="align-middle text-center">Final Status</th>
                            <th rowspan="2" class="align-middle text-center">Actions</th>
                        </tr>
                        <tr>
                            <th class="text-center">Finance</th>
                            <th class="text-center">Resources<br><small>(Library + ICT)</small></th>
                            <th class="text-center">Faculty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Reset result pointer
                        $applications_result = $conn->query($applications_query);
                        while ($app = $applications_result->fetch_assoc()): 
                            // Check if Library AND ICT are both approved for Resources column
                            $resources_approved = ($app['library_status'] == 'approved' && $app['ict_status'] == 'approved');
                            $resources_rejected = ($app['library_status'] == 'rejected' || $app['ict_status'] == 'rejected');
                            
                            // Check if all departments are approved
                            $all_approved = ($app['finance_status'] == 'approved' && 
                                           $resources_approved && 
                                           $app['faculty_status'] == 'approved');
                            
                            // Determine resources status
                            if ($resources_approved) {
                                $resources_status = 'approved';
                                $resources_icon = '<i class="fas fa-check-circle"></i>';
                                $resources_badge = 'bg-success';
                            } elseif ($resources_rejected) {
                                $resources_status = 'rejected';
                                $resources_icon = '<i class="fas fa-times-circle"></i>';
                                $resources_badge = 'badge-rejected';
                            } else {
                                $resources_status = 'pending';
                                $resources_icon = '<i class="fas fa-clock"></i>';
                                $resources_badge = 'badge-pending';
                            }
                        ?>
                        <tr class="<?php echo $all_approved && $app['registrar_status'] == 'pending' ? 'table-warning' : ''; ?>">
                            <td>
                                <strong><?php echo htmlspecialchars($app['full_name']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($app['registration_number']); ?></small><br>
                                <small class="text-info"><?php echo htmlspecialchars($app['program_level'] ?? 'N/A'); ?></small>
                            </td>
                            <!-- Finance Column -->
                            <td class="text-center">
                                <?php if ($app['finance_status'] == 'approved'): ?>
                                    <span class="badge bg-success"><i class="fas fa-check-circle"></i></span>
                                <?php elseif ($app['finance_status'] == 'rejected'): ?>
                                    <span class="badge badge-rejected"><i class="fas fa-times-circle"></i></span>
                                <?php else: ?>
                                    <span class="badge badge-pending"><i class="fas fa-clock"></i></span>
                                <?php endif; ?>
                            </td>
                            <!-- Resources Column (Library + ICT) -->
                            <td class="text-center">
                                <span class="badge <?php echo $resources_badge; ?>"><?php echo $resources_icon; ?></span>
                                <br>
                                <small class="text-muted d-block">
                                    Library: <?php echo $app['library_status'] == 'approved' ? '<i class="fas fa-check text-success"></i>' : ($app['library_status'] == 'rejected' ? '<i class="fas fa-times text-danger"></i>' : '<i class="far fa-circle text-warning"></i>'); ?>
                                </small>
                                <small class="text-muted d-block">
                                    ICT: <?php echo $app['ict_status'] == 'approved' ? '<i class="fas fa-check text-success"></i>' : ($app['ict_status'] == 'rejected' ? '<i class="fas fa-times text-danger"></i>' : '<i class="far fa-circle text-warning"></i>'); ?>
                                </small>
                            </td>
                            <!-- Faculty Column -->
                            <td class="text-center">
                                <?php if ($app['faculty_status'] == 'approved'): ?>
                                    <span class="badge bg-success"><i class="fas fa-check-circle"></i></span>
                                <?php elseif ($app['faculty_status'] == 'rejected'): ?>
                                    <span class="badge badge-rejected"><i class="fas fa-times-circle"></i></span>
                                <?php else: ?>
                                    <span class="badge badge-pending"><i class="fas fa-clock"></i></span>
                                <?php endif; ?>
                            </td>
                            <!-- Final Status Column -->
                            <td class="text-center">
                                <?php if ($app['registrar_status'] == 'approved'): ?>
                                    <span class="badge badge-approved fs-6"><i class="fas fa-check-circle"></i> Approved</span>
                                <?php else: ?>
                                    <span class="badge badge-pending fs-6"><i class="fas fa-clock"></i> Pending</span>
                                <?php endif; ?>
                            </td>
                            <!-- Actions Column -->
                            <td class="text-center">
                                <?php if ($all_approved && $app['registrar_status'] == 'pending'): ?>
                                    <button class="btn btn-sm btn-success mb-1" onclick="approveApplication(<?php echo $app['id']; ?>, '<?php echo htmlspecialchars($app['full_name']); ?>')">
                                        <i class="bi bi-check-circle"></i> Final Approve
                                    </button>
                                <?php elseif ($app['registrar_status'] == 'approved'): ?>
                                    <?php
                                    // Check if already in graduation list
                                    $check_grad = $conn->query("SELECT id FROM graduation_list WHERE application_id = {$app['id']}");
                                    if ($check_grad->num_rows == 0):
                                    ?>
                                        <button class="btn btn-sm btn-primary mb-1" onclick="showAddToGraduationModal(<?php echo $app['id']; ?>, '<?php echo htmlspecialchars($app['full_name']); ?>', '<?php echo htmlspecialchars($app['registration_number']); ?>')">
                                            <i class="bi bi-mortarboard"></i> Add to Graduation List
                                        </button>
                                    <?php else: ?>
                                        <span class="badge bg-info fs-6"><i class="fas fa-graduation-cap"></i> In Graduation List</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <small class="text-muted">Waiting for approvals...</small>
                                    <?php if (!$all_approved): ?>
                                        <br><small class="text-danger">
                                            <?php 
                                            $pending = [];
                                            if ($app['finance_status'] != 'approved') $pending[] = 'Finance';
                                            if (!$resources_approved) $pending[] = 'Resources';
                                            if ($app['faculty_status'] != 'approved') $pending[] = 'Faculty';
                                            echo implode(', ', $pending);
                                            ?>
                                        </small>
                                    <?php endif; ?>
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

<!-- Add to Graduation List Confirmation Modal -->
<div class="modal fade" id="addToGraduationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-graduation-cap"></i> Confirm Graduation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="add_to_graduation_list.php">
                <div class="modal-body">
                    <input type="hidden" name="application_id" id="gradAppId">
                    
                    <div class="alert alert-success">
                        <h6 class="alert-heading"><i class="fas fa-check-circle"></i> All Approvals Confirmed</h6>
                        <hr>
                        <p class="mb-0"><strong>Student:</strong> <span id="gradStudentName"></span></p>
                        <p class="mb-0"><strong>Reg. No:</strong> <span id="gradRegNo"></span></p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <strong>⚠️ Important:</strong><br>
                        By adding this student to the graduation list, you confirm that:
                        <ul class="mb-0 mt-2">
                            <li>All department clearances are verified</li>
                            <li>Student is eligible for graduation</li>
                            <li>This action will be logged and audited</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Graduation Year</label>
                        <input type="number" class="form-control" name="graduation_year" value="2026" min="2020" max="2030" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Additional Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="Any remarks or special notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Confirm & Add to Graduation List</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function approveApplication(appId, studentName) {
    if (confirm('Give final approval for ' + studentName + '?\n\nThis confirms all department clearances are complete.')) {
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

function showAddToGraduationModal(appId, studentName, regNo) {
    document.getElementById('gradAppId').value = appId;
    document.getElementById('gradStudentName').textContent = studentName;
    document.getElementById('gradRegNo').textContent = regNo;
    
    var modal = new bootstrap.Modal(document.getElementById('addToGraduationModal'));
    modal.show();
}

// Legacy function for backward compatibility
function addToGraduationList(appId) {
    showAddToGraduationModal(appId, 'Student', 'N/A');
}
</script>

<?php include '../includes/footer.php'; ?>