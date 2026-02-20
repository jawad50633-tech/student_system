<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Future Leaders Academy - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --academy-cyan: #00d4ff;
            --deep-dark: #060b28;
            --glass-white: rgba(255, 255, 255, 0.95);
        }

        body { 
            background: linear-gradient(rgba(6, 11, 40, 0.92), rgba(6, 11, 40, 0.92)), url('../uploads/background.png');
            background-size: cover;
            background-attachment: fixed;
            font-family: 'Inter', sans-serif;
            padding-top: 100px; /* Space for the fixed header */
        }

        /* 3D Glass Header Bar */
        .navbar-custom {
            background: var(--glass-white);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-bottom: 3px solid var(--academy-cyan);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3), inset 0 -2px 5px rgba(0,0,0,0.05);
            padding: 12px 0;
            position: fixed;
            top: 20px; /* Floating look */
            left: 20px;
            right: 20px;
            border-radius: 20px;
            z-index: 1050;
            transition: all 0.3s ease;
        }

        /* Logo Styling */
        .header-logo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid var(--academy-cyan);
            box-shadow: 0 4px 10px rgba(0, 212, 255, 0.3);
            margin-right: 15px;
        }

        /* Center Title: 3D & Stylish */
        .academy-center-title {
            position: absolute;
            left: 40%;
            transform: translateX(-60%); 
            color: #000;
            font-weight: 800;
            font-size: 1.6rem;
            letter-spacing: -1.5px;
            text-transform: uppercase;
            margin: 0;
            text-shadow: 1px 1px 0px #fff, 2px 2px 0px rgba(0,0,0,0.1);
            white-space: nowrap; /* Prevents text from wrapping */
        }

        /* Nav Links */
        .nav-link-custom {
            color: #000 !important;
            font-weight: 700;
            font-size: 0.9rem;
            padding: 8px 18px !important;
            border-radius: 50px;
            transition: 0.3s;
            display: flex;
            align-items: center;
        }

        .nav-link-custom:hover {
            background: rgba(0, 212, 255, 0.1);
            color: var(--academy-cyan) !important;
            transform: translateY(-2px);
        }

        .nav-link-custom i {
            font-size: 1.1rem;
            margin-right: 8px;
        }

        /* Logout Button - Unique 3D Style */
        .btn-logout {
            background: #000;
            color: #fff !important;
            border-radius: 50px;
            padding: 8px 20px !important;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .btn-logout:hover {
            background: #ff4d4d;
            transform: scale(1.05);
        }

        @media (max-width: 992px) {
            .academy-center-title { display: none; }
            .navbar-custom { top: 0; left: 0; right: 0; border-radius: 0; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
            <img src="../uploads/Logo_Academy.png" class="header-logo" alt="Logo">
            <span style="color: #000; font-weight: 800; letter-spacing: -1px;">Admin Panel</span>
        </a>

        <div class="academy-center-title d-none d-lg-block">
            AI Future Leaders Academy
        </div>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link nav-link-custom" href="dashboard.php">
                        <i class="bi bi-grid-fill"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom" href="students.php">
                        <i class="bi bi-people-fill"></i> Students
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom" href="fees.php">
                        <i class="bi bi-wallet2"></i> Fees
                    </a>
                </li>
                <li class="nav-item ms-lg-3">
                    <a class="nav-link btn-logout" href="../logout.php">
                        <i class="bi bi-power me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <main class="col-12 px-md-4">
