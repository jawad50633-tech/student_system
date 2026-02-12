<?php
// Start the session to track login state
session_start();
require_once 'config.php';

// 1. REDIRECT IF ALREADY LOGGED IN
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin/dashboard.php');
    exit;
}

$error = '';

// 2. LOGIN LOGIC
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validating against the hardcoded admin or the hashed password provided in your original snippet
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
    <title>Login | AI Future Leaders Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            /* Path set to your local uploads folder */
            background: linear-gradient(rgba(6, 11, 40, 0.75), rgba(6, 11, 40, 0.75)), 
                        url('uploads/background.png') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', sans-serif;
            color: #fff;
            margin: 0;
            overflow: hidden;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 420px;
            margin: auto;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-container img {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            border: 2px solid #00d4ff;
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.4);
            background: rgba(0, 0, 0, 0.3);
        }

        .form-control {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff !important;
            border-radius: 12px;
            padding: 12px 15px;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #00d4ff;
            box-shadow: 0 0 8px rgba(0, 212, 255, 0.3);
        }

        .btn-primary {
            background: linear-gradient(135deg, #00d4ff 0%, #0055ff 100%);
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-primary:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 20px rgba(0, 212, 255, 0.3);
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="logo-container">
            <img src="uploads/Logo Web.png" alt="Academy Logo">
            <h3 class="mt-3 fw-bold">AI Future Leaders Academy</h3>
            <p style="color: rgba(255,255,255,0.6); font-size: 0.9rem;">Empowering Young Talent</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small" style="background: rgba(220, 53, 69, 0.2); border: 1px solid #dc3545; color: #ff8e98;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="form-label small text-white-50">Admin Username</label>
                <input type="text" name="username" class="form-control" placeholder="admin" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label small text-white-50">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login to Dashboard</button>
        </form>
    </div>

</body>
</html>
