<?php
require_once 'php/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        header('Location: help.php?error=Please fill in all fields');
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO contact_submissions (name, email, subject, message)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$name, $email, $subject, $message]);
        
        header('Location: help.php?success=Your message has been sent successfully');
    } catch (PDOException $e) {
        header('Location: help.php?error=Failed to send message. Please try again later.');
    }
    exit();
} else {
    header('Location: help.php');
    exit();
} 