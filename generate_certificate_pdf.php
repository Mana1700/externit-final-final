<?php
require_once 'php/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    die('Certificate ID not provided');
}

// Fetch certificate details with additional information
$stmt = $pdo->prepare("
    SELECT c.*, 
           s.name as student_name,
           t.title as task_title,
           t.estimated_hours,
           comp.name as company_name,
           sub.is_best as is_best_submission
    FROM certificates c
    JOIN students s ON c.student_id = s.id
    JOIN tasks t ON c.task_id = t.id
    JOIN companies comp ON c.company_id = comp.id
    JOIN submissions sub ON c.submission_id = sub.id
    WHERE c.id = ?
");
$stmt->execute([$_GET['id']]);
$cert = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cert) {
    die('Certificate not found');
}

// Check if the user has permission to view this certificate
if ($_SESSION['user_type'] === 'student') {
    $stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $student = $stmt->fetch();
    if ($student['id'] !== $cert['student_id']) {
        die('Access denied');
    }
} elseif ($_SESSION['user_type'] === 'company') {
    $stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $company = $stmt->fetch();
    if ($company['id'] !== $cert['company_id']) {
        die('Access denied');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Achievement - <?php echo htmlspecialchars($cert['task_title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        @page {
            size: landscape;
            margin: 0;
        }
        :root {
            --certificate-primary: #056954;
            --certificate-text: #045544;
            --certificate-accent: #0A8C71;
            --certificate-border: #056954;
            --certificate-gradient-start: rgba(5, 105, 84, 0.75);
            --certificate-gradient-end: rgba(10, 140, 113, 0.65);
        }
        body {
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .certificate-page {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .certificate-container {
            width: 1100px;
            height: 750px;
            background: #fff;
            position: relative;
            padding: 2rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .certificate-inner {
            height: 100%;
            width: 100%;
            position: relative;
            border: 2px solid var(--certificate-border);
            display: grid;
            grid-template-columns: 300px 1fr;
            overflow: hidden;
        }
        .certificate-sidebar {
            background: linear-gradient(135deg, 
                rgba(5, 105, 84, 0.15) 0%,
                rgba(10, 140, 113, 0.25) 100%
            );
            padding: 2rem;
            color: #056954;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            border-right: 1px solid rgba(5, 105, 84, 0.15);
            position: relative;
        }
        
        .certificate-sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.85);
            z-index: -1;
        }

        .company-logo {
            width: 180px;
            height: auto;
            margin-bottom: 1rem;
        }
        .certificate-main {
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }
        .certificate-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .certificate-title {
            font-size: 3rem;
            color: #056954;
            letter-spacing: 4px;
            text-transform: uppercase;
            font-weight: 300;
            margin: 0;
            line-height: 1.2;
        }
        .certificate-subtitle {
            font-size: 1.2rem;
            color: #0A8C71;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 0.5rem;
            opacity: 0.9;
        }
        .certificate-content {
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
        }
        .student-name {
            font-size: 2.5rem;
            font-weight: 300;
            color: #056954;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 1rem 0;
            line-height: 1.2;
            opacity: 0.9;
        }
        .task-title {
            font-size: 1.6rem;
            color: #045544;
            font-style: italic;
            margin: 1rem 0;
            line-height: 1.4;
            opacity: 0.85;
        }
        .company {
            font-size: 1.2rem;
            color: #0A8C71;
            margin: 0.5rem 0;
            opacity: 0.9;
        }
        .task-details {
            font-size: 1.1rem;
            color: #0A8C71;
            margin: 1rem 0;
            opacity: 0.85;
        }
        .date {
            font-style: italic;
            font-size: 1rem;
            color: #0A8C71;
            margin-top: 2rem;
            opacity: 0.8;
        }
        .certificate-footer {
            position: absolute;
            bottom: 1.5rem;
            left: 3rem;
            right: 3rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #0A8C71;
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .signature-line {
            width: 200px;
            border-top: 1px solid rgba(5, 105, 84, 0.6);
            margin: 0.5rem 0;
        }
        .best-submission-badge {
            position: absolute;
            top: 2rem;
            right: 2rem;
            background: rgba(5, 105, 84, 0.9);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
            box-shadow: 0 2px 4px rgba(5, 105, 84, 0.2);
        }
        .certificate-id {
            color: #056954;
            text-align: center;
            font-size: 0.9rem;
            letter-spacing: 1px;
            background: rgba(5, 105, 84, 0.08);
            padding: 0.75rem 1.25rem;
            border-radius: 6px;
            border: 1px solid rgba(5, 105, 84, 0.15);
        }
        @media print {
            body {
                background: white;
            }
            .certificate-page {
                padding: 0;
                height: 100vh;
            }
            .certificate-container {
                width: 100%;
                height: 100%;
                box-shadow: none;
            }
            nav, .print-button {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navigation.php'; ?>

    <div class="certificate-page">
        <div class="certificate-container">
            <div class="certificate-inner">
                <div class="certificate-sidebar">
                    <img src="assets/images/Logo.png" alt="ExternIT Logo" class="company-logo">
                    <div class="certificate-id">
                        Certificate ID:<br>
                        <?php echo str_pad($cert['id'], 8, '0', STR_PAD_LEFT); ?>
                    </div>
                </div>
                
                <div class="certificate-main">
                    <?php if ($cert['is_best_submission']): ?>
                    <div class="best-submission-badge">Best Submission</div>
                    <?php endif; ?>
                    
                    <div class="certificate-header">
                        <h1 class="certificate-title">Certificate</h1>
                        <div class="certificate-subtitle">of Achievement</div>
                    </div>
                    
                    <div class="certificate-content">
                        <p>This is to certify that</p>
                        
                        <p class="student-name"><?php echo htmlspecialchars($cert['student_name']); ?></p>
                        
                        <p>has successfully completed the task</p>
                        
                        <p class="task-title">"<?php echo htmlspecialchars($cert['task_title']); ?>"</p>
                        
                        <p class="company">assigned by <?php echo htmlspecialchars($cert['company_name']); ?></p>
                        
                        <p class="task-details">Task Duration: <?php echo htmlspecialchars($cert['estimated_hours']); ?> Hours</p>
                        
                        <p class="date">Issued on <?php echo date('F j, Y', strtotime($cert['issue_date'])); ?></p>
                    </div>
                    
                    <div class="certificate-footer">
                        <div class="signature">
                            <div class="signature-line"></div>
                            <div>Company Representative</div>
                        </div>
                        <div class="date-issued">
                            <?php echo date('F j, Y', strtotime($cert['issue_date'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <button class="btn btn-secondary print-button" onclick="window.print()">
            <i class="bi bi-printer"></i> Print Certificate
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 