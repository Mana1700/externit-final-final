<?php
/**
 * Common utility functions for the ExternIT platform
 */

// Sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate email
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Hash password
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// Generate random string
function generate_random_string($length = 10) {
    return bin2hex(random_bytes($length));
}

// Upload file
function upload_file($file, $destination, $allowed_types = ['pdf', 'doc', 'docx', 'zip']) {
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Check file type
    if (!in_array($file_extension, $allowed_types)) {
        return [
            'success' => false,
            'message' => 'Invalid file type. Allowed types: ' . implode(', ', $allowed_types)
        ];
    }
    
    // Generate unique filename
    $filename = generate_random_string() . '.' . $file_extension;
    $filepath = $destination . '/' . $filename;
    
    // Create directory if it doesn't exist
    if (!file_exists($destination)) {
        mkdir($destination, 0777, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'success' => true,
            'filepath' => $filepath,
            'filename' => $filename
        ];
    }
    
    return [
        'success' => false,
        'message' => 'Failed to upload file'
    ];
}

// Format date
function format_date($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check if user is student
function is_student() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student';
}

// Check if user is company
function is_company() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'company';
}

// Redirect with message
function redirect_with_message($url, $message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
    header("Location: $url");
    exit();
}

// Display flash message
function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message']['message'];
        $type = $_SESSION['flash_message']['type'];
        unset($_SESSION['flash_message']);
        
        return "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                    {$message}
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>";
    }
    return '';
}

// Get user profile
function get_user_profile($user_id, $user_type) {
    global $db;
    
    $table = $user_type === 'student' ? 'student_profiles' : 'company_profiles';
    $stmt = $db->prepare("SELECT * FROM $table WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Format currency
function format_currency($amount) {
    return '$' . number_format($amount, 2);
}

// Truncate text
function truncate_text($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

// Get file size in human readable format
function format_file_size($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

// Check if string contains HTML
function contains_html($string) {
    return $string != strip_tags($string);
}

// Get time ago
function time_ago($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
} 