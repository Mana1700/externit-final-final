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
$status = $_GET['status'] ?? 'active';
$sort = $_GET['sort'] ?? 'newest';
$search = $_GET['search'] ?? '';

// Build the query
$query = "
    SELECT t.*, 
           (SELECT COUNT(DISTINCT s.id) 
            FROM submissions s 
            WHERE s.task_id = t.id) as total_submissions,
           (SELECT COUNT(DISTINCT s2.id) 
            FROM submissions s2 
            WHERE s2.task_id = t.id 
            AND s2.status = 'pending') as pending_submissions
    FROM tasks t
    WHERE t.company_id = ?
";

$params = [$company_id];

// Add status filter
if ($status !== 'all') {
    $query .= " AND t.status = ?";
    $params[] = $status;
}

// Add search filter
if (!empty($search)) {
    $query .= " AND t.title LIKE ?";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
}

// Add GROUP BY to ensure no duplicate counts
$query .= " GROUP BY t.id";

// Add sorting
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

// Get counts for each status
$stmt = $pdo->prepare("
    SELECT 
        COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
        COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled
    FROM tasks
    WHERE company_id = ?
");
$stmt->execute([$company_id]);
$counts = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tasks - ExternIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .task-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-bottom: 1.5rem;
            background: #B85C38;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .task-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .task-card .card-header {
            background: transparent;
            color: white;
            border: none;
            padding: 1.75rem 1.5rem;
            position: relative;
        }

        .task-card .card-header h5 {
            color: white;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            line-height: 1.4;
            min-height: 2.8rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            padding-right: 100px; /* Make space for the status badge */
        }

        .task-meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin: 0;
        }

        .task-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
            padding: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            backdrop-filter: blur(5px);
        }

        .task-meta-item i {
            font-size: 1rem;
            opacity: 0.9;
        }

        .status-badge {
            position: absolute;
            top: 1.75rem;
            right: 1.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            backdrop-filter: blur(5px);
            z-index: 1;
        }

        .status-badge i {
            font-size: 0.75rem;
        }

        .status-badge.active i {
            color: #28a745;
        }

        .status-badge.completed i {
            color: #17a2b8;
        }

        .status-badge.cancelled i {
            color: #dc3545;
        }

        .task-card .card-body {
            background: white;
            padding: 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 150px;
        }

        .task-description {
            color: #6c757d;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 0.95rem;
            line-height: 1.5;
            height: 3em;
            flex-grow: 1;
        }

        .task-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid rgba(0, 0, 0, 0.08);
            margin-top: auto;
        }

        .task-posted {
            color: #6c757d;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .task-posted i {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .task-actions {
            display: flex;
            gap: 0.75rem;
            margin: 0;
        }

        .task-actions .btn {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: var(--border-radius);
            transition: all 0.2s ease;
        }

        .task-actions .btn i {
            font-size: 1rem;
        }

        .task-actions .btn-outline-company {
            color: #B85C38;
            border-color: #B85C38;
        }

        .task-actions .btn-outline-company:hover {
            background: #B85C38;
            color: white;
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

            .task-actions {
                flex-direction: column;
            }
        }

        .task-meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin: 0;
        }

        .task-card {
            height: 100%;
            position: relative;
        }

        .task-card .card-body {
            display: flex;
            flex-direction: column;
        }

        .task-description {
            flex-grow: 1;
            margin-bottom: 1rem;
        }

        .task-footer {
            margin-top: auto;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
        }

        .dropdown-item i {
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .task-meta {
                grid-template-columns: 1fr;
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

        <!-- Stats Section -->
        <div class="stats-card">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value"><?php echo $counts['active']; ?></div>
                    <div class="stat-label">Active Tasks</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $counts['completed']; ?></div>
                    <div class="stat-label">Completed Tasks</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $counts['cancelled']; ?></div>
                    <div class="stat-label">Cancelled Tasks</div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Manage Tasks</h5>
                <a href="create_task.php" class="btn btn-primary-company">
                    <i class="bi bi-plus-lg"></i> Create New Task
                </a>
            </div>
            <form action="manage_tasks.php" method="GET" class="row g-3">
                <div class="col-md-4">
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control" name="search" placeholder="Search tasks..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
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

        <!-- Tasks List -->
        <?php if (empty($tasks)): ?>
            <div class="alert alert-company">
                <i class="bi bi-info-circle"></i>
                No tasks found matching your criteria.
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($tasks as $task): ?>
                    <div class="col">
                        <div class="task-card h-100">
                            <div class="card-header">
                                <h5><?php echo htmlspecialchars($task['title']); ?></h5>
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
                                <span class="status-badge <?php echo $task['status']; ?>">
                                    <i class="bi bi-circle-fill"></i>
                                    <?php echo ucfirst($task['status']); ?>
                                </span>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <p class="task-description" title="<?php echo htmlspecialchars($task['description']); ?>">
                                    <?php 
                                        $words = str_word_count($task['description'], 1);
                                        if (count($words) > 15) {
                                            echo htmlspecialchars(implode(' ', array_slice($words, 0, 15))) . '...';
                                        } else {
                                            echo htmlspecialchars($task['description']);
                                        }
                                    ?>
                                </p>
                                <div class="task-footer mt-auto">
                                    <div class="task-posted">
                                        <i class="bi bi-calendar"></i>
                                        Posted: <?php echo date('M d, Y', strtotime($task['created_at'])); ?>
                                    </div>
                                    <div class="task-actions">
                                        <div class="dropdown">
                                            <button class="btn btn-outline-company dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots"></i> Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="dropdown-item">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="review_submissions.php?task_id=<?php echo $task['id']; ?>" class="dropdown-item">
                                                        <i class="bi bi-eye"></i> View Submissions
                                                    </a>
                                                </li>
                                                <?php if ($task['status'] === 'active'): ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="php/update_task_status.php" method="POST">
                                                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                            <input type="hidden" name="status" value="completed">
                                                            <button type="submit" class="dropdown-item text-success">
                                                                <i class="bi bi-check-lg"></i> Mark as Completed
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form action="php/update_task_status.php" method="POST">
                                                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                            <input type="hidden" name="status" value="cancelled">
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="bi bi-x-lg"></i> Cancel Task
                                                            </button>
                                                        </form>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 