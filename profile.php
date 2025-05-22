<?php
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Get user profile based on type
if ($user_type === 'student') {
    $stmt = $pdo->prepare("
        SELECT u.email, u.photo, s.*
        FROM users u
        JOIN students s ON u.id = s.user_id
        WHERE u.id = ?
    ");
} else {
    $stmt = $pdo->prepare("
        SELECT u.email, u.photo, c.*
        FROM users u
        JOIN companies c ON u.id = c.user_id
        WHERE u.id = ?
    ");
}
$stmt->execute([$user_id]);
$profile = $stmt->fetch();

if (!$profile) {
    header('Location: login.php?error=' . urlencode('Profile not found'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - ExternIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body class="bg-light">
    <?php include 'includes/navigation.php'; ?>

    <div class="container mt-4">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card profile-section mb-4">
                    <div class="card-body text-center">
                        <div class="profile-picture mb-4">
                            <?php if ($profile['photo']): ?>
                                <img src="<?php echo htmlspecialchars($profile['photo']); ?>" alt="Profile Photo" class="profile-photo">
                            <?php else: ?>
                                <i class="bi <?php echo $user_type === 'student' ? 'bi-person-circle' : 'bi-building'; ?>" style="font-size: 4rem; color: var(--text-muted);"></i>
                            <?php endif; ?>
                        </div>
                        <h3 class="mb-2"><?php echo htmlspecialchars($profile['name']); ?></h3>
                        <p class="text-muted mb-1"><?php echo ucfirst($user_type); ?></p>
                        <p class="text-muted"><?php echo htmlspecialchars($profile['email']); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card profile-section mb-4">
                    <div class="card-body">
                        <h3 class="section-title <?php echo $user_type === 'student' ? 'student-text' : 'company-text'; ?> mb-4">Profile Information</h3>
                        <form action="php/handlers/update_profile_handler.php" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-4">
                                        <label for="photo" class="form-label">Update Profile Photo</label>
                                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                                        <small class="text-muted d-block mt-2">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Max size: 2MB. Formats: JPG, PNG, GIF
                                        </small>
                                    </div>
                                    <?php if ($user_type === 'student'): ?>
                                        <div class="mb-4">
                                            <h4 class="section-title student-text mb-3">Personal Details</h4>
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Full Name</label>
                                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($profile['name']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="phone" class="form-label">Phone Number</label>
                                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>" placeholder="+60 12-345 6789">
                                            </div>
                                            <div class="mb-3">
                                                <label for="bio" class="form-label">Bio</label>
                                                <textarea class="form-control" id="bio" name="bio" rows="4" placeholder="Tell us about yourself, your skills, and your interests..."><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                                                <small class="text-muted">Maximum 500 characters</small>
                                            </div>
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($profile['email']); ?>" readonly disabled>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <h4 class="section-title student-text mb-3">Educational Details</h4>
                                            <div class="mb-3">
                                                <label for="university" class="form-label">University</label>
                                                <input type="text" class="form-control" id="university" name="university" value="<?php echo htmlspecialchars($profile['university']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="major" class="form-label">Major</label>
                                                <input type="text" class="form-control" id="major" name="major" value="<?php echo htmlspecialchars($profile['major']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="graduation_year" class="form-label">Graduation Year</label>
                                                <input type="number" class="form-control" id="graduation_year" name="graduation_year" value="<?php echo htmlspecialchars($profile['graduation_year']); ?>" required>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Company Name</label>
                                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($profile['name']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="industry" class="form-label">Industry</label>
                                            <input type="text" class="form-control" id="industry" value="<?php echo htmlspecialchars($profile['industry']); ?>" readonly disabled>
                                        </div>
                                        <div class="mb-3">
                                            <label for="website" class="form-label">Website</label>
                                            <input type="url" class="form-control" id="website" name="website" value="<?php echo htmlspecialchars($profile['website']); ?>" placeholder="https://">
                                        </div>
                                        <div class="mb-4">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($profile['email']); ?>" readonly disabled>
                                        </div>
                                    <?php endif; ?>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn <?php echo $user_type === 'student' ? 'student-btn' : 'company-btn'; ?> btn-lg">
                                            <i class="bi bi-check2-circle me-2"></i>Update Profile
                                        </button>
                                        <button type="button" class="btn btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#deleteProfileModal">
                                            <i class="bi bi-trash me-2"></i>Delete Account
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Profile Modal -->
    <div class="modal fade" id="deleteProfileModal" tabindex="-1" aria-labelledby="deleteProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteProfileModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>Delete Account
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete your account? This action cannot be undone and will:</p>
                    <?php if ($user_type === 'student'): ?>
                        <ul class="mt-3">
                            <li>Delete all your personal information</li>
                            <li>Remove all your task submissions</li>
                            <li>Delete your profile picture</li>
                            <li>Remove your account permanently</li>
                        </ul>
                    <?php else: ?>
                        <ul class="mt-3">
                            <li>Delete all your company information</li>
                            <li>Remove all your posted tasks</li>
                            <li>Delete all related submissions from students</li>
                            <li>Remove your company logo</li>
                            <li>Remove your account permanently</li>
                        </ul>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="php/handlers/delete_profile_handler.php" method="POST" style="display: inline;">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Delete Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 