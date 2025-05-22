<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is a company
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'company') {
    header('Location: ../login.php');
    exit();
}

// Get company ID from companies table
try {
    $stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $company = $stmt->fetch();
    if (!$company) {
        header('Location: ../company_dashboard.php?error=Company not found');
        exit();
    }
    $company_id = $company['id'];
} catch (PDOException $e) {
    header('Location: ../company_dashboard.php?error=Failed to retrieve company information');
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
        header('Location: ../company_dashboard.php?error=All required fields must be filled#createTaskModal');
        exit();
    }

    // Validate numeric fields
    if ($estimated_hours < 1 || $estimated_hours > 168) {
        header('Location: ../company_dashboard.php?error=Invalid estimated hours#createTaskModal');
        exit();
    }
    if ($max_submissions < 1 || $max_submissions > 100) {
        header('Location: ../company_dashboard.php?error=Invalid maximum submissions#createTaskModal');
        exit();
    }
    if ($budget < 100) {
        header('Location: ../company_dashboard.php?error=Minimum reward is 100 EGP#createTaskModal');
        exit();
    }

    // Validate deadline
    $deadline_obj = new DateTime($deadline);
    $today = new DateTime();
    if ($deadline_obj <= $today) {
        header('Location: ../company_dashboard.php?error=Deadline must be in the future#createTaskModal');
        exit();
    }

    // Handle file upload if present
    $template_file_path = null;
    if (isset($_FILES['template_file']) && $_FILES['template_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['template_file'];
        
        // Validate file size (10MB max)
        if ($file['size'] > 10 * 1024 * 1024) {
            header('Location: ../company_dashboard.php?error=Template file must be less than 10MB#createTaskModal');
            exit();
        }

        // Create upload directory if it doesn't exist
        $upload_dir = '../uploads/templates/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('template_') . '.' . $file_extension;
        $template_file_path = 'uploads/templates/' . $file_name;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $upload_dir . $file_name)) {
            header('Location: ../company_dashboard.php?error=Failed to upload template file#createTaskModal');
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

        // Commit transaction
        $pdo->commit();
        
        // Redirect to company dashboard with success message
        header('Location: ../company_dashboard.php?success=Task created successfully');
        exit();

    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        
        // Delete uploaded file if it exists
        if ($template_file_path && file_exists('../' . $template_file_path)) {
            unlink('../' . $template_file_path);
        }

        header('Location: ../company_dashboard.php?error=Failed to create task: ' . $e->getMessage() . '#createTaskModal');
        exit();
    }
} else {
    // If not POST request, redirect to dashboard
    header('Location: ../company_dashboard.php');
    exit();
} 