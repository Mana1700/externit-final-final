<?php
session_start();
require_once 'php/db.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: login.php');
    exit();
}

// Get task ID from URL
$task_id = $_GET['task_id'] ?? null;
if (!$task_id) {
    header('Location: student_dashboard.php');
    exit();
}

// Get student ID
$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();
$student_id = $student['id'];

// Check if student has already submitted
$stmt = $pdo->prepare("SELECT COUNT(*) FROM submissions WHERE task_id = ? AND student_id = ?");
$stmt->execute([$task_id, $student_id]);
if ($stmt->fetchColumn() > 0) {
    header('Location: student_dashboard.php?error=You have already submitted a solution for this task');
    exit();
}

// Fetch task details
$stmt = $pdo->prepare("
    SELECT t.*, c.name as company_name,
           (SELECT COUNT(*) FROM submissions s WHERE s.task_id = t.id) as current_submissions
    FROM tasks t
    JOIN companies c ON t.company_id = c.id
    WHERE t.id = ?
");
$stmt->execute([$task_id]);
$task = $stmt->fetch();

// Check if task exists and is still open
if (!$task || 
    $task['status'] !== 'active' || 
    strtotime($task['deadline']) <= time() || 
    $task['current_submissions'] >= $task['max_submissions']) {
    header('Location: student_dashboard.php?error=This task is no longer accepting submissions');
    exit();
}

$page_title = "Submit Solution - " . $task['title'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        /* Add styles consistent with other pages */
        .task-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            background: white;
        }
        .task-card .card-header {
            background: linear-gradient(45deg, #2A9D8F, #264653);
            border: none;
            border-radius: 12px 12px 0 0;
            padding: 1.5rem;
        }
        .task-card .card-header .card-title {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .task-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .task-meta-item {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.875rem;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            background-color: rgba(255, 255, 255, 0.2); /* Light background for contrast */
            color: white; /* White text */
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .task-meta-item i {
            font-size: 0.9rem;
            color: white; /* White icon */
        }
        .required-field::after {
            content: " *";
            color: #dc3545; /* Bootstrap danger color */
        }
        .student-btn {
            background-color: #2A9D8F;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 0.625rem 1.25rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        .student-btn:hover {
            background-color: #238177;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(42, 157, 143, 0.2);
        }
    </style>
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

        <!-- Back Button -->
        <div class="mb-4">
            <a href="view_task.php?id=<?php echo $task_id; ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Task
            </a>
        </div>

        <div class="card task-card student-task-card">
            <div class="card-header">
                <h2 class="card-title">Submit Solution</h2>
                <div class="task-meta">
                    <div class="task-meta-item">
                        <i class="bi bi-file-earmark-text"></i>
                        <span><?php echo htmlspecialchars($task['title']); ?></span>
                    </div>
                    <div class="task-meta-item">
                        <i class="bi bi-building"></i>
                        <span><?php echo htmlspecialchars($task['company_name']); ?></span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="includes/components/handlers/submit_task_handler.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
                    
                    <div class="mb-4">
                        <label for="brief" class="form-label required-field">Brief Description</label>
                        <textarea class="form-control" id="brief" name="brief" rows="4" required 
                                placeholder="Provide a brief description of your solution and any notes for the reviewer..."></textarea>
                        <div class="form-text">
                            Explain your approach and any important details about your submission.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="solution_file" class="form-label required-field">Solution File</label>
                        <input type="file" class="form-control" id="solution_file" name="solution_file" required>
                        <div class="form-text">
                            Maximum file size: 10MB. Accepted formats: zip, rar, pdf, doc, docx
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <button type="submit" class="btn student-btn">
                            <i class="bi bi-cloud-upload"></i> Submit Solution
                        </button>
                        <div class="task-meta-item" style="background-color: transparent; border: none; color: #6c757d;">
                            <i class="bi bi-calendar-check" style="color: #6c757d;"></i>
                            <span>Deadline: <?php echo date('M d, Y', strtotime($task['deadline'])); ?></span>
                        </div>
                    </div>
                </form>
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

        // File size validation
        document.getElementById('solution_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 10 * 1024 * 1024) { // 10MB in bytes
                    alert('File size must be less than 10MB');
                    e.target.value = '';
                }

                const allowedTypes = ['application/zip', 'application/x-rar-compressed', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Invalid file type. Please upload a zip, rar, pdf, doc, or docx file.');
                    e.target.value = '';
                }
            }
        });
    </script>
</body>
</html> 