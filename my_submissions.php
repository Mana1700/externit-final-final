<?php
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: login.php');
    exit();
}

// Get student ID
$stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();
$student_id = $student['id'];

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$company_filter = isset($_GET['company']) ? $_GET['company'] : 'all';
$difficulty_filter = isset($_GET['difficulty']) ? $_GET['difficulty'] : 'all';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build the query with filters
$query = "
    SELECT s.*, t.title as task_title, t.budget, t.difficulty,
           c.name as company_name, c.id as company_id, u.photo as company_photo,
           (SELECT cert.id FROM certificates cert WHERE cert.submission_id = s.id) as certificate_id,
           CASE 
               WHEN s.status = 'pending' THEN 'Pending Review'
               WHEN s.status = 'approved' OR s.status = 'accepted' THEN 'Approved'
               WHEN s.status = 'rejected' THEN 'Rejected'
               ELSE 'Under Review'
           END as status_text,
           CASE 
               WHEN s.status = 'accepted' THEN 'approved'
               ELSE s.status
           END as normalized_status
    FROM submissions s
    JOIN tasks t ON s.task_id = t.id
    JOIN companies c ON t.company_id = c.id
    JOIN users u ON c.user_id = u.id
    WHERE s.student_id = ?
";

$params = [$student_id];

if ($status_filter !== 'all') {
    if ($status_filter === 'approved') {
        $query .= " AND (s.status = 'approved' OR s.status = 'accepted')";
    } else {
        $query .= " AND s.status = ?";
        $params[] = $status_filter;
    }
}

if ($company_filter !== 'all') {
    $query .= " AND c.id = ?";
    $params[] = $company_filter;
}

if ($difficulty_filter !== 'all') {
    $query .= " AND t.difficulty = ?";
    $params[] = $difficulty_filter;
}

if ($date_from) {
    $query .= " AND DATE(s.created_at) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $query .= " AND DATE(s.created_at) <= ?";
    $params[] = $date_to;
}

$query .= " ORDER BY s.created_at DESC";

// Fetch filtered submissions
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$submissions = $stmt->fetchAll();

// Fetch all companies for filter dropdown
$stmt = $pdo->prepare("
    SELECT DISTINCT c.id, c.name 
    FROM companies c 
    JOIN tasks t ON c.id = t.company_id 
    JOIN submissions s ON t.id = s.task_id 
    WHERE s.student_id = ?
    ORDER BY c.name
");
$stmt->execute([$student_id]);
$companies = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Submissions - ExternIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .filters-section {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .filter-group label {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 0.25rem;
        }
        .filter-group select,
        .filter-group input {
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .filter-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }
        .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .filter-tag {
            background: var(--student-primary-light);
            color: var(--student-primary);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .filter-tag i {
            cursor: pointer;
            font-size: 0.8rem;
        }
        .filter-tag i:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navigation.php'; ?>

    <div class="container mt-4">
        <div class="page-header">
            <h2 class="student-text">My Submissions</h2>
            <div class="badge-base">
                <i class="bi bi-file-earmark-text"></i>
                <?php echo count($submissions); ?> Submissions
            </div>
        </div>

        <div class="filters-section">
            <form id="filters-form" method="GET" class="filters-grid">
                <div class="filter-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending Review</option>
                        <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="company">Company</label>
                    <select name="company" id="company" class="form-select">
                        <option value="all" <?php echo $company_filter === 'all' ? 'selected' : ''; ?>>All Companies</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?php echo $company['id']; ?>" <?php echo $company_filter == $company['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($company['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="difficulty">Difficulty</label>
                    <select name="difficulty" id="difficulty" class="form-select">
                        <option value="all" <?php echo $difficulty_filter === 'all' ? 'selected' : ''; ?>>All Difficulties</option>
                        <option value="easy" <?php echo $difficulty_filter === 'easy' ? 'selected' : ''; ?>>Easy</option>
                        <option value="medium" <?php echo $difficulty_filter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="hard" <?php echo $difficulty_filter === 'hard' ? 'selected' : ''; ?>>Hard</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="date_from">From Date</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="<?php echo $date_from; ?>">
                </div>

                <div class="filter-group">
                    <label for="date_to">To Date</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="<?php echo $date_to; ?>">
                </div>
            </form>

            <div class="filter-actions">
                <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                    <i class="bi bi-x-circle"></i> Clear Filters
                </button>
                <button type="submit" form="filters-form" class="btn btn-primary-student">
                    <i class="bi bi-funnel"></i> Apply Filters
                </button>
            </div>

            <?php if ($status_filter !== 'all' || $company_filter !== 'all' || $difficulty_filter !== 'all' || $date_from || $date_to): ?>
            <div class="active-filters">
                <?php if ($status_filter !== 'all'): ?>
                    <div class="filter-tag">
                        Status: <?php echo ucfirst($status_filter); ?>
                        <i class="bi bi-x" onclick="removeFilter('status')"></i>
                    </div>
                <?php endif; ?>
                
                <?php if ($company_filter !== 'all'): ?>
                    <div class="filter-tag">
                        Company: <?php 
                            foreach ($companies as $company) {
                                if ($company['id'] == $company_filter) {
                                    echo htmlspecialchars($company['name']);
                                    break;
                                }
                            }
                        ?>
                        <i class="bi bi-x" onclick="removeFilter('company')"></i>
                    </div>
                <?php endif; ?>
                
                <?php if ($difficulty_filter !== 'all'): ?>
                    <div class="filter-tag">
                        Difficulty: <?php echo ucfirst($difficulty_filter); ?>
                        <i class="bi bi-x" onclick="removeFilter('difficulty')"></i>
                    </div>
                <?php endif; ?>
                
                <?php if ($date_from): ?>
                    <div class="filter-tag">
                        From: <?php echo date('M j, Y', strtotime($date_from)); ?>
                        <i class="bi bi-x" onclick="removeFilter('date_from')"></i>
                    </div>
                <?php endif; ?>
                
                <?php if ($date_to): ?>
                    <div class="filter-tag">
                        To: <?php echo date('M j, Y', strtotime($date_to)); ?>
                        <i class="bi bi-x" onclick="removeFilter('date_to')"></i>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if (empty($submissions)): ?>
            <div class="alert alert-info">
                <div class="alert-content">
                    <i class="bi bi-info-circle"></i>
                    <div class="alert-message">
                        <h6>No Submissions Found</h6>
                        <p>No submissions match your filter criteria. Try adjusting your filters or view all submissions.</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="submissions-grid">
                <?php foreach ($submissions as $submission): ?>
                    <div class="submission-card">
                        <div class="submission-card-header">
                            <div class="submission-title">
                                <h3><?php echo htmlspecialchars($submission['task_title']); ?></h3>
                                <div class="company-info">
                                    <?php if ($submission['company_photo']): ?>
                                        <img src="<?php echo htmlspecialchars($submission['company_photo']); ?>" alt="<?php echo htmlspecialchars($submission['company_name']); ?>" class="company-photo-small">
                                    <?php else: ?>
                                        <i class="bi bi-building"></i>
                                    <?php endif; ?>
                                    <span><?php echo htmlspecialchars($submission['company_name']); ?></span>
                                </div>
                            </div>
                            <div class="badge-status <?php echo strtolower($submission['normalized_status']); ?>">
                                <?php
                                    $icon = 'bi-hourglass-split';
                                    switch (strtolower($submission['normalized_status'])) {
                                        case 'approved':
                                            $icon = 'bi-check-circle-fill';
                                            break;
                                        case 'rejected':
                                            $icon = 'bi-x-circle-fill';
                                            break;
                                    }
                                ?>
                                <i class="bi <?php echo $icon; ?>"></i>
                                <?php echo htmlspecialchars($submission['status_text']); ?>
                            </div>
                        </div>

                        <div class="submission-card-body">
                            <div class="meta-group">
                                <div class="meta-item">
                                    <i class="bi bi-calendar-check"></i>
                                    <span>Submitted: <?php echo date('F j, Y', strtotime($submission['created_at'])); ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="bi bi-bar-chart"></i>
                                    <span><?php echo ucfirst($submission['difficulty']); ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="bi bi-currency-dollar"></i>
                                    <span><?php echo number_format($submission['budget']); ?> EGP</span>
                                </div>
                            </div>

                            <?php if ($submission['feedback']): ?>
                                <div class="feedback-section">
                                    <h6>Feedback</h6>
                                    <p><?php echo nl2br(htmlspecialchars($submission['feedback'])); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="submission-card-footer">
                            <div class="action-buttons">
                                <a href="view_submission.php?id=<?php echo $submission['id']; ?>" class="btn btn-primary-student">
                                    <i class="bi bi-eye"></i>
                                    View Details
                                </a>
                                <?php if (($submission['status'] === 'approved' || $submission['status'] === 'accepted') && $submission['certificate_id']): ?>
                                    <a href="generate_certificate_pdf.php?id=<?php echo $submission['certificate_id']; ?>" class="btn btn-outline-student">
                                        <i class="bi bi-award"></i>
                                        View Certificate
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function clearFilters() {
            window.location.href = 'my_submissions.php';
        }

        function removeFilter(filterName) {
            const form = document.getElementById('filters-form');
            const input = form.querySelector(`[name="${filterName}"]`);
            if (input) {
                if (input.tagName === 'SELECT') {
                    input.value = 'all';
                } else {
                    input.value = '';
                }
                form.submit();
            }
        }

        // Ensure "To Date" is not before "From Date"
        document.getElementById('date_from').addEventListener('change', function() {
            document.getElementById('date_to').min = this.value;
        });

        document.getElementById('date_to').addEventListener('change', function() {
            document.getElementById('date_from').max = this.value;
        });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 