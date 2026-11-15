<?php
require_once __DIR__ . '/includes/config.php';
sessionStart();

$tranId = $_POST['tran_id'] ?? '';

$_SESSION['flash'] = ['msg' => 'পেমেন্ট বাতিল হয়েছে।', 'type' => 'error'];
header('Location: ' . SITE_URL . '/customer/bookings.php');
exit;