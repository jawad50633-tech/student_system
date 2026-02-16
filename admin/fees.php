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

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
    body { 
        background: linear-gradient(rgba(6, 11, 40, 0.96), rgba(6, 11, 40, 0.96)), url('../uploads/background.png');
        background-size: cover; background-attachment: fixed; font-family: 'Inter', sans-serif; color: #fff;
    }
    
    .stylish-header-bar {
        background: rgba(255, 255, 255, 0.08); backdrop-filter: blur(15px);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 20px; padding: 20px 35px; margin-bottom: 30px;
        display: flex; justify-content: space-between; align-items: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    .black-title { color: #fff; font-weight: 800; font-size: 2.2rem; letter-spacing: -1.5px; margin: 0; }

    .fees-card { 
        background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px);
        border-radius: 28px; border: 1px solid rgba(255,255,255,0.12);
        overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.5);
    }

    /* Table Visibility Fixes */
    .table { color: #ffffff !important; margin-bottom: 0; width: 100%; }
    .table thead th { 
        background: rgba(0,0,0,0.6); color: #00d4ff !important; 
        padding: 20px; border: none; font-size: 0.8rem; 
        text-transform: uppercase; letter-spacing: 1.5px;
    }
    .table tbody tr { background: rgba(255, 255, 255, 0.02); transition: 0.3s; }
    .table tbody tr:hover { background: rgba(0, 212, 255, 0.08) !important; }
    
    .table td { 
        padding: 18px 15px; border-bottom: 1px solid rgba(255,255,255,0.05); 
        color: #ffffff !important; vertical-align: middle;
    }

    /* Buttons & Status */
    .status-paid { color: #00ff88 !important; font-weight: 800; font-size: 11px; letter-spacing: 0.5px; }
    .status-unpaid { color: #ff4d4d !important; font-weight: 800; font-size: 11px; letter-spacing: 0.5px; }
    
    .btn-collect {
        background: #00d4ff; color: #000 !important; font-weight: 700; 
        border-radius: 50px; border: none; padding: 8px 18px;
        box-shadow: 0 4px 15px rgba(0, 212, 255, 0.3); transition: 0.3s;
    }
    .btn-collect:hover { transform: scale(1.05); background: #fff; }

    .btn-history {
        border: 1px solid rgba(255, 255, 255, 0.4); color: #fff !important;
        border-radius: 50px; padding: 7px 18px; font-size: 0.85rem; transition: 0.3s;
    }
    .btn-history:hover { background: rgba(255,255,255,1); color: #000 !important; }

    /* Modal Styling */
    .modal-content {
        background: rgba(10, 18, 42, 0.95); backdrop-filter: blur(20px);
        border: 1px solid rgba(0, 212, 255, 0.4); border-radius: 30px; color: #fff;
    }
    .payment-option-card {
        background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
        border-radius: 20px; padding: 25px 15px; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
        cursor: pointer; text-align: center;
    }
    .payment-option-card:hover:not(.disabled) {
        background: #00d4ff; color: #000; transform: translateY(-12px);
        box-shadow: 0 15px 30px rgba(0, 212, 255, 0.4);
    }
    .payment-option-card.disabled { opacity: 0.25; cursor: not-allowed; border: 1px dashed rgba(255,255,255,0.2); }
</style>

<div class="container py-5">
    <?php if ($message): ?>
        <div class="alert alert-success bg-success text-white border-0 rounded-4 mb-4 shadow-lg"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="stylish-header-bar">
        <div>
            <h2 class="black-title">Fees Management</h2>
            <p class="text-info small mb-0 fw-bold">Academic Session: <?php echo date('F Y'); ?></p>
        </div>
        <div class="text-end">
            <span class="badge bg-info text-dark px-4 py-2 rounded-pill fw-bold">Live Portal</span>
        </div>
    </div>

    <div class="fees-card shadow-lg">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Student Name</th>
                        <th>Class</th>
                        <th>Admission Fee</th>
                        <th>Monthly Fee</th>
                        <th class="text-end pe-4">Actions</th>
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
                        <td><span class="opacity-75"><?php echo htmlspecialchars($s['class_name'] ?? 'No Class'); ?></span></td>
                        <td>
                            <?php echo $s['has_admission'] > 0 
                                ? '<span class="status-paid"><i class="bi bi-check-circle-fill"></i> PAID</span>' 
                                : '<span class="status-unpaid"><i class="bi bi-exclamation-circle"></i> DUE</span>'; ?>
                        </td>
                        <td>
                            <?php echo $s['paid_this_month'] > 0 
                                ? '<span class="status-paid"><i class="bi bi-check-circle-fill"></i> PAID</span>' 
                                : '<span class="status-unpaid"><i class="bi bi-exclamation-circle"></i> DUE</span>'; ?>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-collect btn-sm me-2" 
                                    onclick="openPaymentModal('<?php echo $s['id']; ?>', '<?php echo addslashes($s['name']); ?>', <?php echo $s['has_admission']; ?>, <?php echo $s['paid_this_month']; ?>)">
                                Collect
                            </button>
                            <a href="receipts.php?student_id=<?php echo $s['id']; ?>" class="btn btn-sm btn-history">History</a>
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
                <h3 class="fw-bold mb-1" id="modalStudentName">...</h3>
                <p class="text-info small mb-4">Select the fee type to process</p>
                
                <div class="row g-3">
                    <div class="col-6">
                        <form method="POST" id="formAdmission">
                            <input type="hidden" name="student_id" id="modal_sid_adm">
                            <input type="hidden" name="fee_type" value="Admission">
                            <div class="payment-option-card" id="cardAdmission" onclick="submitIfActive('formAdmission')">
                                <div style="font-size: 40px;" class="mb-2">🎓</div>
                                <div class="fw-bold">Admission</div>
                                <div class="small opacity-50">800 PKR</div>
                            </div>
                        </form>
                    </div>
                    <div class="col-6">
                        <form method="POST" id="formMonthly">
                            <input type="hidden" name="student_id" id="modal_sid_mon">
                            <input type="hidden" name="fee_type" value="Monthly">
                            <div class="payment-option-card" id="cardMonthly" onclick="submitIfActive('formMonthly')">
                                <div style="font-size: 40px;" class="mb-2">📅</div>
                                <div class="fw-bold">Monthly</div>
                                <div class="small opacity-50">3000 PKR</div>
                            </div>
                        </form>
                    </div>
                </div>
                <button type="button" class="btn btn-link text-white-50 mt-4 text-decoration-none" data-bs-dismiss="modal">Close Window</button>
            </div>
        </div>
    </div>
</div>

<script>
function openPaymentModal(id, name, hasAdm, hasMon) {
    document.getElementById('modalStudentName').innerText = name;
    document.getElementById('modal_sid_adm').value = id;
    document.getElementById('modal_sid_mon').value = id;

    const cardAdm = document.getElementById('cardAdmission');
    if(hasAdm > 0) {
        cardAdm.classList.add('disabled');
        cardAdm.innerHTML = '<div style="font-size: 40px;" class="mb-2">✅</div><div class="fw-bold">Already Paid</div>';
    } else {
        cardAdm.classList.remove('disabled');
        cardAdm.innerHTML = '<div style="font-size: 40px;" class="mb-2">🎓</div><div class="fw-bold">Admission</div><div class="small opacity-50">800 PKR</div>';
    }

    const cardMon = document.getElementById('cardMonthly');
    if(hasMon > 0) {
        cardMon.classList.add('disabled');
        cardMon.innerHTML = '<div style="font-size: 40px;" class="mb-2">✅</div><div class="fw-bold">Already Paid</div>';
    } else {
        cardMon.classList.remove('disabled');
        cardMon.innerHTML = '<div style="font-size: 40px;" class="mb-2">📅</div><div class="fw-bold">Monthly</div><div class="small opacity-50">3000 PKR</div>';
    }

    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}

function submitIfActive(formId) {
    const form = document.getElementById(formId);
    if(!form.querySelector('.payment-option-card').classList.contains('disabled')) {
        if(confirm("Process this payment now?")) {
            const hiddenBtn = document.createElement('input');
            hiddenBtn.type = 'hidden';
            hiddenBtn.name = 'pay_fee';
            form.appendChild(hiddenBtn);
            form.submit();
        }
    }
}
</script>

<?php include '../includes/footer.php'; ?>
