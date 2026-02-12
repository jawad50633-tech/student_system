<?php
require_once '../config.php';
require_once '../includes/auth_check.php';

$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;
$receipt_id = isset($_GET['print']) ? $_GET['print'] : null;

// --- PART 1: PRINT VIEW (3 HORIZONTAL COPIES) ---
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
        <title>Receipt_<?php echo $r['receipt_number']; ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
        <style>
            /* Reset and typography */
            body { font-family: 'Inter', sans-serif; color: #000; background: #fff; margin: 0; padding: 0; }
            
            /* Print-specific landscape orientation */
            @page { size: A4 landscape; margin: 5mm; }
            
            .no-print-nav { 
                background: #f8f9fa; padding: 10px; 
                text-align: center; border-bottom: 1px solid #ddd; 
            }

            /* Main Horizontal Wrapper */
            .receipt-wrapper {
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                gap: 10px;
                padding: 10px;
                width: 100%;
            }

            /* Individual Receipt (1/3 of the width) */
            .receipt-box {
                flex: 1;
                border: 1.5px dashed #000;
                padding: 15px;
                background: #fff;
                position: relative;
                min-height: 180mm; /* Ensures they all have the same height */
            }

            .copy-tag {
                background: #000; color: #fff;
                font-size: 9px; padding: 2px 8px;
                font-weight: bold; position: absolute;
                top: 0; right: 10px; border-radius: 0 0 5px 5px;
            }

            .header-section { text-align: center; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
            .logo-img { width: 45px; height: 45px; border-radius: 50%; margin-bottom: 5px; }
            .academy-name { font-size: 1rem; font-weight: 800; text-transform: uppercase; margin: 0; line-height: 1.2; }
            
            .data-row { margin-bottom: 12px; }
            .label { font-size: 9px; font-weight: 700; color: #555; text-transform: uppercase; display: block; }
            .value { font-size: 12px; font-weight: 600; color: #000; display: block; border-bottom: 1px solid #f0f0f0; }

            .amount-section {
                background: #f9f9f9;
                border: 1px solid #000;
                padding: 8px;
                margin-top: 15px;
                text-align: center;
                border-radius: 5px;
            }
            .amount-text { font-size: 1.2rem; font-weight: 800; }

            .signature-area {
                margin-top: 40px;
                border-top: 1px solid #000;
                font-size: 10px;
                text-align: center;
                padding-top: 5px;
            }

            @media print {
                .no-print-nav { display: none; }
                body { background: #fff; }
                .receipt-wrapper { padding: 0; gap: 5px; }
                .receipt-box { border-color: #ccc; }
            }
        </style>
    </head>
    <body onload="window.print()">
        <div class="no-print-nav">
            <button onclick="window.print()" class="btn btn-dark btn-sm">Print Now (Landscape)</button>
            <a href="receipts.php?student_id=<?php echo $r['student_id']; ?>" class="btn btn-outline-secondary btn-sm">Back</a>
            <p class="small text-muted mt-2 mb-0">Note: Please ensure printer settings are set to <b>Landscape</b> orientation.</p>
        </div>

        <div class="receipt-wrapper">
            <?php foreach ($copies as $copy_name): ?>
            <div class="receipt-box">
                <div class="copy-tag"><?php echo $copy_name; ?></div>
                
                <div class="header-section">
                    <img src="../uploads/Logo Web.png" class="logo-img">
                    <h1 class="academy-name">AI Future Leaders Academy</h1>
                    <small style="font-size: 9px;">Official Fee Receipt</small>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <span class="label">Receipt No</span>
                        <span class="value">#<?php echo $r['receipt_number']; ?></span>
                    </div>
                    <div class="col-6">
                        <span class="label">Date</span>
                        <span class="value"><?php echo date('d-m-Y', strtotime($r['payment_date'])); ?></span>
                    </div>
                </div>

                <div class="data-row">
                    <span class="label">Student Name</span>
                    <span class="value text-uppercase"><?php echo htmlspecialchars($r['student_name']); ?></span>
                </div>

                <div class="data-row">
                    <span class="label">Academic Track</span>
                    <span class="value"><?php echo htmlspecialchars($r['class_name']); ?></span>
                </div>

                <div class="data-row">
                    <span class="label">Payment For</span>
                    <span class="value"><?php echo $r['fee_type']; ?></span>
                </div>

                <div class="amount-section">
                    <span class="label" style="color:#000;">Total Paid (PKR)</span>
                    <div class="amount-text"><?php echo number_format($r['amount']); ?>/-</div>
                </div>

                <div class="signature-area">
                    Authorized Signatory
                </div>
                
                <div class="mt-4 text-center">
                    <small style="font-size: 8px; color: #888;">This is a computer-generated receipt.</small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// --- PART 2: DASHBOARD HISTORY TABLE ---
include '../includes/header.php';
?>

<style>
    body { background-color: #060b28; color: #fff; font-family: 'Inter', sans-serif; }
    .history-card { 
        background: rgba(255, 255, 255, 0.98); 
        border-radius: 24px; 
        color: #000; 
        overflow: hidden; 
    }
    .table-head-dark { background: #000; color: #fff; }
    .btn-action-print { background: #00d4ff; color: #000; font-weight: 700; border: none; }
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="font-weight: 800;">Student Ledgers</h2>
        <a href="fees.php" class="btn btn-outline-light rounded-pill px-4">← Back</a>
    </div>

    <div class="history-card shadow-lg">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-head-dark">
                    <tr>
                        <th class="ps-4">Receipt #</th>
                        <th>Type</th>
                        <th>Amount</th>
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
                        <td class="fw-bold"><?php echo number_format($r['amount']); ?> PKR</td>
                        <td><?php echo date('d M, Y', strtotime($r['payment_date'])); ?></td>
                        <td class="text-center">
                            <a href="receipts.php?print=<?php echo $r['id']; ?>" target="_blank" class="btn btn-sm btn-action-print px-3 rounded-pill">
                                Generate 3-Copy Slip
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($receipts)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
