<?php
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'company') {
    header('Location: login.php');
    exit();
}

// Get company profile
$stmt = $pdo->prepare("
    SELECT u.email, u.photo, c.*
    FROM users u
    JOIN companies c ON u.id = c.user_id
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$company = $stmt->fetch();
$company_id = $company['id'];

// Fetch pending submissions with student details
$stmt = $pdo->prepare("
    SELECT s.*, t.title as task_title, st.name as student_name, u.photo as student_photo,
           st.university, st.major, st.graduation_year, st.bio, st.phone,
           u.email as student_email,
           t.difficulty, t.budget, t.deadline,
           CASE 
               WHEN s.status = 'pending' THEN 'Under Review'
               WHEN s.status = 'approved' THEN 'Approved'
               WHEN s.status = 'rejected' THEN 'Rejected'
               ELSE s.status
           END as status_text,
           s.status, s.comments,
           (SELECT COUNT(*) 
            FROM submissions s2 
            JOIN tasks t2 ON s2.task_id = t2.id 
            WHERE s2.student_id = st.id 
            AND (s2.status = 'approved' OR s2.status = 'accepted')
           ) as completed_tasks
    FROM submissions s
    JOIN tasks t ON s.task_id = t.id
    JOIN students st ON s.student_id = st.id
    JOIN users u ON st.user_id = u.id
    WHERE t.company_id = ? AND s.status = 'pending'
    ORDER BY s.created_at DESC
    LIMIT 3
");
$stmt->execute([$company_id]);
$submissions = $stmt->fetchAll();

// Get total pending submissions count
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total
    FROM submissions s
    JOIN tasks t ON s.task_id = t.id
    WHERE t.company_id = ? AND s.status = 'pending'
");
$stmt->execute([$company_id]);
$total_submissions = $stmt->fetch()['total'];

// Fetch active tasks
$stmt = $pdo->prepare("
    SELECT t.*,
           (SELECT COUNT(*) FROM submissions s WHERE s.task_id = t.id) as current_submissions
    FROM tasks t
    WHERE t.company_id = ? AND t.status = 'active' AND t.deadline > NOW()
    ORDER BY t.created_at DESC
    LIMIT 3
");
$stmt->execute([$company_id]);
$tasks = $stmt->fetchAll();

// Get total active tasks count
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total
    FROM tasks
    WHERE company_id = ? AND status = 'active' AND deadline > NOW()
");
$stmt->execute([$company_id]);
$total_tasks = $stmt->fetch()['total'];

$is_approved = $company['approval_status'] === 'approved';
$is_rejected = $company['approval_status'] === 'rejected';

if ($company['approval_status'] === 'approved' && isset($_GET['just_approved'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Congratulations! Your company has been <b>approved</b> and you can now post tasks.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard - ExternIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body class="company-view">
    <!-- Navigation -->
    <?php include 'includes/navigation.php'; ?>

    <div class="container mt-4">
        <?php
        if ($company['approval_status'] === 'pending') {
            echo '<div class="alert alert-warning">Your company is pending approval. You cannot post tasks yet.</div>';
        } elseif ($company['approval_status'] === 'rejected') {
            echo '<div class="alert alert-danger">Your company was rejected. Reason: ' . htmlspecialchars($company['rejection_reason']) . '</div>';
        }
        ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Profile Section -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 text-center">
                        <div class="profile-picture">
                            <?php if ($company['photo']): ?>
                                <img src="<?php echo htmlspecialchars($company['photo']); ?>" alt="Profile Photo" class="profile-photo">
                            <?php else: ?>
                                <i class="bi bi-building fs-1"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h3 class="mb-1"><?php echo htmlspecialchars($company['name']); ?></h3>
                                <p class="text-muted mb-1"><?php echo htmlspecialchars($company['industry']); ?></p>
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($company['website']); ?></p>
                            </div>
                            <a href="profile.php" class="btn btn-outline-company">
                                <i class="bi bi-pencil-square"></i> Edit Profile
                            </a>
                        </div>
                        <div class="company-stats-grid">
                            <div class="company-stat-item">
                                <div class="company-stat-value">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <?php echo $total_tasks; ?>
                                </div>
                                <div class="company-stat-label">Active Tasks</div>
                            </div>
                            <div class="company-stat-item">
                                <div class="company-stat-value">
                                    <i class="bi bi-people"></i>
                                    <?php 
                                        $stmt = $pdo->prepare("
                                            SELECT COUNT(DISTINCT student_id) 
                                            FROM submissions s 
                                            JOIN tasks t ON s.task_id = t.id 
                                            WHERE t.company_id = ?
                                        ");
                                        $stmt->execute([$company_id]);
                                        echo $stmt->fetchColumn();
                                    ?>
                                </div>
                                <div class="company-stat-label">Students</div>
                            </div>
                            <div class="company-stat-item">
                                <div class="company-stat-value">
                                    <i class="bi bi-check-circle"></i>
                                    <?php 
                                        $stmt = $pdo->prepare("
                                            SELECT COUNT(*) 
                                            FROM submissions s 
                                            JOIN tasks t ON s.task_id = t.id 
                                            WHERE t.company_id = ? AND (s.status = 'approved' OR s.status = 'accepted')
                                        ");
                                        $stmt->execute([$company_id]);
                                        echo $stmt->fetchColumn();
                                    ?>
                                </div>
                                <div class="company-stat-label">Completed</div>
                            </div>
                            <div class="company-stat-item">
                                <div class="company-stat-value">
                                    <i class="bi bi-currency-dollar"></i>
                                    <?php 
                                        $stmt = $pdo->prepare("
                                            SELECT COALESCE(SUM(t.budget), 0) 
                                            FROM submissions s 
                                            JOIN tasks t ON s.task_id = t.id 
                                            WHERE t.company_id = ? AND (s.status = 'approved' OR s.status = 'accepted')
                                        ");
                                        $stmt->execute([$company_id]);
                                        echo number_format($stmt->fetchColumn());
                                    ?>
                                </div>
                                <div class="company-stat-label">EGP Paid</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Task CTA -->
        <div class="create-task-card mb-4">
            <div class="d-flex align-items-center justify-content-between p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="create-task-icon">
                        <i class="bi bi-lightbulb-fill"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 fw-semibold">Ready to Find Your Next Talent?</h4>
                        <p class="mb-0 text-muted">Create a task and connect with skilled students</p>
                    </div>
                </div>
                <?php if (!$is_approved): ?>
                    <button type="button" class="btn btn-primary-company create-task-btn" data-bs-toggle="modal" data-bs-target="#cannotPostTaskModal">
                        <i class="bi bi-plus-circle-fill me-2"></i>Create Task
                    </button>
                    <!-- Cannot Post Task Modal -->
                    <div class="modal fade" id="cannotPostTaskModal" tabindex="-1" aria-labelledby="cannotPostTaskModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-warning-subtle">
                                    <h5 class="modal-title" id="cannotPostTaskModalLabel">Cannot Post Task</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <?php if ($is_rejected): ?>
                                        <div class="alert alert-danger mb-3">Your company was <b>rejected</b> and cannot post tasks.</div>
                                        <div><b>Reason:</b> <?php echo htmlspecialchars($company['rejection_reason']); ?></div>
                                    <?php else: ?>
                                        <div class="alert alert-warning mb-0">Your company is <b>pending approval</b>. You cannot post tasks until an admin approves your company.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <button type="button" class="btn btn-primary-company create-task-btn" data-bs-toggle="modal" data-bs-target="#postTaskModal">
                        <i class="bi bi-plus-circle-fill me-2"></i>Create Task
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <!-- Pending Submissions -->
            <div class="col-md-7">
                <div class="tasks-section">
                    <div class="section-header">
                <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="company-section-header mb-0">
                        <i class="bi bi-inbox"></i>
                        Pending Submissions
                            </h4>
                        </div>
                </div>
                    
                    <div class="company-tasks-container">
                    <?php if ($submissions): ?>
                        <?php foreach ($submissions as $submission): ?>
                                <div class="card company-task-card mb-4">
                                <div class="card-header">
                                        <h2 class="card-title"><?php echo htmlspecialchars($submission['task_title']); ?></h2>
                                        <div class="student-info">
                                            <div class="student-photo-container">
                                                <?php if ($submission['student_photo']): ?>
                                                    <img src="<?php echo htmlspecialchars($submission['student_photo']); ?>" 
                                                         alt="<?php echo htmlspecialchars($submission['student_name']); ?>" 
                                                         class="company-photo-small">
                                                <?php else: ?>
                                                    <i class="bi bi-person company-photo-placeholder"></i>
                                                <?php endif; ?>
                                                <span class="student-name"><?php echo htmlspecialchars($submission['student_name']); ?></span>
                                            </div>
                                            <div class="completed-tasks">
                                                <i class="bi bi-check-circle"></i>
                                                <span><?php echo $submission['completed_tasks']; ?> Tasks Completed</span>
                                            </div>
                                            <button type="button" class="btn btn-view-profile" data-bs-toggle="modal" data-bs-target="#studentProfileModal<?php echo $submission['student_id']; ?>">
                                                <i class="bi bi-person-badge"></i> View Profile
                                            </button>
                                        </div>
                                </div>
                                <div class="card-body">
                                        <div class="task-meta mb-3">
                                            <span class="task-badge">
                                                <i class="bi bi-currency-dollar"></i>
                                                <span><?php echo number_format($submission['budget']); ?> EGP</span>
                                            </span>
                                            <span class="task-status open">
                                                <i class="bi bi-hourglass-split"></i>
                                                <?php echo htmlspecialchars($submission['status_text']); ?>
                                            </span>
                                        </div>

                                        <div class="task-description">
                                            <h5>Submission Comments</h5>
                                            <p><?php echo !empty($submission['comments']) ? nl2br(htmlspecialchars($submission['comments'])) : 'No comments provided.'; ?></p>
                                        </div>

                                        <div class="task-footer">
                                            <div class="task-actions">
                                                <a href="review_submission.php?id=<?php echo $submission['id']; ?>" class="btn btn-primary-company">
                                                    <i class="bi bi-eye"></i> Review Submission
                                                </a>
                                            </div>
                                        </div>
                                        </div>
                                    </div>

                                <!-- Student Profile Modal -->
                                <div class="modal fade" id="studentProfileModal<?php echo $submission['student_id']; ?>" tabindex="-1" aria-labelledby="studentProfileModalLabel<?php echo $submission['student_id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header student-profile-header">
                                                <div class="header-content">
                                                    <div class="profile-section">
                                                        <?php if ($submission['student_photo']): ?>
                                                            <div class="profile-photo-wrapper">
                                                                <img src="<?php echo htmlspecialchars($submission['student_photo']); ?>" 
                                                                     alt="<?php echo htmlspecialchars($submission['student_name']); ?>" 
                                                                     class="profile-photo-modal">
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="profile-photo-wrapper">
                                                                <div class="profile-photo-placeholder-modal">
                                                                    <i class="bi bi-person-circle"></i>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="profile-info">
                                                            <h5 class="modal-title" id="studentProfileModalLabel<?php echo $submission['student_id']; ?>">
                                                                <?php echo htmlspecialchars($submission['student_name']); ?>
                                                            </h5>
                                                            <div class="profile-meta">
                                                                <span class="university">
                                                                    <i class="bi bi-building"></i>
                                                                    <?php echo htmlspecialchars($submission['university']); ?>
                                        </span>
                                                                <span class="major">
                                                                    <i class="bi bi-book"></i>
                                                                    <?php echo htmlspecialchars($submission['major']); ?>
                                        </span>
                                    </div>
                                                        </div>
                                                    </div>
                                                    <div class="stats-preview">
                                                        <div class="stat-badge">
                                                            <i class="bi bi-check-circle"></i>
                                                            <span><?php echo $submission['completed_tasks']; ?> Tasks</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-4">
                                                    <div class="col-md-6">
                                                        <div class="student-details-card">
                                                            <div class="card-icon">
                                                                <i class="bi bi-person-lines-fill"></i>
                                                            </div>
                                                            <h6 class="section-title">Contact Information</h6>
                                                            <div class="contact-info">
                                                                <div class="contact-item">
                                                                    <div class="contact-icon">
                                                                        <i class="bi bi-envelope"></i>
                                                                    </div>
                                                                    <div class="contact-details">
                                                                        <span class="label">Email Address</span>
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <span class="value"><?php echo htmlspecialchars($submission['student_email']); ?></span>
                                                                            <a href="mailto:<?php echo htmlspecialchars($submission['student_email']); ?>" class="btn btn-sm btn-outline-company">
                                                                                <i class="bi bi-send"></i>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <?php if ($submission['phone']): ?>
                                                                <div class="contact-item">
                                                                    <div class="contact-icon">
                                                                        <i class="bi bi-telephone"></i>
                                                                    </div>
                                                                    <div class="contact-details">
                                                                        <span class="label">Phone Number</span>
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <span class="value"><?php echo htmlspecialchars($submission['phone']); ?></span>
                                                                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $submission['phone']); ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                                                                <i class="bi bi-whatsapp"></i>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="student-details-card">
                                                            <div class="card-icon">
                                                                <i class="bi bi-mortarboard"></i>
                                                            </div>
                                                            <h6 class="section-title">Education</h6>
                                                            <div class="education-info">
                                                                <div class="education-item">
                                                                    <div class="education-icon">
                                                                        <i class="bi bi-building"></i>
                                                                    </div>
                                                                    <div class="education-details">
                                                                        <span class="label">University</span>
                                                                        <span class="value"><?php echo htmlspecialchars($submission['university']); ?></span>
                                                                    </div>
                                                                </div>
                                                                <div class="education-item">
                                                                    <div class="education-icon">
                                                                        <i class="bi bi-book"></i>
                                                                    </div>
                                                                    <div class="education-details">
                                                                        <span class="label">Major</span>
                                                                        <span class="value"><?php echo htmlspecialchars($submission['major']); ?></span>
                                                                    </div>
                                                                </div>
                                                                <div class="education-item">
                                                                    <div class="education-icon">
                                                                        <i class="bi bi-calendar"></i>
                                                                    </div>
                                                                    <div class="education-details">
                                                                        <span class="label">Graduation Year</span>
                                                                        <span class="value"><?php echo htmlspecialchars($submission['graduation_year']); ?></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php if ($submission['bio']): ?>
                                                <div class="student-details-card mt-4">
                                                    <div class="card-icon">
                                                        <i class="bi bi-person-badge"></i>
                                                    </div>
                                                    <h6 class="section-title">About</h6>
                                                    <div class="bio-content">
                                                        <?php echo nl2br(htmlspecialchars($submission['bio'])); ?>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-company">
                                <i class="bi bi-info-circle"></i>
                            No pending submissions at the moment.
                        </div>
                    <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Active Tasks -->
            <div class="col-md-5">
                <div class="tasks-section">
                    <div class="section-header">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="company-section-header mb-0">
                                <i class="bi bi-list-task"></i>
                                Active Tasks
                            </h4>
                            <div class="d-flex gap-2">
                                <?php if ($total_tasks > 3): ?>
                                    <a href="manage_tasks.php" class="btn btn-outline-company btn-sm">
                                        <i class="bi bi-list"></i> View All
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="company-tasks-container">
                        <?php if ($tasks): ?>
                            <?php foreach ($tasks as $task): ?>
                                <div class="company-dashboard-task-card">
                                    <div class="card-header">
                                        <h5><?php echo htmlspecialchars($task['title']); ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="company-dashboard-meta">
                                            <div class="company-meta-label">
                                                <i class="bi bi-currency-dollar"></i>
                                                <span><?php echo number_format($task['budget']); ?> EGP</span>
                                            </div>
                                            <div class="company-meta-label">
                                                <i class="bi bi-calendar"></i>
                                                <span><?php echo date('M d', strtotime($task['deadline'])); ?></span>
                                            </div>
                                        </div>

                                        <div class="badge-group">
                                            <span class="badge-outline-company">
                                                <i class="bi bi-bar-chart"></i> <?php echo ucfirst($task['difficulty']); ?>
                                            </span>
                                            <span class="badge-outline-company">
                                                <i class="bi bi-people"></i> <?php echo $task['current_submissions']; ?> Submissions
                                            </span>
                                        </div>

                                        <div class="company-progress">
                                            <div class="company-progress-bar" 
                                                 style="width: <?php echo ($task['current_submissions'] / $task['max_submissions']) * 100; ?>%">
                                            </div>
                                        </div>
                                        
                                        <div class="card-actions">
                                            <a href="manage_task.php?id=<?php echo $task['id']; ?>" class="btn btn-primary-company btn-sm">
                                                <i class="bi bi-eye"></i> View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-company">
                                <i class="bi bi-info-circle"></i>
                                No active tasks. Create a new task to get started!
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

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

                    <form action="includes/components/create_task_handler.php" method="POST" enctype="multipart/form-data" id="createTaskForm">
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
                                    <input type="number" class="form-control" id="max_submissions" name="max_submissions" min="1" max="100" required>
                                    <div class="form-text">Maximum number of students allowed to submit their contributions (1-100)</div>
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
        .create-task-card {
            background: white;
            border-radius: var(--border-radius-lg);
            border: 1px solid rgba(191, 109, 58, 0.1);
            box-shadow: 0 2px 4px rgba(191, 109, 58, 0.05);
        }

        .create-task-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(45deg, var(--company-primary), var(--company-secondary));
            border-radius: 12px;
            color: white;
            font-size: 1.5rem;
        }

        .create-task-btn {
            padding: 0.5rem 1.25rem;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .create-task-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(191, 109, 58, 0.2);
        }

        @media (max-width: 768px) {
            .create-task-card > div {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
            
            .create-task-btn {
                width: 100%;
            }
        }

        .company-task-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            background: white;
        }

        .company-task-card .card-header {
            background: linear-gradient(45deg, var(--company-primary), var(--company-secondary));
            border: none;
            border-radius: 12px 12px 0 0;
            padding: 1.5rem;
        }

        .company-task-card .card-header .card-title {
            color: white;
            margin: 0 0 1rem 0;
            font-size: 1.1rem;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .company-task-card .task-meta {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .company-task-card .task-meta-item {
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

        .company-task-card .task-meta-item i {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .company-task-card .company-photo-small {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .company-task-card .company-photo-placeholder {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.9);
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .company-task-card .task-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.875rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--company-primary);
            background-color: rgba(var(--company-primary-rgb), 0.1);
            border: 1px solid rgba(var(--company-primary-rgb), 0.2);
        }

        .company-task-card .task-badge i {
            font-size: 0.85rem;
            color: var(--company-primary);
        }

        .company-task-card .task-status {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.875rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .company-task-card .task-status.open {
            color: var(--company-primary);
            background-color: rgba(var(--company-primary-rgb), 0.1);
            border: 1px solid rgba(var(--company-primary-rgb), 0.2);
        }

        .company-task-card .task-description {
            margin-bottom: 2rem;
        }

        .company-task-card .task-description h5 {
            color: var(--company-primary);
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .company-task-card .task-description p {
            color: #495057;
            line-height: 1.6;
        }

        .company-task-card .task-footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #E9ECEF;
        }

        .company-task-card .btn-primary-company {
            background-color: var(--company-primary);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 0.625rem 1.25rem;
            font-size: 0.95rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .company-task-card .btn-primary-company:hover {
            background-color: var(--company-secondary);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(var(--company-primary-rgb), 0.3);
        }

        .student-profile-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .student-profile-link:hover {
            color: white;
            transform: translateY(-1px);
        }

        .student-profile-link .company-photo-small {
            border-color: rgba(255, 255, 255, 0.3);
        }

        .student-profile-link:hover .company-photo-small {
            border-color: rgba(255, 255, 255, 0.5);
        }

        .task-meta-item i {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .task-badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }

        .task-badge i {
            font-size: 1rem;
        }

        .student-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1.25rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.2s ease;
            margin-bottom: 1rem;
        }

        .student-info:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
        }

        .student-photo-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .student-name {
            color: rgba(255, 255, 255, 0.95);
            font-weight: 500;
            font-size: 0.95rem;
        }

        .completed-tasks {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.75rem;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 6px;
            color: rgba(255, 255, 255, 0.95);
            font-size: 0.85rem;
            font-weight: 500;
        }

        .completed-tasks i {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.95);
        }

        .btn-view-profile {
            margin-left: auto;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background: white;
            border: none;
            border-radius: 8px;
            color: var(--company-primary);
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-view-profile:hover {
            background: var(--company-primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-view-profile i {
            font-size: 1.1rem;
            color: var(--company-primary);
        }

        .btn-view-profile:hover i {
            color: white;
        }
    </style>

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