<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

try {
    // Start transaction
    $pdo->beginTransaction();

    // Get user's photo path before deletion
    $stmt = $pdo->prepare("SELECT photo FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // Delete profile photo if exists
    if ($user && $user['photo']) {
        $photo_path = '../../' . $user['photo'];
        if (file_exists($photo_path)) {
            unlink($photo_path);
        }
    }

    // Delete based on user type
    if ($user_type === 'student') {
        // Delete student's submissions and related files
        $stmt = $pdo->prepare("
            SELECT s.*, t.title 
            FROM submissions s 
            JOIN tasks t ON s.task_id = t.id 
            WHERE s.student_id = (SELECT id FROM students WHERE user_id = ?)
        ");
        $stmt->execute([$user_id]);
        $submissions = $stmt->fetchAll();

        foreach ($submissions as $submission) {
            // Delete submission files
            if ($submission['file_path']) {
                $file_path = '../../' . $submission['file_path'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
        }

        // Delete submissions
        $stmt = $pdo->prepare("
            DELETE s FROM submissions s 
            WHERE s.student_id = (SELECT id FROM students WHERE user_id = ?)
        ");
        $stmt->execute([$user_id]);

        // Delete student record
        $stmt = $pdo->prepare("DELETE FROM students WHERE user_id = ?");
        $stmt->execute([$user_id]);
    } else {
        // Delete company's tasks and related submissions
        $stmt = $pdo->prepare("
            SELECT t.*, s.file_path 
            FROM tasks t 
            LEFT JOIN submissions s ON s.task_id = t.id 
            WHERE t.company_id = (SELECT id FROM companies WHERE user_id = ?)
        ");
        $stmt->execute([$user_id]);
        $tasks = $stmt->fetchAll();

        foreach ($tasks as $task) {
            // Delete submission files
            if ($task['file_path']) {
                $file_path = '../../' . $task['file_path'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            // Delete task template if exists
            if ($task['template_file_path']) {
                $template_path = '../../' . $task['template_file_path'];
                if (file_exists($template_path)) {
                    unlink($template_path);
                }
            }
        }

        // Delete all submissions for company's tasks
        $stmt = $pdo->prepare("
            DELETE s FROM submissions s 
            INNER JOIN tasks t ON s.task_id = t.id 
            WHERE t.company_id = (SELECT id FROM companies WHERE user_id = ?)
        ");
        $stmt->execute([$user_id]);

        // Delete all tasks
        $stmt = $pdo->prepare("
            DELETE FROM tasks 
            WHERE company_id = (SELECT id FROM companies WHERE user_id = ?)
        ");
        $stmt->execute([$user_id]);

        // Delete company record
        $stmt = $pdo->prepare("DELETE FROM companies WHERE user_id = ?");
        $stmt->execute([$user_id]);
    }

    // Finally, delete user account
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);

    // Commit transaction
    $pdo->commit();

    // Clear session and redirect to login
    session_destroy();
    header('Location: ../../login.php?success=' . urlencode('Your account has been successfully deleted'));
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    header('Location: ../../profile.php?error=' . urlencode('Failed to delete account: ' . $e->getMessage()));
    exit();
} 