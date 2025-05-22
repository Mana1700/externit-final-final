<?php
session_start();
$page_title = 'ExternIT - Connecting Students with Companies';
include 'includes/header.php';
?>

    <!-- Hero Section -->
    <section class="hero py-5 position-relative overflow-hidden">
        <div class="position-absolute top-0 start-0 w-100 h-100 bg-gradient-primary opacity-10"></div>
        <div class="container position-relative">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4 animate-fade-in">Connect, Create, and Grow</h1>
                    <p class="lead mb-4 animate-fade-in-delay">ExternIT bridges the gap between talented students and innovative companies. Complete tasks, earn certificates, and build your portfolio while companies find the perfect solutions for their needs.</p>
                    <div class="d-flex gap-3 animate-fade-in-delay-2">
                        <a href="register.php?type=student" class="btn student-btn btn-lg px-4 py-2 hover-scale">
                            <i class="bi bi-mortarboard-fill me-2"></i>Join as Student
                        </a>
                        <a href="register.php?type=company" class="btn company-btn btn-lg px-4 py-2 hover-scale">
                            <i class="bi bi-building me-2"></i>Join as Company
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="position-relative">
                    <div class="text-center">
  <img src="./assets/images/heroimg.png" 
       alt="ExternIT Platform" 
       class="img-fluid rounded-3 shadow-lg animate-float" 
       style="max-width: 300px;">
</div>
                        <div class="position-absolute top-0 start-0 w-100 h-100 rounded-3 bg-gradient-primary opacity-10 animate-pulse"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="section bg-light py-5 position-relative">
        <div class="container">
            <h2 class="section-title text-center mb-5">How It Works</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm hover-card animate-fade-in">
                        <div class="card-body text-center p-4">
                            <div class="step-number company-theme rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                                <span class="display-6 fw-bold">1</span>
                            </div>
                            <h3 class="h4 mb-3">Post Tasks</h3>
                            <p class="text-muted">Companies post their tasks with clear requirements and deadlines</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm hover-card animate-fade-in-delay">
                        <div class="card-body text-center p-4">
                            <div class="step-number student-theme rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                                <span class="display-6 fw-bold">2</span>
                            </div>
                            <h3 class="h4 mb-3">Submit Solutions</h3>
                            <p class="text-muted">Students submit their solutions and showcase their skills</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm hover-card animate-fade-in-delay-2">
                        <div class="card-body text-center p-4">
                            <div class="step-number student-theme rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                                <span class="display-6 fw-bold">3</span>
                            </div>
                            <h3 class="h4 mb-3">Get Certified</h3>
                            <p class="text-muted">Receive certificates and build your professional portfolio</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="section py-5 position-relative">
        <div class="container">
            <h2 class="section-title text-center mb-5">Benefits</h2>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card student-card h-100 border-0 shadow-sm hover-card animate-fade-in">
                        <div class="card-body p-4">
                            <h3 class="card-title h4 student-text mb-4">
                                <i class="bi bi-mortarboard-fill me-2"></i>For Students
                            </h3>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2 animate-fade-in-delay"><i class="bi bi-check-circle-fill text-success me-2"></i>Gain real-world experience</li>
                                <li class="mb-2 animate-fade-in-delay"><i class="bi bi-check-circle-fill text-success me-2"></i>Build your portfolio</li>
                                <li class="mb-2 animate-fade-in-delay"><i class="bi bi-check-circle-fill text-success me-2"></i>Earn certificates</li>
                                <li class="mb-2 animate-fade-in-delay"><i class="bi bi-check-circle-fill text-success me-2"></i>Get paid for your work</li>
                                <li class="mb-2 animate-fade-in-delay"><i class="bi bi-check-circle-fill text-success me-2"></i>Connect with companies</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card company-card h-100 border-0 shadow-sm hover-card animate-fade-in-delay">
                        <div class="card-body p-4">
                            <h3 class="card-title h4 company-text mb-4">
                                <i class="bi bi-building me-2"></i>For Companies
                            </h3>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2 animate-fade-in-delay"><i class="bi bi-check-circle-fill text-success me-2"></i>Access to fresh talent</li>
                                <li class="mb-2 animate-fade-in-delay"><i class="bi bi-check-circle-fill text-success me-2"></i>Cost-effective solutions</li>
                                <li class="mb-2 animate-fade-in-delay"><i class="bi bi-check-circle-fill text-success me-2"></i>Multiple options to choose from</li>
                                <li class="mb-2 animate-fade-in-delay"><i class="bi bi-check-circle-fill text-success me-2"></i>Streamlined hiring process</li>
                                <li class="mb-2 animate-fade-in-delay"><i class="bi bi-check-circle-fill text-success me-2"></i>Build relationships with future employees</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>