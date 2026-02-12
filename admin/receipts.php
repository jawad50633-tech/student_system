<?php
require_once '../config.php';
require_once '../includes/auth_check.php';

$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;
$receipt_id = isset($_GET['print']) ? $_GET['print'] : null;

if ($receipt_id) {
    // Print View
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
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Receipt - <?php echo $r['receipt_number']; ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            .receipt-box { max-width: 600px; margin: 50px auto; border: 2px solid #eee; padding: 30px; }
            @media print { .no-print { display: none; } }
        </style>
    </head>
    <body onload="window.print()">
        <div class="receipt-box shadow-sm">
            <div class="text-center mb-4">
                <h2>Academy Receipt</h2>
                <p class="text-muted">Official Payment Confirmation</p>
            </div>
            <hr>
            <div class="row mb-3">
                <div class="col-6"><strong>Receipt No:</strong> <?php echo $r['receipt_number']; ?></div>
                <div class="col-6 text-end"><strong>Date:</strong> <?php echo $r['payment_date']; ?></div>
            </div>
            <div class="mb-3"><strong>Student Name:</strong> <?php echo htmlspecialchars($r['student_name']); ?></div>
            <div class="mb-3"><strong>Class:</strong> <?php echo htmlspecialchars($r['class_name']); ?></div>
            <div class="mb-3"><strong>Fee Type:</strong> <?php echo $r['fee_type']; ?></div>
            <div class="mb-3"><strong>Amount:</strong> <span class="h4 text-primary"><?php echo $r['amount']; ?> PKR</span></div>
            <hr>
            <div class="text-center text-muted small">Thank you for your payment!</div>
            <div class="mt-4 text-center no-print">
                <button onclick="window.print()" class="btn btn-primary">Print Receipt</button>
                <a href="receipts.php?student_id=<?php echo $r['student_id']; ?>" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Payment History</h3>
        <a href="fees.php" class="btn btn-secondary">Back to Fees</a>
    </div>

    <div class="card shadow">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Receipt No</th>
                        <th>Fee Type</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Action</th>
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
                        <td><?php echo $r['receipt_number']; ?></td>
                        <td><?php echo $r['fee_type']; ?></td>
                        <td><?php echo $r['amount']; ?></td>
                        <td><?php echo $r['payment_date']; ?></td>
                        <td>
                            <a href="receipts.php?print=<?php echo $r['id']; ?>" target="_blank" class="btn btn-sm btn-success">
                                <i class="bi bi-printer"></i> Print/PDF
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($receipts)): ?>
                        <tr><td colspan="5" class="text-center">No payments found for this student.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
