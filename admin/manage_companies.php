<?php
session_start();
require_once '../php/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle approval/rejection actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_id = $_POST['company_id'] ?? null;
    $action = $_POST['action'] ?? '';
    $rejection_reason = $_POST['rejection_reason'] ?? '';

    if ($company_id && in_array($action, ['approve', 'reject'])) {
        try {
            $pdo->beginTransaction();

            if ($action === 'approve') {
                $stmt = $pdo->prepare("
                    UPDATE companies 
                    SET is_approved = TRUE, 
                        approval_status = 'approved',
                        approval_date = NOW(),
                        rejection_reason = NULL,
                        admin_id = ?
                    WHERE id = ?
                ");
                $stmt->execute([$_SESSION['user_id'], $company_id]);
                // Get the user_id for the approved company
                $stmt = $pdo->prepare("SELECT user_id FROM companies WHERE id = ?");
                $stmt->execute([$company_id]);
                $approved_user_id = $stmt->fetchColumn();
                // Get the email of the approved company
                $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
                $stmt->execute([$approved_user_id]);
                $approved_email = $stmt->fetchColumn();
                // Optionally, you could send an email here
                // Redirect the admin back to the management page with a success message
                $pdo->commit();
                header('Location: manage_companies.php?success=Company approved successfully');
                exit();
            } else {
                $stmt = $pdo->prepare("
                    UPDATE companies 
                    SET is_approved = FALSE, 
                        approval_status = 'rejected',
                        rejection_reason = ?,
                        admin_id = ?
                    WHERE id = ?
                ");
                $stmt->execute([$rejection_reason, $_SESSION['user_id'], $company_id]);
            }

            // Log admin action
            $action_type = $action === 'approve' ? 'company_approval' : 'company_rejection';
            $stmt = $pdo->prepare("
                INSERT INTO admin_actions (admin_id, action_type, target_id, details)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $action_type,
                $company_id,
                $action === 'reject' ? $rejection_reason : null
            ]);

            $pdo->commit();
            header('Location: manage_companies.php?success=Company ' . $action . 'd successfully');
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            header('Location: manage_companies.php?error=' . urlencode($e->getMessage()));
            exit();
        }
    }
}

// Get companies with pending approval
$stmt = $pdo->prepare("
    SELECT c.*, u.email, u.created_at as registration_date
    FROM companies c
    JOIN users u ON c.user_id = u.id
    ORDER BY c.approval_status = 'pending' DESC, c.created_at DESC
");
$stmt->execute();
$companies = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Companies - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navigation.php'; ?>

    <div class="container mt-4">
        <a href="dashboard.php" class="btn btn-outline-primary mb-3">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
        <div class="d-flex align-items-center mb-3">
            <h2 class="mb-0">Manage Companies</h2>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>Industry</th>
                        <th>Email</th>
                        <th>Registration Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($companies as $company): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($company['name']); ?></td>
                            <td><?php echo htmlspecialchars($company['industry']); ?></td>
                            <td><?php echo htmlspecialchars($company['email']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($company['registration_date'])); ?></td>
                            <td>
                                <?php
                                $status_class = [
                                    'pending' => 'warning',
                                    'approved' => 'success',
                                    'rejected' => 'danger'
                                ][$company['approval_status']];
                                ?>
                                <span class="badge bg-<?php echo $status_class; ?>">
                                    <?php echo ucfirst($company['approval_status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($company['approval_status'] === 'pending'): ?>
                                    <button type="button" class="btn btn-success btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#approveModal<?php echo $company['id']; ?>">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#rejectModal<?php echo $company['id']; ?>">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                <?php endif; ?>
                                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailsModal<?php echo $company['id']; ?>">
                                    <i class="fas fa-eye"></i> See Details
                                </button>
                            </td>
                        </tr>

                        <!-- Approve Modal -->
                        <div class="modal fade" id="approveModal<?php echo $company['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Approve Company</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <p>Are you sure you want to approve <?php echo htmlspecialchars($company['name']); ?>?</p>
                                            <input type="hidden" name="company_id" value="<?php echo $company['id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success">Approve</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Reject Modal -->
                        <div class="modal fade" id="rejectModal<?php echo $company['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Reject Company</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="rejection_reason" class="form-label">Reason for Rejection</label>
                                                <textarea class="form-control" id="rejection_reason" name="rejection_reason" required></textarea>
                                            </div>
                                            <input type="hidden" name="company_id" value="<?php echo $company['id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Reject</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Company Details Modal -->
                        <div class="modal fade" id="detailsModal<?php echo $company['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Company Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <ul class="list-group">
                                            <li class="list-group-item"><strong>Name:</strong> <?php echo htmlspecialchars($company['name'] ?? 'N/A'); ?></li>
                                            <li class="list-group-item"><strong>Industry:</strong> <?php echo htmlspecialchars($company['industry'] ?? 'N/A'); ?></li>
                                            <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($company['email'] ?? 'N/A'); ?></li>
                                            <li class="list-group-item"><strong>Website:</strong> <?php echo !empty($company['website']) ? htmlspecialchars($company['website']) : 'N/A'; ?></li>
                                            <li class="list-group-item"><strong>Phone:</strong> <?php echo !empty($company['phone']) ? htmlspecialchars($company['phone']) : 'N/A'; ?></li>
                                            <li class="list-group-item"><strong>Address:</strong> <?php echo !empty($company['address']) ? htmlspecialchars($company['address']) : 'N/A'; ?></li>
                                            <li class="list-group-item"><strong>Registration Date:</strong> <?php echo !empty($company['registration_date']) ? date('M d, Y', strtotime($company['registration_date'])) : 'N/A'; ?></li>
                                            <li class="list-group-item"><strong>Status:</strong> <?php echo ucfirst($company['approval_status'] ?? 'N/A'); ?></li>
                                            <li class="list-group-item"><strong>Approval Date:</strong> <?php echo !empty($company['approval_date']) ? date('M d, Y', strtotime($company['approval_date'])) : 'N/A'; ?></li>
                                            <li class="list-group-item"><strong>Admin ID (approved/rejected by):</strong> <?php echo !empty($company['admin_id']) ? htmlspecialchars($company['admin_id']) : 'N/A'; ?></li>
                                            <?php if ($company['approval_status'] === 'rejected'): ?>
                                                <li class="list-group-item"><strong>Rejection Reason:</strong> <?php echo htmlspecialchars($company['rejection_reason'] ?? 'N/A'); ?></li>
                                            <?php endif; ?>
                                            <li class="list-group-item"><strong>Registration Document:</strong> <?php if (!empty($company['registration_doc'])): ?><a href="../uploads/company_docs/<?php echo htmlspecialchars($company['registration_doc']); ?>" download>Download Document</a><?php else: ?>N/A<?php endif; ?></li>
                                            <li class="list-group-item"><strong>Tax ID:</strong> <?php echo !empty($company['tax_id']) ? htmlspecialchars($company['tax_id']) : 'N/A'; ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 