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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    body {
        background-color: #060b28; /* Deep Navy from logo background */
        color: #e0e0e0;
        font-family: 'Inter', sans-serif;
    }

    /* 3D Glassmorphism Cards */
    .stat-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        transition: all 0.4s ease;
        perspective: 1000px;
        overflow: hidden;
        animation: float 5s ease-in-out infinite;
    }

    .stat-card:hover {
        transform: translateY(-10px) rotateX(5deg);
        border-color: #00d4ff;
        box-shadow: 0 15px 35px rgba(0, 212, 255, 0.2);
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    /* Logo Theme Gradients */
    .text-cyan { color: #00d4ff; }
    .text-violet { color: #9d50bb; }
    .bg-gradient-academy {
        background: linear-gradient(135deg, #00d4ff 0%, #9d50bb 100%);
    }

    .icon-box {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 15px;
    }
</style>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-md-4">
            <div class="card stat-card p-4">
                <div class="icon-box bg-gradient-academy text-white">
                    <i>👥</i>
                </div>
                <h6 class="text-uppercase text-white-50 small">Total Enrollment</h6>
                <h2 class="display-5 fw-bold text-cyan"><?php echo $total_students; ?></h2>
                <p class="small mb-0">Active Students in Academy</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stat-card p-4" style="animation-delay: 0.5s;">
                <div class="icon-box" style="background: rgba(157, 80, 187, 0.2); color: #9d50bb; border: 1px solid #9d50bb;">
                    <i>🎓</i>
                </div>
                <h6 class="text-uppercase text-white-50 small">Active Courses</h6>
                <h2 class="display-5 fw-bold text-violet"><?php echo $total_classes; ?></h2>
                <p class="small mb-0">Ongoing Learning Tracks</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stat-card p-4" style="animation-delay: 1s;">
                <div class="icon-box" style="background: rgba(255, 255, 255, 0.1); color: #fff;">
                    <i>⚙️</i>
                </div>
                <h6 class="text-uppercase text-white-50 small">System Status</h6>
                <div class="d-flex flex-column gap-2 mt-2">
                    <a href="students.php?action=add" class="btn btn-sm btn-outline-info rounded-pill">Enroll Student</a>
                    <a href="fees.php" class="btn btn-sm btn-outline-light rounded-pill">Audit Fees</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card stat-card p-4" style="animation: none;">
                <h5 class="mb-4 text-white">Student Distribution per Course</h5>
                <canvas id="studentChart" height="150"></canvas>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card stat-card p-4 h-100" style="animation: none;">
                <h5 class="mb-4 text-white">Course Summary</h5>
                <div class="table-responsive">
                    <table class="table table-borderless text-white-50">
                        <thead>
                            <tr class="small text-uppercase">
                                <th>Track</th>
                                <th class="text-end">Pop.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($class_stats as $stat): ?>
                            <tr class="border-bottom border-secondary">
                                <td class="py-3 text-white"><?php echo htmlspecialchars($stat['class_name']); ?></td>
                                <td class="text-end text-cyan fw-bold"><?php echo $stat['student_count']; ?></td>
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
        type: 'bar',
        data: {
            labels: <?php echo $labels; ?>,
            datasets: [{
                label: 'Number of Students',
                data: <?php echo $counts; ?>,
                backgroundColor: 'rgba(0, 212, 255, 0.5)',
                borderColor: '#00d4ff',
                borderWidth: 2,
                borderRadius: 10,
                hoverBackgroundColor: '#9d50bb'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: 'rgba(255,255,255,0.05)' },
                    ticks: { color: '#888' }
                },
                x: { 
                    grid: { display: false },
                    ticks: { color: '#888' }
                }
            }
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
