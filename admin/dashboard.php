<?php
session_start();
require_once '../php/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get total number of companies
$stmt = $pdo->prepare("SELECT COUNT(*) FROM companies");
$stmt->execute();
$total_companies = $stmt->fetchColumn();

// Get pending company approvals
$stmt = $pdo->prepare("SELECT COUNT(*) FROM companies WHERE approval_status = 'pending'");
$stmt->execute();
$pending_companies = $stmt->fetchColumn();

// Get total number of students
$stmt = $pdo->prepare("SELECT COUNT(*) FROM students");
$stmt->execute();
$total_students = $stmt->fetchColumn();

// Get total number of tasks
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks");
$stmt->execute();
$total_tasks = $stmt->fetchColumn();

// Get recent admin actions
$stmt = $pdo->prepare("
    SELECT aa.*, u.email as admin_email
    FROM admin_actions aa
    JOIN users u ON aa.admin_id = u.id
    ORDER BY aa.created_at DESC
    LIMIT 5
");
$stmt->execute();
$recent_actions = $stmt->fetchAll();

// Get recent company registrations
$stmt = $pdo->prepare("
    SELECT c.*, u.email
    FROM companies c
    JOIN users u ON c.user_id = u.id
    ORDER BY c.created_at DESC
    LIMIT 5
");
$stmt->execute();
$recent_companies = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ExternIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/main.css">
    <style>
        .stat-card {
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 2px solid rgba(var(--primary-color-rgb), 0.2);
            background: var(--white);
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1), 0 4px 6px rgba(0, 0, 0, 0.05);
            border-color: rgba(var(--primary-color-rgb), 0.3);
        }
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: var(--text-muted);
            font-size: 1rem;
            font-weight: 500;
        }
        .recent-activity {
            max-height: 400px;
            overflow-y: auto;
        }
        .admin-card {
            border-radius: var(--border-radius-lg);
            border: 2px solid rgba(var(--primary-color-rgb), 0.2);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            transition: box-shadow 0.2s ease;
        }
        .admin-card:hover {
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1), 0 4px 6px rgba(0, 0, 0, 0.05);
            border-color: rgba(var(--primary-color-rgb), 0.3);
        }
        .admin-card .card-header {
            background: transparent;
            border-bottom: 2px solid rgba(var(--primary-color-rgb), 0.2);
            padding: 1rem 1.25rem;
        }
        .admin-card .card-header h5 {
            color: var(--primary-color);
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }
        .admin-card .card-body {
            padding: 1.25rem;
        }
        .list-group-item {
            border: 1px solid rgba(var(--primary-color-rgb), 0.2);
            margin-bottom: 0.75rem;
            border-radius: var(--border-radius) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .list-group-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-color: rgba(var(--primary-color-rgb), 0.3);
        }
        .list-group-item:last-child {
            margin-bottom: 0;
        }
        .btn-admin {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            transition: all 0.2s ease;
        }
        .btn-admin:hover {
            background: var(--secondary-color);
            color: var(--white);
            transform: translateY(-2px);
        }
        .badge-status {
            padding: 0.5rem 0.75rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            font-size: 0.875rem;
        }
        .badge-status.pending {
            background: var(--status-pending-bg);
            color: var(--status-pending-fg);
            border: 1px solid var(--status-pending-border);
        }
        .badge-status.approved {
            background: var(--status-accepted-bg);
            color: var(--status-accepted-fg);
            border: 1px solid var(--status-accepted-border);
        }
        .badge-status.rejected {
            background: var(--status-rejected-bg);
            color: var(--status-rejected-fg);
            border: 1px solid var(--status-rejected-border);
        }
    </style>
</head>
<body>
    <?php include '../includes/navigation.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary">Admin Dashboard</h2>
            <div class="admin-actions">
                <a href="manage_companies.php" class="btn btn-admin me-2">
                    <i class="bi bi-building"></i> Manage Companies
                </a>
                <a href="manage_contacts.php" class="btn btn-admin me-2">
                    <i class="bi bi-envelope"></i> Manage Contacts
                </a>
                <a href="admin_payments.php" class="btn btn-admin">
                    <i class="bi bi-currency-dollar"></i> Manage Payments
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_companies; ?></div>
                    <div class="stat-label">Total Companies</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="stat-value"><?php echo $pending_companies; ?></div>
                    <div class="stat-label">Pending Approvals</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-mortarboard"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_students; ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-list-task"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_tasks; ?></div>
                    <div class="stat-label">Total Tasks</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Company Registrations -->
            <div class="col-md-6">
                <div class="admin-card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Company Registrations</h5>
                    </div>
                    <div class="card-body recent-activity">
                        <?php if ($recent_companies): ?>
                            <div class="list-group">
                                <?php foreach ($recent_companies as $company): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($company['name']); ?></h6>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($company['email']); ?> • 
                                                    <?php echo date('M d, Y', strtotime($company['created_at'])); ?>
                                                </small>
                                            </div>
                                            <span class="badge-status <?php echo $company['approval_status']; ?>">
                                                <?php echo ucfirst($company['approval_status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No recent company registrations</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Admin Actions -->
            <div class="col-md-6">
                <div class="admin-card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Admin Actions</h5>
                    </div>
                    <div class="card-body recent-activity">
                        <?php if ($recent_actions): ?>
                            <div class="list-group">
                                <?php foreach ($recent_actions as $action): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo ucfirst(str_replace('_', ' ', $action['action_type'])); ?></h6>
                                                <small class="text-muted">
                                                    By <?php echo htmlspecialchars($action['admin_email']); ?> • 
                                                    <?php echo date('M d, Y H:i', strtotime($action['created_at'])); ?>
                                                </small>
                                            </div>
                                            <?php if ($action['details']): ?>
                                                <small class="text-muted"><?php echo htmlspecialchars($action['details']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No recent admin actions</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 