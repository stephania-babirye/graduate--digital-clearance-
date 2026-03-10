<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Hash Generator - UMU</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-maroon text-white text-center">
                    <h3>Admin Password Hash Generator</h3>
                    <p class="mb-0">Generate secure password hash for database insertion</p>
                </div>
                <div class="card-body">
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['password'])) {
                        $password = $_POST['password'];
                        $hashed = password_hash($password, PASSWORD_DEFAULT);
                        
                        echo '<div class="alert alert-success">';
                        echo '<h5>Password Hash Generated Successfully!</h5>';
                        echo '<p><strong>Your Password:</strong> ' . htmlspecialchars($password) . '</p>';
                        echo '<p><strong>Hashed Password:</strong></p>';
                        echo '<textarea class="form-control" rows="3" readonly onclick="this.select()">' . $hashed . '</textarea>';
                        echo '<hr>';
                        echo '<p><strong>SQL Query to Update Admin Password:</strong></p>';
                        echo '<textarea class="form-control" rows="4" readonly onclick="this.select()">';
                        echo "UPDATE users SET password = '" . $hashed . "' WHERE email = 'admin@umu.ac.ug';";
                        echo '</textarea>';
                        echo '<p class="mt-2 text-muted"><small>Click on the text areas above to select and copy</small></p>';
                        echo '</div>';
                    }
                    ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="password" class="form-label">Enter Your Desired Password:</label>
                            <input type="text" class="form-control form-control-lg" id="password" name="password" required placeholder="Enter your password here">
                            <div class="form-text">This will generate a secure hash for your password</div>
                        </div>
                        <button type="submit" class="btn btn-maroon btn-lg w-100">Generate Password Hash</button>
                    </form>

                    <div class="alert alert-info mt-4">
                        <h5>How to Use:</h5>
                        <ol>
                            <li>Enter your desired password in the field above</li>
                            <li>Click "Generate Password Hash"</li>
                            <li>Copy the SQL query generated</li>
                            <li>Run it in phpMyAdmin to update the admin password</li>
                            <li>Login with email: <strong>admin@umu.ac.ug</strong> and your new password</li>
                        </ol>
                    </div>

                    <div class="text-center mt-3">
                        <a href="../login/index.php" class="btn btn-secondary">Go to Login</a>
                        <a href="../index.php" class="btn btn-outline-secondary">Go to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>