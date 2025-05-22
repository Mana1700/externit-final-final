<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support - ExternIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php"><img src="/externit-final/assets/images/Logo.png" alt="ExternIT Logo" style="height: 40px; width: auto;"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="features.php">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="process.php">How It Works</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="help.php">Help</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Help Content -->
    <section class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <h2>Frequently Asked Questions</h2>
                    
                    <div class="accordion" id="faqAccordion">
                        <!-- For Students -->
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#studentFaq">
                                    For Students
                                </button>
                            </h3>
                            <div id="studentFaq" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <div class="mb-4">
                                        <h5>How do I get started?</h5>
                                        <p>Create an account as a student, browse available tasks, and submit your solutions. Make sure to read the requirements carefully before submitting.</p>
                                    </div>
                                    <div class="mb-4">
                                        <h5>How do I earn certificates?</h5>
                                        <p>Certificates are awarded when a company accepts your submission. The certificate will be available in your dashboard.</p>
                                    </div>
                                    <div class="mb-4">
                                        <h5>How do I get paid?</h5>
                                        <p>When a company accepts your submission, they will process the payment. You'll receive the amount specified in the task budget.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- For Companies -->
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#companyFaq">
                                    For Companies
                                </button>
                            </h3>
                            <div id="companyFaq" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <div class="mb-4">
                                        <h5>How do I post a task?</h5>
                                        <p>Create an account as a company, then use the "Create New Task" button in your dashboard to post tasks with clear requirements and budget.</p>
                                    </div>
                                    <div class="mb-4">
                                        <h5>How do I review submissions?</h5>
                                        <p>Submissions will appear in your dashboard. You can review them, provide feedback, and choose to accept or reject them.</p>
                                    </div>
                                    <div class="mb-4">
                                        <h5>How do I issue certificates?</h5>
                                        <p>When you accept a submission, a certificate is automatically generated for the student. You can view issued certificates in your dashboard.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- General Questions -->
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#generalFaq">
                                    General Questions
                                </button>
                            </h3>
                            <div id="generalFaq" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <div class="mb-4">
                                        <h5>Is there a fee for using ExternIT?</h5>
                                        <p>No, ExternIT is free for both students and companies. Companies only pay for accepted submissions.</p>
                                    </div>
                                    <div class="mb-4">
                                        <h5>How are payments processed?</h5>
                                        <p>Payments are processed securely through our platform. Companies can pay using various payment methods.</p>
                                    </div>
                                    <div class="mb-4">
                                        <h5>What if I have a problem?</h5>
                                        <p>Contact our support team using the form below. We'll get back to you as soon as possible.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <?php if (isset($_GET['success'])): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($_GET['success']); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php elseif (isset($_GET['error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($_GET['error']); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            <h3>Contact Support</h3>
                            <form action="send_message.php" method="POST">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Your Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Send Message</button>
                            </form>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h3>Contact Information</h3>
                            <p><strong>Email:</strong> support@externit.com</p>
                            <p><strong>Phone:</strong> (123) 456-7890</p>
                            <p><strong>Address:</strong> 123 University Ave, Tech City, TC 12345</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>ExternIT</h5>
                    <p>Connecting students with companies for mutual growth and success.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="about.php" class="text-white">About</a></li>
                        <li><a href="features.php" class="text-white">Features</a></li>
                        <li><a href="help.php" class="text-white">Help</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact</h5>
                    <ul class="list-unstyled">
                        <li>Email: info@externit.com</li>
                        <li>Phone: (123) 456-7890</li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 