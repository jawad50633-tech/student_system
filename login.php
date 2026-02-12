<?php
require_once 'config.php';

if (isset($_SESSION['admin_logged_in'])) {
    header('Location: admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Security Note: In production, avoid hardcoding passwords like 'admin123'
    if ($username === 'admin' && ($password === 'admin123' || password_verify($password, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'))) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = 'admin';
        header('Location: admin/dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | AI Future Leaders Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            /* Using your logo as a fixed background */
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('Logo Web.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #fff;
        }

        /* Glassmorphism Effect */
        .login-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.8);
            width: 100%;
            max-width: 450px;
            margin: auto;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo-container img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 2px solid #00d4ff;
            box-shadow: 0 0 15px #00d4ff;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            border-radius: 10px;
            padding: 12px;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            border-color: #00d4ff;
            box-shadow: 0 0 8px rgba(0, 212, 255, 0.5);
        }

        .btn-primary {
            background: linear-gradient(45deg, #00d4ff, #0055ff);
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 212, 255, 0.4);
        }

        label {
            font-size: 0.9rem;
            color: #ddd;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="logo-container">
            <img src="uploads\Logo Web.png" alt="Logo">
            <h2 class="mt-3" style="font-weight: 300;">Academy Portal</h2>
            <p class="text-muted small">Empowering Minds for the Future</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2" style="font-size: 0.85rem;"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter admin ID" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">Login to Dashboard</button>
            <div class="text-center">
                <a href="#" class="text-decoration-none small text-white-50">Forgot Credentials?</a>
            </div>
        </form>
    </div>

</body>
</html>
