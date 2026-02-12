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

include '../includes/header.php';
?>

<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.1);
        --neon-blue: #00d4ff;
        --neon-green: #39ff14;
        --neon-purple: #9d50bb;
    }

    body {
        background: #0f0c29; /* Dark space theme to match your logo */
        color: white;
    }

    /* Floating Animation */
    @keyframes float {
        0% { transform: translateY(0px) rotateX(0deg); }
        50% { transform: translateY(-10px) rotateX(2deg); }
        100% { transform: translateY(0px) rotateX(0deg); }
    }

    .stat-card-container {
        perspective: 1000px; /* Essential for 3D effect */
    }

    .card-3d {
        transition: transform 0.5s cubic-bezier(0.23, 1, 0.32, 1), box-shadow 0.5s;
        transform-style: preserve-3d;
        border: none;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        animation: float 4s ease-in-out infinite;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Apply different delays so they don't move in perfect sync */
    .delay-1 { animation-delay: 0.5s; }
    .delay-2 { animation-delay: 1s; }

    .card-3d:hover {
        transform: rotateY(10deg) rotateX(5deg) scale(1.05);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6), 0 0 20px rgba(0, 212, 255, 0.2);
    }

    .card-body {
        transform: translateZ(50px); /* Pushes content "out" of the card */
    }

    .display-4 {
        font-weight: 800;
        text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
    }

    /* Custom Border Glows */
    .border-student { border-left: 5px solid var(--neon-blue); }
    .border-class { border-left: 5px solid var(--neon-green); }
    .border-action { border-left: 5px solid var(--neon-purple); }

    .table { color: #fff; }
    .card-header { border-bottom: 1px solid rgba(255,255,255,0.1) !important; }
</style>

<div class="container mt-5">
    <div class="row mb-5">
        <div class="col-md-4 stat-card-container">
            <div class="card card-3d border-student shadow h-100">
                <div class="card-body text-center">
                    <h5 class="text-uppercase small tracking-widest text-white-50">Total Students</h5>
                    <h2 class="display-4 text-info"><?php echo $total_students; ?></h2>
                    <div class="mt-2" style="height: 3px; background: var(--neon-blue); width: 50%; margin: auto; border-radius: 10px;"></div>
                </div>
            </div>
        </div>

        <div class="col-md-4 stat-card-container">
            <div class="card card-3d border-class delay-1 shadow h-100">
                <div class="card-body text-center">
                    <h5 class="text-uppercase small tracking-widest text-white-50">Total Classes</h5>
                    <h2 class="display-4 text-success"><?php echo $total_classes; ?></h2>
                    <div class="mt-2" style="height: 3px; background: var(--neon-green); width: 50%; margin: auto; border-radius: 10px;"></div>
                </div>
            </div>
        </div>

        <div class="col-md-4 stat-card-container">
            <div class="card card-3d border-action delay-2 shadow h-100">
                <div class="card-body">
                    <h5 class="text-uppercase small tracking-widest text-white-50 text-center">Quick Actions</h5>
                    <div class="d-grid gap-2 mt-3">
                        <a href="students.php?action=add" class="btn btn-outline-light btn-sm rounded-pill">Add New Student</a>
                        <a href="fees.php" class="btn btn-outline-light btn-sm rounded-pill">Manage Fees</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-3d p-3" style="animation: none;">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0 text-white">Class Distribution</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mt-3">
                        <thead class="text-white-50">
                            <tr>
                                <th>Class Name</th>
                                <th>Student Population</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <?php foreach ($class_stats as $stat): ?>
                            <tr style="vertical-align: middle;">
                                <td><span class="badge bg-primary me-2">AI</span> <?php echo htmlspecialchars($stat['class_name']); ?></td>
                                <td>
                                    <div class="progress bg-dark" style="height: 8px; width: 150px;">
                                        <div class="progress-bar bg-info" style="width: <?php echo ($stat['student_count'] * 10); ?>%"></div>
                                    </div>
                                    <small class="text-white-50"><?php echo $stat['student_count']; ?> Students</small>
                                </td>
                                <td><a href="students.php?class=<?php echo urlencode($stat['class_name']); ?>" class="btn btn-sm btn-link text-info">Analyze Details</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>