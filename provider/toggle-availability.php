<?php
require_once __DIR__ . '/../includes/config.php';
sessionStart(); requireAuth('provider');
$user=auth();
try {
  $prov=db()->prepare("SELECT * FROM providers WHERE user_id=?"); $prov->execute([$user['id']]); $prov=$prov->fetch();
  if(!$prov){ redirect(SITE_URL.'/provider/dashboard.php','প্রোফাইল পাওয়া যায়নি।','error'); }
  $new=!$prov['available'];
  db()->prepare("UPDATE providers SET available=? WHERE id=?")->execute([$new?1:0,$prov['id']]);
  $msg=$new?'আপনি এখন উপলব্ধ হিসেবে চিহ্নিত।':'আপনি এখন অনুপলব্ধ হিসেবে চিহ্নিত।';
  redirect(SITE_URL.'/provider/dashboard.php',$msg);
} catch(Exception $e){ redirect(SITE_URL.'/provider/dashboard.php','ত্রুটি হয়েছে।','error'); }
?>
