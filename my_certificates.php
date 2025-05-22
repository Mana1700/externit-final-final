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

// Fetch all certificates
$stmt = $pdo->prepare("
    SELECT c.*, t.title as task_title, comp.name as company_name
    FROM certificates c
    JOIN tasks t ON c.task_id = t.id
    JOIN companies comp ON c.company_id = comp.id
    WHERE c.student_id = ?
    ORDER BY c.issue_date DESC
");
$stmt->execute([$student_id]);
$certificates = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Certificates - ExternIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
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

        <div class="page-header">
            <h2 class="student-text">My Certificates</h2>
            <div class="badge-base">
                <i class="bi bi-award"></i>
                <?php echo count($certificates); ?> Certificates
            </div>
        </div>

        <?php if (empty($certificates)): ?>
            <div class="alert alert-info">
                <div class="alert-content">
                    <i class="bi bi-info-circle"></i>
                    <div class="alert-message">
                        <h6>No Certificates Yet</h6>
                        <p>Complete tasks successfully to earn certificates and showcase your achievements!</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($certificates as $cert): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="certificate-card">
                            <div class="certificate-card-header">
                                <h3><?php echo htmlspecialchars($cert['task_title']); ?></h3>
                                <div class="badge-base">
                                    #<?php echo str_pad($cert['id'], 8, '0', STR_PAD_LEFT); ?>
                                </div>
                            </div>
                            
                            <div class="certificate-card-body">
                                <div class="meta-item">
                                    <i class="bi bi-building"></i>
                                    <span><?php echo htmlspecialchars($cert['company_name']); ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="bi bi-calendar-check"></i>
                                    <span>Issued: <?php echo date('F j, Y', strtotime($cert['issue_date'])); ?></span>
                                </div>
                            </div>

                            <div class="certificate-card-footer">
                                <a href="generate_certificate_pdf.php?id=<?php echo $cert['id']; ?>" class="btn btn-outline-student">
                                    <i class="bi bi-eye"></i>
                                    View Certificate
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 