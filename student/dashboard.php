<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

// Fetch student profile
$profile_query = "SELECT sp.*, u.email, u.registration_number, u.phone 
                  FROM student_profiles sp 
                  JOIN users u ON sp.user_id = u.id 
                  WHERE u.id = $user_id";
$profile_result = $conn->query($profile_query);
$profile = $profile_result->fetch_assoc();

// Calculate profile completion
$completion = 0;
if ($profile) {
    if ($profile['program_level']) $completion += 20;
    if ($profile['campus']) $completion += 20;
    if ($profile['date_of_birth']) $completion += 20;
    if ($profile['year_of_intake']) $completion += 20;
    if ($profile['photo_path']) $completion += 20;
}
$completion = round($completion);

// Fetch clearance application status
$clearance_query = "SELECT * FROM clearance_applications WHERE user_id = $user_id ORDER BY applied_at DESC LIMIT 1";
$clearance_result = $conn->query($clearance_query);

if (!$clearance_result) {
    die("Database error: " . $conn->error);
}

$clearance = $clearance_result->num_rows > 0 ? $clearance_result->fetch_assoc() : null;

// Check if student is on graduation list
$graduation_query = "SELECT * FROM graduation_list WHERE user_id = $user_id";
$graduation_result = $conn->query($graduation_query);
$on_graduation_list = $graduation_result && $graduation_result->num_rows > 0;

$current_year = (int) date('Y');
$min_intake_year = $current_year - 10;
$max_dob = (new DateTime('today'))->modify('-17 years')->format('Y-m-d');
?>

<div class="container mt-4 mb-5">
    <!-- Welcome Section -->
    <div class="dashboard-card mb-4">
        <h2>Welcome, <?php echo htmlspecialchars($full_name); ?>!</h2>
        <p class="mb-0">Student Dashboard - Graduate Clearance System</p>
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

    <!-- Profile Completion Status -->
    <div class="card mb-4">
        <div class="card-header bg-maroon">
            <h5 class="mb-0">Profile Completion Status</h5>
        </div>
        <div class="card-body">
            <div class="progress" style="height: 30px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $completion; ?>%;" 
                     aria-valuenow="<?php echo $completion; ?>" aria-valuemin="0" aria-valuemax="100">
                    <?php echo $completion; ?>% Complete
                </div>
            </div>
            <?php if ($completion < 100): ?>
                <p class="mt-2 mb-0 text-muted">Please complete your profile before applying for clearance.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4 align-items-start profile-layout-row">
        <!-- Photo Upload Section -->
        <div class="col-12 col-lg-4">
            <div class="card mb-4 profile-photo-card">
                <div class="card-header bg-maroon">
                    <h5 class="mb-0">Graduation Photo</h5>
                </div>
                <div class="card-body text-center">
                    <?php if ($profile && $profile['photo_path']): ?>
                        <img src="../<?php echo htmlspecialchars($profile['photo_path']); ?>" 
                             alt="Student Photo" class="photo-preview img-fluid mb-3">
                        <p class="text-success">✓ Photo Uploaded</p>
                    <?php else: ?>
                        <div class="mb-3">
                            <img src="../assets/images/placeholder.png" alt="No Photo" 
                                 class="photo-preview img-fluid mb-3" 
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%23ddd%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 font-size=%2218%22 text-anchor=%22middle%22 fill=%22%23999%22 dy=%22.3em%22%3ENo Photo%3C/text%3E%3C/svg%3E'">
                        </div>
                    <?php endif; ?>
                    
                    <form action="upload_photo.php" method="POST" enctype="multipart/form-data" id="photoForm">
                        <div class="mb-3">
                            <input type="file" class="form-control" name="photo" id="photoInput" accept="image/jpeg,image/png" onchange="previewPhoto(this)" required>
                        </div>
                        <button type="submit" class="btn btn-maroon w-100">Upload Photo</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Profile Information -->
        <div class="col-12 col-lg-8">
            <div class="card mb-4 profile-info-card">
                <div class="card-header bg-maroon">
                    <h5 class="mb-0">Profile Information</h5>
                </div>
                <div class="card-body profile-info-body">
                    <form action="update_profile.php" method="POST">
                        <div class="row g-3 profile-form-grid">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($full_name); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Registration Number</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($profile['registration_number'] ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="text" class="form-control" name="phone" inputmode="numeric" pattern="\d+" title="Phone number must contain digits only" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Programme Level</label>
                                <select class="form-control" name="program_level" id="programLevel" required>
                                    <option value="">Select Level</option>
                                    <option value="Bachelor">Bachelor's Degree</option>
                                    <option value="Diploma">Diploma</option>
                                    <option value="Masters">Master's Degree</option>
                                    <option value="PhD">PhD</option>
                                </select>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Specific Programme</label>
                                <select class="form-control" name="specific_program" id="specificProgram" required disabled>
                                    <option value="">First select programme level</option>
                                </select>
                                <small class="text-muted">Selected: <strong id="fullProgramme">
                                    <?php 
                                    if ($profile && $profile['program_level']) {
                                        echo htmlspecialchars($profile['program_level']);
                                    } else {
                                        echo 'Select your programme';
                                    }
                                    ?>
                                </strong></small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Campus</label>
                                <select class="form-control" name="campus" required>
                                    <option value="">Select Campus</option>
                                    <option value="Nkozi" <?php echo ($profile['campus'] ?? '') == 'Nkozi' ? 'selected' : ''; ?>>Nkozi</option>
                                    <option value="Rubaga" <?php echo ($profile['campus'] ?? '') == 'Rubaga' ? 'selected' : ''; ?>>Rubaga</option>
                                    <option value="Masaka" <?php echo ($profile['campus'] ?? '') == 'Masaka' ? 'selected' : ''; ?>>Masaka</option>
                                    <option value="Ngetta" <?php echo ($profile['campus'] ?? '') == 'Ngetta' ? 'selected' : ''; ?>>Ngetta</option>
                                    <option value="Fortportal" <?php echo ($profile['campus'] ?? '') == 'Fortportal' ? 'selected' : ''; ?>>Fort Portal</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" name="date_of_birth" max="<?php echo htmlspecialchars($max_dob); ?>" value="<?php echo $profile['date_of_birth'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Year of Intake</label>
                                <input type="number" class="form-control" name="year_of_intake" inputmode="numeric" pattern="\d{4}" min="<?php echo $min_intake_year; ?>" max="<?php echo $current_year; ?>" value="<?php echo $profile['year_of_intake'] ?? ''; ?>" required>
                            </div>
                        </div>
                        <div class="profile-actions mt-3">
                            <button type="submit" class="btn btn-maroon px-4">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Clearance Application Section -->
    <?php if ($completion >= 100): ?>
    <div class="card mb-4">
        <div class="card-header bg-maroon">
            <h5 class="mb-0">Clearance Application</h5>
        </div>
        <div class="card-body">
            <?php if (!$clearance): ?>
                <p>You are eligible to apply for graduation clearance. Click the button below to submit your application.</p>
                <form action="apply_clearance.php" method="POST" onsubmit="return confirm('Are you sure you want to apply for clearance? Make sure all your information is correct.');">
                    <button type="submit" class="btn btn-maroon btn-lg">Apply for Clearance</button>
                </form>
            <?php else: ?>
                <div class="alert alert-info">
                    <strong>Application Submitted:</strong> <?php echo date('F j, Y, g:i a', strtotime($clearance['applied_at'])); ?>
                </div>
                <?php
                $has_rejection = $clearance['finance_status'] == 'rejected' ||
                                 $clearance['library_status'] == 'rejected' ||
                                 $clearance['ict_status'] == 'rejected' ||
                                 $clearance['faculty_status'] == 'rejected' ||
                                 $clearance['registrar_status'] == 'rejected';
                ?>
                <?php if ($has_rejection): ?>
                    <div class="alert alert-warning">
                        One or more departments rejected your clearance. After addressing the issue, you can re-apply.
                    </div>
                    <form action="apply_clearance.php" method="POST" onsubmit="return confirm('Re-apply for clearance and reset all department reviews to pending?');">
                        <button type="submit" class="btn btn-warning btn-lg">Re-Apply for Clearance</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Download Certificate - Show First if All Approved -->
    <?php 
    if ($clearance) {
        $all_approved = $clearance['finance_status'] == 'approved' && 
                        $clearance['library_status'] == 'approved' && 
                        $clearance['ict_status'] == 'approved' && 
                        $clearance['faculty_status'] == 'approved' && 
                        $clearance['registrar_status'] == 'approved';
        
        if ($all_approved): 
    ?>
    <div class="card mb-4 border-success" style="border-width: 3px !important;">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">🎉 Congratulations! Clearance Approved</h4>
        </div>
        <div class="card-body text-center py-4">
            <div class="alert alert-success mb-4">
                <h5 class="mb-2">✓ All Departments Have Approved Your Clearance</h5>
                <p class="mb-0">You can now download your official clearance certificate</p>
            </div>
            <a href="download_certificate.php" class="btn btn-success btn-lg px-5" target="_blank">
                <i class="bi bi-download"></i> Download Clearance Certificate
            </a>
            <p class="text-muted mt-3 mb-0"><small>This certificate is required for graduation proceedings</small></p>
        </div>
    </div>
    <?php 
        endif; // end if ($all_approved)
    ?>

    <!-- Clearance Status Display -->
    <div class="card mb-4">
        <div class="card-header bg-maroon">
            <h5 class="mb-0">Clearance Status by Department</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-3">
                <strong>Color Legend:</strong>
                <span class="badge badge-approved ms-2"><i class="fas fa-check-circle"></i> Green = Approved</span>
                <span class="badge badge-rejected ms-2"><i class="fas fa-times-circle"></i> Blue = Rejected</span>
                <span class="badge badge-pending ms-2"><i class="fas fa-clock"></i> Red = Pending</span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $departments = [
                            'Finance' => $clearance['finance_status'],
                            'Library' => $clearance['library_status'],
                            'ICT' => $clearance['ict_status'],
                            'Faculty' => $clearance['faculty_status'],
                            'Registrar' => $clearance['registrar_status']
                        ];
                        
                        foreach ($departments as $dept => $status):
                            // Get rejection reason if rejected
                            $reason_query = "SELECT rejection_reason FROM department_approvals 
                                           WHERE application_id = {$clearance['id']} 
                                           AND department = '" . strtolower($dept) . "' 
                                           AND status = 'rejected'";
                            $reason_result = $conn->query($reason_query);
                            $reason = $reason_result && $reason_result->num_rows > 0 ? $reason_result->fetch_assoc()['rejection_reason'] : '';
                            
                            $badge_class = '';
                            $status_icon = '';
                            $status_text = ucfirst($status);
                            
                            switch($status) {
                                case 'approved':
                                    $badge_class = 'badge-approved';
                                    $status_icon = '<i class="fas fa-check-circle"></i>';
                                    break;
                                case 'rejected':
                                    $badge_class = 'badge-rejected';
                                    $status_icon = '<i class="fas fa-times-circle"></i>';
                                    break;
                                default:
                                    $badge_class = 'badge-pending';
                                    $status_icon = '<i class="fas fa-clock"></i>';
                            }
                        ?>
                        <tr>
                            <td><strong><?php echo $dept; ?></strong></td>
                            <td><span class="badge <?php echo $badge_class; ?>"><?php echo $status_icon . ' ' . $status_text; ?></span></td>
                            <td><?php echo $reason ? htmlspecialchars($reason) : '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php 
    } // end if ($clearance)
    ?>
    <?php endif; // end if ($completion >= 100) ?>

    <!-- Graduation List Section -->
    <div class="card mb-4">
        <div class="card-header bg-maroon">
            <h5 class="mb-0">Graduation List Status</h5>
        </div>
        <div class="card-body">
            <?php if ($on_graduation_list): ?>
                <div class="alert alert-success">
                    <h5>✓ Confirmed for Graduation</h5>
                    <p>You have been added to the official graduation list.</p>
                    <a href="download_graduation_list.php" class="btn btn-success" target="_blank">Download Graduation List</a>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <strong>Not Confirmed</strong>
                    <p>You have not been added to the graduation list yet. Complete your clearance process first.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        // Check file size (2MB = 2097152 bytes)
        if (input.files[0].size > 2097152) {
            alert('File size must be less than 2MB');
            input.value = '';
            return;
        }
        
        // Check file type
        const fileType = input.files[0].type;
        if (fileType !== 'image/jpeg' && fileType !== 'image/png') {
            alert('Only JPG and PNG files are allowed');
            input.value = '';
            return;
        }
        
        // Preview image
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.querySelector('.photo-preview');
            if (preview) {
                preview.src = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Update programme preview as user types
document.addEventListener('DOMContentLoaded', function() {
    const programLevel = document.getElementById('programLevel');
    const specificProgram = document.getElementById('specificProgram');
    const fullProgramme = document.getElementById('fullProgramme');
    const profileForm = document.querySelector('form[action="update_profile.php"]');
    const phoneInput = profileForm ? profileForm.querySelector('input[name="phone"]') : null;
    const dobInput = profileForm ? profileForm.querySelector('input[name="date_of_birth"]') : null;
    const intakeInput = profileForm ? profileForm.querySelector('input[name="year_of_intake"]') : null;
    const minIntakeYear = <?php echo $min_intake_year; ?>;
    const maxIntakeYear = <?php echo $current_year; ?>;
    const maxDob = '<?php echo $max_dob; ?>';
    
    // Program options for each level
    const programOptions = {
        'Bachelor': [
            'Bachelor of Science (BSc)',
            'Bachelor of Arts (BA)',
            'Bachelor of Commerce (BCom)',
            'Bachelor of Business Administration (BBA)',
            'Bachelor of Education (BEd)',
            'Bachelor of Laws (LLB)',
            'Bachelor of Engineering (BEng)',
            'Bachelor of Technology (BTech)',
            'Bachelor of Nursing (BN)',
            'Bachelor of Social Work (BSW)',
            'Bachelor of Development Studies (BDS)',
            'Bachelor of Public Administration (BPA)'
        ],
        'Diploma': [
            'Diploma in Business Administration',
            'Diploma in Education',
            'Diploma in Nursing',
            'Diploma in Information Technology',
            'Diploma in Journalism',
            'Diploma in Social Work',
            'Diploma in Public Health',
            'Diploma in Accounting',
            'Diploma in Marketing',
            'Diploma in Project Management',
            'Diploma in Human Resource Management'
        ],
        'Masters': [
            'Master of Science (MSc)',
            'Master of Arts (MA)',
            'Master of Business Administration (MBA)',
            'Master of Education (MEd)',
            'Master of Laws (LLM)',
            'Master of Engineering (MEng)',
            'Master of Public Health (MPH)',
            'Master of Social Work (MSW)',
            'Master of Philosophy (MPhil)',
            'Master of Development Studies (MDS)'
        ],
        'PhD': [
            'Doctor of Philosophy (PhD)',
            'Doctor of Education (EdD)',
            'Doctor of Business Administration (DBA)',
            'Doctor of Science (DSc)',
            'Doctor of Laws (LLD)'
        ]
    };
    
    // Update specific program options when level changes
    programLevel.addEventListener('change', function() {
        const level = this.value;
        specificProgram.innerHTML = '<option value="">Select Programme</option>';
        
        if (level && programOptions[level]) {
            programOptions[level].forEach(program => {
                const option = document.createElement('option');
                option.value = program;
                option.textContent = program;
                specificProgram.appendChild(option);
            });
            specificProgram.disabled = false;
        } else {
            specificProgram.disabled = true;
            specificProgram.innerHTML = '<option value="">First select programme level</option>';
        }
        updatePreview();
    });
    
    // Update preview when specific program changes
    specificProgram.addEventListener('change', updatePreview);

    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
    }

    if (intakeInput) {
        intakeInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 4);
        });
    }

    if (profileForm) {
        profileForm.addEventListener('submit', function(event) {
            const phoneValue = phoneInput ? phoneInput.value.trim() : '';
            const dobValue = dobInput ? dobInput.value : '';
            const intakeValue = intakeInput ? intakeInput.value.trim() : '';

            if (phoneValue !== '' && !/^\d+$/.test(phoneValue)) {
                event.preventDefault();
                alert('Phone number must contain digits only.');
                return;
            }

            if (!dobValue || dobValue > maxDob) {
                event.preventDefault();
                alert('Date of birth must be at least 17 years ago.');
                return;
            }

            if (!/^\d{4}$/.test(intakeValue)) {
                event.preventDefault();
                alert('Year of intake must be a 4-digit year.');
                return;
            }

            const intakeYear = parseInt(intakeValue, 10);
            if (intakeYear < minIntakeYear || intakeYear > maxIntakeYear) {
                event.preventDefault();
                alert('Year of intake must be between ' + minIntakeYear + ' and ' + maxIntakeYear + '.');
            }
        });
    }
    
    function updatePreview() {
        const program = specificProgram.value;
        
        if (program) {
            fullProgramme.textContent = program;
        } else {
            fullProgramme.textContent = 'Select your programme';
        }
    }
    
    // Pre-populate specific program if editing existing profile
    <?php if ($profile && $profile['program_level']): ?>
    (function() {
        const savedProgram = <?php echo json_encode($profile['program_level']); ?>;
        
        // Set program level based on detected type
        if (savedProgram.includes('Bachelor')) {
            programLevel.value = 'Bachelor';
        } else if (savedProgram.includes('Diploma')) {
            programLevel.value = 'Diploma';
        } else if (savedProgram.includes('Master')) {
            programLevel.value = 'Masters';
        } else if (savedProgram.includes('PhD') || savedProgram.includes('Doctor')) {
            programLevel.value = 'PhD';
        }
        
        // Trigger change to populate specific program options
        programLevel.dispatchEvent(new Event('change'));
        
        // Set specific program if it matches an option
        setTimeout(function() {
            const options = Array.from(specificProgram.options);
            const matchingOption = options.find(opt => opt.value === savedProgram);
            if (matchingOption) {
                specificProgram.value = savedProgram;
                updatePreview();
            }
        }, 100);
    })();
    <?php endif; ?>
});
</script>

<?php include '../includes/footer.php'; ?>