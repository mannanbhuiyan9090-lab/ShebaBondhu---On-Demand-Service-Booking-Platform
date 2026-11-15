<?php
sessionStart();
$user       = auth();
$pageTitle  = $pageTitle ?? SITE_NAME;
$activePage = $activePage ?? '';
$flash      = flash();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> — সেবা বন্ধু</title>
<meta name="description" content="সেবা বন্ধু — ঢাকার সেরা হোম সার্ভিস প্ল্যাটফর্ম। যাচাইকৃত পেশাদার, তাৎক্ষণিক বুকিং।">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Noto+Sans+Bengali:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/main.css">
</head>
<body>

<?php if ($flash): ?>
<div id="flash-banner"><?= $flash ?></div>
<?php endif; ?>

<nav id="mainNav">
  <!-- Logo -->
  <a class="nav-logo" href="<?= SITE_URL ?>/index.php">
    <div class="nav-logo-mark">
      <svg viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
    </div>
    <div>
      <div class="footer-brand-name" style="color: black;">সেবা<em>বন্ধু</em></div>
      <div class="nav-logo-sub">ঢাকার হোম সার্ভিস</div>
    </div>
  </a>

  <button class="nav-burger" id="navBurger" aria-label="মেনু"><span></span><span></span><span></span></button>

  <div class="nav-links" id="navLinks">
    <a href="<?= SITE_URL ?>/index.php"             class="<?= $activePage==='home'     ?'active':'' ?>">হোম</a>
    <a href="<?= SITE_URL ?>/services.php"          class="<?= $activePage==='services' ?'active':'' ?>">সেবাসমূহ</a>
    <a href="<?= SITE_URL ?>/about.php"             class="<?= $activePage==='about'    ?'active':'' ?>">আমাদের সম্পর্কে</a>
    <a href="<?= SITE_URL ?>/contact.php"           class="<?= $activePage==='contact'  ?'active':'' ?>">যোগাযোগ</a>
    <a href="<?= SITE_URL ?>/register-provider.php" class="<?= $activePage==='register' ?'active':'' ?>">সেবা নিবন্ধন</a>
  </div>

  <div class="nav-actions">
    <?php if ($user): ?>
      <div class="user-chip">
        <div class="chip-avatar"><?= strtoupper(substr($user['name'],0,2)) ?></div>
        <div>
          <div class="chip-name"><?= e(explode(' ',$user['name'])[0]) ?></div>
          <div class="chip-role"><?= match($user['role']){'customer'=>'গ্রাহক','provider'=>'সেবাদাতা','admin'=>'অ্যাডমিন',default=>$user['role']} ?></div>
        </div>
        <div class="chip-menu">
          <?php if ($user['role']==='customer'): ?>
            <a href="<?= SITE_URL ?>/customer/dashboard.php"><span>📊</span> ড্যাশবোর্ড</a>
            <a href="<?= SITE_URL ?>/customer/bookings.php"><span>📅</span> আমার বুকিং</a>
            <a href="<?= SITE_URL ?>/customer/profile.php"><span>👤</span> প্রোফাইল</a>
          <?php elseif ($user['role']==='provider'): ?>
            <a href="<?= SITE_URL ?>/provider/dashboard.php"><span>📊</span> ড্যাশবোর্ড</a>
            <a href="<?= SITE_URL ?>/provider/bookings.php"><span>📅</span> বুকিং সমূহ</a>
            <a href="<?= SITE_URL ?>/provider/profile.php"><span>👤</span> প্রোফাইল</a>
          <?php else: ?>
            <a href="<?= SITE_URL ?>/admin/dashboard.php"><span>⚙️</span> অ্যাডমিন</a>
          <?php endif; ?>
          <a href="<?= SITE_URL ?>/logout.php" class="logout"><span>🚪</span> লগ আউট</a>
        </div>
      </div>
    <?php else: ?>
      <a href="<?= SITE_URL ?>/login.php" class="btn-login-header">লগইন</a>
      <a href="<?= SITE_URL ?>/signup.php" class="btn-signup-header">বিনামূল্যে যোগ দিন</a>
    <?php endif; ?>
  </div>
</nav>

<div class="page-body">
