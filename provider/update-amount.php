<?php
require_once __DIR__ . '/../includes/config.php';
sessionStart(); requireAuth('provider');
$user=auth();
$id=(int)($_POST['id']??0);
$amount=(float)($_POST['amount']??0);

try {
  $prov=db()->prepare("SELECT id FROM providers WHERE user_id=?"); $prov->execute([$user['id']]); $prov=$prov->fetch();
  if(!$prov){ redirect(SITE_URL.'/provider/bookings.php','প্রোফাইল পাওয়া যায়নি।','error'); }

  $stmt=db()->prepare("SELECT * FROM bookings WHERE id=? AND provider_id=?");
  $stmt->execute([$id,$prov['id']]); $b=$stmt->fetch();
  if(!$b){ redirect(SITE_URL.'/provider/bookings.php','বুকিং পাওয়া যায়নি।','error'); }

  if($b['status'] !== 'Completed'){ redirect(SITE_URL.'/provider/bookings.php','শুধুমাত্র সম্পন্ন বুকিং এডিট করা যায়।','error'); }

  db()->prepare("UPDATE bookings SET amount=? WHERE id=?")->execute([$amount,$id]);
  redirect(SITE_URL.'/provider/bookings.php','অ্যামাউন্ট আপডেট হয়েছে।');
} catch(Exception $e){ redirect(SITE_URL.'/provider/bookings.php','একটি ত্রুটি হয়েছে।','error'); }
?>