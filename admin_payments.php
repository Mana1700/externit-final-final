<?php
session_start();
require_once 'php/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle payment status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_id'])) {
    $payment_id = $_POST['payment_id'];
    $new_status = $_POST['status'];
    
    try {
        $pdo->beginTransaction();
        
        // Update payment status
        $stmt = $pdo->prepare("
            UPDATE payments 
            SET status = ?, payment_date = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$new_status, $payment_id]);
        
        // If payment is completed, notify the student
        if ($new_status === 'completed') {
            $stmt = $pdo->prepare("
                INSERT INTO notifications (
                    user_id, type, title, message
                ) SELECT 
                    st.user_id,
                    'payment',
                    'Payment Completed',
                    CONCAT('Your payment of ', p.amount, ' for task \"', t.title, '\" has been completed.')
                FROM payments p
                JOIN certificates c ON p.certificate_id = c.id
                JOIN submissions s ON c.submission_id = s.id
                JOIN students st ON s.student_id = st.id
                JOIN tasks t ON s.task_id = t.id
                WHERE p.id = ?
            ");
            $stmt->execute([$payment_id]);
        }
        
        $pdo->commit();
        header('Location: admin_payments.php?success=Payment status updated successfully');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        header('Location: admin_payments.php?error=' . urlencode($e->getMessage()));
        exit();
    }
}

// Fetch all pending payments with related information
$stmt = $pdo->prepare("
    SELECT 
        p.*,
        c.certificate_number,
        t.title as task_title,
        t.budget,
        st.name as student_name,
        st.iban as student_iban,
        comp.name as company_name
    FROM payments p
    JOIN certificates c ON p.certificate_id = c.id
    JOIN submissions s ON c.submission_id = s.id
    JOIN students st ON s.student_id = st.id
    JOIN tasks t ON s.task_id = t.id
    JOIN companies comp ON t.company_id = comp.id
    ORDER BY p.created_at DESC
");
$stmt->execute();
$payments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Payments - ExternIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>

    <div class="container mt-4">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card company-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h2 class="mb-0" style="font-size:1.3rem;font-weight:600;color:var(--company-primary);">
                    <i class="bi bi-cash-coin me-2"></i> Payment Management
                </h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Certificate</th>
                                <th>Task</th>
                                <th>Student</th>
                                <th>IBAN</th>
                                <th>Company</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td>
                                        <span class="badge-outline-company">
                                            <?php echo htmlspecialchars($payment['certificate_number']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($payment['task_title']); ?></td>
                                    <td>
                                        <span class="badge-outline-student">
                                            <i class="bi bi-person"></i>
                                            <?php echo htmlspecialchars($payment['student_name']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($payment['student_iban']): ?>
                                            <span class="badge badge-base bg-success text-white">
                                                <i class="bi bi-bank"></i>
                                                <?php echo htmlspecialchars($payment['student_iban']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-base bg-danger text-white" data-bs-toggle="tooltip" title="Student must add IBAN to receive payment">
                                                <i class="bi bi-exclamation-circle"></i> No IBAN
                                            </span>
                                            <?php
                                                // Fetch student email (if not already available)
                                                $stmt_email = $pdo->prepare("SELECT u.email FROM students st JOIN users u ON st.user_id = u.id WHERE st.name = ? LIMIT 1");
                                                $stmt_email->execute([ $payment['student_name'] ]);
                                                $student_email_row = $stmt_email->fetch();
                                                $student_email = $student_email_row ? $student_email_row['email'] : '';
                                            ?>
                                            <?php if ($student_email): ?>
                                                <a href="mailto:<?php echo htmlspecialchars($student_email); ?>?subject=Please%20Add%20Your%20IBAN%20to%20ExternIT&body=Dear%20<?php echo urlencode($payment['student_name']); ?>,%0D%0A%0D%0APlease%20add%20your%20IBAN%20to%20your%20ExternIT%20profile%20so%20we%20can%20process%20your%20payment.%0D%0A%0D%0AThank%20you!" class="btn btn-outline-primary btn-sm ms-2" data-bs-toggle="tooltip" title="Contact student by email">
                                                    <i class="bi bi-envelope"></i> Contact
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge-outline-company">
                                            <i class="bi bi-building"></i>
                                            <?php echo htmlspecialchars($payment['company_name']); ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold"><?php echo number_format($payment['amount'], 2); ?></td>
                                    <td>
                                        <span class="badge-status <?php echo $payment['status']; ?>">
                                            <?php if ($payment['status'] === 'pending'): ?>
                                                <i class="bi bi-hourglass-split"></i>
                                            <?php elseif ($payment['status'] === 'completed'): ?>
                                                <i class="bi bi-check-circle"></i>
                                            <?php else: ?>
                                                <i class="bi bi-x-circle"></i>
                                            <?php endif; ?>
                                            <?php echo ucfirst($payment['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($payment['created_at'])); ?></td>
                                    <td>
                                        <?php if ($payment['status'] === 'pending'): ?>
                                            <div class="d-flex gap-2">
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                                    <input type="hidden" name="status" value="completed">
                                                    <button type="submit" class="btn btn-outline-success btn-sm"
                                                            onclick="return confirm('Are you sure you want to mark this payment as completed?')"
                                                            data-bs-toggle="tooltip" title="Mark as Completed">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                                    <input type="hidden" name="status" value="failed">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm"
                                                            onclick="return confirm('Are you sure you want to mark this payment as failed?')"
                                                            data-bs-toggle="tooltip" title="Mark as Failed">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
</body>
</html> 