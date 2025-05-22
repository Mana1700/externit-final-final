<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['user_type'] === 'student' ? 'student_dashboard.php' : 'company_dashboard.php'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ExternIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <!-- Login Form -->
    <section class="section">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-6">
                    <div class="card">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h2 class="mb-0">Login to ExternIT</h2>
                                <a href="index.php" class="btn student-btn">
                                    <i class="bi bi-arrow-left"></i> Back to Home
                                </a>
                            </div>
                            
                            <?php if (isset($_GET['error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($_GET['error']); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <form action="php/login_handler.php" method="POST">
                                <div class="mb-3">
                                    <label for="user_type" class="form-label">I am a</label>
                                    <select class="form-select" id="user_type" name="user_type" required>
                                        <option value="">Select user type</option>
                                        <option value="student">Student</option>
                                        <option value="company">Company</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn student-btn">Login</button>
                                </div>
                            </form>

                            <div class="text-center mt-3">
                                <p>Don't have an account? 
                                    <a href="register.php?type=student" class="student-text">Register as Student</a> or 
                                    <a href="register.php?type=company" class="company-text">Register as Company</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 