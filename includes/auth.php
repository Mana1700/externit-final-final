<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Authentication helper functions
 */

// Register new user
function register_user($data) {
    global $db;
    
    try {
        $db->beginTransaction();
        
        // Insert user
        $stmt = $db->prepare("
            INSERT INTO users (email, password, user_type, name)
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['email'],
            hash_password($data['password']),
            $data['user_type'],
            $data['name']
        ]);
        
        $user_id = $db->lastInsertId();
        
        // Insert profile based on user type
        if ($data['user_type'] === 'student') {
            $stmt = $db->prepare("
                INSERT INTO student_profiles (user_id, university, major, graduation_year)
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $user_id,
                $data['university'],
                $data['major'],
                $data['graduation_year']
            ]);
        } else {
            $stmt = $db->prepare("
                INSERT INTO company_profiles (user_id, company_name, industry, website)
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $user_id,
                $data['company_name'],
                $data['industry'],
                $data['website'] ?? null
            ]);
        }
        
        $db->commit();
        return [
            'success' => true,
            'user_id' => $user_id
        ];
    } catch (Exception $e) {
        $db->rollBack();
        return [
            'success' => false,
            'message' => 'Registration failed: ' . $e->getMessage()
        ];
    }
}

// Login user
function login_user($email, $password) {
    global $db;
    
    try {
        // Get user
        $stmt = $db->prepare("
            SELECT id, email, password, user_type, name
            FROM users
            WHERE email = ?
        ");
        
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !verify_password($password, $user['password'])) {
            return [
                'success' => false,
                'message' => 'Invalid email or password'
            ];
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['user_name'] = $user['name'];
        
        return [
            'success' => true,
            'user' => $user
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Login failed: ' . $e->getMessage()
        ];
    }
}

// Logout user
function logout_user() {
    session_unset();
    session_destroy();
    session_start();
}

// Check if email exists
function email_exists($email) {
    global $db;
    
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    return $stmt->fetch() !== false;
}

// Get user by ID
function get_user($user_id) {
    global $db;
    
    $stmt = $db->prepare("
        SELECT id, email, user_type, name, created_at
        FROM users
        WHERE id = ?
    ");
    
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Update user password
function update_password($user_id, $current_password, $new_password) {
    global $db;
    
    try {
        // Verify current password
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!verify_password($current_password, $user['password'])) {
            return [
                'success' => false,
                'message' => 'Current password is incorrect'
            ];
        }
        
        // Update password
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([hash_password($new_password), $user_id]);
        
        return [
            'success' => true,
            'message' => 'Password updated successfully'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Failed to update password: ' . $e->getMessage()
        ];
    }
}

// Require authentication
function require_auth() {
    if (!is_logged_in()) {
        redirect_with_message('/views/auth/login.php', 'Please log in to access this page', 'warning');
    }
}

// Require student role
function require_student() {
    require_auth();
    if (!is_student()) {
        redirect_with_message('/', 'Access denied: Student access only', 'danger');
    }
}

// Require company role
function require_company() {
    require_auth();
    if (!is_company()) {
        redirect_with_message('/', 'Access denied: Company access only', 'danger');
    }
} 