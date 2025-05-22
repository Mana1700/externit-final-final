<?php
$page_title = "How It Works - ExternIT";
include 'includes/header.php';
?>

<div class="container py-5">
    <h1 class="text-center mb-5">How ExternIT Works</h1>
    
    <!-- Student Section -->
    <div class="row mb-5">
        <div class="col-md-12">
            <h2 class="text-center mb-4">For Students</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 student-card">
                        <div class="card-body text-center">
                            <div class="process-icon mb-3 student-icon">
                                <i class="bi bi-person-plus-fill fs-1"></i>
                            </div>
                            <h3 class="card-title">1. Register</h3>
                            <p class="card-text">Create your student account and complete your profile to get started with ExternIT.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 student-card">
                        <div class="card-body text-center">
                            <div class="process-icon mb-3 student-icon">
                                <i class="bi bi-search fs-1"></i>
                            </div>
                            <h3 class="card-title">2. Browse Tasks</h3>
                            <p class="card-text">Explore available tasks from various companies and find ones that match your skills.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 student-card">
                        <div class="card-body text-center">
                            <div class="process-icon mb-3 student-icon">
                                <i class="bi bi-file-earmark-check fs-1"></i>
                            </div>
                            <h3 class="card-title">3. Submit Work</h3>
                            <p class="card-text">Complete the tasks and submit your work for review by the companies.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Company Section -->
    <div class="row">
        <div class="col-md-12">
            <h2 class="text-center mb-4">For Companies</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 company-card">
                        <div class="card-body text-center">
                            <div class="process-icon mb-3 company-icon">
                                <i class="bi bi-building fs-1"></i>
                            </div>
                            <h3 class="card-title">1. Register</h3>
                            <p class="card-text">Create your company account and set up your profile to start posting tasks.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 company-card">
                        <div class="card-body text-center">
                            <div class="process-icon mb-3 company-icon">
                                <i class="bi bi-plus-circle fs-1"></i>
                            </div>
                            <h3 class="card-title">2. Post Tasks</h3>
                            <p class="card-text">Create and post tasks that you need help with, specifying requirements and deadlines.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 company-card">
                        <div class="card-body text-center">
                            <div class="process-icon mb-3 company-icon">
                                <i class="bi bi-check2-circle fs-1"></i>
                            </div>
                            <h3 class="card-title">3. Review & Reward</h3>
                            <p class="card-text">Review student submissions and reward them with certificates for their work.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="row mt-5">
        <div class="col-md-12 text-center">
            <h3 class="mb-4">Ready to Get Started?</h3>
            <a href="register.php?type=student" class="btn btn-student btn-lg me-3">Register as Student</a>
            <a href="register.php?type=company" class="btn btn-company btn-lg">Register as Company</a>
        </div>
    </div>
</div>

<style>
.process-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--border-radius-pill);
    transition: transform 0.3s ease;
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

.card:hover .process-icon {
    transform: scale(1.1);
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
</style>

<?php include 'includes/footer.php'; ?> 