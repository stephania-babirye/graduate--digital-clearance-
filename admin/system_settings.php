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

// If no settings exist, create default settings
if (!$settings) {
    $insert_query = "INSERT INTO system_settings (university_name, university_email, university_phone, graduation_year, clearance_open) 
                     VALUES ('Uganda Martyrs University', 'info@umu.ac.ug', '+256-414-410-611', 2026, 1)";
    if ($conn->query($insert_query)) {
        $settings_result = $conn->query($settings_query);
        $settings = ($settings_result && $settings_result->num_rows > 0) ? $settings_result->fetch_assoc() : [
            'university_name' => 'Uganda Martyrs University',
            'university_email' => 'info@umu.ac.ug',
            'university_phone' => '+256-414-410-611',
            'graduation_year' => 2026,
            'clearance_open' => 1
        ];
    } else {
        // If insert also fails, use defaults
        $settings = [
            'university_name' => 'Uganda Martyrs University',
            'university_email' => 'info@umu.ac.ug',
            'university_phone' => '+256-414-410-611',
            'graduation_year' => 2026,
            'clearance_open' => 1
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $university_name = mysqli_real_escape_string($conn, $_POST['university_name']);
    $university_email = mysqli_real_escape_string($conn, $_POST['university_email']);
    $university_phone = mysqli_real_escape_string($conn, $_POST['university_phone']);
    $graduation_year = (int)$_POST['graduation_year'];
    $clearance_open = isset($_POST['clearance_open']) ? 1 : 0;
    
    $update_query = "UPDATE system_settings SET 
                    university_name = '$university_name',
                    university_email = '$university_email',
                    university_phone = '$university_phone',
                    graduation_year = $graduation_year,
                    clearance_open = $clearance_open
                    WHERE id = 1";
    
    if ($conn->query($update_query)) {
        // Log activity
        $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                     VALUES ({$_SESSION['user_id']}, 'System Settings Updated', 
                     'Updated system configuration settings', '{$_SERVER['REMOTE_ADDR']}')";
        $conn->query($log_query);
        
        $_SESSION['success'] = "System settings updated successfully!";
        header("Location: system_settings.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to update system settings!";
    }
}
?>

<div class="container mt-4 mb-5">
    <div class="dashboard-card mb-4">
        <h2>System Settings</h2>
        <p class="mb-0">Configure global system preferences</p>
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

    <div class="card">
        <div class="card-header bg-maroon">
            <h5 class="mb-0">Configuration Settings</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <h6 class="text-maroon mb-3">University Information</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">University Name</label>
                        <input type="text" class="form-control" name="university_name" 
                               value="<?php echo htmlspecialchars($settings['university_name'] ?? 'Uganda Martyrs University'); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">University Email</label>
                        <input type="email" class="form-control" name="university_email" 
                               value="<?php echo htmlspecialchars($settings['university_email'] ?? 'info@umu.ac.ug'); ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">University Phone</label>
                        <input type="tel" class="form-control" name="university_phone" 
                               value="<?php echo htmlspecialchars($settings['university_phone'] ?? '+256-414-410-611'); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Graduation Year</label>
                        <input type="number" class="form-control" name="graduation_year" 
                               value="<?php echo htmlspecialchars($settings['graduation_year'] ?? '2026'); ?>" 
                               min="2020" max="2050">
                    </div>
                </div>

                <hr>

                <h6 class="text-maroon mb-3">Clearance Settings</h6>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="clearance_open" 
                               id="clearanceOpen" <?php echo (!empty($settings['clearance_open'])) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="clearanceOpen">
                            Allow New Clearance Applications
                        </label>
                    </div>
                    <small class="text-muted">When disabled, students cannot submit new clearance applications</small>
                </div>

                <hr>

                <div class="alert alert-info">
                    <strong>ℹ️ Note:</strong> These settings affect the entire system. Changes will apply immediately.
                </div>

                <button type="submit" class="btn btn-maroon">💾 Save Settings</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>