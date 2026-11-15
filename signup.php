<?php
require_once __DIR__ . '/includes/config.php';
sessionStart();
if (auth()) { header('Location: '.SITE_URL.'/index.php'); exit; }
$pageTitle='সাইন আপ'; $activePage=''; $err='';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  csrfCheck();
  $name=trim($_POST['name']??''); $email=trim($_POST['email']??'');
  $phone=trim($_POST['phone']??''); $pass=$_POST['password']??''; $pass2=$_POST['password2']??'';
  if($pass!==$pass2)        { $err='পাসওয়ার্ড মিলছে না।'; }
  elseif(strlen($pass)<6)   { $err='পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।'; }
  elseif(!$name||!$email)   { $err='নাম ও ইমেইল আবশ্যক।'; }
  else {
    try {
      $chk=db()->prepare("SELECT id FROM users WHERE email=?"); $chk->execute([$email]);
      if($chk->fetch()) { $err='এই ইমেইলে অ্যাকাউন্ট আছে।'; }
      else {
        $hash=password_hash($pass,PASSWORD_BCRYPT);
        db()->prepare("INSERT INTO users (name,email,phone,password,role) VALUES (?,?,?,?,'customer')")->execute([$name,$email,$phone,$hash]);
        $uid=db()->lastInsertId();
        $u=db()->prepare("SELECT * FROM users WHERE id=?"); $u->execute([$uid]); $user=$u->fetch();
        $_SESSION['user']=['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role']];
        redirect(SITE_URL.'/customer/dashboard.php','সেবা বন্ধু-তে স্বাগতম, '.$name.'! 🎉');
      }
    } catch(Exception $e) { $err='একটি ত্রুটি হয়েছে। আবার চেষ্টা করুন।'; }
  }
}
include __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
  <div class="auth-visual">
    <div class="auth-visual-img"><img src="https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=800&q=80" alt=""></div>
    <div class="auth-visual-inner">
      <div style="font-size:64px;margin-bottom:20px">🏠</div>
      <h2>সেবা বন্ধু-তে<br>যোগ দিন</h2>
      <p>ঢাকার সেরা পেশাদারদের সাথে যুক্ত হন এবং আপনার বাড়ির প্রতিটি কাজ সহজ করুন।</p>
      <div class="auth-visual-features">
        <div class="avf"><span>🆓</span><span>বিনামূল্যে নিবন্ধন</span></div>
        <div class="avf"><span>⭐</span><span>রিভিউ দেখে বেছে নিন</span></div>
        <div class="avf"><span>📱</span><span>যেকোনো ডিভাইস থেকে</span></div>
      </div>
    </div>
  </div>
  <div class="auth-form-area">
    <div class="auth-box">
      <div style="font-family:var(--ff-h);font-size:1.5rem;font-weight:900;margin-bottom:4px">সেবা<span style="color:var(--green)">বন্ধু</span></div>
      <h1 class="auth-title" style="margin-top:20px">অ্যাকাউন্ট তৈরি করুন</h1>
      <p class="auth-sub">বিনামূল্যে শুরু করুন</p>
      <?php if($err): ?><div class="form-err">❌ <?= e($err) ?></div><?php endif; ?>
      <form method="POST">
        <input type="hidden" name="csrf" value="<?= csrf() ?>">
        <div class="form-group"><label>পূর্ণ নাম *</label><input type="text" name="name" placeholder="আপনার নাম" required value="<?= e($_POST['name']??'') ?>"></div>
        <div class="form-row">
          <div class="form-group"><label>ইমেইল *</label><input type="email" name="email" placeholder="email@example.com" required value="<?= e($_POST['email']??'') ?>"></div>
          <div class="form-group"><label>মোবাইল</label><input type="tel" name="phone" placeholder="017XXXXXXXX" value="<?= e($_POST['phone']??'') ?>"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>পাসওয়ার্ড *</label><input type="password" name="password" placeholder="কমপক্ষে ৬ অক্ষর" required></div>
          <div class="form-group"><label>পাসওয়ার্ড নিশ্চিত *</label><input type="password" name="password2" placeholder="আবার লিখুন" required></div>
        </div>
        <button type="submit" class="form-submit">অ্যাকাউন্ট তৈরি করুন →</button>
      </form>
      <div class="auth-link">ইতিমধ্যে অ্যাকাউন্ট আছে? <a href="login.php">লগইন করুন</a></div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
