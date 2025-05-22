<?php
$host = 'localhost';
$dbname = 'newtest';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}

// Function to check and delete closed tasks
function deleteClosedTask($pdo, $task_id) {
    try {
        // Begin transaction
        $pdo->beginTransaction();

        // Delete task and related records
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$task_id]);

        // Delete related submissions
        $stmt = $pdo->prepare("DELETE FROM submissions WHERE task_id = ?");
        $stmt->execute([$task_id]);

        // Commit transaction
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        error_log("Error deleting task: " . $e->getMessage());
        return false;
    }
}
?> 