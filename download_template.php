<?php
session_start();
require_once 'php/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get task ID
$task_id = $_GET['id'] ?? null;
if (!$task_id) {
    header('Location: ' . ($_SESSION['user_type'] === 'student' ? 'student_dashboard.php' : 'company_dashboard.php'));
    exit();
}

// Get task details
$stmt = $pdo->prepare("SELECT template_file_path FROM tasks WHERE id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch();

if (!$task || !$task['template_file_path']) {
    header('Location: view_task.php?id=' . $task_id . '&error=Template file not found');
    exit();
}

$file_path = $task['template_file_path'];
$full_path = __DIR__ . '/' . $file_path;

if (!file_exists($full_path)) {
    header('Location: view_task.php?id=' . $task_id . '&error=Template file not found');
    exit();
}

// Get file info
$file_name = basename($file_path);
$file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
$mime_type = mime_content_type($full_path);

// Set headers for download
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Content-Length: ' . filesize($full_path));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output file
readfile($full_path);
exit(); 