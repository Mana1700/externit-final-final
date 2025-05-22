<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];

    try {
        // First check in the users table
        $stmt = $pdo->prepare("SELECT u.*, COALESCE(s.name, c.name) as name 
            FROM users u 
            LEFT JOIN students s ON u.id = s.user_id AND u.user_type = 'student'
            LEFT JOIN companies c ON u.id = c.user_id AND u.user_type = 'company'
            WHERE u.email = ? AND u.user_type = ?");
        
        $stmt->execute([$email, $user_type]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user_type;
            $_SESSION['name'] = $user['name'];
            
            header("Location: ../" . ($user_type === 'student' ? 'student_dashboard.php' : 'company_dashboard.php'));
            exit();
        } else {
            header("Location: ../login.php?error=" . urlencode("Invalid email or password"));
            exit();
        }
    } catch (PDOException $e) {
        header("Location: ../login.php?error=" . urlencode("Login failed. Please try again."));
        exit();
    }
}
?> 