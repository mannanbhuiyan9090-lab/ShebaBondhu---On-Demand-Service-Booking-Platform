<?php
require_once __DIR__.'/../includes/config.php';
sessionStart();
requireAuth('customer');
$user = auth();

try {
  $bookings = db()->prepare("SELECT b.*, p.company_name, p.service_type, p.emoji, p.city FROM bookings b JOIN providers p ON p.id = b.provider_id WHERE b.customer_id = ? ORDER BY b.created_at DESC LIMIT 8");
  $bookings->execute([$user['id']]);
  $bookings = $bookings->fetchAll();
  
  $s = db()->prepare("SELECT COUNT(*) t, SUM(status = 'Pending') pe, SUM(status = 'Completed') co, SUM(status = 'Approved') ap FROM bookings WHERE customer_id = ?");
  $s->execute([$user['id']]);
  $s = $s->fetch();
  
  $recentReviews = db()->prepare("SELECT r.*, p.company_name FROM reviews r JOIN providers p ON p.id = r.provider_id WHERE r.customer_id = ? ORDER BY r.created_at DESC LIMIT 3");
  $recentReviews->execute([$user['id']]);
  $recentReviews = $recentReviews->fetchAll();
} catch(Exception $e) {
  $bookings = [];
  $s = ['t' => 0, 'pe' => 0, 'co' => 0, 'ap' => 0];
  $recentReviews = [];
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ড্যাশবোর্ড - সেবা বন্ধু</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Hind Siliguri', sans-serif; }
    .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(20px); }
    .glass-dark { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(20px); }
    .card { background: rgba(30, 41, 59, 0.5); backdrop-filter: blur(20px); border: 1px solid rgba(71, 85, 105, 0.3); border-radius: 1.5rem; }
    .sidebar-link { transition: all 0.2s ease; }
    .sidebar-link:hover { background: rgba(71, 85, 105, 0.3); transform: translateX(4px); }
    .sidebar-link.active { background: linear-gradient(90deg, rgba(16, 185, 129, 0.3), rgba(6, 182, 212, 0.1)); border-left: 3px solid #10B981; }
    .gradient-text { background: linear-gradient(135deg, #10B981, #06B6D4); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
    .stat-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3); }
    .btn-action { padding: 8px 16px; border-radius: 12px; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px; }
    .btn-primary { background: linear-gradient(135deg, #10B981, #059669); color: white; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4); }
    .pulse-dot { animation: pulse 2s infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
    input, select { background: rgba(15, 23, 42, 0.6) !important; border: 1px solid rgba(71, 85, 105, 0.5) !important; color: white !important; border-radius: 12px; }
  </style>
</head>
<body class="bg-slate-950 text-white min-h-screen">

<!-- Sidebar -->
<aside id="sidebar" class="w-64 bg-gradient-to-b from-slate-900 to-slate-950 h-screen fixed left-0 top-0 border-r border-slate-800 z-50 flex flex-col">
  <div class="p-6 border-b border-slate-800">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center shadow-lg">
        <i class="fas fa-bolt text-white"></i>
      </div>
      <div>
        <div class="text-xl font-bold gradient-text">সেবা বন্ধু</div>
        <div class="text-xs text-slate-500 uppercase tracking-wider">গ্রাহক প্যানেল</div>
      </div>
    </div>
  </div>
  
  <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
    <a href="dashboard.php" class="sidebar-link active flex items-center gap-3 p-3 rounded-xl text-white">
      <i class="fas fa-gauge-high w-5 text-emerald-400"></i>
      <span>ড্যাশবোর্ড</span>
    </a>
    <a href="bookings.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-calendar-check w-5"></i>
      <span>আমার বুকিং</span>
    </a>
    <a href="profile.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-user w-5"></i>
      <span>প্রোফাইল</span>
    </a>
    <a href="<?= SITE_URL ?>/services.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-search w-5"></i>
      <span>সেবা খুঁজুন</span>
    </a>
  </nav>
  <div class="p-4 border-t border-slate-800">
    <a href="<?= SITE_URL ?>/logout.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-red-500/10 text-slate-400 hover:text-red-400 transition-all group">
      <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center font-bold text-sm shadow-lg">
        <?= strtoupper(mb_substr($user['name'], 0, 2)) ?>
      </div>
      <div class="flex-1">
        <div class="font-semibold text-sm"><?= e(explode(' ', $user['name'])[0]) ?></div>
        <div class="text-xs text-slate-500">গ্রাহক</div>
      </div>
      <i class="fas fa-right-from-bracket group-hover:rotate-180 transition-transform"></i>
    </a>
  </div>
</aside>

<button id="menuToggle" class="lg:hidden fixed top-4 left-4 z-50 p-3 bg-slate-800 rounded-xl shadow-lg">
  <i class="fas fa-bars w-5 h-5"></i>
</button>

<div class="lg:ml-64 min-h-screen">
  <header class="sticky top-0 z-40 glass-dark border-b border-slate-800/50">
    <div class="flex items-center justify-between p-4 md:p-6">
      <div class="hidden md:flex items-center gap-3 ml-12">
        <h1 class="text-xl md:text-2xl font-bold text-white">
          <i class="fas fa-home mr-2 text-emerald-400"></i> ড্যাশবোর্ড
        </h1>
        <span class="flex items-center gap-2 px-3 py-1 bg-emerald-500/20 text-emerald-400 rounded-full text-xs font-bold">
          <span class="w-2 h-2 bg-emerald-400 rounded-full pulse-dot"></span>
          অনলাইন
        </span>
      </div>
      <div class="md:hidden flex items-center gap-2">
        <span class="text-lg font-bold gradient-text">সেবা বন্ধু</span>
      </div>
      
      <div class="flex items-center gap-3">
        <a href="<?= SITE_URL ?>/services.php" class="hidden md:flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 rounded-xl font-semibold shadow-lg transition-all text-sm">
          <i class="fas fa-plus w-4"></i>
          <span>নতুন বুকিং</span>
        </a>
      </div>
    </div>
  </header>

  <main class="p-4 md:p-8 max-w-7xl mx-auto">
    <div class="mb-8">
      <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">
        স্বাগতম, <span class="gradient-text"><?= e(explode(' ', $user['name'])[0]) ?>!</span> 👋
      </h2>
      <p class="text-slate-400">আপনার সেবা বন্ধু গ্রাহক প্যানেলে স্বাগতম।</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
      <div class="stat-card card p-6 text-center">
        <div class="w-14 h-14 bg-emerald-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-calendar-check text-2xl text-emerald-400"></i>
        </div>
        <div class="text-3xl md:text-4xl font-bold text-white mb-1"><?= number_format($s['t'] ?? 0) ?></div>
        <div class="text-slate-400 text-sm font-medium">মোট বুকিং</div>
      </div>

      <div class="stat-card card p-6 text-center">
        <div class="w-14 h-14 bg-amber-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-clock text-2xl text-amber-400"></i>
        </div>
        <div class="text-3xl md:text-4xl font-bold text-white mb-1"><?= number_format($s['pe'] ?? 0) ?></div>
        <div class="text-slate-400 text-sm font-medium">অপেক্ষমান</div>
      </div>

      <div class="stat-card card p-6 text-center">
        <div class="w-14 h-14 bg-cyan-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-check-circle text-2xl text-cyan-400"></i>
        </div>
        <div class="text-3xl md:text-4xl font-bold text-white mb-1"><?= number_format($s['ap'] ?? 0) ?></div>
        <div class="text-slate-400 text-sm font-medium">অনুমোদিত</div>
      </div>

      <div class="stat-card card p-6 text-center">
        <div class="w-14 h-14 bg-emerald-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-check-double text-2xl text-emerald-400"></i>
        </div>
        <div class="text-3xl md:text-4xl font-bold text-white mb-1"><?= number_format($s['co'] ?? 0) ?></div>
        <div class="text-slate-400 text-sm font-medium">সম্পন্ন</div>
      </div>
    </div>

    <!-- Recent Bookings -->
    <div class="card overflow-hidden mb-8">
      <div class="p-6 border-b border-slate-700/50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center shadow-lg">
            <i class="fas fa-calendar-alt text-white"></i>
          </div>
          <div>
            <h3 class="text-lg font-bold">সাম্প্রতিক বুকিং</h3>
            <p class="text-slate-500 text-sm">আপনার সর্বশেষ বুকিং সমূহ</p>
          </div>
        </div>
        <a href="bookings.php" class="btn-action bg-slate-700/50 text-slate-300 hover:bg-slate-600/50 text-sm">
          সব দেখুন <i class="fas fa-arrow-right"></i>
        </a>
      </div>

      <?php if (empty($bookings)): ?>
        <div class="p-12 text-center">
          <div class="w-20 h-20 bg-slate-800/50 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-calendar-xmark text-4xl text-slate-600"></i>
          </div>
          <p class="text-slate-500 mb-4">এখনো কোনো বুকিং নেই।</p>
          <a href="<?= SITE_URL ?>/services.php" class="btn-action btn-primary">
            <i class="fas fa-search"></i> সেবা খুঁজুন
          </a>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-slate-800/50 border-b border-slate-700/50">
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">রেফারেন্স</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">সেবাদাতা</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">তারিখ</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">স্ট্যাটাস</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/30">
              <?php foreach ($bookings as $b): ?>
                <tr class="hover:bg-slate-800/30 transition-all">
                  <td class="px-6 py-4">
                    <span class="font-mono text-purple-400 bg-purple-500/10 px-3 py-1.5 rounded-lg text-sm font-bold">
                      <?= e($b['ref_code']) ?>
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-cyan-500 to-blue-500 rounded-xl flex items-center justify-center text-lg">
                        <?= $b['emoji'] ?>
                      </div>
                      <div>
                        <div class="font-semibold"><?= e($b['company_name']) ?></div>
                        <div class="text-xs text-slate-500"><?= svcLabel($b['service_type']) ?></div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-slate-400"><?= date('d M Y', strtotime($b['preferred_date'])) ?></td>
                  <td class="px-6 py-4">
                    <?php
                    $statusConfig = [
                      'Completed' => ['bg-emerald-500/20 text-emerald-400', '✓ সম্পন্ন'],
                      'Approved' => ['bg-blue-500/20 text-blue-400', '✓ অনুমোদিত'],
                      'Pending' => ['bg-amber-500/20 text-amber-400', '⏳ অপেক্ষমান'],
                      'Cancelled' => ['bg-red-500/20 text-red-400', '✕ বাতিল'],
                    ];
                    $config = $statusConfig[$b['status']] ?? ['bg-slate-500/20 text-slate-400', $b['status']];
                    ?>
                    <span class="px-3 py-1.5 rounded-lg text-xs font-bold <?= $config[0] ?>"><?= $config[1] ?></span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- CTA Section -->
    <div class="card p-8 bg-gradient-to-r from-emerald-600/20 to-teal-600/20 border-emerald-500/30">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="flex items-center gap-4">
          <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-2xl flex items-center justify-center shadow-xl">
            <i class="fas fa-rocket text-white text-2xl"></i>
          </div>
          <div>
            <h3 class="text-xl font-bold mb-1">নতুন সেবা বুক করুন</h3>
            <p class="text-slate-400">ঢাকার সেরা পেশাদার সেবাদাতাদের সাথে যুক্ত হন</p>
          </div>
        </div>
        <a href="<?= SITE_URL ?>/services.php" class="btn-action btn-primary text-lg px-8 py-4">
          <i class="fas fa-search"></i> সেবা খুঁজুন
        </a>
      </div>
    </div>
  </main>
</div>

<script>
document.getElementById('menuToggle')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('-translate-x-full');
});
</script>

</body>
</html>
