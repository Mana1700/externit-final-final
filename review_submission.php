<?php
session_start();
require_once 'php/db.php';

// Check if user is logged in and is a company
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'company') {
    header('Location: login.php');
    exit();
}

// Get submission ID
$submission_id = $_GET['id'] ?? null;
if (!$submission_id) {
    header('Location: company_dashboard.php');
    exit();
}

// Get company ID
$stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$company = $stmt->fetch();
$company_id = $company['id'];

// Fetch submission details with task and student information
$stmt = $pdo->prepare("
    SELECT s.*, t.title as task_title, t.company_id, t.budget,
           st.name as student_name, st.id as student_id,
           t.id as task_id
    FROM submissions s
    JOIN tasks t ON s.task_id = t.id
    JOIN students st ON s.student_id = st.id
    WHERE s.id = ? AND t.company_id = ?
");
$stmt->execute([$submission_id, $company_id]);
$submission = $stmt->fetch();

// Check if submission exists and belongs to this company
if (!$submission) {
    header('Location: company_dashboard.php?error=Submission not found');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $feedback = $_POST['feedback'] ?? '';
    $is_best = isset($_POST['is_best']) ? 1 : 0;

    if (!in_array($action, ['accept', 'reject'])) {
        header("Location: review_submission.php?id=$submission_id&error=Invalid action");
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Update submission status
        $status = ($action === 'accept') ? 'accepted' : 'rejected';
        $stmt = $pdo->prepare("
            UPDATE submissions 
            SET status = ?, feedback = ?, is_best = ?
            WHERE id = ?
        ");
        $stmt->execute([$status, $feedback, $is_best, $submission_id]);

        // If accepted, create certificate
        if ($action === 'accept') {
            // Generate unique certificate number
            $certificate_number = 'CERT-' . strtoupper(uniqid()) . '-' . date('Y');
            
            // Create certificate
            $stmt = $pdo->prepare("
                INSERT INTO certificates (
                    submission_id, task_id, student_id, company_id,
                    certificate_number, issue_date
                ) VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $submission_id,
                $submission['task_id'],
                $submission['student_id'],
                $company_id,
                $certificate_number
            ]);

            // Create notification for the student
            $stmt = $pdo->prepare("
                INSERT INTO notifications (
                    user_id, type, title, message
                ) VALUES (
                    (SELECT user_id FROM students WHERE id = ?),
                    'certificate',
                    'Certificate Issued',
                    CONCAT('Congratulations! Your submission for \'', 
                           (SELECT title FROM tasks WHERE id = ?), 
                           '\' has been approved and a certificate has been issued.')
                )
            ");
            $stmt->execute([$submission['student_id'], $submission['task_id']]);

            // If this is the best submission, create payment record
            if ($is_best) {
                $stmt = $pdo->prepare("
                    INSERT INTO payments (
                        certificate_id, amount, status, payment_date
                    ) VALUES (
                        LAST_INSERT_ID(), ?, 'pending', NOW()
                    )
                ");
                $stmt->execute([$submission['budget']]);
            }
        }

        $pdo->commit();
        $success_message = ($action === 'accept') 
            ? ($is_best ? 'Submission accepted as best submission. Certificate issued and payment initiated.' : 'Submission accepted and certificate issued successfully')
            : 'Submission rejected successfully';
        header("Location: company_dashboard.php?success=" . urlencode($success_message));
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: review_submission.php?id=$submission_id&error=" . urlencode($e->getMessage()));
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Submission - ExternIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <div class="container mt-4">
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card company-card">
                    <div class="card-header">
                        <h2 class="card-title company-text">Review Submission</h2>
                        <div class="task-meta mt-2">
                            <span class="badge-outline-company">
                                <i class="bi bi-file-text"></i>
                                <span><?php echo htmlspecialchars($submission['task_title']); ?></span>
                            </span>
                            <span class="badge-outline-company">
                                <i class="bi bi-person"></i>
                                <span><?php echo htmlspecialchars($submission['student_name']); ?></span>
                            </span>
                            <span class="badge-outline-company">
                                <i class="bi bi-clock"></i>
                                <span>Submitted: <?php echo date('M d, Y H:i', strtotime($submission['created_at'])); ?></span>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Submission Details -->
                        <div class="mb-4">
                            <h5>Student's Comments</h5>
                            <div class="p-3 bg-light rounded">
                                <?php echo nl2br(htmlspecialchars($submission['comments'] ?? 'No comments provided')); ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5>Submitted File</h5>
                            <a href="download_submission.php?id=<?php echo $submission_id; ?>" class="btn btn-outline-company">
                                <i class="bi bi-download"></i> Download Submission
                            </a>
                        </div>

                        <!-- Review Form -->
                        <form action="review_submission.php?id=<?php echo $submission_id; ?>" method="POST" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="feedback" class="form-label required-field">Feedback</label>
                                <textarea class="form-control" id="feedback" name="feedback" rows="4" required
                                    placeholder="Provide detailed feedback for the student..."></textarea>
                                <div class="form-text">
                                    Your feedback will help the student understand your decision and improve their work.
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label required-field">Decision</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="action" id="accept" value="accept" required>
                                        <label class="form-check-label" for="accept">
                                            Accept & Issue Certificate
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="action" id="reject" value="reject" required>
                                        <label class="form-check-label" for="reject">
                                            Reject
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4 best-submission-option" style="display: none;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_best" id="is_best">
                                    <label class="form-check-label" for="is_best">
                                        Select as Best Submission (Will receive payment of <?php echo htmlspecialchars($submission['budget']); ?>)
                                    </label>
                                </div>
                                <div class="form-text">
                                    Only one submission can be selected as the best submission and receive payment.
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn company-btn">Submit Review</button>
                                <a href="company_dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Form validation
        (function() {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        document.addEventListener('DOMContentLoaded', function() {
            const acceptRadio = document.getElementById('accept');
            const rejectRadio = document.getElementById('reject');
            const bestSubmissionOption = document.querySelector('.best-submission-option');

            function toggleBestSubmissionOption() {
                bestSubmissionOption.style.display = acceptRadio.checked ? 'block' : 'none';
                if (!acceptRadio.checked) {
                    document.getElementById('is_best').checked = false;
                }
            }

            acceptRadio.addEventListener('change', toggleBestSubmissionOption);
            rejectRadio.addEventListener('change', toggleBestSubmissionOption);
        });
    </script>
</body>
</html> 