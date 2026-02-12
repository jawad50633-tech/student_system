<?php
require_once '../config.php';
require_once '../includes/auth_check.php';

$message = '';
$current_month = date('m');
$current_year = date('Y');

// Handle Fee Payment
if (isset($_POST['pay_fee'])) {
    $student_id = $_POST['student_id'];
    $fee_type = $_POST['fee_type'];
    
    // Applying your specific rules
    $amount = ($fee_type == 'Admission') ? 800 : 3000;
    
    $receipt_number = 'REC-' . strtoupper(substr(md5(time()), 0, 6)) . rand(10, 99);
    $payment_date = date('Y-m-d');

    $stmt = $pdo->prepare("INSERT INTO fees (student_id, fee_type, amount, status, payment_date, receipt_number) VALUES (?, ?, ?, 'Paid', ?, ?)");
    if ($stmt->execute([$student_id, $fee_type, $amount, $payment_date, $receipt_number])) {
        $message = "Payment of $amount PKR recorded successfully!";
    }
}

include '../includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap" rel="stylesheet">
<style>
    body { 
        background: linear-gradient(rgba(6, 11, 40, 0.9), rgba(6, 11, 40, 0.9)), url('../uploads/background.png');
        background-size: cover; font-family: 'Inter', sans-serif;
    }
    .stylish-header-bar {
        background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);
        border-radius: 20px; padding: 20px 35px; margin-bottom: 30px;
        display: flex; justify-content: space-between; align-items: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    .black-title { color: #000; font-weight: 800; font-size: 2.2rem; letter-spacing: -1.8px; margin: 0; }
    
    .fees-card { background: #fff; border-radius: 28px; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.4); }
    .table thead th { background: #000; color: #fff; padding: 18px; border: none; font-size: 0.75rem; text-transform: uppercase; }
    
    .status-paid { background: #e3fcef; color: #00a854; padding: 4px 12px; border-radius: 50px; font-weight: 700; font-size: 11px; }
    .status-unpaid { background: #fff1f0; color: #f5222d; padding: 4px 12px; border-radius: 50px; font-weight: 700; font-size: 11px; }
    
    .btn-pay { background: #00d4ff; color: #000; font-weight: 700; border-radius: 50px; border: none; transition: 0.3s; }
    .btn-pay:hover { background: #000; color: #fff; }
</style>

<div class="container py-5">
    <?php if ($message): ?>
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="stylish-header-bar">
        <div>
            <h2 class="black-title">Fees Management</h2>
            <p class="text-muted small mb-0">Tracking payments for <b><?php echo date('F Y'); ?></b></p>
        </div>
        <span class="badge bg-dark px-3 py-2 rounded-pill">Today: <?php echo date('d M, Y'); ?></span>
    </div>

    <div class="fees-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Student</th>
                        <th>Class</th>
                        <th>Admission (800)</th>
                        <th>Monthly (3000)</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody style="color: #000;">
                    <?php
                    // Logic updated to check if monthly fee exists for THE CURRENT MONTH
                    $students = $pdo->prepare("
                        SELECT s.id, s.name, c.class_name,
                        (SELECT COUNT(*) FROM fees WHERE student_id = s.id AND fee_type = 'Admission') as has_admission,
                        (SELECT COUNT(*) FROM fees WHERE student_id = s.id AND fee_type = 'Monthly' 
                         AND MONTH(payment_date) = ? AND YEAR(payment_date) = ?) as paid_this_month
                        FROM students s 
                        LEFT JOIN classes c ON s.class_id = c.id
                    ");
                    $students->execute([$current_month, $current_year]);
                    $rows = $students->fetchAll();

                    foreach ($rows as $s):
                    ?>
                    <tr>
                        <td class="ps-4 fw-bold"><?php echo htmlspecialchars($s['name']); ?></td>
                        <td><span class="text-muted small"><?php echo htmlspecialchars($s['class_name']); ?></span></td>
                        
                        <td>
                            <?php if ($s['has_admission'] > 0): ?>
                                <span class="status-paid">✓ PAID</span>
                            <?php else: ?>
                                <span class="status-unpaid">⚠ UNPAID</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if ($s['paid_this_month'] > 0): ?>
                                <span class="status-paid">✓ PAID</span>
                            <?php else: ?>
                                <span class="status-unpaid">⚠ DUE</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-pay dropdown-toggle px-3" data-bs-toggle="dropdown">
                                    Collect Payment
                                </button>
                                <ul class="dropdown-menu shadow border-0">
                                    <li>
                                        <form method="POST">
                                            <input type="hidden" name="student_id" value="<?php echo $s['id']; ?>">
                                            <input type="hidden" name="fee_type" value="Admission">
                                            <button type="submit" name="pay_fee" class="dropdown-item py-2" <?php echo $s['has_admission'] > 0 ? 'disabled' : ''; ?>>
                                                Pay Admission (800)
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form method="POST">
                                            <input type="hidden" name="student_id" value="<?php echo $s['id']; ?>">
                                            <input type="hidden" name="fee_type" value="Monthly">
                                            <button type="submit" name="pay_fee" class="dropdown-item py-2" <?php echo $s['paid_this_month'] > 0 ? 'disabled' : ''; ?>>
                                                Pay Monthly (3000)
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                            <a href="receipts.php?student_id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3 ms-2">History</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
