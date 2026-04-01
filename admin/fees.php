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

    $student_stmt = $pdo->prepare("
    SELECT c.class_name 
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.id
    WHERE s.id = ?
");
$student_stmt->execute([$student_id]);
$student_data = $student_stmt->fetch();

$class_name = $student_data['class_name'] ?? '';

if ($fee_type == 'Admission') {
    $base_amount = 1000;
} else {

    switch (strtolower($class_name)) {
        case 'ai':
        case 'ai class':
            $base_amount = 3000;
            break;

        case 'python':
        case 'python class':
            $base_amount = 4000;
            break;

        case 'cybersecurity':
        case 'cybersecurity class':
            $base_amount = 5000;
            break;

        default:
            $base_amount = 3000; // fallback
    }
}

// Detect correct dropdown
if ($fee_type == 'Admission') {
    $discount_percent = isset($_POST['discount_option_adm'])
        ? (int) $_POST['discount_option_adm']
        : 0;
} else {
    $discount_percent = isset($_POST['discount_option_mon'])
        ? (int) $_POST['discount_option_mon']
        : 0;
}

// Allow only valid values
if (!in_array($discount_percent, [0, 20, 100])) {
    $discount_percent = 0;
}

// Calculate
$discount = ($base_amount * $discount_percent) / 100;
$final_amount = $base_amount - $discount;

    // Safety check: Prevent duplicate entry
    $check = $pdo->prepare("SELECT COUNT(*) FROM fees WHERE student_id = ? AND fee_type = ? AND (fee_type = 'Admission' OR (MONTH(payment_date) = ? AND YEAR(payment_date) = ?))");
    $check->execute([$student_id, $fee_type, $current_month, $current_year]);
    
    if ($check->fetchColumn() == 0) {
        $receipt_number = 'REC-' . strtoupper(substr(md5(time()), 0, 6)) . rand(10, 99);
        $payment_date = date('Y-m-d');

        // $stmt = $pdo->prepare("INSERT INTO fees (student_id, fee_type, amount, status, payment_date, receipt_number) VALUES (?, ?, ?, 'Paid', ?, ?)");
        $stmt = $pdo->prepare("INSERT INTO fees 
(student_id, fee_type, amount, discount, status, payment_date, receipt_number) 
VALUES (?, ?, ?, ?, 'Paid', ?, ?)");

$stmt1 = $pdo->prepare("INSERT INTO fees_backup 
(student_id, fee_type, amount, discount, status, payment_date, receipt_number) 
VALUES (?, ?, ?, ?, 'Paid', ?, ?)");
        
        if ($stmt->execute([$student_id, $fee_type, $final_amount, $discount, $payment_date, $receipt_number])) {
            $stmt1->execute([$student_id, $fee_type, $final_amount, $discount, $payment_date, $receipt_number]);
            $message = "Payment of $final_amount PKR recorded successfully!";
        }
    } else {
        $message = "Error: This fee has already been settled.";
    }
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">

<style>
    body { 
        background: linear-gradient(rgba(0, 0, 0, 0.96), rgba(0, 0, 0, 0.96)), url('../uploads/background.png');
        background-size: cover; background-attachment: fixed; font-family: 'Inter', sans-serif; color: #fff;
    }
    
    .stylish-header-bar {
        background: #000; border: 1px solid #222; border-radius: 15px; padding: 25px; margin-bottom: 30px;
        display: flex; justify-content: space-between; align-items: center; border-left: 5px solid #00d4ff;
    }
    .main-title { color: #fff; font-weight: 900; font-size: 2rem; letter-spacing: -1px; margin: 0; }

    .fees-card { background: #000; border-radius: 20px; border: 1px solid #222; overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.8); }

    .table { margin-bottom: 0; background: #000 !important; border-collapse: separate; border-spacing: 0 5px; }
    .table thead th { 
        background: #111; color: #00d4ff !important; padding: 20px; border: none; 
        font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px;
    }

    /* STUDENT INFO & CLASS (BLACK TEXT ON LIGHT BG) */
    .col-light-data { 
        background: #f8f9fa !important; color: #000 !important; 
        border-bottom: 1px solid #dee2e6 !important;
    }
    .stu-name-black { color: #000 !important; font-weight: 800; font-size: 1.1rem; display: block; }
    .class-tag-black { color: #444 !important; font-weight: 700; font-size: 0.9rem; background: #e9ecef; padding: 2px 8px; border-radius: 4px; }

    /* STATUS BOXES (DARK EMERALD GREEN) */
    .status-paid-box { 
        color: #065f46 !important; background: rgba(16, 185, 129, 0.15); 
        padding: 6px 14px; border-radius: 8px; border: 1px solid #065f46;
        font-weight: 800; font-size: 11px; display: inline-flex; align-items: center; gap: 8px;
    }
    .status-unpaid-box { 
        color: #991b1b !important; background: rgba(239, 68, 68, 0.1); 
        padding: 6px 14px; border-radius: 8px; border: 1px solid #991b1b;
        font-weight: 800; font-size: 11px; display: inline-flex; align-items: center; gap: 8px;
    }

    /* ACTION BUTTONS */
    .btn-collect { 
        background: #00d4ff; color: #000 !important; font-weight: 800; 
        border-radius: 10px; border: none; padding: 10px 20px; transition: 0.3s;
    }
    .btn-collect:hover { background: #fff; transform: translateY(-2px); }
    
    .btn-history { 
        background: #1a1a1a; color: #00d4ff !important; border: 1px solid #333;
        border-radius: 10px; padding: 10px 15px; transition: 0.3s;
    }
    .btn-history:hover { background: #00d4ff; color: #000 !important; }

    /* MODAL STYLING */
    .modal-content { background: #0b0f19; border: 1px solid #00d4ff; border-radius: 25px; color: #fff; }
    .payment-card { transition: 0.3s; border: 1px solid #333; cursor: pointer; }
    .payment-card:hover:not(.disabled) { border-color: #00d4ff; background: #111 !important; transform: translateY(-5px); }
    .payment-card.disabled { opacity: 0.4; cursor: not-allowed !important; background: #050505 !important; border-color: #1a1a1a; }
</style>

<div class="container py-5">
    <?php if($message): ?>
        <div class="alert alert-info border-0 shadow-lg mb-4 bg-dark text-info fw-bold">
            <i class="fa-solid fa-circle-info me-2"></i> <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="stylish-header-bar shadow-lg">
        <div>
            <h2 class="main-title"><i class="fa-solid fa-vault text-info me-2"></i>FEES MANAGER</h2>
            <p class="text-secondary small mb-0">Record and track student payments for <?php echo date('F Y'); ?></p>
        </div>
    </div>

    <div class="fees-card">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Student Details</th>
                        <th>Class</th>
                        <th>Admission Fee</th>
                        <th>Monthly Tuition</th>
                        <th class="text-end pe-4">System Actions</th>
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
                        <td class="ps-4 col-light-data">
                            <span class="stu-name-black"><i class="fa-solid fa-circle-user me-2"></i><?php echo htmlspecialchars($s['name']); ?></span>
                            <small class="text-muted fw-bold">REF: #STU-<?php echo $s['id']; ?></small>
                        </td>
                        <td class="col-light-data">
                            <span class="class-tag-black"><?php echo htmlspecialchars($s['class_name'] ?? 'General'); ?></span>
                        </td>
                        <td>
                            <?php if ($s['has_admission'] > 0): ?>
                                <div class="status-paid-box"><i class="fa-solid fa-circle-check"></i> PAID</div>
                            <?php else: ?>
                                <div class="status-unpaid-box"><i class="fa-solid fa-circle-xmark"></i> DUE</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($s['paid_this_month'] > 0): ?>
                                <div class="status-paid-box"><i class="fa-solid fa-circle-check"></i> PAID</div>
                            <?php else: ?>
                                <div class="status-unpaid-box"><i class="fa-solid fa-clock-rotate-left"></i> DUE</div>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-collect btn-sm me-2" onclick="openPaymentModal('<?php echo $s['id']; ?>', '<?php echo addslashes($s['name']); ?>', <?php echo $s['has_admission']; ?>, <?php echo $s['paid_this_month']; ?>)">
                                <i class="fa-solid fa-hand-holding-dollar"></i> Collect
                            </button>
                            <a href="receipts.php?student_id=<?php echo $s['id']; ?>" class="btn btn-sm btn-history">
                                <i class="fa-solid fa-list-ul"></i>
                            </a>
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
        <div class="modal-content shadow-lg">
            <div class="modal-body p-5 text-center">
                <i class="fa-solid fa-circle-dollar-to-slot fa-3x text-info mb-3"></i>
                <h3 class="fw-bold text-white mb-4" id="modalStudentName">STUDENT NAME</h3>
                <div class="row g-3">
                    <div class="col-6">
                        <form method="POST" id="formAdmission">
                            <input type="hidden" name="student_id" id="modal_sid_adm">
                            <input type="hidden" name="fee_type" value="Admission">
                            <select name="discount_option_adm" class="form-select">
                                <option value="0" selected>Normal Fees</option>
                                <option value="20">20% Discount</option>
                                <option value="100">100% Scholarship</option>
                            </select>
                            <div id="cardAdmission" class="payment-card p-4 rounded-4 bg-dark text-white" onclick="submitIfActive('formAdmission', 'cardAdmission')">
                                <i class="fa-solid fa-graduation-cap fa-2x mb-2 text-info"></i><br>
                                <span id="textAdmission" class="fw-bold">Admission</span>
                            </div>
                        </form>
                    </div>
                    <div class="col-6">
                        <form method="POST" id="formMonthly">
                            <input type="hidden" name="student_id" id="modal_sid_mon">
                            <input type="hidden" name="fee_type" value="Monthly">
                            
                            <select name="discount_option_mon" class="form-select">
                                <option value="0" selected>Normal Fees</option>
                                <option value="20">20% Discount</option>
                                <option value="100">100% Scholarship</option>
                            </select>
                            <div id="cardMonthly" class="payment-card p-4 rounded-4 bg-dark text-white" onclick="submitIfActive('formMonthly', 'cardMonthly')">
                                <i class="fa-solid fa-calendar-check fa-2x mb-2 text-info"></i><br>
                                <span id="textMonthly" class="fw-bold">Monthly Fee</span>
                            </div>
                        </form>
                    </div>
                </div>
                <button type="button" class="btn btn-link text-secondary mt-4 text-decoration-none" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleScholarship(checkbox, hiddenInputId) {
    document.getElementById(hiddenInputId).value = checkbox.checked ? 1 : 0;
}
function openPaymentModal(id, name, hasAdm, hasMon) {
    document.getElementById('modalStudentName').innerText = name.toUpperCase();
    document.getElementById('modal_sid_adm').value = id;
    document.getElementById('modal_sid_mon').value = id;

    // UI Logic for Admission Button
    const admCard = document.getElementById('cardAdmission');
    const admText = document.getElementById('textAdmission');
    if (hasAdm > 0) {
        admCard.classList.add('disabled');
        admText.innerHTML = '<i class="fa-solid fa-check-double text-success"></i> Settled';
    } else {
        admCard.classList.remove('disabled');
        admText.innerText = 'Admission';
    }

    // UI Logic for Monthly Button
    const monCard = document.getElementById('cardMonthly');
    const monText = document.getElementById('textMonthly');
    if (hasMon > 0) {
        monCard.classList.add('disabled');
        monText.innerHTML = '<i class="fa-solid fa-check-double text-success"></i> Paid';
    } else {
        monCard.classList.remove('disabled');
        monText.innerText = 'Monthly Fee';
    }

    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}

function submitIfActive(formId, cardId) {
    const card = document.getElementById(cardId);
    if(card.classList.contains('disabled')) return;

    if(confirm("Confirm this payment collection?")) {
        const f = document.getElementById(formId);
        const b = document.createElement('input'); 
        b.type='hidden'; b.name='pay_fee';
        f.appendChild(b); 
        f.submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>
