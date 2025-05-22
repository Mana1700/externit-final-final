<?php
session_start();
require_once 'php/db.php';

// Check if user is logged in and is a company
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'company') {
    header('Location: login.php');
    exit();
}

// Get submission ID
$submission_id = $_GET['id'] ?? null;
if (!$submission_id) {
    header('Location: company_dashboard.php');
    exit();
}

// Get company ID
$stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$company = $stmt->fetch();
$company_id = $company['id'];

// Get submission details
$stmt = $pdo->prepare("
    SELECT s.*, t.title as task_title, t.company_id
    FROM submissions s
    JOIN tasks t ON s.task_id = t.id
    WHERE s.id = ? AND t.company_id = ?
");
$stmt->execute([$submission_id, $company_id]);
$submission = $stmt->fetch();

if (!$submission) {
    header('Location: review_submissions.php?error=Submission not found');
    exit();
}

$file_path = $submission['file_path'];
$full_path = __DIR__ . '/' . $file_path;

if (!file_exists($full_path)) {
    header('Location: review_submissions.php?error=Submission file not found');
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
?> 