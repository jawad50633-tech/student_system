<?php
require_once '../config.php';
require_once '../includes/auth_check.php';

$message = '';

// Handle Fee Payment
if (isset($_POST['pay_fee'])) {
    $student_id = $_POST['student_id'];
    $fee_type = $_POST['fee_type'];
    $amount = ($fee_type == 'Admission') ? 800 : 3000;
    $receipt_number = 'REC-' . time() . rand(10, 99);
    $payment_date = date('Y-m-d');

    $stmt = $pdo->prepare("INSERT INTO fees (student_id, fee_type, amount, status, payment_date, receipt_number) VALUES (?, ?, ?, 'Paid', ?, ?)");
    $stmt->execute([$student_id, $fee_type, $amount, $payment_date, $receipt_number]);
    $message = "Fee payment recorded successfully!";
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Fees Management</h3>
    </div>

    <div class="card shadow">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Admission Fee</th>
                        <th>Monthly Fee</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $students = $pdo->query("
                        SELECT s.id, s.name, c.class_name,
                        (SELECT status FROM fees WHERE student_id = s.id AND fee_type = 'Admission' LIMIT 1) as admission_status,
                        (SELECT status FROM fees WHERE student_id = s.id AND fee_type = 'Monthly' ORDER BY id DESC LIMIT 1) as monthly_status
                        FROM students s 
                        LEFT JOIN classes c ON s.class_id = c.id
                    ")->fetchAll();

                    foreach ($students as $s):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s['name']); ?></td>
                        <td><?php echo htmlspecialchars($s['class_name']); ?></td>
                        <td>
                            <?php if ($s['admission_status'] == 'Paid'): ?>
                                <span class="badge bg-success">Paid</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Unpaid</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($s['monthly_status'] == 'Paid'): ?>
                                <span class="badge bg-success">Paid</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Unpaid</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                    Pay Fee
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="student_id" value="<?php echo $s['id']; ?>">
                                            <input type="hidden" name="fee_type" value="Admission">
                                            <button type="submit" name="pay_fee" class="dropdown-item" <?php echo $s['admission_status'] == 'Paid' ? 'disabled' : ''; ?>>Admission (800)</button>
                                        </form>
                                    </li>
                                    <li>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="student_id" value="<?php echo $s['id']; ?>">
                                            <input type="hidden" name="fee_type" value="Monthly">
                                            <button type="submit" name="pay_fee" class="dropdown-item">Monthly (3000)</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                            <a href="receipts.php?student_id=<?php echo $s['id']; ?>" class="btn btn-sm btn-info text-white">View Receipts</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
