<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | AI Future Leaders Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(rgba(6, 11, 40, 0.75), rgba(6, 11, 40, 0.75)), 
                        url('uploads/background.png') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #fff;
            margin: 0;
        }

        /* Glassmorphism Effect */
        .login-card {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 420px;
            margin: auto;
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
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
            padding: 5px;
            background: rgba(0, 0, 0, 0.2);
        }

        .form-control {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff !important;
            border-radius: 12px;
            padding: 12px 15px;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #00d4ff;
            box-shadow: 0 0 0 0.25rem rgba(0, 212, 255, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, #00d4ff 0%, #0055ff 100%);
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            box-shadow: 0 10px 20px rgba(0, 212, 255, 0.3);
            transform: scale(1.02);
        }

        .text-muted-custom {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="logo-container">
            <img src="uploads/Logo Web.png" alt="Academy Logo">
            <h3 class="mt-3 fw-bold">Academy Portal</h3>
            <p class="text-muted-custom">AI Future Leaders Academy</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger bg-danger text-white border-0 py-2 small"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="form-label small text-white-50">Admin Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter username" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label small text-white-50">Secret Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">Authorize Access</button>
            <div class="text-center">
                <a href="#" class="text-decoration-none small text-white-50">Access Recovery</a>
            </div>
        </form>
    </div>

</body>
</html>
