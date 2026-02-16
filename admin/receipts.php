<?php
echo "Receipts page loaded";

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config.php';
require_once '../includes/auth_check.php';

if (!isset($_GET['student_id']) || empty($_GET['student_id'])) {
    die("Invalid access.");
}

$student_id = (int) $_GET['student_id'];
$receipt_id = isset($_GET['print']) ? (int) $_GET['print'] : null;


// --- PART 1: PRINT VIEW (HORIZONTAL COPIES) ---
if ($receipt_id) {
    // Updated query to include father_name and the student's primary ID
    $stmt = $pdo->prepare("
    SELECT f.*, s.name as student_name, c.class_name 
    FROM fees f 
    JOIN students s ON f.student_id = s.id 
    LEFT JOIN classes c ON s.class_id = c.id 
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

            .receipt-wrapper { display: flex; flex-direction: row; justify-content: space-between; gap: 15px; padding: 10px; width: 100%; }

            .receipt-box {
                flex: 1; border: 1.5px dashed #000; padding: 15px; background: #fff;
                position: relative; min-height: 185mm; display: flex; flex-direction: column;
            }

            .copy-tag {
                background: #000; color: #fff; font-size: 9px; padding: 2px 8px;
                font-weight: bold; position: absolute; top: 0; right: 10px; border-radius: 0 0 5px 5px;
            }

            .header-section { text-align: center; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
            .logo-img { width: 45px; height: 45px; border-radius: 50%; margin-bottom: 5px; }
            .academy-name { font-size: 1rem; font-weight: 800; text-transform: uppercase; margin: 0; line-height: 1.2; }
            
            .label { font-size: 9px; font-weight: 700; color: #555; text-transform: uppercase; display: block; }
            .value { font-size: 12px; font-weight: 600; color: #000; display: block; border-bottom: 1px solid #f0f0f0; margin-bottom: 12px; }

            .amount-section { background: #f9f9f9; border: 1px solid #000; padding: 10px; margin-top: 15px; text-align: center; border-radius: 5px; }
            .amount-text { font-size: 1.3rem; font-weight: 800; }

            .footer-signature { margin-top: auto; padding-top: 60px; }
            .signature-line { border-top: 1.5px solid #000; font-size: 11px; font-weight: 700; text-align: center; padding-top: 5px; }

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
                <span class="label">Student Name</span><span class="value text-uppercase"><?php echo htmlspecialchars($r['student_name']); ?></span>
                <span class="label">Course Track</span><span class="value"><?php echo htmlspecialchars($r['class_name']); ?></span>
                <span class="label">Payment For</span><span class="value"><?php echo $r['fee_type']; ?></span>
                <div class="amount-section"><span class="label">Total Paid</span><div class="amount-text"><?php echo number_format($r['amount']); ?> PKR</div></div>
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

// --- PART 2: DASHBOARD HISTORY VIEW ---
include '../includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap" rel="stylesheet">
<style>
    body { 
        background: linear-gradient(rgba(6, 11, 40, 0.9), rgba(6, 11, 40, 0.9)), url('../uploads/background.png');
        background-size: cover;
        font-family: 'Inter', sans-serif;
    }

    /* The Glass Header for the Black Title */
    .stylish-header-bar {
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 20px 35px;
        margin-bottom: 30px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    /* High-end Stylish Black Font */
    .black-title {
        color: #000000;
        font-weight: 800;
        font-size: 2.2rem;
        letter-spacing: -1.8px;
        margin: 0;
        text-transform: none;
    }

    .history-card { 
        background: rgba(255, 255, 255, 0.98); 
        border-radius: 28px; 
        color: #000; 
        overflow: hidden; 
        box-shadow: 0 15px 40px rgba(0,0,0,0.4);
    }

    .table thead th {
        background: #000;
        color: #fff;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
        padding: 18px;
        border: none;
    }

    .btn-generate {
        background: #00d4ff;
        color: #000;
        font-weight: 700;
        border-radius: 50px;
        padding: 8px 24px;
        border: none;
        transition: 0.3s;
    }

    .btn-generate:hover {
        background: #000;
        color: #fff;
        transform: scale(1.05);
    }
</style>

<div class="container py-5">
    <div class="stylish-header-bar">
        <h2 class="black-title">Payment Records</h2>
        <a href="fees.php" class="btn btn-dark rounded-pill px-4 fw-bold">← Back to Fees</a>
    </div>

    <div class="history-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Receipt ID</th>
                        <th>Fee Category</th>
                        <th>Amount (PKR)</th>
                        <th>Date of Payment</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody style="color: #000;">
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM fees WHERE student_id = ? ORDER BY id DESC");
                    $stmt->execute([$student_id]);
                    $receipts = $stmt->fetchAll();

                    foreach ($receipts as $r):
                    ?>
                    <tr>
                        <td class="ps-4 fw-800" style="font-weight: 800;">#<?php echo $r['receipt_number']; ?></td>
                        <td><span class="badge bg-light text-dark border px-3 py-2"><?php echo $r['fee_type']; ?></span></td>
                        <td class="fw-bold"><?php echo number_format($r['amount']); ?></td>
                        <td><?php echo date('d M, Y', strtotime($r['payment_date'])); ?></td>
                        <td class="text-center">
                            <a href="receipts.php?print=<?php echo $r['id']; ?>" target="_blank" class="btn btn-sm btn-generate">
                                Generate Slip
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($receipts)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No transactions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
