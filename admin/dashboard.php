<?php
require_once '../config.php';
require_once '../includes/auth_check.php';

// Fetch Statistics
$total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$total_classes = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();

$class_stats = $pdo->query("
    SELECT c.class_name, COUNT(s.id) as student_count 
    FROM classes c 
    LEFT JOIN students s ON c.id = s.class_id 
    GROUP BY c.id
")->fetchAll();

$labels = json_encode(array_column($class_stats, 'class_name'));
$counts = json_encode(array_column($class_stats, 'student_count'));

include '../includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">

<style>
    :root {
        --deep-black: #0a0a0a;
        --electric-cyan: #00d4ff;
        --royal-violet: #9d50bb;
        /* Milky glass for better contrast with dark text */
        --glass-bg: rgba(255, 255, 255, 0.85); 
    }

    body {
        background: linear-gradient(rgba(6, 11, 40, 0.8), rgba(6, 11, 40, 0.8)), 
                    url('../uploads/background.png') no-repeat center center fixed;
        background-size: cover;
        font-family: 'Inter', sans-serif;
        color: var(--deep-black);
    }

    /* 3D Container */
    .dashboard-grid {
        perspective: 2000px;
    }

    .stat-card {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 2px solid rgba(255, 255, 255, 0.5);
        border-radius: 24px;
        padding: 2rem;
        transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        transform-style: preserve-3d;
        height: 100%;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    }

    /* Professional 3D Hover */
    .stat-card:hover {
        transform: rotateY(10deg) rotateX(5deg) translateZ(20px);
        border-color: var(--electric-cyan);
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
    }

    .icon-box {
        width: 55px;
        height: 55px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        margin-bottom: 20px;
        background: #fff;
        border: 1px solid #ddd;
        box-shadow: 4px 4px 10px rgba(0,0,0,0.05);
    }

    /* Dark Text Styles */
    .stat-value {
        font-size: 3rem;
        font-weight: 800;
        color: var(--deep-black);
        line-height: 1;
        margin-bottom: 10px;
    }

    .stat-label {
        color: #444; /* Dark grey-black */
        text-transform: uppercase;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 1px;
    }

    .data-section {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 24px;
        padding: 30px;
        border: 1px solid rgba(0,0,0,0.05);
        color: var(--deep-black);
    }

    .btn-action {
        background: var(--deep-black);
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 12px;
        font-weight: 600;
        transition: 0.3s;
    }

    .btn-action:hover {
        background: var(--electric-cyan);
        color: var(--deep-black);
        transform: scale(1.05);
    }

    h5 { font-weight: 800; color: var(--deep-black); }
</style>

<div class="container py-5 dashboard-grid">
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="icon-box">👥</div>
                <h6 class="stat-label">Total Enrollment</h6>
                <div class="stat-value"><?php echo $total_students; ?></div>
                <p class="small text-muted mb-0 font-monospace">ACTIVE_RECORDS</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="icon-box" style="border-color: var(--royal-violet);">🎓</div>
                <h6 class="stat-label">Academic Tracks</h6>
                <div class="stat-value"><?php echo $total_classes; ?></div>
                <p class="small text-muted mb-0 font-monospace">LIVE_COURSES</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card" style="border-bottom: 5px solid var(--electric-cyan);">
                <h6 class="stat-label mb-3">Management</h6>
                <div class="d-grid gap-2 mt-4">
                    <a href="students.php?action=add" class="btn btn-action">Quick Enrollment</a>
                    <a href="fees.php" class="btn btn-outline-dark rounded-3 fw-bold">Financial Audit</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="data-section shadow-sm">
                <h5 class="mb-4">Visual Analytics</h5>
                <canvas id="studentChart" height="140"></canvas>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="data-section shadow-sm h-100">
                <h5 class="mb-4">Course Density</h5>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="small text-muted text-uppercase">
                            <tr>
                                <th>Course</th>
                                <th class="text-end">Students</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($class_stats as $stat): ?>
                            <tr>
                                <td class="py-3 fw-semibold"><?php echo htmlspecialchars($stat['class_name']); ?></td>
                                <td class="text-end py-3">
                                    <span class="badge bg-dark px-3 py-2"><?php echo $stat['student_count']; ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('studentChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo $labels; ?>,
            datasets: [{
                label: 'Student Distribution',
                data: <?php echo $counts; ?>,
                fill: true,
                backgroundColor: 'rgba(0, 212, 255, 0.1)',
                borderColor: '#0a0a0a', /* Dark black line for the chart */
                borderWidth: 4,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#00d4ff'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: '#f0f0f0' }, ticks: { color: '#0a0a0a', font: { weight: 'bold' } } },
                x: { grid: { display: false }, ticks: { color: '#0a0a0a', font: { weight: 'bold' } } }
            }
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
