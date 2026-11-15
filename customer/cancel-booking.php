<?php
require_once __DIR__ . '/../includes/config.php';
sessionStart(); requireAuth('customer');
$user=auth();
$id=(int)($_GET['id']??0);
if(!$id){ redirect(SITE_URL.'/customer/bookings.php','অবৈধ বুকিং।','error'); }
try {
  $stmt=db()->prepare("SELECT * FROM bookings WHERE id=? AND customer_id=? AND status='Pending'");
  $stmt->execute([$id,$user['id']]); $b=$stmt->fetch();
  if(!$b){ redirect(SITE_URL.'/customer/bookings.php','বুকিং বাতিল করা সম্ভব নয়।','error'); }
  db()->prepare("UPDATE bookings SET status='Cancelled' WHERE id=?")->execute([$id]);
  redirect(SITE_URL.'/customer/bookings.php','বুকিং বাতিল করা হয়েছে।');
} catch(Exception $e){ redirect(SITE_URL.'/customer/bookings.php','একটি ত্রুটি হয়েছে।','error'); }
