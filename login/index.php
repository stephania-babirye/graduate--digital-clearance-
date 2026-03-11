<?php 
session_start();
include '../includes/header.php'; 
?>
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-lg">
                <div class="card-header bg-maroon text-center">
                    <h3 class="mb-0" style="color: #FFD700;">LOGIN</h3>
                    <p class="mb-0 mt-2" style="color: #FFE5B4;">Login to access your dashboard</p>
                </div>
                <div class="card-body p-4">
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="authenticate.php">
                <div class="form-group mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <div class="form-group text-end mb-3">
                    <a href="forgot.php" class="text-maroon">Forgot Password?</a>
                </div>
                <button type="submit" class="btn btn-maroon w-100 mb-3">Login</button>
            </form>
            
            <hr>
            
            <div class="text-center mt-3">
                <p class="mb-0">Don't have an account? <a href="register.php" class="text-maroon font-weight-bold">Register Here</a></p>
            </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>