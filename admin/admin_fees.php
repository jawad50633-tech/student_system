<?php
session_start();
require_once '../config.php';
require_once '../includes/auth_check.php';


/* =========================
   FETCH TOTAL DATA FROM fees TABLE
   ========================= */

// Total Collected (sum of amount)
$total_stmt = $pdo->query("SELECT SUM(amount) as total FROM fees_backup");
$total_collected = $total_stmt->fetch()['total'] ?? 0;

// Total Admission Fees
$admission_stmt = $pdo->query("SELECT SUM(amount) as total FROM fees_backup WHERE fee_type = 'Admission'");
$total_admission = $admission_stmt->fetch()['total'] ?? 0;

// Total Monthly Fees
$monthly_stmt = $pdo->query("SELECT SUM(amount) as total FROM fees_backup WHERE fee_type = 'Monthly'");
$total_monthly = $monthly_stmt->fetch()['total'] ?? 0;

// Total Discounts Given
$discount_stmt = $pdo->query("SELECT SUM(discount) as total FROM fees_backup");
$total_discount = $discount_stmt->fetch()['total'] ?? 0;

// Total Transactions
$count_stmt = $pdo->query("SELECT COUNT(*) as total FROM fees_backup");
$total_transactions = $count_stmt->fetch()['total'] ?? 0;

// Net Income
$net_income = $total_collected;

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fees Audit (Hidden)</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 40px;
        }

        h1 {
            margin-bottom: 25px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .card {
            background: #ffffff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-4px);
        }

        .card h2 {
            margin: 0 0 10px;
            font-size: 15px;
            color: #777;
        }

        .amount {
            font-size: 26px;
            font-weight: bold;
            color: #2c3e50;
        }

        .note {
            margin-top: 40px;
            font-size: 13px;
            color: #888;
        }

        .hidden-tag {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<h1>🔒 Total Fees Audit Dashboard</h1>
<div class="hidden-tag">Hidden Page – Admin Access Only</div>

<div class="grid">

    <div class="card">
        <h2>Total Collected</h2>
        <div class="amount">Rs. <?php echo number_format($total_collected, 2); ?></div>
    </div>

    <div class="card">
        <h2>Total Admission Fees</h2>
        <div class="amount">Rs. <?php echo number_format($total_admission, 2); ?></div>
    </div>

    <div class="card">
        <h2>Total Monthly Fees</h2>
        <div class="amount">Rs. <?php echo number_format($total_monthly, 2); ?></div>
    </div>

    <div class="card">
        <h2>Total Discounts Given</h2>
        <div class="amount">Rs. <?php echo number_format($total_discount, 2); ?></div>
    </div>

    <div class="card">
        <h2>Net Income</h2>
        <div class="amount">Rs. <?php echo number_format($net_income, 2); ?></div>
    </div>

    <div class="card">
        <h2>Total Transactions</h2>
        <div class="amount"><?php echo number_format($total_transactions); ?></div>
    </div>

</div>

<div class="note">
    Generated on: <?php echo date("d M Y - h:i A"); ?>
</div>

</body>
</html>