<?php
session_start();
require_once '../php/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submission_id']) && isset($_POST['status'])) {
    $submission_id = $_POST['submission_id'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("
            UPDATE contact_submissions 
            SET status = ? 
            WHERE id = ?
        ");
        $stmt->execute([$status, $submission_id]);
        
        header('Location: manage_contacts.php?success=Status updated successfully');
        exit();
    } catch (PDOException $e) {
        header('Location: manage_contacts.php?error=Failed to update status');
        exit();
    }
}

// Get all contact submissions
$stmt = $pdo->prepare("
    SELECT * FROM contact_submissions 
    ORDER BY 
        CASE 
            WHEN status = 'unread' THEN 1
            WHEN status = 'read' THEN 2
            ELSE 3
        END,
        created_at DESC
");
$stmt->execute();
$submissions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Contact Submissions - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navigation.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Contact Form Submissions</h2>
            <a href="/Externit-final/admin/manage_companies.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Companies
            </a>
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
                        <th>Date</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission): ?>
                        <tr class="<?php echo $submission['status'] === 'unread' ? 'table-warning' : ''; ?>">
                            <td><?php echo date('M d, Y H:i', strtotime($submission['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($submission['name']); ?></td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($submission['email']); ?>">
                                    <?php echo htmlspecialchars($submission['email']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($submission['subject']); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $submission['status'] === 'unread' ? 'warning' : 
                                        ($submission['status'] === 'read' ? 'info' : 'success'); 
                                ?>">
                                    <?php echo ucfirst($submission['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-info btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#viewModal<?php echo $submission['id']; ?>">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" 
                                            data-bs-toggle="dropdown">
                                        Status
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <form method="POST" class="dropdown-item">
                                                <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                                                <input type="hidden" name="status" value="unread">
                                                <button type="submit" class="btn btn-link p-0">Mark as Unread</button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" class="dropdown-item">
                                                <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                                                <input type="hidden" name="status" value="read">
                                                <button type="submit" class="btn btn-link p-0">Mark as Read</button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" class="dropdown-item">
                                                <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                                                <input type="hidden" name="status" value="replied">
                                                <button type="submit" class="btn btn-link p-0">Mark as Replied</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- View Modal -->
                        <div class="modal fade" id="viewModal<?php echo $submission['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Message Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <strong>From:</strong> <?php echo htmlspecialchars($submission['name']); ?>
                                            (<?php echo htmlspecialchars($submission['email']); ?>)
                                        </div>
                                        <div class="mb-3">
                                            <strong>Subject:</strong> <?php echo htmlspecialchars($submission['subject']); ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Date:</strong> <?php echo date('M d, Y H:i', strtotime($submission['created_at'])); ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Message:</strong>
                                            <div class="p-3 bg-light rounded">
                                                <?php echo nl2br(htmlspecialchars($submission['message'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <a href="mailto:<?php echo htmlspecialchars($submission['email']); ?>" 
                                           class="btn btn-primary">
                                            <i class="fas fa-reply"></i> Reply
                                        </a>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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