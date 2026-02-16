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

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap" rel="stylesheet">
<style>
    body { 
        background: linear-gradient(rgba(6, 11, 40, 0.95), rgba(6, 11, 40, 0.95)), url('../uploads/background.png');
        background-size: cover; font-family: 'Inter', sans-serif; color: #fff;
    }
    
    .stylish-header-bar {
        background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(15px);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 20px; padding: 20px 35px; margin-bottom: 30px;
        display: flex; justify-content: space-between; align-items: center;
    }
    .black-title { color: #fff; font-weight: 800; font-size: 2.2rem; letter-spacing: -1.5px; margin: 0; }

    .fees-card { 
        background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px);
        border-radius: 28px; border: 1px solid rgba(255,255,255,0.1);
        overflow: hidden; 
    }

    .table { color: #fff !important; margin-bottom: 0; }
    .table thead th { background: rgba(0,0,0,0.3); color: #00d4ff; padding: 18px; border: none; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; }
    .table td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); }

    .status-paid { color: #00ff88; font-weight: 800; font-size: 11px; }
    .status-unpaid { color: #ff4d4d; font-weight: 800; font-size: 11px; }

    /* 3D Modal Styling */
    .modal-content {
        background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(20px);
        border: 1px solid rgba(0, 212, 255, 0.3); border-radius: 30px;
        color: #fff; transform: perspective(1000px) rotateX(0deg); transition: 0.5s;
    }
    .payment-option-card {
        background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
        border-radius: 20px; padding: 20px; transition: 0.3s; cursor: pointer;
        text-align: center; height: 100%; display: flex; flex-direction: column; justify-content: center;
    }
    .payment-option-card:hover:not(.disabled) {
        background: #00d4ff; color: #000; transform: translateY(-10px) scale(1.05);
        box-shadow: 0 15px 30px rgba(0, 212, 255, 0.4);
    }
    .payment-option-card.disabled { opacity: 0.3; cursor: not-allowed; }
    
    .btn-collect {
        background: #00d4ff; color: #000; font-weight: 700; border-radius: 50px;
        padding: 8px 20px; border: none; box-shadow: 0 4px 15px rgba(0, 212, 255, 0.3);
    }
</style>

<div class="container py-5">
    <?php if ($message): ?>
        <div class="alert alert-info bg-info text-white border-0 rounded-4 mb-4"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="stylish-header-bar">
        <div>
            <h2 class="black-title">Fees Management</h2>
            <p class="text-info small mb-0">Portal for <b><?php echo date('F Y'); ?></b></p>
        </div>
        <span class="badge bg-info text-dark px-3 py-2 rounded-pill fw-bold">ID: <?php echo date('d-m-Y'); ?></span>
    </div>

    <div class="fees-card shadow-lg">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Student Name</th>
                        <th>Class</th>
                        <th>Admission Status</th>
                        <th>Monthly Status</th>
                        <th class="text-end pe-4">Operations</th>
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
                        <td class="ps-4 fw-bold"><?php echo htmlspecialchars($s['name']); ?></td>
                        <td><span class="opacity-75"><?php echo htmlspecialchars($s['class_name']); ?></span></td>
                        <td><?php echo $s['has_admission'] > 0 ? '<span class="status-paid">● PAID</span>' : '<span class="status-unpaid">○ DUE</span>'; ?></td>
                        <td><?php echo $s['paid_this_month'] > 0 ? '<span class="status-paid">● PAID</span>' : '<span class="status-unpaid">○ DUE</span>'; ?></td>
                        <td class="text-end pe-4">
                            <button class="btn btn-collect btn-sm me-2" 
                                    onclick="openPaymentModal('<?php echo $s['id']; ?>', '<?php echo addslashes($s['name']); ?>', <?php echo $s['has_admission']; ?>, <?php echo $s['paid_this_month']; ?>)">
                                Collect
                            </button>
                            <a href="receipts.php?student_id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-light rounded-pill opacity-50">Ledger</a>
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
            <div class="modal-body p-4 text-center">
                <h4 class="fw-extrabold mb-1" id="modalStudentName">Student Name</h4>
                <p class="text-info small mb-4">Select Fee Category to Collect</p>
                
                <div class="row g-3">
                    <div class="col-6">
                        <form method="POST" id="formAdmission">
                            <input type="hidden" name="student_id" id="modal_sid_adm">
                            <input type="hidden" name="fee_type" value="Admission">
                            <div class="payment-option-card" id="cardAdmission" onclick="submitIfActive('formAdmission')">
                                <div class="fs-1 mb-2">🎓</div>
                                <div class="fw-bold">Admission</div>
                                <div class="small opacity-75">800 PKR</div>
                            </div>
                        </form>
                    </div>
                    <div class="col-6">
                        <form method="POST" id="formMonthly">
                            <input type="hidden" name="student_id" id="modal_sid_mon">
                            <input type="hidden" name="fee_type" value="Monthly">
                            <div class="payment-option-card" id="cardMonthly" onclick="submitIfActive('formMonthly')">
                                <div class="fs-1 mb-2">📅</div>
                                <div class="fw-bold">Monthly</div>
                                <div class="small opacity-75">3000 PKR</div>
                            </div>
                        </form>
                    </div>
                </div>
                <button type="button" class="btn btn-link text-white-50 mt-4 underline" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
function openPaymentModal(id, name, hasAdm, hasMon) {
    document.getElementById('modalStudentName').innerText = name;
    document.getElementById('modal_sid_adm').value = id;
    document.getElementById('modal_sid_mon').value = id;

    // Handle Admission Card
    const cardAdm = document.getElementById('cardAdmission');
    if(hasAdm > 0) {
        cardAdm.classList.add('disabled');
        cardAdm.innerHTML = '<div class="fs-1 mb-2">✅</div><div class="fw-bold">Paid</div>';
    } else {
        cardAdm.classList.remove('disabled');
        cardAdm.innerHTML = '<div class="fs-1 mb-2">🎓</div><div class="fw-bold">Admission</div><div class="small opacity-75">800 PKR</div>';
    }

    // Handle Monthly Card
    const cardMon = document.getElementById('cardMonthly');
    if(hasMon > 0) {
        cardMon.classList.add('disabled');
        cardMon.innerHTML = '<div class="fs-1 mb-2">✅</div><div class="fw-bold">Paid</div>';
    } else {
        cardMon.classList.remove('disabled');
        cardMon.innerHTML = '<div class="fs-1 mb-2">📅</div><div class="fw-bold">Monthly</div><div class="small opacity-75">3000 PKR</div>';
    }

    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}

function submitIfActive(formId) {
    const form = document.getElementById(formId);
    const card = form.querySelector('.payment-option-card');
    if(!card.classList.contains('disabled')) {
        const confirmPay = confirm("Confirm Payment Collection?");
        if(confirmPay) {
            const btn = document.createElement('input');
            btn.type = 'hidden';
            btn.name = 'pay_fee';
            form.appendChild(btn);
            form.submit();
        }
    }
}
</script>

<?php include '../includes/footer.php'; ?>
