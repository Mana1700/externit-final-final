<?php
session_start();
require_once 'php/db.php';

// Check if user is logged in and is a company
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'company') {
    header('Location: login.php');
    exit();
}

// Get company ID and details
$stmt = $pdo->prepare("
    SELECT c.*, u.photo
    FROM companies c
    JOIN users u ON c.user_id = u.id
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$company = $stmt->fetch();
$company_id = $company['id'];

// Get filter parameters
$status = $_GET['status'] ?? 'pending';
$sort = $_GET['sort'] ?? 'newest';
$search = $_GET['search'] ?? '';
$task_id = $_GET['task_id'] ?? null;

// Get counts for each status
$stmt = $pdo->prepare("
    SELECT 
        COUNT(CASE WHEN s.status = 'pending' THEN 1 END) as pending,
        COUNT(CASE WHEN s.status = 'accepted' THEN 1 END) as accepted,
        COUNT(CASE WHEN s.status = 'rejected' THEN 1 END) as rejected
    FROM submissions s
    JOIN tasks t ON s.task_id = t.id
    WHERE t.company_id = ?
");
$stmt->execute([$company_id]);
$counts = $stmt->fetch();

// Get tasks with submission counts
$query = "
    SELECT t.*, 
           COUNT(s.id) as total_submissions,
           COUNT(CASE WHEN s.status = 'pending' THEN 1 END) as pending_submissions
    FROM tasks t
    LEFT JOIN submissions s ON t.id = s.task_id
    WHERE t.company_id = ?
    GROUP BY t.id
";

$params = [$company_id];

// Add search filter for tasks
if (!empty($search)) {
    $query .= " AND t.title LIKE ?";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
}

// Add sorting for tasks
switch ($sort) {
    case 'oldest':
        $query .= " ORDER BY t.created_at ASC";
        break;
    case 'budget':
        $query .= " ORDER BY t.budget DESC";
        break;
    case 'difficulty':
        $query .= " ORDER BY 
            CASE t.difficulty 
                WHEN 'expert' THEN 1 
                WHEN 'advanced' THEN 2 
                WHEN 'intermediate' THEN 3 
                WHEN 'beginner' THEN 4 
            END";
        break;
    default: // newest
        $query .= " ORDER BY t.created_at DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

// If a task is selected, get its submissions
$submissions = [];
if ($task_id) {
    $query = "
        SELECT s.*, t.title as task_title, t.budget, t.difficulty,
               st.name as student_name, st.university, st.major,
               u.photo as student_photo
        FROM submissions s
        JOIN tasks t ON s.task_id = t.id
        JOIN students st ON s.student_id = st.id
        JOIN users u ON st.user_id = u.id
        WHERE t.id = ?
    ";

    $params = [$task_id];

    // Add status filter for submissions
    if ($status !== 'all') {
        $query .= " AND s.status = ?";
        $params[] = $status;
    }

    $query .= " ORDER BY s.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $submissions = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Submissions - ExternIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .task-card {
            border: none;
            border-radius: var(--border-radius-lg);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-bottom: 1.5rem;
            cursor: pointer;
        }

        .task-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .task-card.active {
            border: 2px solid var(--company-primary);
        }

        .task-card .card-header {
            background: linear-gradient(45deg, var(--company-primary), var(--company-secondary));
            color: white;
            border: none;
            border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
            padding: 1.25rem;
        }

        .task-card .card-header h5 {
            color: white;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .task-card .card-body {
            padding: 1.5rem;
        }

        .task-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .task-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.875rem;
        }

        .submission-card {
            border: none;
            border-radius: var(--border-radius-lg);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-bottom: 1.5rem;
        }

        .submission-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .submission-card .card-header {
            background: linear-gradient(45deg, var(--company-primary), var(--company-secondary));
            color: white;
            border: none;
            border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
            padding: 1.25rem;
        }

        .submission-card .card-body {
            padding: 1.5rem;
        }

        .student-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .student-photo {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .student-photo-placeholder {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .student-photo-placeholder i {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-badge.pending {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.2);
        }

        .status-badge.accepted {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.2);
        }

        .status-badge.rejected {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }

        .filter-section {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .stats-card {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 1rem;
            border-radius: var(--border-radius);
            background: rgba(var(--company-primary-rgb), 0.05);
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--company-primary);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .search-box {
            position: relative;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .search-box input {
            padding-left: 2.5rem;
        }

        .back-to-tasks {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--company-primary);
            text-decoration: none;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .back-to-tasks:hover {
            color: var(--company-secondary);
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filter-section {
                padding: 1rem;
            }

            .task-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body class="company-view">
    <?php include 'includes/navigation.php'; ?>

    <div class="container mt-4">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!$task_id): ?>
            <!-- Stats Section -->
            <div class="stats-card">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $counts['pending']; ?></div>
                        <div class="stat-label">Pending Submissions</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $counts['accepted']; ?></div>
                        <div class="stat-label">Accepted Submissions</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $counts['rejected']; ?></div>
                        <div class="stat-label">Rejected Submissions</div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <form action="review_submissions.php" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" class="form-control" name="search" placeholder="Search tasks..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="sort">
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                            <option value="budget" <?php echo $sort === 'budget' ? 'selected' : ''; ?>>Highest Budget</option>
                            <option value="difficulty" <?php echo $sort === 'difficulty' ? 'selected' : ''; ?>>Difficulty Level</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary-company w-100">Apply Filters</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($task_id): ?>
            <!-- Back to Tasks Link -->
            <a href="review_submissions.php" class="back-to-tasks">
                <i class="bi bi-arrow-left"></i>
                Back to Tasks
            </a>

            <!-- Submissions Filter -->
            <div class="filter-section mb-4">
                <form action="review_submissions.php" method="GET" class="row g-3">
                    <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
                    <div class="col-md-4">
                        <select class="form-select" name="status">
                            <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="accepted" <?php echo $status === 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                            <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary-company w-100">Apply Filter</button>
                    </div>
                </form>
            </div>

            <!-- Submissions List -->
            <?php if (empty($submissions)): ?>
                <div class="alert alert-company">
                    <i class="bi bi-info-circle"></i>
                    No submissions found for this task.
                </div>
            <?php else: ?>
                <?php foreach ($submissions as $submission): ?>
                    <div class="submission-card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="mb-2"><?php echo htmlspecialchars($submission['task_title']); ?></h5>
                                    <div class="task-meta">
                                        <div class="task-meta-item">
                                            <i class="bi bi-bar-chart"></i>
                                            <span><?php echo ucfirst($submission['difficulty']); ?></span>
                                        </div>
                                        <div class="task-meta-item">
                                            <i class="bi bi-currency-dollar"></i>
                                            <span><?php echo number_format($submission['budget']); ?> EGP</span>
                                        </div>
                                    </div>
                                </div>
                                <span class="status-badge <?php echo $submission['status']; ?>">
                                    <i class="bi bi-circle-fill"></i>
                                    <?php echo ucfirst($submission['status']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="student-info">
                                <?php if ($submission['student_photo']): ?>
                                    <img src="<?php echo htmlspecialchars($submission['student_photo']); ?>" 
                                         alt="<?php echo htmlspecialchars($submission['student_name']); ?>" 
                                         class="student-photo">
                                <?php else: ?>
                                    <div class="student-photo-placeholder">
                                        <i class="bi bi-person-circle"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($submission['student_name']); ?></h6>
                                    <p class="mb-0 text-muted">
                                        <?php echo htmlspecialchars($submission['university']); ?> - 
                                        <?php echo htmlspecialchars($submission['major']); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted">
                                    <i class="bi bi-clock"></i>
                                    Submitted: <?php echo date('M d, Y H:i', strtotime($submission['created_at'])); ?>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="review_submission.php?id=<?php echo $submission['id']; ?>" 
                                       class="btn btn-primary-company">
                                        <i class="bi bi-eye"></i> Review
                                    </a>
                                    <a href="download_submission.php?id=<?php echo $submission['id']; ?>" 
                                       class="btn btn-outline-company">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php else: ?>
            <!-- Tasks List -->
            <?php if (empty($tasks)): ?>
                <div class="alert alert-company">
                    <i class="bi bi-info-circle"></i>
                    No tasks found matching your criteria.
                </div>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <a href="review_submissions.php?task_id=<?php echo $task['id']; ?>" class="text-decoration-none">
                        <div class="task-card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-2"><?php echo htmlspecialchars($task['title']); ?></h5>
                                        <div class="task-meta">
                                            <div class="task-meta-item">
                                                <i class="bi bi-bar-chart"></i>
                                                <span><?php echo ucfirst($task['difficulty']); ?></span>
                                            </div>
                                            <div class="task-meta-item">
                                                <i class="bi bi-currency-dollar"></i>
                                                <span><?php echo number_format($task['budget']); ?> EGP</span>
                                            </div>
                                            <div class="task-meta-item">
                                                <i class="bi bi-people"></i>
                                                <span><?php echo $task['total_submissions']; ?> Submissions</span>
                                            </div>
                                            <?php if ($task['pending_submissions'] > 0): ?>
                                                <div class="task-meta-item">
                                                    <i class="bi bi-clock"></i>
                                                    <span><?php echo $task['pending_submissions']; ?> Pending</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 