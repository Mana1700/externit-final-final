<?php
session_start();
require_once 'php/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get task ID from URL
$task_id = $_GET['id'] ?? null;
if (!$task_id) {
    header('Location: ' . ($_SESSION['user_type'] === 'student' ? 'student_dashboard.php' : 'company_dashboard.php'));
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

// Fetch task details
$stmt = $pdo->prepare("
    SELECT t.*, c.name as company_name, u.photo as company_photo,
           (SELECT COUNT(*) FROM submissions s WHERE s.task_id = t.id) as current_submissions
    FROM tasks t
    JOIN companies c ON t.company_id = c.id
    JOIN users u ON c.user_id = u.id
    WHERE t.id = ?
");
$stmt->execute([$task_id]);
$task = $stmt->fetch();

if (!$task) {
    header('Location: ' . ($_SESSION['user_type'] === 'student' ? 'student_dashboard.php' : 'company_dashboard.php'));
    exit();
}

// Check if student has already submitted
$has_submitted = false;
if ($student_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM submissions WHERE task_id = ? AND student_id = ?");
    $stmt->execute([$task_id, $student_id]);
    $has_submitted = $stmt->fetchColumn() > 0;
}

// Check if task is still open for submissions
$is_open = $task['current_submissions'] < $task['max_submissions'] && 
           strtotime($task['deadline']) > time() &&
           $task['status'] === 'active';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Details - ExternIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .task-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .task-card .card-header {
            background: linear-gradient(45deg, #2A9D8F, #264653);
            border: none;
            border-radius: 12px 12px 0 0;
            padding: 1.5rem;
        }

        .task-card .card-header h2 {
            color: white;
            margin: 0 0 1rem 0;
            font-size: 1.75rem;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .task-meta {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .task-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
            padding: 0.375rem 0.875rem;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .task-meta-item i {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .company-photo-small {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .company-photo-placeholder {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.9);
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            margin-right: 0.5rem;
        }

        .task-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.875rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            color: #2A9D8F;
            background-color: rgba(42, 157, 143, 0.1);
            border: 1px solid rgba(42, 157, 143, 0.2);
        }

        .task-badge i {
            font-size: 0.85rem;
            color: #2A9D8F;
        }

        .task-status {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.875rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .task-status.open {
            color: #2D9CDB;
            background-color: rgba(45, 156, 219, 0.1);
            border: 1px solid rgba(45, 156, 219, 0.2);
        }

        .task-status.closed {
            color: #EB5757;
            background-color: rgba(235, 87, 87, 0.1);
            border: 1px solid rgba(235, 87, 87, 0.2);
        }

        .task-status i {
            font-size: 0.85rem;
        }

        .task-progress {
            height: 8px;
            background-color: #E9ECEF;
            border-radius: 4px;
            margin: 1.5rem 0;
            overflow: hidden;
        }

        .task-progress-bar {
            height: 100%;
            background: linear-gradient(45deg, #2A9D8F, #264653);
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .task-description {
            margin-bottom: 2rem;
        }

        .task-description h5 {
            color: #2A9D8F;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .task-description p {
            color: #495057;
            line-height: 1.6;
        }

        .task-footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #E9ECEF;
        }

        /* Base Button Styles - Radius handled by specific classes or modular CSS */
        .btn {
            padding: 0.625rem 1.25rem;
            /* border-radius: 50px; <-- Removed */
            font-size: 0.95rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn i {
            font-size: 1rem;
        }

        .student-btn {
            /* Inherits border-radius: 6px from _buttons.css */
            background-color: #2A9D8F;
            color: white;
            border: none;
            box-shadow: 0 2px 4px rgba(42, 157, 143, 0.2);
        }

        .student-btn:hover {
            background-color: #238177;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(42, 157, 143, 0.3);
        }

        /* Ensure outline buttons use 6px radius */
        .btn-outline-secondary {
            border: 2px solid #6c757d;
            color: #6c757d;
            border-radius: 6px; /* Added */
        }

        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: white;
            transform: translateY(-1px);
        }
        
        .btn-outline-primary {
            /* Assumes this uses Bootstrap's primary color definition */
            /* If not, define colors. Add radius */
             border-radius: 6px; /* Added */
        }

        /* Update alert radius for consistency */
        .alert {
            border: none;
            border-radius: 6px; /* Updated from 12px */
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .alert i {
            font-size: 1.25rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navigation.php'; ?>

<div class="container mt-4">
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_GET['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="container mt-4">
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Back Button -->
        <div class="mb-4">
            <a href="view_tasks.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Tasks
            </a>
        </div>

        <!-- Task Details -->
        <div class="card task-card <?php echo $_SESSION['user_type'] === 'student' ? 'student-task-card' : 'company-task-card'; ?> mb-4">
            <div class="card-header">
                <h2 class="card-title"><?php echo htmlspecialchars($task['title']); ?></h2>
                <div class="task-meta">
                    <div class="task-meta-item">
                        <?php if ($task['company_photo']): ?>
                            <img src="<?php echo htmlspecialchars($task['company_photo']); ?>" alt="<?php echo htmlspecialchars($task['company_name']); ?>" class="company-photo-small">
                        <?php else: ?>
                            <i class="bi bi-building company-photo-placeholder"></i>
                        <?php endif; ?>
                        <span><?php echo htmlspecialchars($task['company_name']); ?></span>
                    </div>
                    <div class="task-meta-item">
                        <i class="bi bi-currency-dollar"></i>
                        <span><?php echo number_format($task['budget']); ?> EGP</span>
                    </div>
                    <div class="task-meta-item">
                        <i class="bi bi-clock"></i>
                        <span><?php echo htmlspecialchars($task['estimated_hours']); ?> hours</span>
                    </div>
                    <div class="task-meta-item">
                        <i class="bi bi-calendar-check"></i>
                        <span><?php echo date('M d, Y', strtotime($task['deadline'])); ?></span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="task-meta mb-3">
                    <span class="task-badge">
                        <i class="bi bi-bar-chart"></i>
                        <?php echo ucfirst(htmlspecialchars($task['difficulty'])); ?>
                    </span>
                    <span class="task-status <?php echo $is_open ? 'open' : 'closed'; ?>">
                        <i class="bi <?php echo $is_open ? 'bi-unlock' : 'bi-lock'; ?>"></i>
                        <?php echo $is_open ? 'Open for Submissions' : 'Closed'; ?>
                    </span>
                </div>

                <div class="task-description">
                    <h5>Description</h5>
                    <p><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
                </div>

                <div class="task-description">
                    <h5>Expected Deliverables</h5>
                    <p><?php echo nl2br(htmlspecialchars($task['deliverables'])); ?></p>
                </div>

                <?php if ($task['template_file_path']): ?>
                    <div class="task-description">
                        <h5><i class="bi bi-file-earmark-text"></i> Sample Work / Template</h5>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle-fill"></i>
                            <div>
                                <strong>Download the template or sample work</strong>
                                <p class="mb-0">This file contains a template or example of what the company is looking for. It will help you understand the expected format and requirements better.</p>
                            </div>
                        </div>
                        <div class="task-footer">
                            <div class="task-actions">
                                <a href="download_template.php?id=<?php echo $task['id']; ?>" class="btn btn-outline-primary" download>
                                    <i class="bi bi-download"></i> Download Template/Sample
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($_SESSION['user_type'] === 'student'): ?>
                    <div class="task-footer">
                        <?php if ($is_open && !$has_submitted): ?>
                            <a href="submit_task.php?task_id=<?php echo $task_id; ?>" class="btn student-btn">
                                <i class="bi bi-upload"></i> Submit Solution
                            </a>
                        <?php elseif ($has_submitted): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> You have already submitted a solution for this task.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> This task is no longer accepting submissions.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 