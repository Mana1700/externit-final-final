<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/tcpdf/tcpdf.php';  // Include TCPDF directly

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /newtest/login.php');
    exit();
}

// Check if certificate ID is provided
if (!isset($_GET['id'])) {
    die('Certificate ID not provided');
}

$certificate_id = intval($_GET['id']);

// Fetch certificate details
$query = "SELECT c.*, t.title as task_name, t.description, comp.name as company_name, 
          s.name as student_name, s.university, s.major, sub.is_best,
          ct.template_html as special_template
          FROM certificates c
          JOIN tasks t ON c.task_id = t.id
          JOIN companies comp ON t.company_id = comp.id
          JOIN students s ON c.student_id = s.id
          JOIN submissions sub ON c.submission_id = sub.id
          LEFT JOIN certificate_templates ct ON t.id = ct.task_id AND ct.is_best_submission = true
          WHERE c.id = ?";

$stmt = $pdo->prepare($query);
$stmt->execute([$certificate_id]);
$certificate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$certificate) {
    die('Certificate not found');
}

// Verify user has permission to view this certificate
if ($_SESSION['user_type'] === 'student' && $_SESSION['user_id'] != $certificate['student_id']) {
    die('Access denied');
}

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('ExternIT');
$pdf->SetAuthor('ExternIT');
$pdf->SetTitle('Certificate of ' . ($certificate['is_best'] ? 'Excellence' : 'Completion') . ' - ' . $certificate['task_name']);

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(15, 15, 15);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Certificate content
if ($certificate['is_best'] && $certificate['special_template']) {
    // Use special template for best submission
    $html = '
    <style>
        .certificate-special {
            background: linear-gradient(135deg, #FFF8F3 0%, #FFF 100%);
            border: 3px solid #BF6D3A;
            padding: 40px;
            position: relative;
            margin: 20px;
        }
        .certificate-special::before {
            content: "BEST SUBMISSION";
            position: absolute;
            top: 20px;
            right: -15px;
            background: linear-gradient(45deg, #BF6D3A, #D98B5B);
            color: white;
            padding: 8px 25px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 2px;
            box-shadow: 0 4px 8px rgba(191, 109, 58, 0.3);
            transform: rotate(5deg);
        }
        .certificate-special::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border: 1px solid #BF6D3A;
            margin: 10px;
            pointer-events: none;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            width: 150px;
            height: auto;
        }
        h1 { 
            color: #BF6D3A; 
            font-size: 36pt; 
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 4px;
            margin: 20px 0;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(191, 109, 58, 0.1);
            font-family: "Times New Roman", serif;
        }
        .certificate-body { 
            text-align: center; 
            line-height: 2;
            font-size: 14pt;
            margin: 40px 0;
        }
        .student-name {
            font-size: 28pt;
            color: #333;
            font-family: "Times New Roman", serif;
            margin: 20px 0;
            font-weight: bold;
        }
        .task-name {
            font-size: 18pt;
            color: #BF6D3A;
            margin: 15px 0;
            font-style: italic;
        }
        .company-name {
            font-size: 20pt;
            color: #333;
            margin: 15px 0;
            font-weight: bold;
        }
        .award-seal {
            width: 150px;
            height: 150px;
            margin: 30px auto;
            background: url(\'seals/gold_seal.png\') no-repeat center center;
            background-size: contain;
            position: relative;
        }
        .award-seal::before {
            content: "";
            position: absolute;
            top: 50%;
            left: -100%;
            right: -100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #BF6D3A, transparent);
        }
        .signature-area {
            margin-top: 50px;
            display: flex;
            justify-content: space-around;
            align-items: flex-end;
            padding: 0 50px;
        }
        .signature {
            text-align: center;
        }
        .signature-line {
            width: 200px;
            border-top: 2px solid #666;
            margin-top: 10px;
            margin-bottom: 5px;
        }
        .signature-title {
            font-size: 12pt;
            color: #666;
            font-style: italic;
        }
        .certificate-footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12pt;
            color: #666;
        }
        .certificate-id {
            font-family: monospace;
            letter-spacing: 2px;
            color: #BF6D3A;
        }
        .date {
            font-style: italic;
            margin: 20px 0;
            font-size: 16pt;
            color: #666;
        }
        .decorative-corner {
            position: absolute;
            width: 50px;
            height: 50px;
            border: 3px solid #BF6D3A;
        }
        .top-left {
            top: 10px;
            left: 10px;
            border-right: none;
            border-bottom: none;
        }
        .top-right {
            top: 10px;
            right: 10px;
            border-left: none;
            border-bottom: none;
        }
        .bottom-left {
            bottom: 10px;
            left: 10px;
            border-right: none;
            border-top: none;
        }
        .bottom-right {
            bottom: 10px;
            right: 10px;
            border-left: none;
            border-top: none;
        }
    </style>
    <div class="certificate-special">
        <div class="decorative-corner top-left"></div>
        <div class="decorative-corner top-right"></div>
        <div class="decorative-corner bottom-left"></div>
        <div class="decorative-corner bottom-right"></div>
        
        <div class="logo">
            <img src="images/logo.png" alt="ExternIT Logo">
        </div>
        
        <div class="certificate-header">
            <h1>Certificate of Excellence</h1>
        </div>
        
        <div class="certificate-body">
            <p>This is to certify that</p>
            <div class="student-name">' . htmlspecialchars($certificate['student_name']) . '</div>
            <p>from</p>
            <div class="university">' . htmlspecialchars($certificate['university']) . '</div>
            <p>has demonstrated exceptional skill and dedication in completing</p>
            <div class="task-name">' . htmlspecialchars($certificate['task_name']) . '</div>
            <p>This submission was selected as the <strong>Best Submission</strong> by</p>
            <div class="company-name">' . htmlspecialchars($certificate['company_name']) . '</div>
            
            <div class="date">Awarded on ' . date('F j, Y', strtotime($certificate['issue_date'])) . '</div>
            
            <div class="award-seal"></div>
        </div>
        
        <div class="signature-area">
            <div class="signature">
                <div class="signature-line"></div>
                <div class="signature-title">Company Representative</div>
                <div>' . htmlspecialchars($certificate['company_name']) . '</div>
            </div>
            <div class="signature">
                <div class="signature-line"></div>
                <div class="signature-title">ExternIT Director</div>
                <div>ExternIT Platform</div>
            </div>
        </div>
        
        <div class="certificate-footer">
            <p>Certificate ID: <span class="certificate-id">' . str_pad($certificate['id'], 6, '0', STR_PAD_LEFT) . '</span></p>
            <p>Verified by ExternIT | Best Submission Award</p>
        </div>
    </div>';
} else {
    // Use standard template
    $html = '
    <style>
        h1 { color: #056954; font-size: 24pt; text-align: center; }
        .certificate-header { text-align: center; margin-bottom: 20px; }
        .certificate-body { text-align: center; line-height: 1.6; }
        .signature { margin-top: 50px; text-align: center; }
    </style>
    <div class="certificate-header">
        <h1>Certificate of Completion</h1>
    </div>
    <div class="certificate-body">
        <p>This is to certify that</p>
        <h2>' . htmlspecialchars($certificate['student_name']) . '</h2>
        <p>from ' . htmlspecialchars($certificate['university']) . '</p>
        <p>has successfully completed the task</p>
        <h3>' . htmlspecialchars($certificate['task_name']) . '</h3>
        <p>assigned by</p>
        <h3>' . htmlspecialchars($certificate['company_name']) . '</h3>
        <p>on ' . date('F j, Y', strtotime($certificate['issue_date'])) . '</p>
    </div>
    <div class="signature">
        <p>Certificate ID: ' . str_pad($certificate['id'], 6, '0', STR_PAD_LEFT) . '</p>
        <p>Verified by ExternIT</p>
    </div>';
}

// Output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Output PDF
$pdf->Output('Certificate_' . str_pad($certificate['id'], 6, '0', STR_PAD_LEFT) . '.pdf', 'D'); 