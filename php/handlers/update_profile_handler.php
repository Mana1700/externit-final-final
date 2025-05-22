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

    // Handle photo upload if provided
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if (!in_array($_FILES['photo']['type'], $allowed_types)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and GIF files are allowed.');
        }

        if ($_FILES['photo']['size'] > $max_size) {
            throw new Exception('File size too large. Maximum size is 2MB.');
        }

        $upload_dir = '../../uploads/profile_photos/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('profile_') . '.' . $file_extension;
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_path)) {
            $photo_path = 'uploads/profile_photos/' . $filename;
            
            // Update photo in users table
            $stmt = $pdo->prepare("UPDATE users SET photo = ? WHERE id = ?");
            $stmt->execute([$photo_path, $user_id]);
        }
    }

    // Update profile based on user type
    if ($user_type === 'student') {
        $stmt = $pdo->prepare("
            UPDATE students 
            SET name = ?, 
                university = ?, 
                major = ?, 
                graduation_year = ?,
                phone = ?,
                bio = ?,
                iban = ?
            WHERE user_id = ?
        ");
        $stmt->execute([
            $_POST['name'],
            $_POST['university'],
            $_POST['major'],
            $_POST['graduation_year'],
            $_POST['phone'] ?? null,
            $_POST['bio'] ?? null,
            $_POST['iban'] ?? null,
            $user_id
        ]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE companies 
            SET name = ?, 
                website = ?
            WHERE user_id = ?
        ");
        $stmt->execute([
            $_POST['name'],
            $_POST['website'],
            $user_id
        ]);
    }

    // Commit transaction
    $pdo->commit();
    header('Location: ../../profile.php?success=' . urlencode('Profile updated successfully'));
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    header('Location: ../../profile.php?error=' . urlencode($e->getMessage()));
    exit();
} 