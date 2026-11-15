<?php
require_once __DIR__ . '/../includes/config.php';
sessionStart(); requireAuth('provider');
$user=auth();
$id=(int)($_GET['id']??0); $action=$_GET['action']??'';
$amount=(float)($_POST['amount']??0);

try {
  $prov=db()->prepare("SELECT id FROM providers WHERE user_id=?"); $prov->execute([$user['id']]); $prov=$prov->fetch();
  if(!$prov){ redirect(SITE_URL.'/provider/bookings.php','প্রোফাইল পাওয়া যায়নি।','error'); }

  $stmt=db()->prepare("SELECT * FROM bookings WHERE id=? AND provider_id=?");
  $stmt->execute([$id,$prov['id']]); $b=$stmt->fetch();
  if(!$b){ redirect(SITE_URL.'/provider/bookings.php','বুকিং পাওয়া যায়নি।','error'); }

  $newStatus=match($action){
    'approve'  => 'Approved',
    'complete' => 'Completed',
    'cancel'   => 'Cancelled',
    default    => null,
  };
  if(!$newStatus){ redirect(SITE_URL.'/provider/bookings.php','অবৈধ অ্যাকশন।','error'); }

  if($action === 'complete' && $amount > 0) {
    $serviceCharge = (int)($amount * 15 / 100);
    $revenue = $amount - $serviceCharge;
    $estimatedPrice = $amount;
    db()->prepare("UPDATE bookings SET status=?, amount=?, service_charge=?, revenue=?, estimated_price=? WHERE id=?")->execute([$newStatus,$amount,$serviceCharge,$revenue,$estimatedPrice,$id]);
  } else if($action === 'approve') {
    db()->prepare("UPDATE bookings SET status=? WHERE id=?")->execute([$newStatus,$id]);
  } else {
    db()->prepare("UPDATE bookings SET status=? WHERE id=?")->execute([$newStatus,$id]);
  }
  
  $msg=match($action){ 'approve'=>'বুকিং অনুমোদিত হয়েছে।','complete'=>'বুকিং সম্পন্ন হিসেবে চিহ্নিত।','cancel'=>'বুকিং বাতিল হয়েছে।',default=>'' };
  redirect(SITE_URL.'/provider/bookings.php',$msg);
} catch(Exception $e){ redirect(SITE_URL.'/provider/bookings.php','একটি ত্রুটি হয়েছে।','error'); }
?>
