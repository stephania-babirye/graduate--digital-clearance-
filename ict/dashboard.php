<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

// Check if user is logged in and is an ICT officer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ict') {
    header("Location: ../login/index.php");
    exit();
}

// Fetch all clearance applications where library is approved
$applications_query = "SELECT ca.*, u.full_name, u.registration_number
                       FROM clearance_applications ca
                       JOIN users u ON ca.user_id = u.id
                       WHERE ca.library_status = 'approved'
                       ORDER BY ca.applied_at DESC";
$applications_result = $conn->query($applications_query);

// Get statistics
$pending_count = $conn->query("SELECT COUNT(*) as count FROM clearance_applications WHERE ict_status = 'pending' AND library_status = 'approved'")->fetch_assoc()['count'];
$approved_count = $conn->query("SELECT COUNT(*) as count FROM clearance_applications WHERE ict_status = 'approved'")->fetch_assoc()['count'];
$rejected_count = $conn->query("SELECT COUNT(*) as count FROM clearance_applications WHERE ict_status = 'rejected'")->fetch_assoc()['count'];
?>

<div class="container mt-4 mb-5">
    <div class="dashboard-card mb-4">
        <h2>ICT Officer Dashboard</h2>
        <p class="mb-0">Manage student ICT equipment clearance</p>
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
                            <th>Laptop Returned</th>
                            <th>Spoilt/Damaged Equipment</th>
                            <th>ICT Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($app = $applications_result->fetch_assoc()): 
                            // Fetch ICT equipment details for this application
                            $ict_details_query = "SELECT laptop_returned, equipment_damaged, damage_description, equipment_notes 
                                                 FROM department_approvals 
                                                 WHERE application_id = {$app['id']} AND department = 'ict' 
                                                 LIMIT 1";
                            $ict_details_result = $conn->query($ict_details_query);
                            $ict_details = $ict_details_result && $ict_details_result->num_rows > 0 
                                          ? $ict_details_result->fetch_assoc() 
                                          : ['laptop_returned' => 'n/a', 'equipment_damaged' => 'n/a', 'damage_description' => '', 'equipment_notes' => ''];
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($app['registration_number']); ?></strong></td>
                            <td><?php echo htmlspecialchars($app['full_name']); ?></td>
                            <td>
                                <?php 
                                if ($ict_details['laptop_returned'] == 'yes') {
                                    echo '<span class="badge bg-success">Yes</span>';
                                } elseif ($ict_details['laptop_returned'] == 'no') {
                                    echo '<span class="badge bg-danger">No</span>';
                                } else {
                                    echo '<span class="badge bg-secondary"><i class="fas fa-clock"></i> Pending</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                if ($ict_details['equipment_damaged'] == 'yes') {
                                    echo '<span class="badge bg-danger">Yes</span>';
                                    if (!empty($ict_details['damage_description'])) {
                                        echo '<br><small class="text-muted">' . htmlspecialchars(substr($ict_details['damage_description'], 0, 50)) . '...</small>';
                                    }
                                } elseif ($ict_details['equipment_damaged'] == 'no') {
                                    echo '<span class="badge bg-success">No</span>';
                                } else {
                                    echo '<span class="badge bg-secondary"><i class="fas fa-clock"></i> Pending</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($app['ict_status'] == 'pending'): ?>
                                    <span class="badge badge-pending"><i class="fas fa-clock"></i> Pending</span>
                                <?php elseif ($app['ict_status'] == 'approved'): ?>
                                    <span class="badge badge-approved"><i class="fas fa-check-circle"></i> Approved</span>
                                <?php else: ?>
                                    <span class="badge badge-rejected"><i class="fas fa-times-circle"></i> Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info mb-1" onclick="viewDetails(<?php echo $app['id']; ?>, '<?php echo htmlspecialchars($app['full_name']); ?>', '<?php echo htmlspecialchars($app['registration_number']); ?>')">
                                    <i class="bi bi-eye"></i> View Details
                                </button>
                                <?php if ($app['ict_status'] != 'approved'): ?>
                                    <button class="btn btn-sm btn-success mb-1" onclick="showApproveModal(<?php echo $app['id']; ?>, '<?php echo htmlspecialchars($app['full_name']); ?>', '<?php echo htmlspecialchars($app['registration_number']); ?>')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                <?php endif; ?>
                                <?php if ($app['ict_status'] == 'pending'): ?>
                                    <button class="btn btn-sm btn-danger mb-1" onclick="showRejectModal(<?php echo $app['id']; ?>, '<?php echo htmlspecialchars($app['full_name']); ?>', '<?php echo htmlspecialchars($app['registration_number']); ?>')">
                                        <i class="fas fa-times"></i> Reject
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
                <h5 class="modal-title">ICT Equipment Details</h5>
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
                <hr>
                <h6 class="text-maroon">ICT Equipment Status</h6>
                <p><strong>Laptop Returned:</strong> <span id="modalLaptop"><i class="fas fa-clock"></i> Pending</span></p>
                <p><strong>Equipment Damaged:</strong> <span id="modalDamaged"><i class="fas fa-clock"></i> Pending</span></p>
                <div id="damageDescDiv" style="display:none;">
                    <strong>Damage Description:</strong>
                    <p id="modalDamageDesc" class="text-danger"></p>
                </div>
                <div id="equipmentNotesDiv" style="display:none;">
                    <strong>Equipment Notes:</strong>
                    <p id="modalEquipmentNotes" class="text-muted"></p>
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
                <h5 class="modal-title">Approve ICT Clearance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="process_action.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="application_id" id="approveAppId">
                    
                    <div class="alert alert-info">
                        <strong>Student:</strong> <span id="approveStudentName"></span><br>
                        <strong>Reg. No:</strong> <span id="approveRegNo"></span>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Laptop Returned? *</label>
                        <select class="form-control" name="laptop_returned" required>
                            <option value="">-- Select --</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Any Spoilt/Damaged Equipment? *</label>
                        <select class="form-control" name="equipment_damaged" id="equipmentDamagedSelect" required onchange="toggleDamageDescription()">
                            <option value="">-- Select --</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="damageDescriptionDiv" style="display:none;">
                        <label class="form-label">Damage Description *</label>
                        <textarea class="form-control" name="damage_description" id="damageDescriptionField" rows="3" 
                                  placeholder="Describe the damaged equipment..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Additional Notes (Optional)</label>
                        <textarea class="form-control" name="equipment_notes" rows="2" 
                                  placeholder="Any additional notes about equipment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Approve Clearance</button>
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
                <h5 class="modal-title">Reject ICT Clearance</h5>
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
                        <label class="form-label">Laptop Returned?</label>
                        <select class="form-control" name="laptop_returned">
                            <option value="pending">Pending</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Any Spoilt/Damaged Equipment?</label>
                        <select class="form-control" name="equipment_damaged" id="rejectEquipmentDamagedSelect" onchange="toggleRejectDamageDescription()">
                            <option value="pending">Pending</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="rejectDamageDescriptionDiv" style="display:none;">
                        <label class="form-label">Damage Description</label>
                        <textarea class="form-control" name="damage_description" id="rejectDamageDescriptionField" rows="3" 
                                  placeholder="Describe the damaged equipment..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Reason for Rejection *</label>
                        <textarea class="form-control" name="reason" rows="4" required 
                                  placeholder="Enter detailed reason for rejection (e.g., Equipment not returned, Damaged items, Outstanding issues...)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-times"></i> Reject Application</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewDetails(id, name, regNo) {
    // Fetch equipment details via AJAX
    fetch('get_equipment_details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modalName').textContent = name;
            document.getElementById('modalRegNo').textContent = regNo;
            
            // Laptop returned status
            if (data.laptop_returned == 'yes') {
                document.getElementById('modalLaptop').innerHTML = '<span class="badge bg-success">Yes</span>';
            } else if (data.laptop_returned == 'no') {
                document.getElementById('modalLaptop').innerHTML = '<span class="badge bg-danger">No</span>';
            } else {
                document.getElementById('modalLaptop').innerHTML = '<span class="badge bg-secondary"><i class="fas fa-clock"></i> Pending</span>';
            }
            
            // Equipment damaged status
            if (data.equipment_damaged == 'yes') {
                document.getElementById('modalDamaged').innerHTML = '<span class="badge bg-danger">Yes</span>';
                if (data.damage_description) {
                    document.getElementById('damageDescDiv').style.display = 'block';
                    document.getElementById('modalDamageDesc').textContent = data.damage_description;
                }
            } else if (data.equipment_damaged == 'no') {
                document.getElementById('modalDamaged').innerHTML = '<span class="badge bg-success">No</span>';
                document.getElementById('damageDescDiv').style.display = 'none';
            } else {
                document.getElementById('modalDamaged').innerHTML = '<span class="badge bg-secondary"><i class="fas fa-clock"></i> Pending</span>';
                document.getElementById('damageDescDiv').style.display = 'none';
            }
            
            // Equipment notes
            if (data.equipment_notes) {
                document.getElementById('equipmentNotesDiv').style.display = 'block';
                document.getElementById('modalEquipmentNotes').textContent = data.equipment_notes;
            } else {
                document.getElementById('equipmentNotesDiv').style.display = 'none';
            }
            
            var modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            modal.show();
        });
}

function showApproveModal(appId, name, regNo) {
    document.getElementById('approveAppId').value = appId;
    document.getElementById('approveStudentName').textContent = name;
    document.getElementById('approveRegNo').textContent = regNo;
    
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

function toggleDamageDescription() {
    var select = document.getElementById('equipmentDamagedSelect');
    var div = document.getElementById('damageDescriptionDiv');
    var field = document.getElementById('damageDescriptionField');
    
    if (select.value == 'yes') {
        div.style.display = 'block';
        field.required = true;
    } else {
        div.style.display = 'none';
        field.required = false;
        field.value = '';
    }
}

function toggleRejectDamageDescription() {
    var select = document.getElementById('rejectEquipmentDamagedSelect');
    var div = document.getElementById('rejectDamageDescriptionDiv');
    
    if (select.value == 'yes') {
        div.style.display = 'block';
    } else {
        div.style.display = 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?>