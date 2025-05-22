<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'ExternIT'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/newtest/css/main.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand company-text fw-bold" href="/newtest/index.php">ExternIT</a>
            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'company'): ?>
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="/newtest/company_dashboard.php">Dashboard</a>
                    <a class="nav-link" href="/newtest/create_task.php">Create Task</a>
                    <a class="nav-link" href="/newtest/review_submissions.php">Review Submissions</a>
                    <a class="nav-link" href="/newtest/company_profile.php">Profile</a>
                </div>
            <?php endif; ?>
        </div>
    </nav>
</body> 
</html> 