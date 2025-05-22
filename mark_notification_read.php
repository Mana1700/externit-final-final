<?php
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['notification_id'])) {
        // Mark single notification as read
        $notification_id = intval($_POST['notification_id']);
        
        // Verify the notification belongs to the current user
        $stmt = $pdo->prepare("
            UPDATE notifications 
            SET is_read = TRUE 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$notification_id, $_SESSION['user_id']]);
    } elseif (isset($_POST['mark_all'])) {
        // Mark all notifications as read
        $stmt = $pdo->prepare("
            UPDATE notifications 
            SET is_read = TRUE 
            WHERE user_id = ? AND is_read = FALSE
        ");
        $stmt->execute([$_SESSION['user_id']]);
    }
}

// Redirect back to the previous page
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit(); 