<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine if user is logged in and their type
$isLoggedIn = isset($_SESSION['user_id']);
$userType = $isLoggedIn ? $_SESSION['user_type'] : '';

// Fetch unread notifications if user is logged in
if ($isLoggedIn) {
    require_once __DIR__ . '/../php/db.php';
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as unread_count 
        FROM notifications 
        WHERE user_id = ? AND is_read = FALSE
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $unreadCount = $stmt->fetch()['unread_count'];
}
?>

<style>
.navbar-brand img {
    height: 40px;
    width: auto;
    transition: transform 0.2s ease;
}

.navbar-brand:hover img {
    transform: scale(1.05);
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: #dc3545;
    color: white;
    border-radius: 50%;
    padding: 0.25em 0.6em;
    font-size: 0.75em;
}

.notification-dropdown {
    width: 350px;
    max-height: 400px;
    overflow-y: auto;
}

.notification-item {
    padding: 10px;
    border-bottom: 1px solid #eee;
}

.notification-item.unread {
    background-color: #f8f9fa;
}

.notification-item:hover {
    background-color: #f0f0f0;
}

.notification-time {
    font-size: 0.8em;
    color: #6c757d;
}

.notification-type {
    font-size: 0.8em;
    padding: 2px 6px;
    border-radius: 3px;
    margin-left: 5px;
}

.notification-type.certificate {
    background-color: #28a745;
    color: white;
}

.notification-type.submission {
    background-color: #007bff;
    color: white;
}

.notification-type.payment {
    background-color: #ffc107;
    color: black;
}
</style>

<nav class="navbar navbar-expand-lg <?php echo $userType === 'student' ? 'student-nav' : 'company-nav'; ?> mb-4">
    <div class="container">
        <a class="navbar-brand" href="/externit-final/index.php">
            <img src="/externit-final/assets/images/Logo.png" alt="ExternIT Logo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if ($isLoggedIn): ?>
                    <?php if ($userType === 'student'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/externit-final/student_dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/externit-final/view_tasks.php">View Tasks</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/externit-final/my_submissions.php">My Submissions</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/externit-final/my_certificates.php">My Certificates</a>
                        </li>
                    <?php elseif ($userType === 'company'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/externit-final/company_dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/externit-final/manage_tasks.php">Manage Tasks</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/externit-final/review_submissions.php">Review Submissions</a>
                        </li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/manage_companies.php">Manage Companies</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/manage_contacts.php">Contact Messages</a>
                        </li>
                    <?php endif; ?>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/externit-final/about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/externit-final/features.php">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/externit-final/process.php">How It Works</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="/externit-final/help.php">Help</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php if ($isLoggedIn): ?>
                    <?php if ($userType === 'student' || $userType === 'company'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell-fill"></i>
                                <?php if ($unreadCount > 0): ?>
                                    <span class="notification-badge"><?php echo $unreadCount; ?></span>
                                <?php endif; ?>
                            </a>
                            <div class="dropdown-menu notification-dropdown" aria-labelledby="notificationDropdown">
                                <?php
                                if ($isLoggedIn) {
                                    $stmt = $pdo->prepare("
                                        SELECT * FROM notifications 
                                        WHERE user_id = ? 
                                        ORDER BY created_at DESC 
                                        LIMIT 10
                                    ");
                                    $stmt->execute([$_SESSION['user_id']]);
                                    $notifications = $stmt->fetchAll();

                                    if (empty($notifications)): ?>
                                        <div class="notification-item text-center">
                                            <p class="mb-0">No notifications</p>
                                        </div>
                                    <?php else: 
                                        foreach ($notifications as $notification): ?>
                                            <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                                        <p class="mb-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                                                        <small class="notification-time">
                                                            <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?>
                                                        </small>
                                                    </div>
                                                    <span class="notification-type <?php echo $notification['type']; ?>">
                                                        <?php echo ucfirst($notification['type']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="text-center p-2 border-top">
                                            <a href="/externit-final/notifications.php" class="btn btn-sm btn-outline-primary">View All Notifications</a>
                                        </div>
                                    <?php endif;
                                }
                                ?>
                            </div>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="/externit-final/profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/externit-final/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/externit-final/login.php">Login</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="registerDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Register
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="registerDropdown">
                            <li><a class="dropdown-item" href="/externit-final/register.php?type=student">As Student</a></li>
                            <li><a class="dropdown-item" href="/externit-final/register.php?type=company">As Company</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mark notifications as read when dropdown is shown
    const notificationDropdown = document.getElementById('notificationDropdown');
    if (notificationDropdown) {
        notificationDropdown.addEventListener('shown.bs.dropdown', function() {
            fetch('/externit-final/mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'mark_all=1'
            });
        });
    }
});
</script> 