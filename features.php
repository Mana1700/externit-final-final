<?php
$page_title = "Features - ExternIT";
include 'includes/header.php';
?>

<div class="container py-5">
    <h1 class="text-center mb-5">ExternIT Features</h1>
    
    <!-- Main Features Section -->
    <div class="row mb-5">
        <div class="col-md-6 mb-4">
            <div class="card h-100 student-card">
                <div class="card-body">
                    <div class="feature-icon mb-3 student-icon">
                        <i class="bi bi-briefcase fs-1"></i>
                    </div>
                    <h3 class="card-title">Real-World Experience</h3>
                    <p class="card-text">Gain practical experience by working on real projects from companies. Build your portfolio with actual industry work.</p>
                    <ul class="feature-list">
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Work on real company projects</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Build a professional portfolio</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Learn industry best practices</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card h-100 company-card">
                <div class="card-body">
                    <div class="feature-icon mb-3 company-icon">
                        <i class="bi bi-people fs-1"></i>
                    </div>
                    <h3 class="card-title">Talent Pipeline</h3>
                    <p class="card-text">Access a pool of talented students and identify potential future employees through their work.</p>
                    <ul class="feature-list">
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Discover emerging talent</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Evaluate skills through real work</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Build relationships with future employees</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Features -->
    <div class="row mb-5">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-award fs-1 text-primary"></i>
                    </div>
                    <h3 class="card-title">Certification System</h3>
                    <p class="card-text">Earn verifiable certificates for completed tasks, showcasing your skills to potential employers.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-chat-dots fs-1 text-primary"></i>
                    </div>
                    <h3 class="card-title">Direct Communication</h3>
                    <p class="card-text">Communicate directly with companies and students through our built-in messaging system.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-graph-up fs-1 text-primary"></i>
                    </div>
                    <h3 class="card-title">Progress Tracking</h3>
                    <p class="card-text">Monitor your progress, track completed tasks, and view your growing portfolio all in one place.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Security & Privacy -->
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="card-title">Secure & Private</h3>
                            <p class="card-text">Your data and work are protected with industry-standard security measures. We prioritize your privacy and confidentiality.</p>
                            <ul class="feature-list">
                                <li><i class="bi bi-shield-check text-success me-2"></i>End-to-end encryption</li>
                                <li><i class="bi bi-shield-check text-success me-2"></i>Secure file storage</li>
                                <li><i class="bi bi-shield-check text-success me-2"></i>Privacy-focused design</li>
                            </ul>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="feature-icon large">
                                <i class="bi bi-shield-lock fs-1 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="row">
        <div class="col-md-12 text-center">
            <h3 class="mb-4">Ready to Experience These Features?</h3>
            <a href="register.php?type=student" class="btn btn-student btn-lg me-3">Join as Student</a>
            <a href="register.php?type=company" class="btn btn-company btn-lg">Join as Company</a>
        </div>
    </div>
</div>

<style>
.feature-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--border-radius-pill);
    transition: transform 0.3s ease;
}

.feature-icon.large {
    width: 120px;
    height: 120px;
}

.student-icon {
    background-color: var(--student-light);
    color: var(--student-primary);
}

.company-icon {
    background-color: var(--company-light);
    color: var(--company-primary);
}

.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: none;
    border-radius: var(--border-radius-lg);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.student-card {
    border: 1px solid rgba(var(--student-primary-rgb), 0.15);
}

.company-card {
    border: 1px solid rgba(var(--company-primary-rgb), 0.15);
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.card:hover .feature-icon {
    transform: scale(1.1);
}

.feature-list {
    list-style: none;
    padding-left: 0;
    margin-top: 1rem;
}

.feature-list li {
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
}

.btn {
    padding: 0.75rem 2rem;
    font-weight: 500;
    transition: all 0.3s ease;
    border-radius: var(--border-radius);
}

.btn-student {
    background: var(--student-gradient);
    color: var(--text-light);
    border: none;
}

.btn-company {
    background: var(--company-gradient);
    color: var(--text-light);
    border: none;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.btn-student:hover {
    background: var(--student-hover);
}

.btn-company:hover {
    background: var(--company-hover);
}

h1, h2, h3 {
    font-family: var(--font-primary);
    color: var(--text-dark);
}

.card-title {
    color: var(--text-dark);
    font-weight: 600;
}

.card-text {
    color: var(--text-muted);
}

.text-primary {
    color: var(--primary-color) !important;
}

.text-success {
    color: var(--success-color) !important;
}
</style>

<?php include 'includes/footer.php'; ?> 