<?php
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: login.php');
    exit();
}

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$difficulty = isset($_GET['difficulty']) ? $_GET['difficulty'] : '';
$min_budget = isset($_GET['min_budget']) && $_GET['min_budget'] !== '' ? (int)$_GET['min_budget'] : 0;
$max_budget = isset($_GET['max_budget']) && $_GET['max_budget'] !== '' ? (int)$_GET['max_budget'] : PHP_INT_MAX;
$company = isset($_GET['company']) ? $_GET['company'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Base query
$query = "
    SELECT t.*, c.name as company_name, u.photo as company_photo,
           (SELECT COUNT(*) FROM submissions s WHERE s.task_id = t.id) as current_submissions
    FROM tasks t
    JOIN companies c ON t.company_id = c.id
    JOIN users u ON c.user_id = u.id
    WHERE t.deadline > NOW() 
    AND t.status = 'active'
    AND t.max_submissions > (SELECT COUNT(*) FROM submissions s WHERE s.task_id = t.id)
";

// Add filters
$params = [];

// Search by title
if ($search) {
    $query .= " AND (t.title LIKE ? OR t.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($difficulty) {
    $query .= " AND t.difficulty = ?";
    $params[] = $difficulty;
}

if ($min_budget > 0) {
    $query .= " AND t.budget >= ?";
    $params[] = $min_budget;
}

if ($max_budget < PHP_INT_MAX) {
    $query .= " AND t.budget <= ?";
    $params[] = $max_budget;
}

if ($company) {
    $query .= " AND c.name LIKE ?";
    $params[] = "%$company%";
}

// Add sorting
switch ($sort) {
    case 'budget_high':
        $query .= " ORDER BY t.budget DESC";
        break;
    case 'budget_low':
        $query .= " ORDER BY t.budget ASC";
        break;
    case 'deadline':
        $query .= " ORDER BY t.deadline ASC";
        break;
    case 'newest':
    default:
        $query .= " ORDER BY t.created_at DESC";
        break;
}

// Execute query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

// Get all companies for filter
$stmt = $pdo->query("SELECT DISTINCT name FROM companies ORDER BY name");
$companies = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get min and max budget for range
$stmt = $pdo->query("SELECT MIN(budget) as min_budget, MAX(budget) as max_budget FROM tasks WHERE status = 'active'");
$budget_range = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Tasks - ExternIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        /* 
           Inline styles removed as they should be handled by main.css imports 
           (e.g., _badges.css, _cards.css, _buttons.css, _layout.css)
           
           Removed definitions for:
           - .task-badge
           - .task-count
           - .task-label
           - .filter-card & .filter-card .card-header h5 
           - .btn-filter
           - .btn-clear
           - .form-select, .form-control overrides
           - .task-card & .task-card .card-header & hover 
           - .task-meta & .task-meta-item
           - .company-name
           - .progress & .progress-bar
           - .btn-view & .btn-view:hover
           - .company-photo-small & .company-photo-placeholder (already removed)
        */

        /* Keep only styles truly specific to this page, if any */
        /* (None identified currently) */

    </style>
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

        <div class="row">
            <div class="col-12">
                <h3 class="mb-4">Available Tasks
                    <span class="task-count ms-2">
                        <i class="bi bi-list-task"></i>
                        <?php echo count($tasks); ?> Tasks
                    </span>
                </h3>
            </div>
        </div>

        <div class="row">
            <!-- Filters -->
            <div class="col-md-3">
                <div class="card filter-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-funnel"></i>Filters
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="filter-form mb-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">Search</label>
                                        <input type="text" name="search" class="form-control" 
                                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" 
                                               placeholder="Search tasks...">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">Sort By</label>
                                        <select name="sort" class="form-select">
                                            <option value="newest" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'newest') ? 'selected' : ''; ?>>Newest</option>
                                            <option value="oldest" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'oldest') ? 'selected' : ''; ?>>Oldest</option>
                                            <option value="budget_high" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'budget_high') ? 'selected' : ''; ?>>Budget (High to Low)</option>
                                            <option value="budget_low" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'budget_low') ? 'selected' : ''; ?>>Budget (Low to High)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">Difficulty</label>
                                        <select name="difficulty" class="form-select">
                                            <option value="">All</option>
                                            <option value="easy" <?php echo (isset($_GET['difficulty']) && $_GET['difficulty'] == 'easy') ? 'selected' : ''; ?>>Easy</option>
                                            <option value="medium" <?php echo (isset($_GET['difficulty']) && $_GET['difficulty'] == 'medium') ? 'selected' : ''; ?>>Medium</option>
                                            <option value="hard" <?php echo (isset($_GET['difficulty']) && $_GET['difficulty'] == 'hard') ? 'selected' : ''; ?>>Hard</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">Min Budget</label>
                                        <input type="number" name="min_budget" class="form-control" 
                                               value="<?php echo isset($_GET['min_budget']) ? htmlspecialchars($_GET['min_budget']) : ''; ?>" 
                                               placeholder="Min budget">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">Max Budget</label>
                                        <input type="number" name="max_budget" class="form-control" 
                                               value="<?php echo isset($_GET['max_budget']) ? htmlspecialchars($_GET['max_budget']) : ''; ?>" 
                                               placeholder="Max budget">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-company w-100 mb-2">
                                        <i class="bi bi-funnel"></i> Apply Filters
                                    </button>
                                    <a href="view_tasks.php" class="btn btn-outline-secondary w-100">
                                        <i class="bi bi-x-circle"></i> Clear Filters
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tasks List -->
            <div class="col-md-9">
                <?php if (empty($tasks)): ?>
                    <div class="alert alert-student">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <div>
                                <h6 class="alert-heading mb-1">No Tasks Found</h6>
                                <p class="mb-0">Try adjusting your filters or search terms to see more tasks.</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                        <div class="card dashboard-task-card mb-4">
                            <div class="card-header">
                                <h5><?php echo htmlspecialchars($task['title']); ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="task-meta mb-3">
                                    <span class="task-badge">
                                        <i class="bi bi-bar-chart"></i> <?php echo ucfirst($task['difficulty']); ?>
                                    </span>
                                    <span class="task-badge">
                                        <i class="bi bi-people"></i> <?php echo $task['current_submissions']; ?>/<?php echo $task['max_submissions']; ?> Submissions
                                    </span>
                                </div>
                                
                                <div class="dashboard-meta">
                                    <div class="meta-label">
                                        <?php if ($task['company_photo']): ?>
                                            <img src="<?php echo htmlspecialchars($task['company_photo']); ?>" 
                                                 alt="<?php echo htmlspecialchars($task['company_name']); ?>" 
                                                 class="company-photo-small">
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
                                        <i class="bi bi-calendar-check"></i> <?php echo date('M d, Y', strtotime($task['deadline'])); ?>
                                    </span>
                                </div>
                                
                                <div class="card-actions">
                                    <a href="view_task.php?id=<?php echo $task['id']; ?>" class="btn btn-primary-student btn-sm">
                                        <i class="bi bi-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 