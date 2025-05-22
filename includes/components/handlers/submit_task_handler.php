<?php
session_start();
require_once '../../../php/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: /externit-final/login.php');
    exit();
}

// Get student ID
$stmt = $pdo->prepare("SELECT s.id FROM students s WHERE s.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();

if (!$student) {
    header('Location: /externit-final/login.php?error=' . urlencode('Student information not found'));
    exit();
}

$student_id = $student['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = $_POST['task_id'] ?? null;
    $brief = $_POST['brief'] ?? '';

    // Validate task ID
    if (!$task_id) {
        header('Location: /externit-final/student_dashboard.php?error=Invalid task');
        exit();
    }

    // Check if task exists and is still open
    $stmt = $pdo->prepare("
        SELECT t.*, 
               (SELECT COUNT(*) FROM submissions s WHERE s.task_id = t.id) as current_submissions
        FROM tasks t
        WHERE t.id = ? AND t.status = 'active'
    ");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch();

    // Check if task should be closed
    $should_close = false;
    $close_reason = '';

    if (!$task) {
        header('Location: /externit-final/student_dashboard.php?error=Task not found');
        exit();
    }

    if (strtotime($task['deadline']) <= time()) {
        $should_close = true;
        $close_reason = 'deadline expired';
    }

    if ($task['current_submissions'] >= $task['max_submissions']) {
        $should_close = true;
        $close_reason = 'maximum submissions reached';
    }

    if ($should_close) {
        // Delete the task since it's closed
        if (deleteClosedTask($pdo, $task_id)) {
            header('Location: /externit-final/student_dashboard.php?error=Task has been closed: ' . $close_reason);
            exit();
        }
    }

    // Check if student has already submitted
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM submissions WHERE task_id = ? AND student_id = ?");
    $stmt->execute([$task_id, $student_id]);
    if ($stmt->fetchColumn() > 0) {
        header('Location: /externit-final/student_dashboard.php?error=You have already submitted a solution for this task');
        exit();
    }

    // Handle file upload
    if (!isset($_FILES['solution_file']) || $_FILES['solution_file']['error'] !== UPLOAD_ERR_OK) {
        $error = isset($_FILES['solution_file']) ? 'Upload error code: ' . $_FILES['solution_file']['error'] : 'No file uploaded';
        header('Location: /externit-final/submit_task.php?task_id=' . $task_id . '&error=' . urlencode($error));
        exit();
    }

    $file = $_FILES['solution_file'];
    
    // Validate file size (10MB max)
    if ($file['size'] > 10 * 1024 * 1024) {
        header('Location: /externit-final/submit_task.php?task_id=' . $task_id . '&error=File size must be less than 10MB');
        exit();
    }

    // Validate file type
    $allowed_types = ['application/zip', 'application/x-rar-compressed', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        header('Location: /externit-final/submit_task.php?task_id=' . $task_id . '&error=' . urlencode('Invalid file type (' . $mime_type . '). Please upload a zip, rar, pdf, doc, or docx file'));
        exit();
    }

    // Create upload directory if it doesn't exist
    $upload_dir = '../../../uploads/submissions/';
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            header('Location: /externit-final/submit_task.php?task_id=' . $task_id . '&error=' . urlencode('Failed to create upload directory'));
            exit();
        }
    }

    // Check directory permissions
    if (!is_writable($upload_dir)) {
        header('Location: /externit-final/submit_task.php?task_id=' . $task_id . '&error=' . urlencode('Upload directory is not writable'));
        exit();
    }

    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $unique_filename = uniqid('submission_') . '_' . time() . '.' . $file_extension;
    $file_path = $upload_dir . $unique_filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        $upload_error = error_get_last();
        header('Location: /externit-final/submit_task.php?task_id=' . $task_id . '&error=' . urlencode('Failed to move uploaded file: ' . ($upload_error['message'] ?? 'Unknown error')));
        exit();
    }

    try {
        // Begin transaction
        $pdo->beginTransaction();

        // Insert submission
        $stmt = $pdo->prepare("
            INSERT INTO submissions (task_id, student_id, comments, file_path, status, created_at)
            VALUES (?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->execute([$task_id, $student_id, $brief, 'uploads/submissions/' . $unique_filename]);

        // Check if this submission makes the task reach its limit
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as submission_count
            FROM submissions
            WHERE task_id = ?
        ");
        $stmt->execute([$task_id]);
        $submission_count = $stmt->fetch()['submission_count'];

        // If max submissions reached, delete the task
        if ($submission_count >= $task['max_submissions']) {
            deleteClosedTask($pdo, $task_id);
        }

        // Commit transaction
        $pdo->commit();

        header('Location: /externit-final/student_dashboard.php?success=Your solution has been submitted successfully');
        exit();
    } catch (Exception $e) {
        // Rollback transaction
        $pdo->rollBack();
        
        // Delete uploaded file if it exists
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Log the error
        error_log("Submission error: " . $e->getMessage());
        
        header('Location: /externit-final/submit_task.php?task_id=' . $task_id . '&error=' . urlencode('Database error: ' . $e->getMessage()));
        exit();
    }
} else {
    header('Location: /externit-final/student_dashboard.php');
    exit();
} 