<?php
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: login.php');
    exit();
}

// Get student profile
$stmt = $pdo->prepare("
    SELECT u.email, u.photo, s.*
    FROM users u
    JOIN students s ON u.id = s.user_id
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();
$student_id = $student['id'];

// Fetch available tasks with company details
$stmt = $pdo->prepare("
    SELECT t.*, c.name as company_name, u.photo as company_photo,
           (SELECT COUNT(*) FROM submissions s WHERE s.task_id = t.id) as current_submissions
    FROM tasks t
    JOIN companies c ON t.company_id = c.id
    JOIN users u ON c.user_id = u.id
    WHERE t.status = 'active' AND t.deadline > NOW()
    ORDER BY t.created_at DESC
    LIMIT 3
");
$stmt->execute();
$tasks = $stmt->fetchAll();

// Get total available tasks count
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total
    FROM tasks t
    WHERE t.status = 'active' AND t.deadline > NOW()
");
$stmt->execute();
$total_tasks = $stmt->fetch()['total'];

// Fetch student's submissions with task details
$stmt = $pdo->prepare("
    SELECT s.*, t.title as task_title, c.name as company_name, u.photo as company_photo,
           t.difficulty, t.budget, t.deadline,
           (SELECT cert.id FROM certificates cert WHERE cert.submission_id = s.id) as certificate_id,
           CASE 
               WHEN s.status = 'pending' THEN 'Under Review'
               WHEN s.status = 'approved' THEN 'Approved'
               WHEN s.status = 'rejected' THEN 'Rejected'
               ELSE s.status
           END as status_text,
           s.status
    FROM submissions s
    JOIN tasks t ON s.task_id = t.id
    JOIN companies c ON t.company_id = c.id
    JOIN users u ON c.user_id = u.id
    WHERE s.student_id = ?
    ORDER BY s.created_at DESC
    LIMIT 3
");
$stmt->execute([$student_id]);
$submissions = $stmt->fetchAll();

// Get total submissions count
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total
    FROM submissions
    WHERE student_id = ?
");
$stmt->execute([$student_id]);
$total_submissions = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - ExternIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <div class="container mt-4">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <h2 class="student-text">Dashboard</h2>
        </div>

        <!-- Profile Section -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 text-center">
                        <div class="profile-picture">
                            <?php if ($student['photo']): ?>
                                <img src="<?php echo htmlspecialchars($student['photo']); ?>" alt="Profile Photo" class="profile-photo">
                            <?php else: ?>
                                <i class="bi bi-person-circle fs-1"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h3 class="mb-1"><?php echo htmlspecialchars($student['name']); ?></h3>
                                <p class="text-muted mb-1"><?php echo htmlspecialchars($student['university']); ?></p>
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($student['major']); ?> - Class of <?php echo $student['graduation_year']; ?></p>
                                <?php if (!empty($student['bio'])): ?>
                                    <div class="student-bio mt-3">
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($student['bio'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <a href="profile.php" class="btn btn-outline-student">
                                <i class="bi bi-pencil-square"></i> Edit Profile
                            </a>
                        </div>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-value">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <?php echo $total_submissions; ?>
                                </div>
                                <div class="stat-label">Total Tasks</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">
                                    <i class="bi bi-check-circle"></i>
                                    <?php 
                                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM submissions WHERE student_id = ? AND status = 'approved'");
                                        $stmt->execute([$student_id]);
                                        echo $stmt->fetchColumn();
                                    ?>
                                </div>
                                <div class="stat-label">Completed</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">
                                    <i class="bi bi-award"></i>
                                    <?php 
                                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM certificates WHERE submission_id IN (SELECT id FROM submissions WHERE student_id = ?)");
                                        $stmt->execute([$student_id]);
                                        echo $stmt->fetchColumn();
                                    ?>
                                </div>
                                <div class="stat-label">Certificates</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Available Tasks -->
            <div class="col-md-7">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="student-text mb-0">Available Tasks</h3>
                    <?php if ($total_tasks > 3): ?>
                        <a href="view_tasks.php" class="btn btn-outline-student">
                            <i class="bi bi-list"></i> View All Tasks
                        </a>
                    <?php endif; ?>
                </div>
                <div class="tasks-container">
                    <?php if ($tasks): ?>
                        <?php foreach ($tasks as $task): ?>
                            <div class="card dashboard-task-card">
                                <div class="card-header">
                                    <h5><?php echo htmlspecialchars($task['title']); ?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="dashboard-meta">
                                        <div class="meta-label">
                                            <?php if ($task['company_photo']): ?>
                                                <img src="<?php echo htmlspecialchars($task['company_photo']); ?>" alt="<?php echo htmlspecialchars($task['company_name']); ?>" class="company-photo-small">
                                            <?php else: ?>
                                                <i class="bi bi-building company-photo-placeholder"></i>
                                            <?php endif; ?>
                                            <span><?php echo htmlspecialchars($task['company_name']); ?></span>
                                        </div>
                                        <div class="meta-label">
                                            <i class="bi bi-currency-dollar"></i>
                                            <span><?php echo number_format($task['budget']); ?> EGP</span>
                                        </div>
                                    </div>

                                    <div class="badge-group">
                                        <span class="badge-outline-student">
                                            <i class="bi bi-bar-chart"></i> <?php echo ucfirst($task['difficulty']); ?>
                                        </span>
                                        <span class="badge-outline-student">
                                            <i class="bi bi-calendar-check"></i> <?php echo date('M d', strtotime($task['deadline'])); ?>
                                        </span>
                                    </div>

                                    <div class="dashboard-progress">
                                        <div class="dashboard-progress-bar" 
                                             style="width: <?php echo ($task['current_submissions'] / $task['max_submissions']) * 100; ?>%">
                                        </div>
                                    </div>
                                    
                                    <div class="card-actions">
                                        <span class="badge-base">
                                            <i class="bi bi-people"></i> <?php echo $task['current_submissions']; ?>/<?php echo $task['max_submissions']; ?> Submissions
                                        </span>
                                        <a href="view_task.php?id=<?php echo $task['id']; ?>" class="btn btn-primary-student btn-sm">
                                                <i class="bi bi-eye"></i> View Details
                                            </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-student">
                            No tasks available at the moment. Check back later!
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- My Submissions -->
            <div class="col-md-5">
                <div class="submissions-section">
                    <div class="section-header">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="mb-0"><i class="bi bi-file-earmark-text"></i> My Submissions</h4>
                            <?php if ($total_submissions > 3): ?>
                                <a href="my_submissions.php" class="btn btn-outline-student btn-sm">
                                    <i class="bi bi-list"></i> View All
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="submissions-container">
                        <?php if ($submissions): ?>
                            <?php foreach ($submissions as $submission): ?>
                                <div class="card dashboard-task-card">
                                    <div class="card-body">
                                        <!-- Title -->
                                        <h5 class="card-title mb-3"><?php echo htmlspecialchars($submission['task_title']); ?></h5>
                                        
                                        <!-- Company and Price Row -->
                                        <div class="company-price-row">
                                            <div class="company-info">
                                                <?php if ($submission['company_photo']): ?>
                                                    <img src="<?php echo htmlspecialchars($submission['company_photo']); ?>" alt="<?php echo htmlspecialchars($submission['company_name']); ?>" class="company-photo-small">
                                                <?php else: ?>
                                                    <i class="bi bi-building"></i>
                                                <?php endif; ?>
                                                <span><?php echo htmlspecialchars($submission['company_name']); ?></span>
                                            </div>
                                            <div class="price-tag">
                                                <i class="bi bi-currency-dollar"></i>
                                                <span><?php echo number_format($submission['budget']); ?> EGP</span>
                                            </div>
                                        </div>

                                        <!-- Level and Deadline Row -->
                                        <div class="task-meta-row">
                                            <div class="level-badge">
                                                <i class="bi bi-bar-chart"></i>
                                                <span><?php echo ucfirst($submission['difficulty']); ?></span>
                                            </div>
                                            <div class="deadline-badge">
                                                <i class="bi bi-calendar"></i>
                                                <span><?php echo date('M d, Y', strtotime($submission['created_at'])); ?></span>
                                            </div>
                                        </div>

                                        <!-- Actions and Status Row -->
                                        <div class="actions-status-row">
                                            <div class="action-buttons">
                                                <?php if (($submission['status'] === 'approved' || $submission['status'] === 'accepted') && isset($submission['certificate_id'])): ?>
                                                    <a href="generate_certificate_pdf.php?id=<?php echo $submission['certificate_id']; ?>" class="btn btn-certificate">
                                                        <i class="bi bi-award"></i> Certificate
                                                    </a>
                                                <?php endif; ?>
                                                <a href="view_submission.php?id=<?php echo $submission['id']; ?>" class="btn btn-view-details">
                                                    <i class="bi bi-eye"></i> View Details
                                                </a>
                                            </div>
                                            <div class="badge-status <?php echo strtolower($submission['status']); ?>">
                                                <?php
                                                    $icon = 'bi-clock';
                                                    switch ($submission['status']) {
                                                        case 'approved':
                                                            $icon = 'bi-check-circle-fill';
                                                            break;
                                                        case 'rejected':
                                                            $icon = 'bi-x-circle-fill';
                                                            break;
                                                        case 'pending':
                                                            $icon = 'bi-hourglass-split';
                                                            break;
                                                    }
                                                ?>
                                                <i class="bi <?php echo $icon; ?>"></i>
                                                <?php echo $submission['status_text']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-student">
                                <i class="bi bi-info-circle"></i>
                                No submissions yet. Start working on tasks to build your portfolio!
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 