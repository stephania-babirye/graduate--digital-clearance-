<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header('Location: ../login/index.php');
    exit();
}

include '../includes/header.php';

$user_id = (int) $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

$profile_query = "SELECT title, campus, staff_id FROM staff_profiles WHERE user_id = $user_id LIMIT 1";
$profile_result = $conn->query($profile_query);
$profile = $profile_result && $profile_result->num_rows > 0 ? $profile_result->fetch_assoc() : null;
?>

<div class="container mt-4 mb-5">
    <div class="dashboard-card mb-4">
        <h2>Staff Dashboard</h2>
        <p class="mb-0">Welcome, <?php echo htmlspecialchars($full_name); ?></p>
    </div>

    <div class="card">
        <div class="card-header bg-maroon">
            <h5 class="mb-0">Profile Details</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <strong>Full Name:</strong>
                    <div><?php echo htmlspecialchars($full_name); ?></div>
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Email:</strong>
                    <div><?php echo htmlspecialchars($_SESSION['email']); ?></div>
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Title:</strong>
                    <div><?php echo htmlspecialchars($profile['title'] ?? 'N/A'); ?></div>
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Campus:</strong>
                    <div><?php echo htmlspecialchars($profile['campus'] ?? 'N/A'); ?></div>
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Staff ID:</strong>
                    <div><?php echo htmlspecialchars($profile['staff_id'] ?? 'N/A'); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
