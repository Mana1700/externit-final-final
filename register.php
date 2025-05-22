<?php
session_start();
$user_type = isset($_GET['type']) ? $_GET['type'] : '';
if (!in_array($user_type, ['student', 'company'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ExternIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">ExternIT</a>
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
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Registration Form -->
    <section class="section">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-7">
                    <div class="form-card <?php echo $user_type === 'student' ? 'student-form' : 'company-form'; ?>">
                        <div class="form-header d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-0">Register as <?php echo ucfirst($user_type); ?></h2>
                                <p class="text-light mb-0 mt-2">Join our platform and start your journey</p>
                            </div>
                            <a href="index.php" class="btn <?php echo $user_type === 'student' ? 'student-btn' : 'company-btn'; ?>">
                                <i class="bi bi-arrow-left"></i> Back to Home
                            </a>
                        </div>
                        
                        <div class="form-body">
                            <?php if (isset($_GET['error'])): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                            <?php endif; ?>

                            <form action="includes/components/register_handler.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="user_type" value="<?php echo $user_type; ?>">
                                
                                <!-- Basic Information Section -->
                                <div class="form-section">
                                    <h3 class="form-section-title">Basic Information</h3>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="name" class="form-label required-field">Full Name</label>
                                                <input type="text" class="form-control" id="name" name="name" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="email" class="form-label required-field">Email Address</label>
                                                <input type="email" class="form-control" id="email" name="email" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="password" class="form-label required-field">Password</label>
                                                <input type="password" class="form-control" id="password" name="password" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="confirm_password" class="form-label required-field">Confirm Password</label>
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($user_type === 'student'): ?>
                                <!-- Educational Details Section -->
                                <div class="form-section">
                                    <h3 class="form-section-title">Educational Details</h3>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="university" class="form-label required-field">University</label>
                                                <select class="form-select" id="university" name="university" required>
                                                    <option value="">Select University</option>
                                                    <option value="UKM">Universiti Kebangsaan Malaysia (UKM)</option>
                                                    <option value="UM">Universiti Malaya (UM)</option>
                                                    <option value="UPM">Universiti Putra Malaysia (UPM)</option>
                                                    <option value="UTM">Universiti Teknologi Malaysia (UTM)</option>
                                                    <option value="USM">Universiti Sains Malaysia (USM)</option>
                                                    <option value="UiTM">Universiti Teknologi MARA (UiTM)</option>
                                                    <option value="UNIMAS">Universiti Malaysia Sarawak (UNIMAS)</option>
                                                    <option value="UMS">Universiti Malaysia Sabah (UMS)</option>
                                                    <option value="UIAM">Universiti Islam Antarabangsa Malaysia (UIAM)</option>
                                                    <option value="UPSI">Universiti Pendidikan Sultan Idris (UPSI)</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="major" class="form-label required-field">Major</label>
                                                <select class="form-select" id="major" name="major" required>
                                                    <option value="">Select Major</option>
                                                    <option value="CS">Computer Science</option>
                                                    <option value="IT">Information Technology</option>
                                                    <option value="SE">Software Engineering</option>
                                                    <option value="IS">Information Systems</option>
                                                    <option value="CE">Computer Engineering</option>
                                                    <option value="DS">Data Science</option>
                                                    <option value="AI">Artificial Intelligence</option>
                                                    <option value="CY">Cybersecurity</option>
                                                    <option value="NE">Network Engineering</option>
                                                    <option value="MM">Multimedia Technology</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="graduation_year" class="form-label required-field">Expected Graduation Year</label>
                                                <select class="form-select" id="graduation_year" name="graduation_year" required>
                                                    <option value="">Select Year</option>
                                                    <?php 
                                                    $current_year = date('Y');
                                                    for ($year = $current_year; $year <= $current_year + 6; $year++) {
                                                        echo "<option value=\"$year\">$year</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php else: ?>
                                <!-- Company Details Section -->
                                <div class="form-section">
                                    <h3 class="form-section-title">Company Details</h3>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="industry" class="form-label required-field">Industry/Field</label>
                                                <select class="form-select" id="industry" name="industry" required>
                                                    <option value="">Select Industry</option>
                                                    <option value="Technology">Technology</option>
                                                    <option value="Finance">Finance</option>
                                                    <option value="Healthcare">Healthcare</option>
                                                    <option value="Education">Education</option>
                                                    <option value="Manufacturing">Manufacturing</option>
                                                    <option value="Retail">Retail</option>
                                                    <option value="Consulting">Consulting</option>
                                                    <option value="Telecommunications">Telecommunications</option>
                                                    <option value="Energy">Energy</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="website" class="form-label">Company Website URL</label>
                                                <input type="url" class="form-control" id="website" name="website" placeholder="https://example.com">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="phone" class="form-label">Phone Number</label>
                                                <input type="text" class="form-control" id="phone" name="phone" placeholder="e.g. +201234567890">
                                            </div>

                                            <div class="mb-3">
                                                <label for="address" class="form-label">Company Address</label>
                                                <input type="text" class="form-control" id="address" name="address" placeholder="Company Address">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label required-field">Preferred Fields of Study</label>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="preferred_fields[]" value="CS" id="field_cs">
                                                    <label class="form-check-label" for="field_cs">Computer Science</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="preferred_fields[]" value="IT" id="field_it">
                                                    <label class="form-check-label" for="field_it">Information Technology</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="preferred_fields[]" value="SE" id="field_se">
                                                    <label class="form-check-label" for="field_se">Software Engineering</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="preferred_fields[]" value="IS" id="field_is">
                                                    <label class="form-check-label" for="field_is">Information Systems</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="preferred_fields[]" value="CE" id="field_ce">
                                                    <label class="form-check-label" for="field_ce">Computer Engineering</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="preferred_fields[]" value="DS" id="field_ds">
                                                    <label class="form-check-label" for="field_ds">Data Science</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="preferred_fields[]" value="AI" id="field_ai">
                                                    <label class="form-check-label" for="field_ai">Artificial Intelligence</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="preferred_fields[]" value="CY" id="field_cy">
                                                    <label class="form-check-label" for="field_cy">Cybersecurity</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="preferred_fields[]" value="NE" id="field_ne">
                                                    <label class="form-check-label" for="field_ne">Network Engineering</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="preferred_fields[]" value="MM" id="field_mm">
                                                    <label class="form-check-label" for="field_mm">Multimedia Technology</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="tax_id" class="form-label">Tax Identification Number</label>
                                        <input type="text" class="form-control" id="tax_id" name="tax_id">
                                        <div class="form-text">Optional: Provide your company's tax identification number</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="registration_doc" class="form-label">Company Registration Documents</label>
                                        <div class="file-input-wrapper">
                                            <input type="file" class="form-control" id="registration_doc" name="registration_doc" accept=".pdf,.doc,.docx">
                                        </div>
                                        <div class="form-text">Optional: Upload company registration documents (PDF, DOC, DOCX)</div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="d-grid">
                                    <button type="submit" class="btn <?php echo $user_type === 'student' ? 'student-btn' : 'company-btn'; ?>">Register</button>
                                </div>

                                <div class="text-center mt-4">
                                    <p>Already have an account? 
                                        <a href="login.php" class="<?php echo $user_type === 'student' ? 'student-text' : 'company-text'; ?>">
                                            Login here
                                        </a>
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password confirmation validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }

            <?php if ($user_type === 'company'): ?>
            // Validate at least one preferred field is selected
            const preferredFields = document.querySelectorAll('input[name="preferred_fields[]"]:checked');
            if (preferredFields.length === 0) {
                e.preventDefault();
                alert('Please select at least one preferred field of study.');
            }
            <?php endif; ?>
        });
    </script>
</body>
</html> 