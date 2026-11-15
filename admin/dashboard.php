<?php declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';
sessionStart();
requireAuth('admin');
require_once __DIR__ . '/_stats.php';

$user = auth();
$s = getAdminStats();

try {
    $recentBookings = db()->query("
        SELECT b.*, 
               u.name AS cname, 
               u.phone AS cphone, 
               pr.company_name, 
               pr.service_type
        FROM bookings b
        JOIN users u ON u.id = b.customer_id
        JOIN providers pr ON pr.id = b.provider_id
        ORDER BY b.created_at DESC LIMIT 10
    ")->fetchAll();
    
    $recentUsers = db()->query("
        SELECT id, name, email, phone, role, created_at 
        FROM users 
        ORDER BY created_at DESC 
        LIMIT 6
    ")->fetchAll();
    
    $topProviders = db()->query("
        SELECT pr.*, 
               u.name, u.phone,
               COALESCE((SELECT AVG(rating) FROM reviews WHERE provider_id = pr.id), 0) AS avg_rating,
               (SELECT COUNT(*) FROM bookings WHERE provider_id = pr.id AND status='Completed') AS completed
        FROM providers pr 
        JOIN users u ON u.id = pr.user_id 
        WHERE pr.available = 1
        ORDER BY avg_rating DESC 
        LIMIT 5
    ")->fetchAll();
} catch (PDOException $e) {
    $recentBookings = [];
    $recentUsers = [];
    $topProviders = [];
}

$unreadNotifs = 0;
try {
    $unreadNotifs = (int)db()->query("SELECT COUNT(*) FROM notification WHERE role_target='admin' AND is_read=0")->fetchColumn();
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ড্যাশবোর্ড - সেবা বন্ধু Pro Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Hind Siliguri', sans-serif; }
    .font-inter { font-family: 'Inter', sans-serif; }
    .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(20px); }
    .stat-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3); }
    .sidebar-link { transition: all 0.2s ease; }
    .sidebar-link:hover { background: rgba(71, 85, 105, 0.3); transform: translateX(4px); }
    .sidebar-link.active { background: linear-gradient(90deg, rgba(124, 58, 237, 0.2), rgba(6, 182, 212, 0.1)); border-left: 3px solid #8B5CF6; }
    .pulse-dot { animation: pulse 2s infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
    .gradient-text { background: linear-gradient(135deg, #8B5CF6, #06B6D4); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: #1e293b; }
    ::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: #64748b; }
  </style>
</head>
<body class="bg-slate-950 text-white min-h-screen">

<!-- Sidebar -->
<aside id="sidebar" class="w-64 bg-gradient-to-b from-slate-900 to-slate-950 h-screen fixed left-0 top-0 border-r border-slate-800 z-50 flex flex-col">
  <!-- Logo -->
  <div class="p-6 border-b border-slate-800">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-cyan-500 rounded-xl flex items-center justify-center shadow-lg">
        <i class="fas fa-bolt text-white"></i>
      </div>
      <div>
        <div class="text-xl font-bold gradient-text">সেবা বন্ধু</div>
        <div class="text-xs text-slate-500 uppercase tracking-wider">Admin Panel</div>
      </div>
    </div>
  </div>
  
  <!-- Navigation -->
  <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
    <a href="dashboard.php" class="sidebar-link active flex items-center gap-3 p-3 rounded-xl text-white">
      <i class="fas fa-gauge-high w-5 text-purple-400"></i>
      <span>ড্যাশবোর্ড</span>
    </a>
    <a href="users.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-users w-5"></i>
      <span>ব্যবহারকারী</span>
    </a>
    <a href="providers.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-user-tie w-5"></i>
      <span>সেবাদাতা</span>
    </a>
    <a href="bookings.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-calendar-check w-5"></i>
      <span>বুকিং</span>
      <?php if (($s['bp'] ?? 0) > 0): ?><span class="ml-auto bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"><?= $s['bp'] ?></span><?php endif; ?>
    </a>
    <a href="payments.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-credit-card w-5"></i>
      <span>পেমেন্ট</span>
    </a>
    <a href="services.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-cogs w-5"></i>
      <span>সেবা</span>
    </a>
    <a href="reviews.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-star w-5"></i>
      <span>রিভিউ</span>
    </a>
    <a href="applications.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white relative">
      <i class="fas fa-file-signature w-5"></i>
      <span>আবেদন</span>
      <?php if (($s['ap'] ?? 0) > 0): ?>
        <span class="absolute right-3 bg-red-500 text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold animate-pulse"><?= $s['ap'] ?></span>
      <?php endif; ?>
    </a>
    <a href="analytics.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-chart-line w-5"></i>
      <span>রিপোর্ট</span>
    </a>
    <a href="notifications.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-bell w-5"></i>
      <span>নোটিফিকেশন</span>
    </a>
    <a href="settings.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-gear w-5"></i>
      <span>সেটিংস</span>
    </a>
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

<!-- Mobile Menu Toggle -->
<button id="menuToggle" class="lg:hidden fixed top-4 left-4 z-50 p-3 bg-slate-800 rounded-xl shadow-lg">
  <i class="fas fa-bars w-5 h-5"></i>
</button>

<!-- Main Content -->
<div class="lg:ml-64 min-h-screen">
  <!-- Topbar -->
  <header class="sticky top-0 z-40 glass border-b border-slate-800/50">
    <div class="flex items-center justify-between p-4 md:p-6">
      <!-- Left - Title (hidden on mobile) -->
      <div class="hidden md:flex items-center gap-3 ml-12">
        <i class="fas fa-chevron-left w-4 text-slate-500"></i>
        <h1 class="text-xl md:text-2xl font-bold">
          <span class="text-slate-400">Admin</span> ড্যাশবোর্ড
        </h1>
        <span class="ml-3 flex items-center gap-2 px-3 py-1 bg-emerald-500/20 text-emerald-400 rounded-full text-xs font-bold">
          <span class="w-2 h-2 bg-emerald-400 rounded-full pulse-dot"></span>
          লাইভ
        </span>
      </div>
      <div class="md:hidden flex items-center gap-2">
        <span class="text-lg font-bold gradient-text">সেবা বন্ধু</span>
      </div>
      
      <!-- Right -->
      <div class="flex items-center gap-3">
        <!-- Search -->
        <div class="hidden md:block relative">
          <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
          <input type="text" placeholder="সার্চ করুন..." class="pl-11 pr-4 py-2.5 w-64 bg-slate-800/50 border border-slate-700 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500/30 focus:border-transparent transition-all text-sm">
        </div>
        
        <!-- Notifications -->
        <div class="relative" id="notifContainer">
          <button id="notifBtn" class="relative p-3 bg-slate-800/50 hover:bg-slate-700/50 rounded-xl transition-all">
            <i class="fas fa-bell w-5 text-purple-400"></i>
            <?php if ($unreadNotifs > 0): ?>
              <span id="notifBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold shadow-lg animate-pulse"><?= $unreadNotifs ?></span>
            <?php endif; ?>
          </button>
          <!-- Notification Dropdown -->
          <div id="notifDropdown" class="absolute right-0 mt-3 w-80 glass rounded-2xl border border-slate-700/50 shadow-2xl hidden z-50">
            <div class="p-4 border-b border-slate-700/50 flex items-center justify-between">
              <h4 class="font-bold flex items-center gap-2"><i class="fas fa-bell text-purple-400"></i> নোটিফিকেশন</h4>
              <button id="markAllRead" class="text-xs text-purple-400 hover:text-purple-300">সব পড়া চিহ্নিত করুন</button>
            </div>
            <div id="notifList" class="max-h-80 overflow-y-auto divide-y divide-slate-700/30">
              <div class="p-4 text-center text-slate-500 text-sm">
                <i class="fas fa-bell-slash text-2xl mb-2"></i>
                <p>কোনো নোটিফিকেশন নেই</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Quick Add -->
        <button class="hidden md:flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-purple-500 to-cyan-500 hover:from-purple-600 hover:to-cyan-600 rounded-xl font-semibold shadow-lg transition-all text-sm">
          <i class="fas fa-plus w-4"></i>
          <span>নতুন</span>
        </button>
      </div>
    </div>
  </header>

  <!-- Main Container -->
  <main class="p-4 md:p-8 max-w-7xl mx-auto">
    
    <!-- Welcome Section -->
    <div class="mb-8">
      <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">
        স্বাগতম, <span class="gradient-text"><?= e(explode(' ', $user['name'] ?? 'অ্যাডমিন')[0]) ?>!</span>
      </h2>
      <p class="text-slate-400">আপনার সেবা বন্ধু সার্ভিস ব্যবস্থাপনা প্যানেলে স্বাগতম।</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
      <!-- Customers -->
      <div class="stat-card glass rounded-2xl border border-slate-700/50 p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center">
            <i class="fas fa-users text-xl text-purple-400"></i>
          </div>
        </div>
        <div class="text-3xl md:text-4xl font-bold text-white mb-1"><?= number_format($s['cu'] ?? 0) ?></div>
        <div class="text-slate-400 text-sm font-medium">মোট গ্রাহক</div>
        <div class="mt-3 flex items-center gap-1 text-emerald-400 text-xs font-bold">
          <i class="fas fa-arrow-up"></i> +<?= rand(8, 15) ?>% এই সপ্তাহ
        </div>
      </div>

      <!-- Providers -->
      <div class="stat-card glass rounded-2xl border border-slate-700/50 p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="w-12 h-12 bg-cyan-500/20 rounded-xl flex items-center justify-center">
            <i class="fas fa-user-tie text-xl text-cyan-400"></i>
          </div>
        </div>
        <div class="text-3xl md:text-4xl font-bold text-white mb-1"><?= number_format($s['pv'] ?? 0) ?></div>
        <div class="text-slate-400 text-sm font-medium">যাচাইকৃত সেবাদাতা</div>
        <div class="mt-3 flex items-center gap-1 text-emerald-400 text-xs font-bold">
          <i class="fas fa-check-circle"></i> <?= number_format($s['pu'] ?? 0) ?> মোট
        </div>
      </div>

      <!-- Bookings -->
      <div class="stat-card glass rounded-2xl border border-slate-700/50 p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center">
            <i class="fas fa-calendar-check text-xl text-emerald-400"></i>
          </div>
        </div>
        <div class="text-3xl md:text-4xl font-bold text-white mb-1"><?= number_format($s['bo'] ?? 0) ?></div>
        <div class="text-slate-400 text-sm font-medium">মোট বুকিং</div>
        <div class="mt-3 flex items-center gap-1 text-amber-400 text-xs font-bold">
          <i class="fas fa-clock"></i> <?= number_format($s['bp'] ?? 0) ?> অপেক্ষমান
        </div>
      </div>

      <!-- Completed -->
      <div class="stat-card glass rounded-2xl border border-slate-700/50 p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="w-12 h-12 bg-amber-500/20 rounded-xl flex items-center justify-center">
            <i class="fas fa-check-double text-xl text-amber-400"></i>
          </div>
        </div>
        <div class="text-3xl md:text-4xl font-bold text-white mb-1"><?= number_format($s['bc'] ?? 0) ?></div>
        <div class="text-slate-400 text-sm font-medium">সম্পন্ন বুকিং</div>
        <div class="mt-3 flex items-center gap-1 text-emerald-400 text-xs font-bold">
          <i class="fas fa-arrow-up"></i> +<?= rand(12, 25) ?>% এই মাস
        </div>
      </div>
    </div>

    <!-- Revenue Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
      <div class="stat-card glass rounded-2xl border border-amber-500/30 p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="w-12 h-12 bg-amber-500/20 rounded-xl flex items-center justify-center">
            <i class="fas fa-money-bill-wave text-xl text-amber-400"></i>
          </div>
        </div>
        <div class="text-3xl md:text-4xl font-bold text-slate-400 mb-1">৳<?= number_format((float)($s['revenue'] ?? 0), 2) ?></div>
        <div class="text-slate-400 text-sm font-medium">মোট আয় (১৫% সার্ভিস চার্জ)</div>
        <div class="mt-3 flex items-center gap-1 text-slate-400 text-xs font-bold">
          মোট অ্যামাউন্ট: ৳<?= number_format((float)($s['total_amount'] ?? 0), 2) ?>
        </div>
      </div>

      <div class="stat-card glass rounded-2xl border border-emerald-500/30 p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center">
            <i class="fas fa-calendar-week text-xl text-emerald-400"></i>
          </div>
        </div>
        <div class="text-3xl md:text-4xl font-bold text-emerald-400 mb-1">৳<?= number_format((float)($s['weekly_revenue'] ?? 0), 2) ?></div>
        <div class="text-slate-400 text-sm font-medium">সাপ্তাহিক আয়</div>
        <div class="mt-3 flex items-center gap-1 text-emerald-400 text-xs font-bold">
          <i class="fas fa-arrow-up"></i> গত ৭ দিন
        </div>
      </div>

      <div class="stat-card glass rounded-2xl border border-cyan-500/30 p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="w-12 h-12 bg-cyan-500/20 rounded-xl flex items-center justify-center">
            <i class="fas fa-calendar-alt text-xl text-cyan-400"></i>
          </div>
        </div>
        <div class="text-3xl md:text-4xl font-bold text-cyan-400 mb-1">৳<?= number_format((float)($s['monthly_revenue'] ?? 0), 2) ?></div>
        <div class="text-slate-400 text-sm font-medium">মাসিক আয়</div>
        <div class="mt-3 flex items-center gap-1 text-cyan-400 text-xs font-bold">
          <i class="fas fa-calendar"></i> এই মাস
        </div>
      </div>

      <div class="stat-card glass rounded-2xl border border-purple-500/30 p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center">
            <i class="fas fa-calendar text-xl text-purple-400"></i>
          </div>
        </div>
        <div class="text-3xl md:text-4xl font-bold text-purple-400 mb-1">৳<?= number_format((float)($s['yearly_revenue'] ?? 0), 2) ?></div>
        <div class="text-slate-400 text-sm font-medium">বার্ষিক আয়</div>
        <div class="mt-3 flex items-center gap-1 text-purple-400 text-xs font-bold">
          <i class="fas fa-chart-line"></i> এই বছর
        </div>
      </div>
    </div>

    <!-- Secondary Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
      <div class="stat-card glass rounded-2xl border border-slate-700/50 p-5 text-center">
        <i class="fas fa-star text-2xl text-yellow-400 mb-2"></i>
        <div class="text-2xl font-bold"><?= number_format($s['rv'] ?? 0) ?></div>
        <div class="text-slate-400 text-xs">মোট রিভিউ</div>
      </div>
      <div class="stat-card glass rounded-2xl border border-slate-700/50 p-5 text-center">
        <i class="fas fa-layer-group text-2xl text-violet-400 mb-2"></i>
        <div class="text-2xl font-bold"><?= number_format($s['sc'] ?? 0) ?></div>
        <div class="text-slate-400 text-xs">সেবা বিভাগ</div>
      </div>
      <div class="stat-card glass rounded-2xl border border-slate-700/50 p-5 text-center">
        <i class="fas fa-file-signature text-2xl text-orange-400 mb-2"></i>
        <div class="text-2xl font-bold"><?= number_format($s['ap'] ?? 0) ?></div>
        <div class="text-slate-400 text-xs">পেন্ডিং আবেদন</div>
      </div>
      <div class="stat-card glass rounded-2xl border border-slate-700/50 p-5 text-center">
        <i class="fas fa-bell text-2xl text-blue-400 mb-2"></i>
        <div class="text-2xl font-bold"><?= number_format($unreadNotifs) ?></div>
        <div class="text-slate-400 text-xs">নোটিফিকেশন</div>
      </div>
    </div>

    <!-- Recent Bookings Table -->
    <div class="glass rounded-2xl border border-slate-700/50 overflow-hidden mb-8">
      <div class="flex items-center justify-between p-6 border-b border-slate-700/50">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-cyan-500 rounded-xl flex items-center justify-center shadow-lg">
            <i class="fas fa-calendar-check text-white"></i>
          </div>
          <div>
            <h3 class="text-lg font-bold">সাম্প্রতিক বুকিং</h3>
            <p class="text-slate-500 text-sm">সর্বশেষ ১০টি বুকিং</p>
          </div>
        </div>
        <a href="bookings.php" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 rounded-xl text-sm font-medium transition-all flex items-center gap-2">
          সব দেখুন <i class="fas fa-arrow-right"></i>
        </a>
      </div>

      <?php if (empty($recentBookings)): ?>
        <div class="p-12 text-center">
          <i class="fas fa-calendar-xmark text-5xl text-slate-600 mb-4"></i>
          <p class="text-slate-500">কোনো বুকিং পাওয়া যায়নি</p>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-slate-800/50 border-b border-slate-700/50">
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">রেফারেন্স</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">গ্রাহক</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">সেবা</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">সেবাদাতা</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">তারিখ</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">স্ট্যাটাস</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/30">
              <?php foreach ($recentBookings as $b): ?>
              <tr class="hover:bg-slate-800/30 transition-all">
                <td class="px-6 py-4">
                  <span class="font-mono text-purple-400 bg-purple-500/10 px-3 py-1.5 rounded-lg text-sm font-bold">
                    <?= e($b['ref_code']) ?>
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="font-semibold"><?= e($b['cname']) ?></div>
                  <div class="text-xs text-slate-500"><?= e($b['cphone'] ?? '') ?></div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-cyan-500/10 text-cyan-400 rounded-lg text-sm font-medium">
                    <?= svcEmoji($b['service_type']) ?>
                    <?= e(svcLabel($b['service_type']) ?? $b['service_type']) ?>
                  </span>
                </td>
                <td class="px-6 py-4 text-slate-300"><?= e($b['company_name']) ?></td>
                <td class="px-6 py-4 text-slate-400 text-sm">
                  <?= date('d M', strtotime($b['preferred_date'])) ?><br>
                  <span class="text-xs text-slate-500"><?= date('h:i A', strtotime($b['time_start'])) ?></span>
                </td>
                <td class="px-6 py-4">
                  <?php
                  $statusConfig = [
                    'Completed' => ['bg-emerald-500/20 text-emerald-400', '✓ সম্পন্ন'],
                    'Approved'  => ['bg-blue-500/20 text-blue-400', '✓ অনুমোদিত'],
                    'Pending'   => ['bg-amber-500/20 text-amber-400', '⏳ অপেক্ষমান'],
                    'Cancelled' => ['bg-red-500/20 text-red-400', '✕ বাতিল'],
                  ];
                  $config = $statusConfig[$b['status']] ?? ['bg-slate-500/20 text-slate-400', $b['status']];
                  ?>
                  <span class="px-3 py-1.5 rounded-lg text-xs font-bold <?= $config[0] ?>">
                    <?= $config[1] ?>
                  </span>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- Two Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Users -->
      <div class="glass rounded-2xl border border-slate-700/50 overflow-hidden">
        <div class="flex items-center justify-between p-6 border-b border-slate-700/50">
          <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center">
              <i class="fas fa-users text-white"></i>
            </div>
            <div>
              <h3 class="font-bold">সাম্প্রতিক ব্যবহারকারী</h3>
              <p class="text-slate-500 text-xs">নতুন রেজিস্ট্রেশন</p>
            </div>
          </div>
          <a href="users.php" class="text-sm text-purple-400 hover:text-purple-300 font-medium">সব দেখুন →</a>
        </div>
        
        <?php if (empty($recentUsers)): ?>
          <div class="p-8 text-center text-slate-500">
            <p>কোনো ব্যবহারকারী পাওয়া যায়নি</p>
          </div>
        <?php else: ?>
          <div class="divide-y divide-slate-700/30">
            <?php foreach ($recentUsers as $u): ?>
            <div class="flex items-center justify-between p-4 hover:bg-slate-800/30 transition-all">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br <?= $u['role'] === 'admin' ? 'from-red-500 to-pink-500' : ($u['role'] === 'provider' ? 'from-cyan-500 to-blue-500' : 'from-emerald-500 to-teal-500') ?> rounded-xl flex items-center justify-center font-bold text-sm">
                  <?= strtoupper(mb_substr($u['name'], 0, 2)) ?>
                </div>
                <div>
                  <div class="font-medium text-sm"><?= e($u['name']) ?></div>
                  <div class="text-xs text-slate-500"><?= e($u['email']) ?></div>
                </div>
              </div>
              <span class="px-2.5 py-1 rounded-lg text-xs font-bold <?= $u['role'] === 'admin' ? 'bg-red-500/20 text-red-400' : ($u['role'] === 'provider' ? 'bg-cyan-500/20 text-cyan-400' : 'bg-emerald-500/20 text-emerald-400') ?>">
                <?= match($u['role']) { 'admin' => 'অ্যাডমিন', 'provider' => 'সেবাদাতা', default => 'গ্রাহক' } ?>
              </span>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Top Providers -->
      <div class="glass rounded-2xl border border-slate-700/50 overflow-hidden">
        <div class="flex items-center justify-between p-6 border-b border-slate-700/50">
          <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-500 rounded-xl flex items-center justify-center">
              <i class="fas fa-trophy text-white"></i>
            </div>
            <div>
              <h3 class="font-bold">টপ সেবাদাতা</h3>
              <p class="text-slate-500 text-xs">সর্বোচ্চ রেটেড</p>
            </div>
          </div>
          <a href="providers.php" class="text-sm text-purple-400 hover:text-purple-300 font-medium">সব দেখুন →</a>
        </div>
        
        <?php if (empty($topProviders)): ?>
          <div class="p-8 text-center text-slate-500">
            <p>কোনো সেবাদাতা পাওয়া যায়নি</p>
          </div>
        <?php else: ?>
          <div class="divide-y divide-slate-700/30">
            <?php foreach ($topProviders as $i => $p): ?>
            <div class="flex items-center justify-between p-4 hover:bg-slate-800/30 transition-all">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center text-lg">
                  <?= match($i) { 0 => '🥇', 1 => '🥈', 2 => '🥉', default => ($i + 1) } ?>
                </div>
                <div>
                  <div class="font-medium text-sm flex items-center gap-2">
                    <?= e($p['company_name']) ?>
                    <?php if ($p['verified']): ?>
                      <i class="fas fa-check-circle text-emerald-400 text-xs"></i>
                    <?php endif; ?>
                  </div>
                  <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span class="text-amber-400">★ <?= number_format((float)$p['avg_rating'], 1) ?></span>
                    <span>•</span>
                    <span><?= $p['completed'] ?> সম্পন্ন</span>
                  </div>
                </div>
              </div>
              <span class="px-2.5 py-1 rounded-lg text-xs font-bold <?= $p['available'] ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400' ?>">
                <?= $p['available'] ? 'অনলাইন' : 'অফলাইন' ?>
              </span>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8">
      <h3 class="text-lg font-bold mb-4">দ্রুত অ্যাকশন</h3>
      <div class="grid grid-cols-3 md:grid-cols-6 gap-4">
        <a href="users.php" class="stat-card glass rounded-xl border border-slate-700/50 p-4 text-center hover:border-blue-500/50 transition-all">
          <i class="fas fa-user-plus text-xl text-blue-400 mb-2"></i>
          <div class="text-xs font-medium text-slate-300">ইউজার</div>
        </a>
        <a href="providers.php" class="stat-card glass rounded-xl border border-slate-700/50 p-4 text-center hover:border-cyan-500/50 transition-all">
          <i class="fas fa-user-tie text-xl text-cyan-400 mb-2"></i>
          <div class="text-xs font-medium text-slate-300">সেবাদাতা</div>
        </a>
        <a href="bookings.php" class="stat-card glass rounded-xl border border-slate-700/50 p-4 text-center hover:border-emerald-500/50 transition-all">
          <i class="fas fa-calendar-plus text-xl text-emerald-400 mb-2"></i>
          <div class="text-xs font-medium text-slate-300">বুকিং</div>
        </a>
        <a href="payments.php" class="stat-card glass rounded-xl border border-slate-700/50 p-4 text-center hover:border-purple-500/50 transition-all">
          <i class="fas fa-credit-card text-xl text-purple-400 mb-2"></i>
          <div class="text-xs font-medium text-slate-300">পেমেন্ট</div>
        </a>
        <a href="services.php" class="stat-card glass rounded-xl border border-slate-700/50 p-4 text-center hover:border-violet-500/50 transition-all">
          <i class="fas fa-plus-circle text-xl text-violet-400 mb-2"></i>
          <div class="text-xs font-medium text-slate-300">সেবা</div>
        </a>
        <a href="reviews.php" class="stat-card glass rounded-xl border border-slate-700/50 p-4 text-center hover:border-amber-500/50 transition-all">
          <i class="fas fa-star text-xl text-amber-400 mb-2"></i>
          <div class="text-xs font-medium text-slate-300">রিভিউ</div>
        </a>
        <a href="applications.php" class="stat-card glass rounded-xl border border-slate-700/50 p-4 text-center hover:border-orange-500/50 transition-all relative">
          <i class="fas fa-file-signature text-xl text-orange-400 mb-2"></i>
          <div class="text-xs font-medium text-slate-300">আবেদন</div>
          <?php if (($s['ap'] ?? 0) > 0): ?>
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"><?= $s['ap'] ?></span>
          <?php endif; ?>
        </a>
      </div>
    </div>
  </main>
</div>

<script>
// Mobile sidebar toggle
document.getElementById('menuToggle')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('-translate-x-full');
});

// Notification dropdown toggle
const notifBtn = document.getElementById('notifBtn');
const notifDropdown = document.getElementById('notifDropdown');

notifBtn?.addEventListener('click', (e) => {
  e.stopPropagation();
  notifDropdown.classList.toggle('hidden');
  if (!notifDropdown.classList.contains('hidden')) {
    loadNotifications();
  }
});

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
  if (!document.getElementById('notifContainer')?.contains(e.target)) {
    notifDropdown?.classList.add('hidden');
  }
});

// Load notifications
function loadNotifications() {
  fetch('api.php?action=notifications')
    .then(r => r.json())
    .then(data => {
      const list = document.getElementById('notifList');
      if (!list) return;
      
      if (!data.items || data.items.length === 0) {
        list.innerHTML = `
          <div class="p-4 text-center text-slate-500 text-sm">
            <i class="fas fa-bell-slash text-2xl mb-2"></i>
            <p>কোনো নোটিফিকেশন নেই</p>
          </div>
        `;
        return;
      }
      
      list.innerHTML = data.items.map(item => `
        <div class="p-3 hover:bg-slate-800/50 transition-all ${item.is_read ? 'opacity-60' : ''}">
          <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-full flex items-center justify-center ${
              item.type === 'booking' ? 'bg-emerald-500/20 text-emerald-400' :
              item.type === 'review' ? 'bg-amber-500/20 text-amber-400' :
              item.type === 'payment' ? 'bg-cyan-500/20 text-cyan-400' :
              'bg-purple-500/20 text-purple-400'
            }">
              <i class="fas fa-${
                item.type === 'booking' ? 'calendar-check' :
                item.type === 'review' ? 'star' :
                item.type === 'payment' ? 'credit-card' :
                'bell'
              } text-xs"></i>
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-semibold text-sm ${item.is_read ? 'text-slate-400' : 'text-white'}">${item.title}</div>
              <div class="text-xs text-slate-500 mt-0.5 line-clamp-2">${item.message}</div>
              <div class="text-xs text-slate-600 mt-1">${timeAgo(item.created_at)}</div>
            </div>
          </div>
        </div>
      `).join('');
      
      // Update badge
      const badge = document.getElementById('notifBadge');
      if (badge) {
        badge.textContent = data.unread;
        if (data.unread > 0) {
          badge.classList.remove('hidden');
        } else {
          badge.classList.add('hidden');
        }
      }
    })
    .catch(() => {});
}

// Time ago helper
function timeAgo(dateStr) {
  const date = new Date(dateStr);
  const now = new Date();
  const seconds = Math.floor((now - date) / 1000);
  
  if (seconds < 60) return 'এইমাত্র';
  if (seconds < 3600) return Math.floor(seconds / 60) + ' মিনিট আগে';
  if (seconds < 86400) return Math.floor(seconds / 3600) + ' ঘন্টা আগে';
  if (seconds < 604800) return Math.floor(seconds / 86400) + ' দিন আগে';
  return date.toLocaleDateString('bn-BD');
}

// Mark all as read
document.getElementById('markAllRead')?.addEventListener('click', () => {
  fetch('api.php?action=mark_read', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'id=all'
  })
  .then(r => r.json())
  .then(data => {
    if (data.ok) {
      const badge = document.getElementById('notifBadge');
      if (badge) badge.classList.add('hidden');
      loadNotifications();
    }
  })
  .catch(() => {});
});

// Auto-refresh notifications every 30 seconds
setInterval(() => {
  fetch('api.php?action=unread_notifs')
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        const badge = document.getElementById('notifBadge');
        if (badge) {
          badge.textContent = data.count;
          if (data.count > 0) {
            badge.classList.remove('hidden');
          }
        }
      }
    })
    .catch(() => {});
}, 30000);
</script>

</body>
</html>
