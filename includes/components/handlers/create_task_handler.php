<?php
session_start();
require_once '../../../php/db.php';

// Check if user is logged in and is a company
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'company') {
    header('Location: /externit-final/login.php');
    exit();
}

// Get company ID and check approval status
$stmt = $pdo->prepare("
    SELECT c.id, c.is_approved, c.approval_status 
    FROM companies c 
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$company = $stmt->fetch();

if (!$company) {
    header('Location: /externit-final/login.php?error=' . urlencode('Company information not found'));
    exit();
}

// Check if company is approved
if (!$company['is_approved'] || $company['approval_status'] !== 'approved') {
    header('Location: /externit-final/company_dashboard.php?error=' . urlencode('Your company account is not yet approved. Please wait for admin approval.'));
    exit();
}

// ... rest of the existing task creation code ...
// ... existing code ... 