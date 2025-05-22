<?php
session_start();
require_once '../../php/db.php';

// Check if user is logged in and is a company
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'company') {
    header('Location: ../../login.php');
    exit();
}

// Get company ID from companies table
try {
    $stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $company = $stmt->fetch();
    if (!$company) {
        header('Location: ../../company_dashboard.php?error=Company not found');
        exit();
    }
    $company_id = $company['id'];
} catch (PDOException $e) {
    header('Location: ../../company_dashboard.php?error=Failed to retrieve company information');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $difficulty = $_POST['difficulty'];
    $deadline = $_POST['due_date'];
    $estimated_hours = (int)$_POST['estimated_hours'];
    $max_submissions = (int)$_POST['max_submissions'];
    $budget = (float)$_POST['reward'];
    $deliverables = trim($_POST['deliverables']);

    // Validate required fields
    if (empty($title) || empty($description) || empty($difficulty) || 
        empty($deadline) || empty($estimated_hours) || empty($max_submissions) || 
        empty($budget) || empty($deliverables)) {
        header('Location: ../../company_dashboard.php?error=All required fields must be filled#createTaskModal');
        exit();
    }

    // Validate numeric fields
    if ($estimated_hours < 1 || $estimated_hours > 168) {
        header('Location: ../../company_dashboard.php?error=Invalid estimated hours#createTaskModal');
        exit();
    }
    if ($max_submissions < 1 || $max_submissions > 100) {
        header('Location: ../../company_dashboard.php?error=Invalid maximum submissions#createTaskModal');
        exit();
    }
    if ($budget < 100) {
        header('Location: ../../company_dashboard.php?error=Minimum reward is 100 EGP#createTaskModal');
        exit();
    }

    // Validate deadline
    $deadline_obj = new DateTime($deadline);
    $today = new DateTime();
    if ($deadline_obj <= $today) {
        header('Location: ../../company_dashboard.php?error=Deadline must be in the future#createTaskModal');
        exit();
    }

    // Handle file upload if present
    $template_file_path = null;
    if (isset($_FILES['template_file']) && $_FILES['template_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['template_file'];
        
        // Validate file size (10MB max)
        if ($file['size'] > 10 * 1024 * 1024) {
            header('Location: ../../company_dashboard.php?error=Template file must be less than 10MB#createTaskModal');
            exit();
        }

        // Create upload directory if it doesn't exist
        $upload_dir = '../../uploads/templates/';
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                header('Location: ../../company_dashboard.php?error=Failed to create upload directory#createTaskModal');
                exit();
            }
        }

        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('template_') . '.' . $file_extension;
        $template_file_path = 'uploads/templates/' . $file_name;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $upload_dir . $file_name)) {
            header('Location: ../../company_dashboard.php?error=Failed to upload template file#createTaskModal');
            exit();
        }
    }

    try {
        // Begin transaction
        $pdo->beginTransaction();

        // Insert task
        $stmt = $pdo->prepare("
            INSERT INTO tasks (
                company_id, title, description, difficulty, deadline, 
                estimated_hours, max_submissions, budget, deliverables, 
                template_file_path, created_at, status
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'active'
            )
        ");
        
        $stmt->execute([
            $company_id, $title, $description, $difficulty, $deadline,
            $estimated_hours, $max_submissions, $budget, $deliverables,
            $template_file_path
        ]);

        // Get the task ID
        $task_id = $pdo->lastInsertId();

        // Create a special certificate template for the best submission
        $stmt = $pdo->prepare("
            INSERT INTO certificate_templates (
                task_id, is_best_submission, template_html
            ) VALUES (?, true, ?)
        ");

        $special_template = '
<style>
    .certificate-special {
        background: linear-gradient(135deg, rgba(191, 109, 58, 0.1) 0%, rgba(191, 109, 58, 0.05) 100%);
        border: 2px solid #BF6D3A;
        padding: 20px;
        position: relative;
    }
    .certificate-special::before {
        content: "Best Submission";
        position: absolute;
        top: 10px;
        right: 10px;
        background: #BF6D3A;
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 14px;
    }
    .certificate-special h1 { 
        color: #BF6D3A; 
        font-size: 28pt; 
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 2px;
    }
    .certificate-body { 
        text-align: center; 
        line-height: 1.8;
        font-size: 14pt;
    }
    .award-seal {
        width: 120px;
        height: 120px;
        margin: 20px auto;
        background: url(\'seals/best_submission.png\') no-repeat center center;
        background-size: contain;
    }
    .signature-area {
        margin-top: 40px;
        display: flex;
        justify-content: space-around;
    }
    .signature-line {
        width: 200px;
        border-top: 1px solid #666;
        margin-top: 10px;
    }
</style>
<div class="certificate-special">
    <div class="certificate-header">
        <h1>Certificate of Excellence</h1>
    </div>
    <div class="certificate-body">
        <p>This is to certify that</p>
        <h2>{STUDENT_NAME}</h2>
        <p>from {UNIVERSITY}</p>
        <p>has demonstrated exceptional skill and dedication in completing</p>
        <h3>{TASK_NAME}</h3>
        <p>This submission was selected as the <strong>Best Submission</strong> by</p>
        <h3>{COMPANY_NAME}</h3>
        <p>Awarded on {DATE}</p>
        <div class="award-seal"></div>
    </div>
    <div class="signature-area">
        <div class="signature">
            <div class="signature-line"></div>
            <p>Company Representative</p>
        </div>
        <div class="signature">
            <div class="signature-line"></div>
            <p>ExternIT Director</p>
        </div>
    </div>
    <div class="certificate-footer">
        <p>Certificate ID: {CERTIFICATE_ID}</p>
        <p>Verified by ExternIT | Best Submission Award</p>
    </div>
</div>';

        $stmt->execute([$task_id, $special_template]);

        // Commit transaction
        $pdo->commit();
        
        // Redirect to company dashboard with success message
        header('Location: ../../company_dashboard.php?success=Task created successfully');
        exit();

    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        
        // Delete uploaded file if it exists
        if ($template_file_path && file_exists('../../' . $template_file_path)) {
            unlink('../../' . $template_file_path);
        }

        header('Location: ../../company_dashboard.php?error=Failed to create task: ' . $e->getMessage() . '#createTaskModal');
        exit();
    }
} else {
    // If not POST request, redirect to dashboard
    header('Location: ../../company_dashboard.php');
    exit();
} 