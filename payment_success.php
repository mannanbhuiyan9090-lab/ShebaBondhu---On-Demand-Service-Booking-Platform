<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/ssl_payment.php';
sessionStart();

$tranId = $_POST['tran_id'] ?? '';
$valId = $_POST['val_id'] ?? '';

if ($tranId && $valId) {
    $verify = sslVerifyPayment($tranId);
    
    if ($verify['verified']) {
        db()->prepare("UPDATE bookings SET payment_status = 'paid' WHERE ref_code = ?")->execute([$tranId]);
        
        $stmt = db()->prepare("SELECT b.*, u.email, u.phone FROM bookings b JOIN users u ON u.id = b.customer_id WHERE b.ref_code = ?");
        $stmt->execute([$tranId]);
        $booking = $stmt->fetch();
        
        if ($booking) {
            $_SESSION['flash'] = ['msg' => 'পেমেন্ট সফল হয়েছে!', 'type' => 'success'];
            header('Location: ' . SITE_URL . '/customer/bookings.php');
            exit;
        }
    }
}

$_SESSION['flash'] = ['msg' => 'পেমেন্ট যাচাই হয়নি।', 'type' => 'error'];
header('Location: ' . SITE_URL . '/customer/bookings.php');
exit;