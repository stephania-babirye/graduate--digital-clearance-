<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php");
    exit();
}

// Get current settings
$settings_query = "SELECT * FROM system_settings WHERE id = 1";
$settings_result = $conn->query($settings_query);
$settings = ($settings_result && $settings_result->num_rows > 0) ? $settings_result->fetch_assoc() : null;

// Set defaults if settings not found
if (!$settings) {
    $settings = [
        'clearance_open' => 1,
        'graduation_year' => 2026,
        'university_name' => 'Uganda Martyrs University',
        'university_email' => 'info@umu.ac.ug',
        'university_phone' => '+256-414-410-611'
    ];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'toggle_clearance') {
        $new_status = $settings['clearance_open'] ? 0 : 1;
        $status_text = $new_status ? 'opened' : 'closed';
        
        $update_query = "UPDATE system_settings SET clearance_open = $new_status WHERE id = 1";
        
        if ($conn->query($update_query)) {
            // Log activity
            $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                         VALUES ({$_SESSION['user_id']}, 'Clearance Status Changed', 
                         'Clearance applications $status_text for graduation year {$settings['graduation_year']}', 
                         '{$_SERVER['REMOTE_ADDR']}')";
            $conn->query($log_query);
            
            $_SESSION['success'] = "Clearance applications $status_text successfully!";
        } else {
            $_SESSION['error'] = "Failed to update clearance status!";
        }
    } elseif ($action == 'update_year') {
        $new_year = (int)$_POST['graduation_year'];
        
        $update_query = "UPDATE system_settings SET graduation_year = $new_year WHERE id = 1";
        
        if ($conn->query($update_query)) {
            // Log activity
            $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                         VALUES ({$_SESSION['user_id']}, 'Graduation Year Updated', 
                         'Changed graduation year from {$settings['graduation_year']} to $new_year', 
                         '{$_SERVER['REMOTE_ADDR']}')";
            $conn->query($log_query);
            
            $_SESSION['success'] = "Graduation year updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update graduation year!";
        }
    } elseif ($action == 'lock_records') {
        // This would lock all clearance records - preventing edits
        $lock_query = "UPDATE clearance_applications SET locked = 1";
        
        if ($conn->query($lock_query)) {
            // Log activity
            $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                         VALUES ({$_SESSION['user_id']}, 'Records Locked', 
                         'Locked all clearance records', 
                         '{$_SERVER['REMOTE_ADDR']}')";
            $conn->query($log_query);
            
            $_SESSION['success'] = "All clearance records locked successfully!";
        } else {
            $_SESSION['error'] = "Failed to lock records!";
        }
    }
    
    // Refresh settings
    header("Location: graduation_closure.php");
    exit();
}

// Refresh settings after any changes
$settings_result = $conn->query($settings_query);
$settings = ($settings_result && $settings_result->num_rows > 0) ? $settings_result->fetch_assoc() : $settings;

// Ensure settings has required keys
if (!isset($settings['graduation_year'])) $settings['graduation_year'] = 2026;
if (!isset($settings['clearance_open'])) $settings['clearance_open'] = 1;

// Get statistics
$total_applications_query = "SELECT COUNT(*) as total FROM clearance_applications";
$total_applications = $conn->query($total_applications_query)->fetch_assoc()['total'];

$approved_applications_query = "SELECT COUNT(*) as total FROM clearance_applications WHERE registrar_status = 'approved' AND finance_status = 'approved' AND library_status = 'approved' AND ict_status = 'approved' AND faculty_status = 'approved'";
$approved_applications = $conn->query($approved_applications_query)->fetch_assoc()['total'];

$pending_applications_query = "SELECT COUNT(*) as total FROM clearance_applications WHERE registrar_status = 'pending'";
$pending_applications = $conn->query($pending_applications_query)->fetch_assoc()['total'];

$graduated_students_query = "SELECT COUNT(*) as total FROM graduation_list";
$graduated_students = $conn->query($graduated_students_query)->fetch_assoc()['total'];
?>

<div class="container mt-4 mb-5">
    <div class="dashboard-card mb-4">
        <h2>Graduation Closure Management</h2>
        <p class="mb-0">Control clearance applications and graduation processes</p>
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

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary">📊</div>
                <div class="stat-details">
                    <h3><?php echo $total_applications; ?></h3>
                    <p>Total Applications</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success">✅</div>
                <div class="stat-details">
                    <h3><?php echo $approved_applications; ?></h3>
                    <p>Approved</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning">⏳</div>
                <div class="stat-details">
                    <h3><?php echo $pending_applications; ?></h3>
                    <p>Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-info">🎓</div>
                <div class="stat-details">
                    <h3><?php echo $graduated_students; ?></h3>
                    <p>Graduated</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-maroon">
                    <h5 class="mb-0">Current Status</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label"><strong>Graduation Year:</strong></label>
                        <h3 class="text-maroon"><?php echo $settings['graduation_year']; ?></h3>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>Clearance Applications:</strong></label>
                        <h4>
                            <?php if ($settings['clearance_open']): ?>
                                <span class="badge bg-success">OPEN</span>
                            <?php else: ?>
                                <span class="badge bg-danger">CLOSED</span>
                            <?php endif; ?>
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-maroon">
                    <h5 class="mb-0">Update Graduation Year</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_year">
                        <div class="mb-3">
                            <label class="form-label">New Graduation Year</label>
                            <input type="number" class="form-control" name="graduation_year" 
                                   value="<?php echo $settings['graduation_year']; ?>" 
                                   min="2020" max="2050" required>
                        </div>
                        <button type="submit" class="btn btn-maroon" 
                                onclick="return confirm('Change graduation year? This affects all clearance applications.')">
                            Update Year
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-maroon">
            <h5 class="mb-0">Control Actions</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <h5 class="card-title">
                                <?php echo $settings['clearance_open'] ? 'Close' : 'Open'; ?> Applications
                            </h5>
                            <p class="card-text">
                                <?php if ($settings['clearance_open']): ?>
                                    Prevent new clearance applications from being submitted
                                <?php else: ?>
                                    Allow students to submit new clearance applications
                                <?php endif; ?>
                            </p>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="action" value="toggle_clearance">
                                <button type="submit" class="btn <?php echo $settings['clearance_open'] ? 'btn-danger' : 'btn-success'; ?>" 
                                        onclick="return confirm('<?php echo $settings['clearance_open'] ? 'Close' : 'Open'; ?> clearance applications?')">
                                    <?php echo $settings['clearance_open'] ? 'Close Applications' : 'Open Applications'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <h5 class="card-title">Lock All Records</h5>
                            <p class="card-text">
                                Lock all clearance records to prevent any modifications
                            </p>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="action" value="lock_records">
                                <button type="submit" class="btn btn-warning" 
                                        onclick="return confirm('Lock all clearance records? This cannot be undone!')">
                                    Lock Records
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <h5 class="card-title">System Settings</h5>
                            <p class="card-text">
                                Configure global system settings and preferences
                            </p>
                            <a href="system_settings.php" class="btn btn-info">
                                Configure Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center">
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>