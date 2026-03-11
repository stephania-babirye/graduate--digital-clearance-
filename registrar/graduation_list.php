<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

// Check if user is logged in and is a registrar
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registrar') {
    header("Location: ../login/index.php");
    exit();
}

// Fetch graduation list
$grad_list_query = "SELECT gl.*, u.full_name, u.registration_number, u.email, 
                    sp.program_level, sp.course, sp.campus
                    FROM graduation_list gl
                    JOIN users u ON gl.user_id = u.id
                    JOIN student_profiles sp ON u.id = sp.user_id
                    ORDER BY gl.added_at DESC";
$grad_list_result = $conn->query($grad_list_query);

// Set default graduation year
$graduation_year = 2026;
?>

<div class="container mt-4 mb-5">
    <div class="dashboard-card mb-4">
        <h2><i class="fas fa-graduation-cap"></i> Graduation List</h2>
        <p class="mb-0">Students cleared for graduation</p>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        <a href="generate_graduation_pdf.php" class="btn btn-maroon"><i class="fas fa-file-pdf"></i> Generate PDF</a>
    </div>

    <div class="card">
        <div class="card-header bg-maroon">
            <h5 class="mb-0"><i class="fas fa-users"></i> Confirmed Graduates</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Reg. Number</th>
                            <th>Full Name</th>
                            <th>Course</th>
                            <th>Campus</th>
                            <th>Graduation Year</th>
                            <th>Added Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        while ($grad = $grad_list_result->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><strong><?php echo htmlspecialchars($grad['registration_number']); ?></strong></td>
                            <td><?php echo htmlspecialchars($grad['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($grad['course'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($grad['campus'] ?? 'N/A'); ?></td>
                            <td><span class="badge bg-primary"><i class="fas fa-calendar-alt"></i> <?php echo $graduation_year; ?></span></td>
                            <td><?php echo date('M j, Y', strtotime($grad['added_at'])); ?></td>
                            <td>
                                <?php if ($grad['confirmation_status'] == 'confirmed'): ?>
                                    <span class="badge bg-success"><i class="fas fa-check-circle"></i> Confirmed</span>
                                <?php else: ?>
                                    <span class="badge bg-warning"><i class="fas fa-clock"></i> Not Confirmed</span>
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

<?php include '../includes/footer.php'; ?>