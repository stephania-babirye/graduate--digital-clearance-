<?php 
session_start();
include '../includes/header.php'; 
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h3 class="text-center mb-4">Student Registration</h3>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="process_register.php">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="reg_number">Registration Number</label>
                            <input type="text" class="form-control" id="reg_number" name="reg_number" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required placeholder="yourname@stud.umu.ac.ug">
                            <small class="form-text text-muted">⚠️ Must contain '@stud' (e.g., yourname@stud.umu.ac.ug)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="program_level">Programme Level</label>
                            <select class="form-control" id="program_level" name="program_level" required>
                                <option value="">Select Level</option>
                                <option value="Bachelor">Bachelor's Degree</option>
                                <option value="Diploma">Diploma</option>
                                <option value="Masters">Master's Degree</option>
                                <option value="PhD">PhD</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="specific_program">Specific Programme</label>
                            <select class="form-control" id="specific_program" name="specific_program" required disabled>
                                <option value="">First select programme level</option>
                            </select>
                            <small class="form-text text-muted">Selected: <strong id="fullProgramme">Select your programme</strong></small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="campus">Campus</label>
                            <select class="form-control" id="campus" name="campus" required>
                                <option value="">Select Campus</option>
                                <option value="Nkozi">Nkozi</option>
                                <option value="Rubaga">Rubaga</option>
                                <option value="Masaka">Masaka</option>
                                <option value="Ngetta">Ngetta</option>
                                <option value="Fortportal">Fort Portal</option>
                            </select>
                        </div>
                    </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-maroon btn-block">Register</button>
            </form>
            
            <div class="text-center mt-3">
                <p>Already have an account? <a href="index.php" class="font-weight-bold">Login Here</a></p>
            </div>
        </div>
    </div>
</div>

<script>
// Programme selection logic
document.addEventListener('DOMContentLoaded', function() {
    const programLevel = document.getElementById('program_level');
    const specificProgram = document.getElementById('specific_program');
    const courseName = document.getElementById('course');
    const fullProgramme = document.getElementById('fullProgramme');
    
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
    
    // Update preview when any field changes
    specificProgram.addEventListener('change', updatePreview);
    
    function updatePreview() {
        const program = specificProgram.value;
        
        if (program) {
            fullProgramme.textContent = program;
        } else {
            fullProgramme.textContent = 'Select your programme';
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>