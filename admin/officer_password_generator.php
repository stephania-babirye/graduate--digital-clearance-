<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officer Password Generator - UMU</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-maroon text-white text-center">
                    <h3>Department Officer Password Generator</h3>
                    <p class="mb-0">Generate secure password hashes for all department officers</p>
                </div>
                <div class="card-body">
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['passwords'])) {
                        echo '<div class="alert alert-success">';
                        echo '<h5>Password Hashes Generated Successfully!</h5>';
                        echo '<p class="mb-3">Copy and run the SQL queries below in phpMyAdmin:</p>';
                        
                        $officers = [
                            'finance' => ['email' => 'finance@finance', 'name' => 'Finance Officer'],
                            'library' => ['email' => 'library@Lib', 'name' => 'Library Officer'],
                            'ict' => ['email' => 'ict@ict.umu.ac.ug', 'name' => 'ICT Officer'],
                            'dean' => ['email' => 'dean@umu.ac.ug', 'name' => 'Faculty Dean'],
                            'registrar' => ['email' => 'registrar@umu.ac.ug', 'name' => 'Academic Registrar']
                        ];
                        
                        foreach ($officers as $role => $info) {
                            if (!empty($_POST['passwords'][$role])) {
                                $password = $_POST['passwords'][$role];
                                $hashed = password_hash($password, PASSWORD_DEFAULT);
                                
                                echo '<div class="mb-4 border p-3 rounded">';
                                echo '<h6 class="text-maroon">' . $info['name'] . '</h6>';
                                echo '<p class="mb-1"><strong>Email:</strong> ' . $info['email'] . '</p>';
                                echo '<p class="mb-1"><strong>Password:</strong> ' . htmlspecialchars($password) . '</p>';
                                echo '<p class="mb-2"><strong>SQL Query:</strong></p>';
                                echo '<textarea class="form-control" rows="2" readonly onclick="this.select()">';
                                echo "UPDATE users SET password = '" . $hashed . "' WHERE email = '" . $info['email'] . "';";
                                echo '</textarea>';
                                echo '</div>';
                            }
                        }
                        
                        echo '<hr>';
                        echo '<h6>Or run all at once:</h6>';
                        echo '<textarea class="form-control" rows="10" readonly onclick="this.select()">';
                        foreach ($officers as $role => $info) {
                            if (!empty($_POST['passwords'][$role])) {
                                $password = $_POST['passwords'][$role];
                                $hashed = password_hash($password, PASSWORD_DEFAULT);
                                echo "UPDATE users SET password = '" . $hashed . "' WHERE email = '" . $info['email'] . "';\n";
                            }
                        }
                        echo '</textarea>';
                        echo '<p class="mt-2 text-muted"><small>Click on any text area to select and copy</small></p>';
                        echo '</div>';
                    }
                    ?>
                    
                    <form method="POST" action="">
                        <div class="alert alert-info">
                            <strong>Instructions:</strong> Enter the desired password for each department officer below. Leave blank to skip.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <strong>Finance Officer</strong>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted small mb-2">Email: finance@finance</p>
                                        <label class="form-label">Password:</label>
                                        <input type="text" class="form-control" name="passwords[finance]" placeholder="Enter password">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <strong>Library Officer</strong>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted small mb-2">Email: library@Lib</p>
                                        <label class="form-label">Password:</label>
                                        <input type="text" class="form-control" name="passwords[library]" placeholder="Enter password">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <strong>ICT Officer</strong>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted small mb-2">Email: ict@ict.umu.ac.ug</p>
                                        <label class="form-label">Password:</label>
                                        <input type="text" class="form-control" name="passwords[ict]" placeholder="Enter password">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <strong>Faculty Dean</strong>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted small mb-2">Email: dean@umu.ac.ug</p>
                                        <label class="form-label">Password:</label>
                                        <input type="text" class="form-control" name="passwords[dean]" placeholder="Enter password">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <strong>Academic Registrar</strong>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted small mb-2">Email: registrar@umu.ac.ug</p>
                                        <label class="form-label">Password:</label>
                                        <input type="text" class="form-control" name="passwords[registrar]" placeholder="Enter password">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-maroon btn-lg w-100">Generate Password Hashes</button>
                    </form>

                    <div class="alert alert-warning mt-4">
                        <h6>How to Use:</h6>
                        <ol>
                            <li>Enter passwords for the officers you want to set up</li>
                            <li>Click "Generate Password Hashes"</li>
                            <li>Copy the SQL queries generated</li>
                            <li>Open phpMyAdmin and select the <code>graduation_clearance</code> database</li>
                            <li>Go to the SQL tab and paste the queries</li>
                            <li>Click "Go" to execute</li>
                            <li>Officers can now login with their new passwords</li>
                        </ol>
                    </div>

                    <div class="text-center mt-4">
                        <a href="../login/index.php" class="btn btn-secondary">Go to Login</a>
                        <a href="../index.php" class="btn btn-outline-secondary">Go to Home</a>
                        <a href="../admin/password_generator.php" class="btn btn-outline-maroon">Admin Password Generator</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>