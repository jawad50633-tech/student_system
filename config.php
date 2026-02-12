<?php
// Database configuration for InfinityFree
// Note: Replace these with your actual InfinityFree DB credentials
define('DB_HOST', 'sql208.infinityfree.com');
define('DB_USER', 'if0_40816501');
define('DB_PASS', 'lirW3R3fNvdaIU');
define('DB_NAME', 'if0_40816501_db');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // In production, you might want to log this instead of showing it
    // die("Connection failed: " . $e->getMessage());
    die("Database connection error. Please check your config.php settings.");
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Base URL (adjust if needed)
define('BASE_URL', '/');
?>
