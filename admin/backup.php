<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php");
    exit();
}

$backup_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_backup'])) {
    $timestamp = date('Y-m-d_H-i-s');
    $backup_file = "graduation_clearance_backup_$timestamp.sql";
    $backup_path = "../backups/$backup_file";
    
    // Create backups directory if it doesn't exist
    if (!file_exists('../backups')) {
        mkdir('../backups', 0777, true);
    }
    
    // Database credentials
    $host = 'localhost:3307';
    $user = 'root';
    $pass = '';
    $db = 'graduation_clearance';
    
    // Build mysqldump command
    $command = "mysqldump --host=$host --user=$user --password=$pass $db > $backup_path 2>&1";
    
    exec($command, $output, $result);
    
    if ($result === 0 && file_exists($backup_path)) {
        // Log activity
        $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
                     VALUES ({$_SESSION['user_id']}, 'Database Backup', 
                     'Created database backup: $backup_file', '{$_SERVER['REMOTE_ADDR']}')";
        $conn->query($log_query);
        
        $backup_message = "<div class='alert alert-success'>Backup created successfully: <strong>$backup_file</strong></div>";
    } else {
        $backup_message = "<div class='alert alert-danger'>Failed to create backup. Error: " . implode("\n", $output) . "</div>";
    }
}

// Get list of existing backups
$backups = [];
if (file_exists('../backups')) {
    $files = scandir('../backups');
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
            $backups[] = [
                'name' => $file,
                'size' => filesize("../backups/$file"),
                'date' => filemtime("../backups/$file")
            ];
        }
    }
    // Sort by date descending
    usort($backups, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}
?>

<div class="container mt-4 mb-5">
    <div class="dashboard-card mb-4">
        <h2>Database Backup & Restore</h2>
        <p class="mb-0">Create and manage database backups</p>
    </div>

    <?php echo $backup_message; ?>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-maroon">
                    <h5 class="mb-0">Create New Backup</h5>
                </div>
                <div class="card-body">
                    <p>Create a complete backup of the graduation clearance database.</p>
                    <form method="POST" action="">
                        <button type="submit" name="create_backup" class="btn btn-maroon" 
                                onclick="return confirm('Create database backup now?')">
                            📦 Create Backup
                        </button>
                    </form>
                    <hr>
                    <small class="text-muted">
                        <strong>Note:</strong> Backups are stored in the <code>/backups</code> directory.
                        Make sure to download and store backups in a secure location.
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Backup Information</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><strong>📊 Total Backups:</strong> <?php echo count($backups); ?></li>
                        <li><strong>💾 Total Size:</strong> <?php echo number_format(array_sum(array_column($backups, 'size')) / 1024 / 1024, 2); ?> MB</li>
                        <li><strong>📅 Last Backup:</strong> <?php echo count($backups) > 0 ? date('M j, Y g:i A', $backups[0]['date']) : 'None'; ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-maroon">
            <h5 class="mb-0">Available Backups</h5>
        </div>
        <div class="card-body">
            <?php if (count($backups) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Backup File</th>
                            <th>Size</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($backup['name']); ?></code></td>
                            <td><?php echo number_format($backup['size'] / 1024, 2); ?> KB</td>
                            <td><?php echo date('M j, Y g:i A', $backup['date']); ?></td>
                            <td>
                                <a href="download_backup.php?file=<?php echo urlencode($backup['name']); ?>" 
                                   class="btn btn-sm btn-primary">📥 Download</a>
                                <a href="delete_backup.php?file=<?php echo urlencode($backup['name']); ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Delete this backup?')">🗑️ Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-center text-muted">No backups available. Create your first backup above.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="alert alert-warning mt-4">
        <strong>⚠️ Important Security Notes:</strong>
        <ul class="mb-0">
            <li>Regularly download and store backups in a secure, off-site location</li>
            <li>Test backup restoration periodically to ensure data integrity</li>
            <li>Delete old backups to save server space</li>
            <li>Never share backup files as they contain sensitive data</li>
        </ul>
    </div>

    <div class="text-center mt-4">
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>