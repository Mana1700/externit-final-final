<?php
session_start();
// Check if user is logged in and is a company
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'company') {
    header('Location: login.php');
    exit();
}

require_once 'php/db.php';
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT approval_status, rejection_reason FROM companies WHERE user_id = ?");
$stmt->execute([$user_id]);
$company = $stmt->fetch();
$is_approved = $company && $company['approval_status'] === 'approved';
$is_rejected = $company && $company['approval_status'] === 'rejected';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Task - ExternIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .modal-dialog {
            max-width: 800px;
        }
        .modal-content {
            border: none;
            border-radius: 15px;
        }
        .form-header {
            background-color: #BF6D3A;
            color: white;
            padding: 1.5rem;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        .form-body {
            padding: 2rem;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <?php if (!$is_approved): ?>
        <div class="alert alert-warning text-center mt-4">
            <?php if ($is_rejected): ?>
                Your company was <b>rejected</b> and cannot post tasks. Reason: <b><?php echo htmlspecialchars($company['rejection_reason']); ?></b>
            <?php else: ?>
                Your company is <b>pending approval</b>. You cannot post tasks until an admin approves your company.
            <?php endif; ?>
        </div>
    <?php else: ?>
    <!-- Post Task Button -->
    <div class="d-flex justify-content-center mt-4">
        <button type="button" class="btn company-btn" data-bs-toggle="modal" data-bs-target="#postTaskModal">
            <i class="bi bi-plus-circle"></i> Post New Task
        </button>
    </div>

    <!-- Post Task Modal -->
    <div class="modal fade" id="postTaskModal" tabindex="-1" aria-labelledby="postTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="form-header d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Post a New Task</h2>
                        <p class="text-light mb-0 mt-2">Create an opportunity for students</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="form-body">
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                    <?php endif; ?>

                    <form action="php/post_task_handler.php" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label required-field">Task Title</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>

                                <div class="mb-3">
                                    <label for="difficulty" class="form-label required-field">Difficulty Level</label>
                                    <select class="form-select" id="difficulty" name="difficulty" required>
                                        <option value="">Select Difficulty</option>
                                        <option value="beginner">Beginner</option>
                                        <option value="intermediate">Intermediate</option>
                                        <option value="advanced">Advanced</option>
                                        <option value="expert">Expert</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="due_date" class="form-label required-field">Due Date</label>
                                    <input type="date" class="form-control" id="due_date" name="due_date" required>
                                </div>

                                <div class="mb-3">
                                    <label for="estimated_hours" class="form-label required-field">Estimated Hours</label>
                                    <input type="number" class="form-control" id="estimated_hours" name="estimated_hours" min="1" max="168" required>
                                    <div class="form-text">Estimated hours needed to complete the task (1-168 hours)</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_submissions" class="form-label required-field">Maximum Submissions</label>
                                    <input type="number" class="form-control" id="max_submissions" name="max_submissions" min="1" max="10" required>
                                    <div class="form-text">Maximum submissions per student (1-10)</div>
                                </div>

                                <div class="mb-3">
                                    <label for="reward" class="form-label required-field">Reward (EGP)</label>
                                    <input type="number" class="form-control" id="reward" name="reward" min="100" required>
                                    <div class="form-text">Minimum: 100 EGP</div>
                                </div>

                                <div class="mb-3">
                                    <label for="template_file" class="form-label">Template File</label>
                                    <input type="file" class="form-control" id="template_file" name="template_file">
                                    <div class="form-text">Optional (Max: 10MB)</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label required-field">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="deliverables" class="form-label required-field">Expected Deliverables</label>
                            <textarea class="form-control" id="deliverables" name="deliverables" rows="3" required></textarea>
                            <div class="form-text">Clearly specify what students need to submit</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn company-btn">Post Task</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('due_date').min = today;

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const dueDate = new Date(document.getElementById('due_date').value);
            const today = new Date();
            
            if (dueDate <= today) {
                e.preventDefault();
                alert('Due date must be in the future!');
            }

            const fileInput = document.getElementById('template_file');
            if (fileInput.files.length > 0) {
                const fileSize = fileInput.files[0].size / 1024 / 1024; // in MB
                if (fileSize > 10) {
                    e.preventDefault();
                    alert('Template file size must be less than 10MB!');
                }
            }
        });
    </script>
</body>
</html> 