<?php
require_once __DIR__ . '/includes/config.php';
sessionStart();
if (auth()) { header('Location: '.SITE_URL.'/index.php'); exit; }
$pageTitle='লগইন'; $activePage=''; $err='';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  csrfCheck();
  $email=trim($_POST['email']??''); $pass=$_POST['password']??'';
  try {
    $u=db()->prepare("SELECT * FROM users WHERE email=?"); $u->execute([$email]); $user=$u->fetch();
    
    // Debug removed - login fixed
    
    if ($user && password_verify($pass,$user['password'])) {
      $_SESSION['user']=['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role']];
$dest = $_GET['redirect'] ?? (isset($user['role']) && $user['role'] === 'admin' ? SITE_URL.'/admin/dashboard.php' : SITE_URL.'/'.$user['role'].'/dashboard.php');
      redirect($dest,'স্বাগতম, '.$user['name'].'! 👋');
    } else { $err='ইমেইল বা পাসওয়ার্ড সঠিক নয়।'; }
  } catch(Exception $e) { $err='একটি ত্রুটি হয়েছে। আবার চেষ্টা করুন।'; }
}
include __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
  <div class="auth-visual">
    <div class="auth-visual-img"><img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80" alt=""></div>
    <div class="auth-visual-inner">
      <div style="font-size:64px;margin-bottom:20px">🔧</div>
      <h2>সেবা বন্ধু-তে আপনাকে <br> পুনরায় স্বাগতম।</h2>
      <p>ঢাকার সেরা হোম সার্ভিস প্ল্যাটফর্মে লগইন করুন।</p>
      <div class="auth-visual-features">
        <div class="avf"><span>✅</span><span>যাচাইকৃত পেশাদার</span></div>
        <div class="avf"><span>⚡</span><span>মিনিটেই বুকিং</span></div>
        <div class="avf"><span>🛡️</span><span>১০০% সন্তুষ্টি গ্যারান্টি</span></div>
        <div class="avf"><span>💰</span><span>স্বচ্ছ মূল্য নির্ধারণ</span></div>
      </div>
    </div>
  </div>
  <div class="auth-form-area">
    <div class="auth-box">
      <div style="font-family:var(--ff-h);font-size:1.5rem;font-weight:900;margin-bottom:4px">সেবা<span style="color:var(--green)">বন্ধু</span></div>
      <h1 class="auth-title" style="margin-top:20px">লগইন করুন</h1>
      <p class="auth-sub">আপনার অ্যাকাউন্টে প্রবেশ করুন</p>
      <?php if($err): ?><div class="form-err">❌ <?= e($err) ?></div><?php endif; ?>
      <form method="POST">
        <input type="hidden" name="csrf" value="<?= csrf() ?>">
        <div class="form-group">
          <label>ইমেইল ঠিকানা</label>
          <input type="email" name="email" placeholder="আপনার ইমেইল" required value="<?= e($_POST['email']??'') ?>">
        </div>
        <div class="form-group">
          <label>পাসওয়ার্ড</label>
          <input type="password" name="password" placeholder="আপনার পাসওয়ার্ড" required>
        </div>
        <div style="text-align:right;margin-bottom:18px">
          <a href="forgot-password.php" style="font-size:13px;color:var(--green);font-weight:600">পাসওয়ার্ড ভুলে গেছেন?</a>
        </div>
        <button type="submit" class="form-submit">লগইন করুন →</button>
      </form>

      <!-- Demo credentials -->
      <div style="margin-top:20px;padding:14px;background:rgba(99,102,241,.06);border-radius:var(--r);border:1px solid rgba(99,102,241,.15);font-size:12px;color:var(--ink-3)">
        <div style="font-weight:700;margin-bottom:8px;color:var(--ink-2)">🔑 ডেমো অ্যাকাউন্ট:</div>
        <div>অ্যাডমিন: <strong>admin@handygo.com</strong> / <strong>admin123</strong></div>
        <div>গ্রাহক: <strong>rahela@example.com</strong> / <strong>test123</strong></div>
        <div>সেবাদাতা: <strong>royal@handygo.com</strong> / <strong>test123</strong></div>
      </div>

      <div class="auth-link">অ্যাকাউন্ট নেই? <a href="signup.php">বিনামূল্যে তৈরি করুন</a></div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
