<?php
session_start();
require_once 'php/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get submission ID from URL
$submission_id = $_GET['id'] ?? null;
if (!$submission_id) {
    header('Location: my_submissions.php');
    exit();
}

// Get student ID if user is a student
$student_id = null;
if ($_SESSION['user_type'] === 'student') {
    $stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $student = $stmt->fetch();
    $student_id = $student['id'];
}

// Fetch submission details
$stmt = $pdo->prepare("
    SELECT s.*, t.title as task_title, t.description as task_description, 
           c.name as company_name, u.photo as company_photo,
           st.name as student_name, st.university, st.major,
           CASE 
               WHEN s.status = 'pending' THEN 'Pending Review'
               WHEN s.status = 'approved' THEN 'Approved'
               WHEN s.status = 'rejected' THEN 'Rejected'
               ELSE s.status
           END as status_text
    FROM submissions s
    JOIN tasks t ON s.task_id = t.id
    JOIN companies c ON t.company_id = c.id
    JOIN users u ON c.user_id = u.id
    JOIN students st ON s.student_id = st.id
    WHERE s.id = ? AND s.student_id = ?
");
$stmt->execute([$submission_id, $student_id]);
$submission = $stmt->fetch();

if (!$submission) {
    header('Location: my_submissions.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Submission - ExternIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card student-card">
                    <div class="card-header">
                        <h2 class="card-title student-text">Submission Details</h2>
                        <div class="task-meta mt-2">
                            <span class="badge-outline-student">
                                <i class="bi bi-file-text"></i>
                                <span><?php echo htmlspecialchars($submission['task_title']); ?></span>
                            </span>
                            <span class="badge-outline-student">
                                <i class="bi bi-building"></i>
                                <span><?php echo htmlspecialchars($submission['company_name']); ?></span>
                            </span>
                            <span class="badge-outline-student">
                                <i class="bi bi-clock"></i>
                                <span>Submitted: <?php echo date('M d, Y H:i', strtotime($submission['created_at'])); ?></span>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Task Description -->
                        <div class="mb-4">
                            <h5>Task Description</h5>
                            <div class="p-3 bg-light rounded">
                                <?php echo nl2br(htmlspecialchars($submission['task_description'])); ?>
                            </div>
                        </div>

                        <!-- Your Submission -->
                        <div class="mb-4">
                            <h5>Your Submission</h5>
                            <div class="p-3 bg-light rounded">
                                <?php echo nl2br(htmlspecialchars($submission['comments'] ?? 'No comments provided')); ?>
                            </div>
                        </div>

                        <!-- Submission Files -->
                        <?php if ($submission['file_path']): ?>
                        <div class="mb-4">
                            <h5>Submitted Files</h5>
                            <div class="p-3 bg-light rounded">
                                <a href="download_submission.php?id=<?php echo $submission['id']; ?>" class="btn btn-primary-student">
                                    <i class="bi bi-download"></i> Download Submission
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Status -->
                        <div class="mb-4">
                            <h5>Status</h5>
                            <div class="badge-status <?php echo strtolower($submission['status']); ?>">
                                <i class="bi <?php echo match($submission['status']) {
                                    'approved' => 'bi-check-circle-fill',
                                    'rejected' => 'bi-x-circle-fill',
                                    'pending' => 'bi-hourglass-split',
                                    default => 'bi-clock'
                                }; ?>"></i>
                                <?php echo $submission['status_text']; ?>
                            </div>
                        </div>

                        <!-- Feedback -->
                        <?php if ($submission['feedback']): ?>
                        <div class="mb-4">
                            <h5>Feedback</h5>
                            <div class="p-3 bg-light rounded">
                                <?php echo nl2br(htmlspecialchars($submission['feedback'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Certificate -->
                        <?php if (($submission['status'] === 'approved' || $submission['status'] === 'accepted') && $submission['certificate_id']): ?>
                        <div class="mb-4">
                            <h5>Certificate</h5>
                            <div class="p-3 bg-light rounded">
                                <a href="generate_certificate_pdf.php?id=<?php echo $submission['certificate_id']; ?>" class="btn btn-outline-student">
                                    <i class="bi bi-award"></i> View Certificate
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="mt-4">
                            <a href="my_submissions.php" class="btn btn-outline-student">
                                <i class="bi bi-arrow-left"></i> Back to Submissions
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 