<?php
require_once '../config.php';
require_once '../includes/auth_check.php';

// Fetch Statistics
$total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$total_classes = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();

// Data for Chart: Class Names and Student Counts
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

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    :root {
        --deep-black: #0a0a0a;
        --electric-cyan: #00d4ff;
        --royal-violet: #9d50bb;
        --milky-glass: rgba(255, 255, 255, 0.88);
    }

    body {
        /* Professional Background with your brand colors */
        background: linear-gradient(rgba(6, 11, 40, 0.85), rgba(6, 11, 40, 0.85)), 
                    url('../uploads/background.png') no-repeat center center fixed;
        background-size: cover;
        font-family: 'Inter', sans-serif;
        color: var(--deep-black);
        margin: 0;
    }

    .dashboard-container {
        perspective: 2000px;
        padding-top: 50px;
        padding-bottom: 50px;
    }

    /* 3D Glass Cards */
    .stat-card {
        background: var(--milky-glass);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 28px;
        padding: 2.5rem;
        transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        transform-style: preserve-3d;
        height: 100%;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }

    .stat-card:hover {
        transform: rotateY(8deg) rotateX(4deg) translateZ(20px);
        border-color: var(--electric-cyan);
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
    }

    .icon-box {
        width: 60px;
        height: 60px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 20px;
        background: white;
        box-shadow: 0 8px 15px rgba(0,0,0,0.05);
        border: 1px solid #eee;
    }

    /* Typography */
    .stat-value {
        font-size: 3.2rem;
        font-weight: 800;
        color: var(--deep-black);
        line-height: 1;
        letter-spacing: -2px;
    }

    .stat-label {
        color: #555;
        text-transform: uppercase;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 1.2px;
        margin-bottom: 8px;
        display: block;
    }

    /* Section Styling */
    .data-section {
        background: var(--milky-glass);
        border-radius: 28px;
        padding: 35px;
        border: 1px solid rgba(255,255,255,0.4);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .btn-custom {
        background: var(--deep-black);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 14px;
        font-weight: 600;
        transition: 0.3s;
    }

    .btn-custom:hover {
        background: var(--electric-cyan);
        color: var(--deep-black);
        transform: translateY(-3px);
    }

    .table thead th {
        border-bottom: 2px solid #eee;
        color: #888;
        font-weight: 700;
        font-size: 0.7rem;
    }
</style>

<div class="container dashboard-container">
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="icon-box">👥</div>
                <span class="stat-label">Total Enrollment</span>
                <div class="stat-value"><?php echo $total_students; ?></div>
                <p class="small text-muted mt-2 mb-0">Active student accounts</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card" style="border-right: 4px solid var(--royal-violet);">
                <div class="icon-box">🎓</div>
                <span class="stat-label">Academic Tracks</span>
                <div class="stat-value"><?php echo $total_classes; ?></div>
                <p class="small text-muted mt-2 mb-0">Certified AI curriculum</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card" style="border-right: 4px solid var(--electric-cyan);">
                <div class="icon-box">⚙️</div>
                <span class="stat-label">Administrative</span>
                <div class="d-grid gap-2 mt-3">
                    <a href="students.php?action=add" class="btn btn-custom">New Enrollment</a>
                    <a href="fees.php" class="btn btn-outline-dark border-2 fw-bold py-2 rounded-3">Fee Audit</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="data-section">
                <h5 class="fw-800 mb-4" style="font-weight: 800;">Visual Analytics</h5>
                <div style="height: 300px;">
                    <canvas id="studentChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="data-section h-100">
                <h5 class="fw-800 mb-4" style="font-weight: 800;">Course Summary</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-uppercase">
                                <th>Track Name</th>
                                <th class="text-end">Students</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($class_stats as $stat): ?>
                            <tr>
                                <td class="py-3 fw-semibold"><?php echo htmlspecialchars($stat['class_name']); ?></td>
                                <td class="text-end py-3">
                                    <span class="badge bg-dark rounded-pill px-3"><?php echo $stat['student_count']; ?></span>
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
    
    // Create subtle gradient for the area chart
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(0, 212, 255, 0.2)');
    gradient.addColorStop(1, 'rgba(0, 212, 255, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo $labels; ?>,
            datasets: [{
                label: 'Student Count',
                data: <?php echo $counts; ?>,
                fill: true,
                backgroundColor: gradient,
                borderColor: '#0a0a0a', // Deep Black line
                borderWidth: 4,
                tension: 0.4,
                pointRadius: 6,
                pointBackgroundColor: '#00d4ff',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { 
                    beginAtZero: true, // No negative values
                    min: 0,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { 
                        color: '#0a0a0a', 
                        stepSize: 1, 
                        font: { weight: 'bold', family: 'Inter' } 
                    }
                },
                x: { 
                    grid: { display: false },
                    ticks: { 
                        color: '#0a0a0a', 
                        font: { weight: 'bold', family: 'Inter' } 
                    }
                }
            }
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
