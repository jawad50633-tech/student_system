<?php
require_once '../config.php';
require_once '../includes/auth_check.php';

// --- ACTION: DELETE FEE RECORD ---
if (isset($_GET['delete'])) {
    $fee_id = $_GET['delete'];
    $sid = $_GET['student_id'];
    $stmt = $pdo->prepare("DELETE FROM fees WHERE id = ?");
    if ($stmt->execute([$fee_id])) {
        header("Location: receipts.php?student_id=$sid&msg=Deleted");
        exit;
    }
}

$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;
$receipt_id = isset($_GET['print']) ? $_GET['print'] : null;

// --- MODE 1: PRINTING A SPECIFIC RECEIPT ---
if ($receipt_id) {
    $stmt = $pdo->prepare("
        SELECT f.*, s.id as sid, s.name as student_name, s.father_name as f_name, c.class_name 
        FROM fees f 
        LEFT JOIN students s ON f.student_id = s.id 
        LEFT JOIN classes c ON s.class_id = c.id 
        WHERE f.id = ?
    ");
    $stmt->execute([$receipt_id]);
    $r = $stmt->fetch();

    if (!$r) die("Receipt ID #$receipt_id not found.");

    $copies = ['Office Copy', 'Student Copy', 'Teacher Copy'];
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Receipt_<?php echo $r['receipt_number']; ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap');
            body { font-family: 'Montserrat', sans-serif; background: #fff; color: #000; }
            @page { size: A4 landscape; margin: 0; }
            .no-print-nav { background: #111; padding: 15px; text-align: center; color: #fff; }
            .receipt-wrapper { display: flex; padding: 10px; gap: 5px; }
            .receipt-box { 
                flex: 1; border: 2px solid #000; padding: 15px; position: relative;
                min-height: 180mm; background: #fff;
            }
            .header-strip { background: #000; color: #fff; padding: 10px; text-align: center; margin-bottom: 15px; }
            .school-name { font-weight: 900; font-size: 14px; margin-bottom: 0; letter-spacing: 1px; }
            .receipt-label { font-size: 10px; font-weight: bold; border: 1px solid #fff; display: inline-block; padding: 2px 8px; margin-top: 5px; }
            
            .info-grid { border: 1px solid #000; border-radius: 4px; margin-bottom: 15px; }
            .info-row { display: flex; border-bottom: 1px solid #eee; }
            .info-row:last-child { border-bottom: none; }
            .info-key { flex: 1; background: #f4f4f4; padding: 5px 10px; font-size: 10px; font-weight: bold; text-transform: uppercase; border-right: 1px solid #eee; }
            .info-val { flex: 2; padding: 5px 10px; font-size: 12px; font-weight: 700; }

            .amount-section { border: 3px double #000; padding: 15px; text-align: center; margin-top: 20px; border-radius: 10px; }
            .amount-val { font-size: 24px; font-weight: 900; }
            
            .footer-sign { margin-top: auto; display: flex; justify-content: space-between; align-items: flex-end; padding-bottom: 20px; }
            .stamp-box { width: 80px; height: 80px; border: 1px dashed #ccc; font-size: 9px; text-align: center; line-height: 80px; color: #ccc; }
            .line { border-top: 2px solid #000; width: 120px; text-align: center; font-size: 10px; font-weight: 800; padding-top: 5px; }

            @media print { .no-print-nav { display: none; } .receipt-box { border-right: 1px dashed #000; } }
        </style>
    </head>
    <body onload="window.print()">
        <div class="no-print-nav">
            <button onclick="window.print()" class="btn btn-info btn-sm fw-bold">PRINT VOUCHER</button>
            <a href="receipts.php?student_id=<?php echo $r['student_id']; ?>" class="btn btn-outline-light btn-sm ms-3">RETURN TO LEDGER</a>
        </div>

        <div class="receipt-wrapper">
            <?php foreach ($copies as $copy): ?>
            <div class="receipt-box">
                <div class="header-strip">
                    <h2 class="school-name">AI FUTURE LEADERS ACADEMY</h2>
                    <div class="receipt-label"><?php echo strtoupper($copy); ?></div>
                </div>

                <div class="info-grid">
                    <div class="info-row"><div class="info-key">Receipt No:</div><div class="info-val">#<?php echo $r['receipt_number']; ?></div></div>
                    <div class="info-row"><div class="info-key">Reg No:</div><div class="info-val">STU-<?php echo $r['sid']; ?></div></div>
                    <div class="info-row"><div class="info-key">Student:</div><div class="info-val"><?php echo strtoupper($r['student_name']); ?></div></div>
                    <div class="info-row"><div class="info-key">Father:</div><div class="info-val"><?php echo strtoupper($r['f_name'] ?? '---'); ?></div></div>
                    <div class="info-row"><div class="info-key">Class:</div><div class="info-val"><?php echo $r['class_name']; ?></div></div>
                    <div class="info-row"><div class="info-key">Paid Date:</div><div class="info-val"><?php echo date('d-m-Y', strtotime($r['payment_date'])); ?></div></div>
                </div>

                <div class="amount-section">
                    <div style="font-size: 10px; font-weight: bold;">TOTAL FEE PAID</div>
                    <div class="amount-val"><?php echo number_format($r['amount']); ?> PKR</div>
                    <div style="font-size: 9px; font-style: italic;">(<?php echo ($r['amount'] == 800) ? "Admission Charges" : "Monthly Tuition"; ?>)</div>
                </div>

                <div class="footer-sign">
                    <div class="stamp-box">ACADEMY STAMP</div>
                    <div class="line">Authorized Signature</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// --- MODE 2: LEDGER VIEW ---
if ($student_id) {
    include '../includes/header.php';
    ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; }
        .ledger-card { background: #080808; border: 1px solid #222; border-radius: 20px; overflow: hidden; }
        .table { margin-bottom: 0; color: #fff; }
        .table thead th { background: #111; color: #00d4ff; border: none; padding: 20px; font-size: 12px; text-transform: uppercase; }
        .table tbody td { border-bottom: 1px solid #1a1a1a; padding: 18px; vertical-align: middle; }
        .btn-action { width: 35px; height: 35px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; transition: 0.3s; border: none; }
        .btn-print { background: rgba(0, 212, 255, 0.1); color: #00d4ff; }
        .btn-print:hover { background: #00d4ff; color: #000; }
        .btn-delete { background: rgba(255, 62, 62, 0.1); color: #ff3e3e; margin-left: 8px; }
        .btn-delete:hover { background: #ff3e3e; color: #fff; }
    </style>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">Student Ledger</h2>
                <p class="text-secondary small">Viewing all transaction history</p>
            </div>
            <a href="fees.php" class="btn btn-outline-secondary btn-sm rounded-pill px-4">Close Ledger</a>
        </div>

        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-danger border-0 bg-dark text-danger py-2 px-3 small mb-4">Record deleted successfully.</div>
        <?php endif; ?>

        <div class="ledger-card shadow-lg">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Receipt Number</th>
                            <th>Amount</th>
                            <th>Payment Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM fees WHERE student_id = ? ORDER BY id DESC");
                        $stmt->execute([$student_id]);
                        while ($row = $stmt->fetch()):
                        ?>
                        <tr>
                            <td class="fw-bold text-info">#<?php echo $row['receipt_number']; ?></td>
                            <td><span class="badge bg-dark border border-secondary p-2 px-3"><?php echo number_format($row['amount']); ?> PKR</span></td>
                            <td class="text-secondary"><?php echo date('d M, Y', strtotime($row['payment_date'])); ?></td>
                            <td class="text-end">
                                <a href="receipts.php?print=<?php echo $row['id']; ?>" target="_blank" class="btn-action btn-print" title="Print Receipt">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                                <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $row['id']; ?>, <?php echo $student_id; ?>)" class="btn-action btn-delete" title="Delete Entry">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function confirmDelete(feeId, studentId) {
        if (confirm("⚠️ WARNING: Are you sure you want to delete this payment record? This action cannot be undone.")) {
            window.location.href = "receipts.php?delete=" + feeId + "&student_id=" + studentId;
        }
    }
    </script>
    <?php
    include '../includes/footer.php';
} else {
    header("Location: fees.php");
}
?>
