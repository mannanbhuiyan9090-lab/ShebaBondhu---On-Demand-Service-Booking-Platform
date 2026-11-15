<?php
require_once __DIR__ . '/includes/config.php';
sessionStart();
if(auth()){ header('Location: '.SITE_URL.'/index.php'); exit; }
$pageTitle='পাসওয়ার্ড পুনরুদ্ধার'; $activePage=''; $msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email=trim($_POST['email']??'');
  if($email){ $msg='যদি এই ইমেইলে অ্যাকাউন্ট থাকে, একটি পুনরুদ্ধার লিংক পাঠানো হবে।'; }
  else { $msg='ইমেইল ঠিকানা দিন।'; }
}
include __DIR__ . '/includes/header.php';
?>
<div style="min-height:80vh;display:flex;align-items:center;justify-content:center;padding:40px 5vw">
  <div class="auth-box" style="max-width:440px;width:100%">
    <div style="font-family:var(--ff-h);font-size:1.5rem;font-weight:900;margin-bottom:4px;text-align:center">সেবা<span style="color:var(--green)">বন্ধু</span></div>
    <h1 class="auth-title" style="margin-top:20px">পাসওয়ার্ড পুনরুদ্ধার</h1>
    <p class="auth-sub">আপনার ইমেইল দিন, পুনরুদ্ধারের লিংক পাঠানো হবে।</p>
    <?php if($msg): ?><div class="form-ok">📧 <?= e($msg) ?></div><?php endif; ?>
    <form method="POST">
      <div class="form-group"><label>ইমেইল ঠিকানা</label><input type="email" name="email" placeholder="আপনার ইমেইল" required></div>
      <button type="submit" class="form-submit">লিংক পাঠান →</button>
    </form>
    <div class="auth-link"><a href="login.php">← লগইনে ফিরুন</a></div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
