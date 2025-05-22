<?php
session_start();
require_once '../php/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];

    // Add error logging
    error_log("Login attempt - Email: $email, User Type: $user_type");

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND user_type = ?");
    $stmt->execute([$email, $user_type]);
    $user = $stmt->fetch();

    if (!$user) {
        error_log("[ADMIN LOGIN] No user found for email: $email and user_type: $user_type");
        header("Location: login.php?error=" . urlencode("No admin user found with this email and type."));
        exit();
    }

    error_log("[ADMIN LOGIN] User found. Email: {$user['email']}, User Type: {$user['user_type']}, Hash: {$user['password']}");

    // Plain text password check (not secure)
    if ($password !== $user['password']) {
        error_log("[ADMIN LOGIN] Password verification failed for email: $email");
        header("Location: login.php?error=" . urlencode("Incorrect password for admin user."));
        exit();
    }

    error_log("[ADMIN LOGIN] Password verification successful for email: $email");
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_type'] = $user_type;
    $_SESSION['name'] = $user['email'];
    header("Location: manage_companies.php");
    exit();
}
?> 