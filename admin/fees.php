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

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
<style>
    body { 
        background: linear-gradient(rgba(0, 0, 0, 0.94), rgba(0, 0, 0, 0.94)), url('../uploads/background.png');
        background-size: cover; background-attachment: fixed; font-family: 'Inter', sans-serif; color: #fff;
    }
    
    .stylish-header-bar {
        background: #000; border: 1px solid #333; border-radius: 15px; padding: 20px 35px; margin-bottom: 30px;
        display: flex; justify-content: space-between; align-items: center;
    }
    .main-title { color: #00d4ff; font-weight: 900; font-size: 2.2rem; letter-spacing: -1px; margin: 0; }

    .fees-card { background: #000; border-radius: 20px; border: 1px solid #222; overflow: hidden; }

    .table { margin-bottom: 0; background: #000 !important; }
    .table thead th { 
        background: #111; color: #00d4ff !important; padding: 22px; border: none; 
        font-size: 0.85rem; text-transform: uppercase; letter-spacing: 2px; font-weight: 800;
    }

    /* SPECIFIC FIX: Black Text on Light Background for Student/Class */
    .col-black-text { 
        background: #e9ecef !important; /* Light Grey background */
        color: #000000 !important;      /* Pure Black text */
        border-right: 1px solid #ccc;
    }
    
    .stu-name-black { color: #000 !important; font-weight: 800; font-size: 1.05rem; display: block; }
    .class-tag-black { color: #333 !important; font-weight: 600; font-size: 0.9rem; }
    .stu-id-black { color: #555 !important; font-size: 0.75rem; font-weight: bold; }

    /* Other Columns stay dark */
    .table td { padding: 20px 15px; border-bottom: 1px solid #1a1a1a; vertical-align: middle; }

    /* Neon Status Indicators */
    .status-paid { color: #00ff88 !important; font-weight: 800; font-size: 12px; }
    .status-unpaid { color: #ff3e3e !important; font-weight: 800; font-size: 12px; }
    
    .btn-collect { background: #00d4ff; color: #000 !important; font-weight: 800; border-radius: 8px; border: none; padding: 10px 20px; }
    .btn-history { background: #111; color: #aaa !important; border: 1px solid #333; border-radius: 8px; padding: 9px 18px; }

    .modal-content { background: #000; border: 1px solid #333; border-radius: 20px; color: #fff; }
</style>

<div class="container py-5">
    <?php if ($message): ?>
        <div class="alert alert-dark border-secondary text-info rounded-3 mb-4"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="stylish-header-bar">
        <div>
            <h2 class="main-title">FEES PORTAL</h2>
            <p class="text-secondary small mb-0 fw-bold">Current Period: <?php echo date('F Y'); ?></p>
        </div>
    </div>

    <div class="fees-card shadow-lg">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Student Info</th>
                        <th>Class</th>
                        <th>Admission</th>
                        <th>Monthly Fee</th>
                        <th class="text-end pe-4">Control</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $students = $pdo->prepare("
                        SELECT s.id, s.name, c.class_name,
                        (SELECT COUNT(*) FROM fees WHERE student_id = s.id AND fee_type = 'Admission') as has_admission,
                        (SELECT COUNT(*) FROM fees WHERE student_id = s.id AND fee_type = 'Monthly' 
                         AND MONTH(payment_date) = ? AND YEAR(payment_date) = ?) as paid_this_month
                        FROM students s 
                        LEFT JOIN classes c ON s.class_id = c.id
                    ");
                    $students->execute([$current_month, $current_year]);
                    while ($s = $students->fetch()):
                    ?>
                    <tr>
                        <td class="ps-4 col-black-text">
                            <span class="stu-name-black"><?php echo htmlspecialchars($s['name']); ?></span>
                            <span class="stu-id-black">ID: #<?php echo $s['id']; ?></span>
                        </td>
                        
                        <td class="col-black-text">
                            <span class="class-tag-black"><?php echo htmlspecialchars($s['class_name'] ?? 'No Class'); ?></span>
                        </td>

                        <td>
                            <?php echo $s['has_admission'] > 0 ? '<span class="status-paid">PAID</span>' : '<span class="status-unpaid">DUE</span>'; ?>
                        </td>
                        <td>
                            <?php echo $s['paid_this_month'] > 0 ? '<span class="status-paid">PAID</span>' : '<span class="status-unpaid">DUE</span>'; ?>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-collect btn-sm me-2" onclick="openPaymentModal('<?php echo $s['id']; ?>', '<?php echo addslashes($s['name']); ?>', <?php echo $s['has_admission']; ?>, <?php echo $s['paid_this_month']; ?>)">COLLECT</button>
                            <a href="receipts.php?student_id=<?php echo $s['id']; ?>" class="btn btn-sm btn-history">LEDGER</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-5 text-center">
                <h3 class="fw-bold text-info mb-4" id="modalStudentName">...</h3>
                <div class="row g-3">
                    <div class="col-6">
                        <form method="POST" id="formAdmission">
                            <input type="hidden" name="student_id" id="modal_sid_adm">
                            <input type="hidden" name="fee_type" value="Admission">
                            <div class="payment-option-card p-4 border rounded" id="cardAdmission" style="cursor:pointer;" onclick="submitIfActive('formAdmission')">
                                🎓<br>ADMISSION
                            </div>
                        </form>
                    </div>
                    <div class="col-6">
                        <form method="POST" id="formMonthly">
                            <input type="hidden" name="student_id" id="modal_sid_mon">
                            <input type="hidden" name="fee_type" value="Monthly">
                            <div class="payment-option-card p-4 border rounded" id="cardMonthly" style="cursor:pointer;" onclick="submitIfActive('formMonthly')">
                                📅<br>MONTHLY
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openPaymentModal(id, name, hasAdm, hasMon) {
    document.getElementById('modalStudentName').innerText = name.toUpperCase();
    document.getElementById('modal_sid_adm').value = id;
    document.getElementById('modal_sid_mon').value = id;
    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}

function submitIfActive(formId) {
    if(confirm("Process Payment?")) {
        const f = document.getElementById(formId);
        const b = document.createElement('input');
