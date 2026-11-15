<?php
require_once __DIR__ . '/includes/config.php';
sessionStart();

$tranId = $_POST['tran_id'] ?? '';

if ($tranId) {
    db()->prepare("UPDATE bookings SET payment_status = 'unpaid' WHERE ref_code = ?")->execute([$tranId]);
}

$_SESSION['flash'] = ['msg' => 'পেমেন্ট ব্যর্থ হয়েছে।', 'type' => 'error'];
header('Location: ' . SITE_URL . '/customer/bookings.php');
exit;