<?php 
include 'config/base_url.php';
include 'includes/header.php'; 
?>
<div class="container-fluid px-0">
    <!-- Hero Carousel Section -->
    <div id="heroCarousel" class="carousel slide carousel-fade mb-5" data-bs-ride="carousel" data-bs-interval="4000">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
        </div>
        
        <div class="carousel-inner">
            <!-- Slide 1 -->
            <div class="carousel-item active">
                <div class="hero-slide" style="background: linear-gradient(rgba(128, 0, 0, 0.2), rgba(218, 165, 32, 0.2)), url('<?php echo BASE_URL; ?>assets/images/graduation1.jpg') center/cover; min-height: 500px; display: flex; align-items: center; justify-content: center;">
                    <div class="text-center text-white px-4">
                        <h2 class="mb-3 fw-bold" style="font-size: 3rem; color: white; text-shadow: 3px 3px 8px rgba(0,0,0,0.9), 0 0 10px rgba(0,0,0,0.7);">Uganda Martyrs University</h2>
                        <h3 class="mb-4" style="font-size: 2rem; color: white; text-shadow: 3px 3px 8px rgba(0,0,0,0.9), 0 0 10px rgba(0,0,0,0.7);">Graduate Digital Clearance System</h3>
                        <p class="mb-4 lead" style="color: white; text-shadow: 2px 2px 6px rgba(0,0,0,0.9), 0 0 8px rgba(0,0,0,0.7);">Welcome to the official clearance portal for UMU graduates. Complete your clearance process online with ease.</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="<?php echo BASE_URL; ?>login/index.php" class="btn btn-gold btn-lg px-5 shadow">Login</a>
                            <a href="<?php echo BASE_URL; ?>login/index.php" class="btn btn-outline-light btn-lg px-5 shadow">Check Clearance Status</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Slide 2 -->
            <div class="carousel-item">
                <div class="hero-slide" style="background: linear-gradient(rgba(0, 60, 113, 0.2), rgba(218, 165, 32, 0.2)), url('<?php echo BASE_URL; ?>assets/images/graduation2.jpg') center/cover; min-height: 500px; display: flex; align-items: center; justify-content: center;">
                    <div class="text-center text-white px-4">
                        <h2 class="mb-3 fw-bold" style="font-size: 3rem; color: white; text-shadow: 3px 3px 8px rgba(0,0,0,0.9), 0 0 10px rgba(0,0,0,0.7);">Celebrate Your Achievement</h2>
                        <h3 class="mb-4" style="font-size: 2rem; color: white; text-shadow: 3px 3px 8px rgba(0,0,0,0.9), 0 0 10px rgba(0,0,0,0.7);">Fast & Secure Clearance Process</h3>
                        <p class="mb-4 lead" style="color: white; text-shadow: 2px 2px 6px rgba(0,0,0,0.9), 0 0 8px rgba(0,0,0,0.7);">Get your graduation clearance certificate with just a few clicks</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="<?php echo BASE_URL; ?>login/index.php" class="btn btn-gold btn-lg px-5 shadow">Get Started</a>
                            <a href="<?php echo BASE_URL; ?>login/index.php" class="btn btn-outline-light btn-lg px-5 shadow">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Slide 3 -->
            <div class="carousel-item">
                <div class="hero-slide" style="background: linear-gradient(rgba(128, 0, 0, 0.2), rgba(0, 60, 113, 0.2)), url('<?php echo BASE_URL; ?>assets/images/graduation3.jpg') center/cover; min-height: 500px; display: flex; align-items: center; justify-content: center;">
                    <div class="text-center text-white px-4">
                        <h2 class="mb-3 fw-bold" style="font-size: 3rem; color: white; text-shadow: 3px 3px 8px rgba(0,0,0,0.9), 0 0 10px rgba(0,0,0,0.7);">Your Future Starts Here</h2>
                        <h3 class="mb-4" style="font-size: 2rem; color: white; text-shadow: 3px 3px 8px rgba(0,0,0,0.9), 0 0 10px rgba(0,0,0,0.7);">Complete All Requirements Online</h3>
                        <p class="mb-4 lead" style="color: white; text-shadow: 2px 2px 6px rgba(0,0,0,0.9), 0 0 8px rgba(0,0,0,0.7);">Track your clearance status in real-time from anywhere</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="<?php echo BASE_URL; ?>login/index.php" class="btn btn-gold btn-lg px-5 shadow">Apply Now</a>
                            <a href="<?php echo BASE_URL; ?>login/index.php" class="btn btn-outline-light btn-lg px-5 shadow">View Status</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</div>

<div class="container">
    
    <section class="mt-5 mb-5">
        <div class="text-center mb-4">
            <p class="lead fw-bold" style="color: #800000; font-size: 1.3rem;">For a student to qualify for graduation, all University requirements must be fulfilled</p>
        </div>
    </section>
    
    <section class="mt-4">
        <h3>How It Works</h3>
        <div class="row mt-4">
            <div class="col-md-3 text-center mb-3">
                <div class="stat-card">
                    <h4 class="text-maroon">Step 1</h4>
                    <p>Register/Login to your account</p>
                </div>
            </div>
            <div class="col-md-3 text-center mb-3">
                <div class="stat-card">
                    <h4 class="text-maroon">Step 2</h4>
                    <p>Complete your profile & upload photo</p>
                </div>
            </div>
            <div class="col-md-3 text-center mb-3">
                <div class="stat-card">
                    <h4 class="text-maroon">Step 3</h4>
                    <p>Apply for clearance</p>
                </div>
            </div>
            <div class="col-md-3 text-center mb-3">
                <div class="stat-card">
                    <h4 class="text-maroon">Step 4</h4>
                    <p>Download your certificate</p>
                </div>
            </div>
        </div>
        
        <div class="alert alert-info mt-4 text-center border-0 shadow-sm" style="background: linear-gradient(135deg, #B85C5C 0%, #DAA520 100%); color: white;">
            <h5 class="mb-2"><i class="fas fa-graduation-cap"></i> Important Notice</h5>
            <p class="mb-0">All clearance requirements must be met before you can download your graduation clearance certificate and appear on the official graduation list.</p>
        </div>
    </section>
    
    <section class="mt-4 text-center">
        <h4 class="text-maroon">Need Help?</h4>
        <p><strong>Email:</strong> support@umu.ac.ug | <strong>Phone:</strong> +256765491973</p>
        <p class="text-muted">Office Hours: Monday - Friday, 9:00 AM - 5:00 PM</p>
    </section>
</div>
<?php include 'includes/footer.php'; ?>