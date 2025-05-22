<?php
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch all notifications for the user
$stmt = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

// Mark all notifications as read
$stmt = $pdo->prepare("
    UPDATE notifications 
    SET is_read = TRUE 
    WHERE user_id = ? AND is_read = FALSE
");
$stmt->execute([$_SESSION['user_id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - ExternIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <div class="container mt-4">
        <div class="page-header mb-4">
            <h2 class="student-text">Notifications</h2>
            <div class="badge-base">
                <i class="bi bi-bell-fill"></i>
                <?php echo count($notifications); ?> Notifications
            </div>
        </div>

        <?php if (empty($notifications)): ?>
            <div class="alert alert-info">
                <div class="alert-content">
                    <i class="bi bi-info-circle"></i>
                    <div class="alert-message">
                        <h6>No Notifications</h6>
                        <p>You don't have any notifications yet.</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="list-group list-group-flush">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                    <p class="mb-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                                    <small class="text-muted"><?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?></small>
                                </div>
                                <span class="badge bg-<?php echo match($notification['type']) {
                                    'certificate' => 'success',
                                    'submission' => 'primary',
                                    'payment' => 'warning',
                                    default => 'secondary'
                                }; ?>">
                                    <?php echo ucfirst($notification['type']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 