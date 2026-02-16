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
    
    // Safety check: Prevent duplicate entry
    $check = $pdo->prepare("SELECT COUNT(*) FROM fees WHERE student_id = ? AND fee_type = ? AND (fee_type = 'Admission' OR (MONTH(payment_date) = ? AND YEAR(payment_date) = ?))");
    $check->execute([$student_id, $fee_type, $current_month, $current_year]);
    
    if ($check->fetchColumn() == 0) {
        $receipt_number = 'REC-' . strtoupper(substr(md5(time()), 0, 6)) . rand(10, 99);
        $payment_date = date('Y-m-d');

        $stmt = $pdo->prepare("INSERT INTO fees (student_id, fee_type, amount, status, payment_date, receipt_number) VALUES (?, ?, ?, 'Paid', ?, ?)");
        if ($stmt->execute([$student_id, $fee_type, $amount, $payment_date, $receipt_number])) {
            $message = "Payment of $amount PKR recorded successfully!";
        }
    } else {
        $message = "Error: This fee has already been paid.";
    }
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    body { 
        background: linear-gradient(rgba(0, 0, 0, 0.96), rgba(0, 0, 0, 0.96)), url('../uploads/background.png');
        background-size: cover; background-attachment: fixed; font-family: 'Inter', sans-serif; color: #fff;
    }
    .stylish-header-bar {
        background: #000; border: 1px solid #222; border-radius: 15px; padding: 25px; margin-bottom: 30px;
        display: flex; justify-content: space-between; align-items: center; border-left: 5px solid #00d4ff;
    }
    .fees-card { background: #000; border-radius: 20px; border: 1px solid #222; overflow: hidden; }
    .table { margin-bottom: 0; background: #000 !important; }
    
    /* Column for Student & Class - Black Text */
    .col-light-data { background: #f8f9fa !important; color: #000 !important; border-bottom: 1px solid #dee2e6 !important; }
    .stu-name-black { color: #000 !important; font-weight: 800; font-size: 1.1rem; display: block; }
    
    /* Status Boxes */
    .status-paid-box { 
        color: #065f46 !important; background: rgba(16, 185, 129, 0.15); 
        padding: 8px 14px; border-radius: 8px; border: 1px solid #065f46;
        font-weight: 800; font-size: 12px; display: inline-flex; align-items: center;
    }
    .status-unpaid-box { 
        color: #991b1b !important; background: rgba(239, 68, 68, 0.15); 
        padding: 8px 14px; border-radius: 8px; border: 1px solid #991b1b;
        font-weight: 800; font-size: 12px; display: inline-flex; align-items: center;
    }
    .status-paid-box i, .status-unpaid-box i { margin-right: 8px; font-size: 14px; }

    .btn-collect { background: #00d4ff; color: #000 !important; font-weight: 800; border-radius: 10px; border: none; padding: 10px 20px; }
    .payment-card.disabled { opacity: 0.4; cursor: not-allowed; pointer-events: none; grayscale: 1; }
    .modal-content { background: #0b0f19; border: 1px solid #00d4ff; border-radius: 25px; }
</style>

<div class="container py-5">
    <div class="stylish-header-bar shadow-lg">
        <div>
            <h2 class="text-white fw-900">FEES MANAGER</h2>
            <p class="text-secondary small mb-0">Monthly dues are active from the 1st of every month.</p>
        </div>
    </div>

    <div class="fees-card shadow-lg">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr class="text-info">
                        <th class="ps-4">Student Details</th>
                        <th>Class</th>
                        <th>Admission</th>
                        <th>Monthly Fee (<?php echo date('F'); ?>)</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Added check for 'status != Paused' so monthly dues disappear if student is paused
                    $students = $pdo->prepare("
                        SELECT s.id, s.name, s.status as student_status, c.class_name,
                        (SELECT COUNT(*) FROM fees WHERE student_id = s.id AND fee_type = 'Admission') as has_admission,
                        (SELECT COUNT(*) FROM fees WHERE student_id = s.id AND fee_type = 'Monthly' 
                         AND MONTH(payment_date) = ? AND YEAR(payment_date) = ?) as paid_this_month
                        FROM students s 
                        LEFT JOIN classes c ON s.class_id = c.id
                        WHERE s.status != 'Paused'
                    ");
                    $students->execute([$current_month, $current_year]);
                    while ($s = $students->fetch()):
                    ?>
                    <tr>
                        <td class="ps-4 col-light-data">
                            <span class="stu-name-black"><i class="fa-solid fa-user-graduate me-2"></i><?php echo htmlspecialchars($s['name']); ?></span>
                        </td>
                        <td class="col-light-data">
                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($s['class_name'] ?? 'N/A'); ?></span>
                        </td>
                        <td>
                            <?php if ($s['has_admission'] > 0): ?>
                                <div class="status-paid-box"><i class="fa-solid fa-circle-check"></i> PAID</div>
                            <?php else: ?>
                                <div class="status-unpaid-box"><i class="fa-solid fa-circle-exclamation"></i> DUE</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($s['paid_this_month'] > 0): ?>
                                <div class="status-paid-box"><i class="fa-solid fa-shield-halved"></i> PAID</div>
                            <?php else: ?>
                                <div class="status-unpaid-box"><i class="fa-solid fa-calendar-day"></i> DUE</div>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-collect btn-sm" onclick="openPaymentModal('<?php echo $s['id']; ?>', '<?php echo addslashes($s['name']); ?>', <?php echo $s['has_admission']; ?>, <?php echo $s['paid_this_month']; ?>)">
                                <i class="fa-solid fa-wallet me-1"></i> Collect
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-white">
            <div class="modal-body p-5 text-center">
                <h3 class="fw-bold mb-4" id="modalStudentName">...</h3>
                <div class="row g-3">
                    <div class="col-6">
                        <form method="POST" id="formAdm">
                            <input type="hidden" name="student_id" id="sid_adm">
                            <input type="hidden" name="fee_type" value="Admission">
                            <div id="cardAdm" class="p-4 rounded-4 bg-dark border border-secondary" style="cursor:pointer;" onclick="process('formAdm', 'cardAdm')">
                                <i class="fa-solid fa-graduation-cap fa-2x mb-2 text-info"></i><br>
                                <span id="txtAdm">Admission</span>
                            </div>
                        </form>
                    </div>
                    <div class="col-6">
                        <form method="POST" id="formMon">
                            <input type="hidden" name="student_id" id="sid_mon">
                            <input type="hidden" name="fee_type" value="Monthly">
                            <div id="cardMon" class="p-4 rounded-4 bg-dark border border-secondary" style="cursor:pointer;" onclick="process('formMon', 'cardMon')">
                                <i class="fa-solid fa-calendar-check fa-2x mb-2 text-info"></i><br>
                                <span id="txtMon">Monthly Fee</span>
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
    document.getElementById('sid_adm').value = id;
    document.getElementById('sid_mon').value = id;

    const cAdm = document.getElementById('cardAdm');
    const cMon = document.getElementById('cardMon');

    if(hasAdm > 0) {
        cAdm.classList.add('disabled');
        document.getElementById('txtAdm').innerHTML = "<i class='fa-solid fa-check text-success'></i> Paid";
    } else {
        cAdm.classList.remove('disabled');
        document.getElementById('txtAdm').innerText = "Admission";
    }

    if(hasMon > 0) {
        cMon.classList.add('disabled');
        document.getElementById('txtMon').innerHTML = "<i class='fa-solid fa-check text-success'></i> Paid";
    } else {
        cMon.classList.remove('disabled');
        document.getElementById('txtMon').innerText = "Monthly Fee";
    }

    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}

function process(fId, cId) {
    if(!document.getElementById(cId).classList.contains('disabled')) {
        if(confirm("Confirm Payment?")) {
            const f = document.getElementById(fId);
            const b = document.createElement('input'); b.type='hidden'; b.name='pay_fee';
            f.appendChild(b); f.submit();
        }
    }
}
</script>

<?php include '../includes/footer.php'; ?>
