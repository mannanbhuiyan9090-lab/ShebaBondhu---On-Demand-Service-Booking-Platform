<?php
declare(strict_types=1);
// সেবা বন্ধু Pro Admin - Main Entry Point
// Redirects to dashboard with login check

require_once __DIR__ . '/../includes/config.php';
sessionStart();

if (!auth() || auth()['role'] !== 'admin') {
    header('Location: ' . SITE_URL . '/login.php?redirect=admin');
    exit;
}

header('Location: dashboard.php');
exit;
?>

