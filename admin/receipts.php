<?php
require_once '../config.php';
require_once '../includes/auth_check.php';

$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;
$receipt_id = isset($_GET['print']) ? $_GET['print'] : null;

// --- PART 1: PRINT VIEW (3 COPIES) ---
if ($receipt_id) {
    $stmt = $pdo->prepare("
        SELECT f.*, s.name as student_name, c.class_name 
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
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Print Receipt - <?php echo $r['receipt_number']; ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
        <style>
            body { font-family: 'Inter', sans-serif; color: #0a0a0a; background: #f4f4f4; }
            .no-print-nav { background: #fff; padding: 15px; border-bottom: 1px solid #ddd; text-align: center; }
            
            .print-area { background: #fff; width: 210mm; margin: 20px auto; padding: 10mm; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            
            .receipt-copy { 
                border: 2px dashed #00d4ff; 
                padding: 20px; 
                margin-bottom: 40px; 
                position: relative; 
                page-break-inside: avoid;
            }

            .copy-tag { 
                position: absolute; top: 0; right: 20px; 
                background: #0a0a0a; color: #fff; 
                font-size: 10px; padding: 2px 12px; 
                border-radius: 0 0 5px 5px; text-transform: uppercase; 
            }

            .logo-img { width: 50px; height: 50px; border-radius: 50%; border: 1px solid #00d4ff; }
            .header-title { font-weight: 800; font-size: 1.4rem; letter-spacing: -0.5px; }
            .data-label { font-size: 10px; color: #666; text-transform: uppercase; font-weight: 600; }
            .data-value { font-weight: 600; font-size: 14px; display: block; }
            
            .amount-box { 
                background: #f8f9fa; border: 1px solid #00d4ff; 
                padding: 10px 20px; border-radius: 8px; 
                font-weight: 800; font-size: 1.2rem; 
            }

            .sig-line { border-top: 1px solid #0a0a0a; margin-top: 30px; width: 180px; text-align: center; font-size: 12px; padding-top: 5px; }

            @media print {
                .no-print-nav { display: none; }
                .print-area { margin: 0; box-shadow: none; width: 100%; }
                body { background: #fff; }
                .receipt-copy { border-color: #eee; }
            }
        </style>
    </head>
    <body onload="window.print()">
        <div class="no-print-nav">
            <button onclick="window.print()" class="btn btn-dark">Print Now</button>
            <a href="receipts.php?student_id=<?php echo $r['student_id']; ?>" class="btn btn-outline-secondary">Back to History</a>
        </div>

        <div class="print-area">
            <?php foreach ($copies as $title): ?>
            <div class="receipt-copy">
                <div class="copy-tag"><?php echo $title; ?></div>
                
                <div class="row align-items-center mb-4">
                    <div class="col-1">
                        <img src="../uploads/Logo Web.png" class="logo-img">
                    </div>
                    <div class="col-8 ps-4">
                        <div class="header-title">AI FUTURE LEADERS ACADEMY</div>
                        <div class="text-muted small">Quality Education for Next Gen Innovators</div>
                    </div>
                    <div class="col-3 text-end">
                        <span class="data-label">Receipt Number</span>
                        <span class="data-value">#<?php echo $r['receipt_number']; ?></span>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12 border-bottom pb-2 mb-2">
                        <span class="data-label">Student Name</span>
                        <span class="data-value text-uppercase" style="font-size: 18px;"><?php echo htmlspecialchars($r['student_name']); ?></span>
                    </div>
                    <div class="col-4">
                        <span class="data-label">Academic Track</span>
                        <span class="data-value"><?php echo htmlspecialchars($r['class_name']); ?></span>
                    </div>
                    <div class="col-4">
                        <span class="data-label">Fee Type</span>
                        <span class="data-value"><?php echo $r['fee_type']; ?></span>
                    </div>
                    <div class="col-4">
                        <span class="data-label">Payment Date</span>
                        <span class="data-value"><?php echo date('d-M-Y', strtotime($r['payment_date'])); ?></span>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-between align-items-end">
                    <div>
                        <span class="data-label">Amount Paid</span>
                        <div class="amount-box"><?php echo number_format($r['amount']); ?> PKR</div>
                    </div>
                    <div class="sig-line">
                        Authorized Signature
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// --- PART 2: DASHBOARD VIEW (HISTORY TABLE) ---
include '../includes/header.php';
?>

<style>
    body { background-color: #060b28; color: #fff; font-family: 'Inter', sans-serif; }
    .history-card { 
        background: rgba(255, 255, 255, 0.95); 
        border-radius: 20px; 
        color: #0a0a0a; 
        overflow: hidden; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    .table thead { background: #0a0a0a; color: #fff; }
    .btn-print { background: #00d4ff; color: #0a0a0a; font-weight: 700; border: none; }
    .btn-print:hover { background: #00b8e6; }
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-800" style="font-weight: 800; letter-spacing: -1px;">Payment History</h2>
        <a href="fees.php" class="btn btn-outline-light rounded-pill px-4">← Back to Fees</a>
    </div>

    <div class="history-card shadow">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Receipt No</th>
                        <th>Fee Type</th>
                        <th>Amount (PKR)</th>
                        <th>Date</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM fees WHERE student_id = ? ORDER BY id DESC");
                    $stmt->execute([$student_id]);
                    $receipts = $stmt->fetchAll();

                    foreach ($receipts as $r):
                    ?>
                    <tr>
                        <td class="ps-4 fw-bold">#<?php echo $r['receipt_number']; ?></td>
                        <td><span class="badge bg-light text-dark border"><?php echo $r['fee_type']; ?></span></td>
                        <td class="fw-bold"><?php echo number_format($r['amount']); ?></td>
                        <td><?php echo date('d M, Y', strtotime($r['payment_date'])); ?></td>
                        <td class="text-center">
                            <a href="receipts.php?print=<?php echo $r['id']; ?>" target="_blank" class="btn btn-sm btn-print px-3 rounded-pill">
                                ⎙ Print 3 Copies
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($receipts)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No payment records found for this student.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
