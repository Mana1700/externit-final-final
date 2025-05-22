<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validate email
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $errors[] = "Invalid email format";
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Email already registered";
    }
    
    // Validate password
    $password = $_POST['password'];
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    $user_type = $_POST['user_type'];
    if (!in_array($user_type, ['student', 'company'])) {
        $errors[] = "Invalid user type";
    }
    
    $name = trim($_POST['name']);
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Insert into users table first
            $stmt = $pdo->prepare("INSERT INTO users (email, password, user_type) VALUES (?, ?, ?)");
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->execute([$email, $hashed_password, $user_type]);
            $user_id = $pdo->lastInsertId();
            
            if ($user_type === 'student') {
                // Validate student fields
                $university = trim($_POST['university'] ?? '');
                $major = trim($_POST['major'] ?? '');
                $graduation_year = filter_var($_POST['graduation_year'], FILTER_VALIDATE_INT);
                
                // Check only required fields
                if (empty($university)) {
                    throw new Exception("Required field missing: University");
                }
                if (empty($major)) {
                    throw new Exception("Required field missing: Major");
                }
                if (!$graduation_year) {
                    throw new Exception("Required field missing: Graduation Year");
                }
                
                $stmt = $pdo->prepare("INSERT INTO students (user_id, name, university, major, graduation_year) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $name, $university, $major, $graduation_year]);
            } else {
                // Validate required company fields
                $industry = trim($_POST['industry'] ?? '');
                $website = trim($_POST['website'] ?? '');
                $address = trim($_POST['address'] ?? '');
                $tax_id = trim($_POST['tax_id'] ?? '');
                
                // Check only required fields
                if (empty($industry)) {
                    throw new Exception("Required field missing: Industry");
                }
                
                // Optional fields can be empty
                $website = empty($website) ? null : $website;
                $address = empty($address) ? null : $address;
                
                $registration_doc = null;
                if (isset($_FILES['registration_doc']) && $_FILES['registration_doc']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = __DIR__ . '/../../uploads/company_docs/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    error_log('Upload directory: ' . $upload_dir);
                    $file_extension = strtolower(pathinfo($_FILES['registration_doc']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['pdf', 'doc', 'docx'];
                    $max_size = 5 * 1024 * 1024; // 5MB
                    if (!in_array($file_extension, $allowed_extensions)) {
                        error_log('Invalid file type: ' . $file_extension);
                        throw new Exception("Invalid file type. Only PDF, DOC, and DOCX files are allowed");
                    }
                    if ($_FILES['registration_doc']['size'] > $max_size) {
                        error_log('File size exceeds limit: ' . $_FILES['registration_doc']['size']);
                        throw new Exception("File size exceeds 5MB limit");
                    }
                    $unique_filename = uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $unique_filename;
                    error_log('Upload path: ' . $upload_path);
                    if (move_uploaded_file($_FILES['registration_doc']['tmp_name'], $upload_path)) {
                        $registration_doc = $unique_filename;
                        error_log('File uploaded successfully: ' . $registration_doc);
                    } else {
                        error_log('Failed to upload file: ' . $_FILES['registration_doc']['tmp_name'] . ' to ' . $upload_path);
                        throw new Exception("Failed to upload file");
                    }
                }
                
                $stmt = $pdo->prepare("INSERT INTO companies (user_id, name, industry, website, address, registration_doc, tax_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $name, $industry, $website, $address, $registration_doc, $tax_id]);
            }
            
            $pdo->commit();
            
            // Set session variables
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_type'] = $user_type;
            $_SESSION['name'] = $name;
            
            header("Location: /externit-final/" . ($user_type === 'student' ? 'student_dashboard.php' : 'company_dashboard.php'));
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Registration failed: " . $e->getMessage();
            header("Location: /externit-final/register.php?type=" . $user_type . "&error=" . urlencode($error));
            exit();
        }
    } else {
        $error = implode(", ", $errors);
        header("Location: /externit-final/register.php?type=" . $user_type . "&error=" . urlencode($error));
        exit();
    }
}
?> 