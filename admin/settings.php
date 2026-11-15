<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';
sessionStart();
requireAuth('admin');
require_once __DIR__ . '/_stats.php';

require_once __DIR__ . '/_notif-scripts.php';

$user = auth();
$s = getAdminStats();

// New bookings count for sidebar badge
$newBookings = (int)db()->query("SELECT COUNT(*) FROM bookings WHERE status='Pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>সেটিংস - সেবা বন্ধু Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Hind Siliguri', sans-serif; }
    .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); }
    .sidebar-link { transition: all 0.2s ease; }
    .sidebar-link:hover { background: rgba(71, 85, 105, 0.3); transform: translateX(4px); }
    .sidebar-link.active { background: linear-gradient(90deg, rgba(124, 58, 237, 0.2), rgba(6, 182, 212, 0.1)); border-left: 3px solid #8B5CF6; }
  </style>
</head>
<body class="bg-slate-950 text-white min-h-screen">

<aside class="w-64 bg-gradient-to-b from-slate-900 to-slate-950 h-screen fixed left-0 top-0 border-r border-slate-800 z-50 flex flex-col">
  <div class="p-6 border-b border-slate-800">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-cyan-500 rounded-xl flex items-center justify-center">
        <i class="fas fa-bolt text-white"></i>
      </div>
      <div>
        <div class="text-xl font-bold bg-gradient-to-r from-purple-400 to-cyan-400 bg-clip-text text-transparent">সেবা বন্ধু</div>
        <div class="text-xs text-slate-500 uppercase tracking-wider">Admin Panel</div>
      </div>
    </div>
  </div>
  
  <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
    <a href="dashboard.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-gauge-high w-5"></i><span>ড্যাশবোর্ড</span></a>
    <a href="users.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-users w-5"></i><span>ব্যবহারকারী</span></a>
    <a href="providers.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-user-tie w-5"></i><span>সেবাদাতা</span></a>
    <a href="bookings.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-calendar-check w-5"></i><span>বুকিং</span>
      <?php if ($newBookings > 0): ?><span class="ml-auto bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"><?= $newBookings ?></span><?php endif; ?>
    </a>
    <a href="payments.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-credit-card w-5"></i><span>পেমেন্ট</span></a>
    <a href="services.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-cogs w-5"></i><span>সেবা</span></a>
    <a href="reviews.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-star w-5"></i><span>রিভিউ</span></a>
    <a href="applications.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-file-signature w-5"></i><span>আবেদন</span></a>
    <a href="analytics.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-chart-line w-5"></i><span>রিপোর্ট</span></a>
    <a href="notifications.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-bell w-5"></i><span>নোটিফিকেশন</span></a>
    <a href="settings.php" class="sidebar-link active flex items-center gap-3 p-3 rounded-xl text-white"><i class="fas fa-gear w-5 text-purple-400"></i><span>সেটিংস</span></a>
  </nav>

  <!-- User Profile -->
  <div class="p-4 border-t border-slate-800">
    <a href="<?= SITE_URL ?>/logout.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-red-500/10 text-slate-400 hover:text-red-400 transition-all group">
      <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-cyan-500 rounded-xl flex items-center justify-center font-bold text-sm shadow-lg">
        <?= strtoupper(mb_substr($user['name'] ?? 'AD', 0, 2)) ?>
      </div>
      <div class="flex-1">
        <div class="font-semibold text-sm"><?= e($user['name'] ?? 'অ্যাডমিন') ?></div>
        <div class="text-xs text-slate-500">সুপার অ্যাডমিন</div>
      </div>
      <i class="fas fa-right-from-bracket group-hover:rotate-180 transition-transform"></i>
    </a>
  </div>
</aside>

<main class="ml-64 p-8">
  <div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-2">⚙️ সেটিংস</h1>
    <p class="text-slate-400 mb-6">আপনার অ্যাডমিন প্যানেল সেটিংস</p>
    
    <div class="grid md:grid-cols-2 gap-6">
      <div class="glass p-5 rounded-xl">
        <h3 class="font-bold text-lg mb-4">🎯 সাইট তথ্য</h3>
        <div class="space-y-3">
          <div class="flex justify-between py-2 border-b border-slate-700">
            <span class="text-slate-400">সাইট নাম</span>
            <span class="font-bold">সেবা বন্ধু</span>
          </div>
          <div class="flex justify-between py-2 border-b border-slate-700">
            <span class="text-slate-400">ভার্সন</span>
            <span>1.0.0</span>
          </div>
          <div class="flex justify-between py-2 border-b border-slate-700">
            <span class="text-slate-400">ডেটাবেজ</span>
            <span>shebabondhu</span>
          </div>
        </div>
      </div>
      
      <div class="glass p-5 rounded-xl">
        <h3 class="font-bold text-lg mb-4">👤 অ্যাডমিন প্রোফাইল</h3>
        <div class="space-y-3">
          <div class="flex justify-between py-2 border-b border-slate-700">
            <span class="text-slate-400">নাম</span>
            <span><?= e($user['name'] ?? 'Admin') ?></span>
          </div>
          <div class="flex justify-between py-2 border-b border-slate-700">
            <span class="text-slate-400">ইমেইল</span>
            <span><?= e($user['email'] ?? 'admin@handygo.com') ?></span>
          </div>
          <div class="flex justify-between py-2">
            <span class="text-slate-400">ভূমিকা</span>
            <span class="text-purple-400 font-bold">সুপার অ্যাডমিন</span>
          </div>
        </div>
      </div>
    </div>
    
    <div class="glass p-5 rounded-xl mt-6">
      <h3 class="font-bold text-lg mb-4">ℹ️ সিস্টেম তথ্য</h3>
      <div class="grid md:grid-cols-3 gap-4 text-sm">
        <div>
          <div class="text-slate-400">PHP ভার্সন</div>
          <div class="font-bold"><?= phpversion() ?></div>
        </div>
        <div>
          <div class="text-slate-400">সার্ভার</div>
          <div class="font-bold">Apache/XAMPP</div>
        </div>
        <div>
          <div class="text-slate-400">ডেটাবেজ</div>
          <div class="font-bold">MySQL</div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php renderAdminNotifScripts(); ?>

</body>
</html>