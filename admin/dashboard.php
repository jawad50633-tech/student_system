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

<style>
    /* Professional Logo-Themed Color Palette */
    :root {
        --deep-navy: #060b28;
        --electric-cyan: #00d4ff;
        --royal-violet: #9d50bb;
        --glass-white: rgba(255, 255, 255, 0.05);
        --border-glow: rgba(0, 212, 255, 0.3);
    }

    body {
        background: linear-gradient(rgba(6, 11, 40, 0.9), rgba(6, 11, 40, 0.9)), 
                    url('../uploads/background.png') no-repeat center center fixed;
        background-size: cover;
        color: #ffffff;
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    .container { perspective: 1500px; }

    /* Professional 3D Card Styling */
    .stat-card {
        background: var(--glass-white);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 24px;
        padding: 2rem;
        transition: all 0.5s cubic-bezier(0.2, 0.8, 0.2, 1);
        transform-style: preserve-3d;
        height: 100%;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }

    /* 3D Hover Effect */
    .stat-card:hover {
        transform: rotateX(7deg) rotateY(-7deg) translateY(-10px);
        border-color: var(--electric-cyan);
        box-shadow: 0 20px 40px rgba(0, 212, 255, 0.25);
        background: rgba(255, 255, 255, 0.08);
    }

    /* Card Content Depth */
    .card-content { transform: translateZ(50px); }

    .icon-glow {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 20px;
        background: rgba(0, 212, 255, 0.1);
        border: 1px solid var(--electric-cyan);
        box-shadow: 0 0 15px var(--border-glow);
        color: var(--electric-cyan);
    }

    .violet-glow {
        background: rgba(157, 80, 187, 0.1);
        border: 1px solid var(--royal-violet);
        box-shadow: 0 0 15px rgba(157, 80, 187, 0.3);
        color: var(--royal-violet);
    }

    .stat-value {
        font-size: 2.8rem;
        font-weight: 800;
        letter-spacing: -1px;
        margin-bottom: 5px;
        background: linear-gradient(to right, #fff, var(--electric-cyan));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .stat-label {
        color: rgba(255, 255, 255, 0.7);
        text-transform: uppercase;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 1.5px;
    }

    /* Chart and Table Containers */
    .data-container {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
        padding: 25px;
        margin-top: 30px;
    }

    .btn-action {
        background: linear-gradient(135deg, var(--electric-cyan), #0055ff);
        border: none;
        color: white;
        font-weight: 600;
        border-radius: 12px;
        padding: 10px 20px;
        transition: 0.3s;
    }

    .btn-action:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(0, 212, 255, 0.4);
        color: white;
    }
</style>

<div class="container py-5">
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="card-content">
                    <div class="icon-glow">👥</div>
                    <h6 class="stat-label">Total Enrollment</h6>
                    <div class="stat-value"><?php echo $total_students; ?></div>
                    <p class="small text-white-50">Registered students in system</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="card-content">
                    <div class="icon-glow violet-glow">🎓</div>
                    <h6 class="stat-label">Active Courses</h6>
                    <div class="stat-value" style="background: linear-gradient(to right, #fff, var(--royal-violet)); -webkit-background-clip: text;"><?php echo $total_classes; ?></div>
                    <p class="small text-white-50">Specialized AI tracks</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="card-content text-center">
                    <div class="icon-glow mx-auto" style="border-color: #fff; color: #fff; box-shadow: none;">⚙️</div>
                    <h6 class="stat-label mb-3">Control Center</h6>
                    <div class="d-grid gap-2">
                        <a href="students.php?action=add" class="btn btn-action btn-sm">New Enrollment</a>
                        <a href="fees.php" class="btn btn-outline-light btn-sm rounded-3">Fee Management</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="data-container">
                <h5 class="mb-4 d-flex align-items-center">
                    <span class="badge bg-info me-2" style="width: 10px; height: 10px; border-radius: 50%;">&nbsp;</span>
                    Class Distribution Growth
                </h5>
                <canvas id="studentChart" height="140"></canvas>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="data-container h-100">
                <h5 class="mb-4">Course Insights</h5>
                <div class="table-responsive">
                    <table class="table table-borderless align-middle text-white-50">
                        <thead>
                            <tr class="small border-bottom border-white-10">
                                <th class="pb-3">TRACK</th>
                                <th class="text-end pb-3">COUNT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($class_stats as $stat): ?>
                            <tr class="border-bottom border-white-10">
                                <td class="py-3 fw-bold text-white"><?php echo htmlspecialchars($stat['class_name']); ?></td>
                                <td class="text-end py-3">
                                    <span class="badge rounded-pill" style="background: rgba(0, 212, 255, 0.1); color: var(--electric-cyan); border: 1px solid var(--electric-cyan);">
                                        <?php echo $stat['student_count']; ?>
                                    </span>
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
    
    // Gradient for Chart
    const chartGradient = ctx.createLinearGradient(0, 0, 0, 400);
    chartGradient.addColorStop(0, 'rgba(0, 212, 255, 0.4)');
    chartGradient.addColorStop(1, 'rgba(157, 80, 187, 0.05)');

    new Chart(ctx, {
        type: 'line', // Line chart looks more professional for "Growth"
        data: {
            labels: <?php echo $labels; ?>,
            datasets: [{
                label: 'Student Count',
                data: <?php echo $counts; ?>,
                fill: true,
                backgroundColor: chartGradient,
                borderColor: '#00d4ff',
                borderWidth: 3,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#00d4ff',
                pointHoverRadius: 6,
                tension: 0.4 // Smooth curves
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: 'rgba(255,255,255,0.05)' },
                    ticks: { color: 'rgba(255,255,255,0.5)' }
                },
                x: { 
                    grid: { display: false },
                    ticks: { color: 'rgba(255,255,255,0.5)' }
                }
            }
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
