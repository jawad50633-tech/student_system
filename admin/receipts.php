<?php
require_once '../config.php';
require_once '../includes/auth_check.php';

$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;
$receipt_id = isset($_GET['print']) ? $_GET['print'] : null;

// --- PART 1: PRINT VIEW (HORIZONTAL COPIES) ---
if ($receipt_id) {
    // Updated query to include father_name and the student's primary ID
    $stmt = $pdo->prepare("
        SELECT f.*, s.id as sid, s.name as student_name, s.father_name, c.class_name 
        FROM fees f 
        JOIN students s ON f.student_id = s.id 
        JOIN classes c ON s.class_id = c.id 
        WHERE f.id = ?
    ");
    $stmt->execute([$receipt_id]);
    $r = $stmt->fetch();

    if (!$r) die("Receipt not found");

    $copies = ['Office Copy', 'Student Copy', 'Teacher Copy'];
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Receipt_<?php echo $r['receipt_number']; ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
        <style>
            body { font-family: 'Inter', sans-serif; color: #000; background: #fff; margin: 0; padding: 0; }
            @page { size: A4 landscape; margin: 5mm; }
            .no-print-nav { background: #f8f9fa; padding: 10px; text-align: center; border-bottom: 1px solid #ddd; }
            .receipt-wrapper { display: flex; flex-direction: row; justify-content: space-between; gap: 12px; padding: 10px; width: 100%; }
            .receipt-box {
                flex: 1; border: 1.5px dashed #000; padding: 15px; background: #fff;
                position: relative; min-height: 185mm; display: flex; flex-direction: column;
            }
            .copy-tag {
                background: #000; color: #fff; font-size: 9px; padding: 2px 8px;
                font-weight: bold; position: absolute; top: 0; right: 10px; border-radius: 0 0 5px 5px;
            }
            .header-section { text-align: center; margin-bottom: 12px; border-bottom: 1px solid #eee; padding-bottom: 8px; }
            .logo-img { width: 45px; height: 45px; border-radius: 50%; margin-bottom: 5px; }
            .academy-name { font-size: 0.95rem; font-weight: 800; text-transform: uppercase; margin: 0; line-height: 1.1; }
            
            .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px; }
            .label { font-size: 8px; font-weight: 700; color: #555; text-transform: uppercase; display: block; margin-bottom: 1px; }
            .value { font-size: 11px; font-weight: 600; color: #000; display: block; border-bottom: 1px solid #f0f0f0; margin-bottom: 8px; overflow: hidden; white-space: nowrap; }
            
            .amount-section { background: #f9f9f9; border: 1px solid #000; padding: 8px; margin-top: 10px; text-align: center; border-radius: 5px; }
            .amount-text { font-size: 1.2rem; font-weight: 800; }
            .footer-signature { margin-top: auto; padding-top: 40px; }
            .signature-line { border-top: 1.2px solid #000; font-size: 10px; font-weight: 700; text-align: center; padding-top: 5px; }
            @media print { .no-print-nav { display: none; } body { background: #fff; } }
        </style>
    </head>
    <body onload="window.print()">
        <div class="no-print-nav">
            <button onclick="window.print()" class="btn btn-dark btn-sm">Print Now</button>
            <a href="receipts.php?student_id=<?php echo $r['student_id']; ?>" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
        <div class="receipt-wrapper">
            <?php foreach ($copies as $copy_name): ?>
            <div class="receipt-box">
                <div class="copy-tag"><?php echo $copy_name; ?></div>
                <div class="header-section">
                    <img src="../uploads/Logo Web.jpg" class="logo-img">
                    <h1 class="academy-name">AI Future Leaders Academy</h1>
                </div>

                <div class="row gx-2">
                    <div class="col-6"><span class="label">Receipt No</span><span class="value">#<?php echo $r['receipt_number']; ?></span></div>
                    <div class="col-6"><span class="label">Student ID</span><span class="value">STU-<?php echo $r['sid']; ?></span></div>
                </div>

                <span class="label">Student Name</span>
                <span class="value text-uppercase"><?php echo htmlspecialchars($r['student_name']); ?></span>

                <span class="label">Father's Name</span>
                <span class="value text-uppercase"><?php echo htmlspecialchars($r['father_name']); ?></span>

                <div class="row gx-2">
                    <div class="col-7">
                        <span class="label">Class / Course</span>
                        <span class="value"><?php echo htmlspecialchars($r['class_name']); ?></span>
                    </div>
                    <div class="col-5">
                        <span class="label">Date</span>
                        <span class="value"><?php echo date('d-m-Y', strtotime($r['payment_date'])); ?></span>
                    </div>
                </div>

                <span class="label">Fee Categorization</span>
                <span class="value">
                    <?php echo ($r['amount'] == 800) ? "Admission (One-Time)" : "Monthly Tuition Fee"; ?>
                </span>

                <div class="amount-section">
                    <span class="label">Net Amount Received</span>
                    <div class="amount-text"><?php echo number_format($r['amount']); ?> PKR</div>
                </div>

                <div class="footer-signature">
                    <div class="signature-line">Authorized Stamp & Signature</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

include '../includes/header.php';
?>
