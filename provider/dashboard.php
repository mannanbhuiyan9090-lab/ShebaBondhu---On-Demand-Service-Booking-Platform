<?php
require_once __DIR__.'/../includes/config.php';
sessionStart();
requireAuth('provider');
$user = auth();

try {
  $prov = db()->prepare("SELECT * FROM providers WHERE user_id = ?");
  $prov->execute([$user['id']]);
  $provider = $prov->fetch();
  
  if ($provider) {
    $bookings = db()->prepare("SELECT b.*, u.name AS customer_name, u.phone AS customer_phone FROM bookings b JOIN users u ON u.id = b.customer_id WHERE b.provider_id = ? ORDER BY b.created_at DESC LIMIT 10");
    $bookings->execute([$provider['id']]);
    $bookings = $bookings->fetchAll();
    
    $stats = db()->prepare("SELECT 
      COUNT(*) as total_count,
      SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
      SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved_count,
      SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_count
    FROM bookings WHERE provider_id = ?");
    $stats->execute([$provider['id']]);
    $stats = $stats->fetch();

    $revenueStats = db()->prepare("SELECT COALESCE(SUM(amount), 0) as total_amount FROM bookings WHERE provider_id = ? AND status = 'Completed' AND amount IS NOT NULL");
    $revenueStats->execute([$provider['id']]);
    $revenueStats = $revenueStats->fetch();
    $totalRevenue = (int)($revenueStats['total_amount'] ?? 0);
        
    $reviews = db()->prepare("SELECT r.*, u.name AS customer_name FROM reviews r JOIN users u ON u.id = r.customer_id WHERE r.provider_id = ? ORDER BY r.created_at DESC LIMIT 5");
    $reviews->execute([$provider['id']]);
    $reviews = $reviews->fetchAll();
  } else {
    $bookings = [];
    $stats = ['total_count' => 0, 'pending_count' => 0, 'approved_count' => 0, 'completed_count' => 0];
    $reviews = [];
    $totalRevenue = 0;
  }
} catch(Exception $e) {
  $bookings = [];
  $stats = ['total_count' => 0, 'pending_count' => 0, 'approved_count' => 0, 'completed_count' => 0];
  $totalRevenue = 0;
  $provider = null;
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ড্যাশবোর্ড - সেবা বন্ধু প্রোভাইডার</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Hind Siliguri', sans-serif; }
    .glass-dark { background: rgba(88, 28, 135, 0.6); backdrop-filter: blur(20px); }
    .card { background: rgba(88, 28, 135, 0.3); backdrop-filter: blur(20px); border: 1px solid rgba(167, 139, 250, 0.3); border-radius: 1.5rem; }
    .sidebar-link { transition: all 0.2s ease; }
    .sidebar-link:hover { background: rgba(71, 85, 105, 0.3); transform: translateX(4px); }
    .sidebar-link.active { background: linear-gradient(90deg, rgba(167, 139, 250, 0.3), rgba(45, 212, 191, 0.1)); border-left: 3px solid #a78bfa; }
    .gradient-text { background: linear-gradient(135deg, #a78bfa, #2dd4bf); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
    .stat-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3); }
    .btn-action { padding: 8px 16px; border-radius: 12px; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px; }
    .btn-primary { background: linear-gradient(135deg, #a78bfa, #2dd4bf); color: white; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(167, 139, 250, 0.4); }
    .btn-success { background: rgba(16, 185, 129, 0.2); color: #10B981; }
    .btn-success:hover { background: rgba(16, 185, 129, 0.3); }
    .btn-danger { background: rgba(239, 68, 68, 0.2); color: #EF4444; }
    .btn-danger:hover { background: rgba(239, 68, 68, 0.3); }
    .pulse-dot { animation: pulse 2s infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
  </style>
</head>
<body class="bg-gradient-to-br from-purple-900 via-slate-900 to-teal-900 text-white min-h-screen">

<aside id="sidebar" class="w-64 bg-gradient-to-b from-purple-900/60 to-teal-900/40 h-screen fixed left-0 top-0 border-r border-purple-500/30 z-50 flex flex-col">
  <div class="p-6 border-b border-purple-500/30">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-teal-500 rounded-xl flex items-center justify-center shadow-lg">
        <i class="fas fa-bolt text-white"></i>
      </div>
      <div>
        <div class="text-xl font-bold gradient-text">সেবা বন্ধু</div>
        <div class="text-xs text-slate-500 uppercase tracking-wider">সেবাদাতা প্যানেল</div>
      </div>
    </div>
  </div>
  
  <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
    <a href="dashboard.php" class="sidebar-link active flex items-center gap-3 p-3 rounded-xl text-white">
      <i class="fas fa-gauge-high w-5 text-purple-400"></i>
      <span>ড্যাশবোর্ড</span>
    </a>
    <a href="bookings.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-calendar-check w-5"></i>
      <span>বুকিং সমূহ</span>
    </a>
    <a href="services.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-tools w-5 text-orange-400"></i>
      <span>আমার সেবা</span>
    </a>
    <a href="profile.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-user w-5"></i>
      <span>প্রোফাইল</span>
    </a>
    <a href="availability.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-toggle-on w-5"></i>
      <span>উপলব্ধতা</span>
    </a>
  </nav>

  <?php if ($provider): ?>
    <div class="p-4 border-t border-slate-700/50">
      <div class="p-3 rounded-xl <?= $provider['available'] ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400' ?> text-center">
        <div class="flex items-center justify-center gap-2 font-bold">
          <span class="w-2 h-2 rounded-full <?= $provider['available'] ? 'bg-emerald-400 pulse-dot' : 'bg-red-400' ?>"></span>
          <?= $provider['available'] ? 'এখন উপলব্ধ' : 'এখন অনুপলব্ধ' ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="p-4 border-t border-slate-700/50">
    <a href="<?= SITE_URL ?>/logout.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-red-500/10 text-slate-400 hover:text-red-400 transition-all group">
      <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-teal-500 rounded-xl flex items-center justify-center font-bold text-sm shadow-lg">
        <?= strtoupper(mb_substr($user['name'], 0, 2)) ?>
      </div>
      <div class="flex-1">
        <div class="font-semibold text-sm"><?= e(explode(' ', $user['name'])[0]) ?></div>
        <div class="text-xs text-orange-400">সেবাদাতা</div>
      </div>
      <i class="fas fa-right-from-bracket group-hover:rotate-180 transition-transform"></i>
    </a>
  </div>
</aside>

<button id="menuToggle" class="lg:hidden fixed top-4 left-4 z-50 p-3 bg-slate-800 rounded-xl shadow-lg">
  <i class="fas fa-bars w-5 h-5"></i>
</button>

<div class="lg:ml-64 min-h-screen">
  <header class="sticky top-0 z-40 glass-dark border-b border-purple-500/30">
    <div class="flex items-center justify-between p-4 md:p-6">
      <div class="hidden md:flex items-center gap-3 ml-12">
        <h1 class="text-xl md:text-2xl font-bold text-white">
          <i class="fas fa-tools mr-2 text-purple-400"></i> সেবাদাতা ড্যাশবোর্ড
        </h1>
        <?php if ($provider): ?>
          <span class="flex items-center gap-2 px-3 py-1 <?= $provider['available'] ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400' ?> rounded-full text-xs font-bold">
            <span class="w-2 h-2 rounded-full <?= $provider['available'] ? 'bg-emerald-400 pulse-dot' : 'bg-red-400' ?>"></span>
            <?= $provider['available'] ? 'উপলব্ধ' : 'অনুপলব্ধ' ?>
          </span>
        <?php endif; ?>
      </div>
      <div class="md:hidden flex items-center gap-2">
        <span class="text-lg font-bold gradient-text">সেবা বন্ধু</span>
      </div>
    </div>
  </header>

  <main class="p-4 md:p-8 max-w-7xl mx-auto">
    <?php if (!$provider): ?>
      <div class="card p-12 text-center">
        <div class="w-20 h-20 bg-orange-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-exclamation-triangle text-4xl text-orange-400"></i>
        </div>
        <h2 class="text-2xl font-bold mb-2">প্রোফাইল তৈরি করুন</h2>
        <p class="text-slate-500 mb-4">আপনার সেবাদাতা প্রোফাইল তৈরি করতে হবে।</p>
        <a href="profile.php" class="btn-action btn-primary">
          <i class="fas fa-user-plus"></i> প্রোফাইল তৈরি করুন
        </a>
      </div>
    <?php else: ?>
      <div class="mb-8">
        <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">
          স্বাগতম, <span class="gradient-text"><?= e(explode(' ', $user['name'])[0]) ?>!</span> 🔧
        </h2>
        <p class="text-slate-400"><?= e($provider['company_name']) ?> - আপনার সেবা প্যানেলে স্বাগতম।</p>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
        <div class="stat-card card p-6 text-center">
          <div class="w-14 h-14 bg-orange-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-calendar-check text-2xl text-orange-400"></i>
          </div>
          <div class="text-3xl md:text-4xl font-bold text-white mb-1"><?= number_format($stats['total_count'] ?? 0) ?></div>
          <div class="text-slate-400 text-sm font-medium">মোট বুকিং</div>
        </div>

        <div class="stat-card card p-6 text-center">
          <div class="w-14 h-14 bg-amber-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-clock text-2xl text-amber-400"></i>
          </div>
          <div class="text-3xl md:text-4xl font-bold text-white mb-1"><?= number_format($stats['pending_count'] ?? 0) ?></div>
          <div class="text-slate-400 text-sm font-medium">অপেক্ষমান</div>
        </div>

        <div class="stat-card card p-6 text-center">
          <div class="w-14 h-14 bg-blue-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-check-circle text-2xl text-blue-400"></i>
          </div>
          <div class="text-3xl md:text-4xl font-bold text-white mb-1"><?= number_format($stats['approved_count'] ?? 0) ?></div>
          <div class="text-slate-400 text-sm font-medium">অনুমোদিত</div>
        </div>

        <div class="stat-card card p-6 text-center">
          <div class="w-14 h-14 bg-emerald-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-trophy text-2xl text-emerald-400"></i>
          </div>
          <div class="text-3xl md:text-4xl font-bold text-white mb-1"><?= number_format($stats['completed_count'] ?? 0) ?></div>
          <div class="text-slate-400 text-sm font-medium">সম্পন্ন</div>
        </div>
        
        <div class="stat-card card p-6 text-center">
          <div class="w-14 h-14 bg-purple-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-money-bill-wave text-2xl text-purple-400"></i>
          </div>
          <div class="text-3xl md:text-4xl font-bold text-white mb-1">৳ <?= number_format($totalRevenue) ?></div>
          <div class="text-slate-400 text-sm font-medium">মোট আয়</div>
        </div>
      </div>

      <!-- Recent Bookings -->
      <div class="card overflow-hidden mb-8">
        <div class="p-6 border-b border-slate-700/50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-teal-500 rounded-xl flex items-center justify-center shadow-lg">
              <i class="fas fa-calendar-alt text-white"></i>
            </div>
            <div>
              <h3 class="text-lg font-bold">সাম্প্রতিক বুকিং</h3>
              <p class="text-slate-500 text-sm">গ্রাহকদের নতুন বুকিং সমূহ</p>
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
            <p class="text-slate-500">এখনো কোনো বুকিং নেই।</p>
          </div>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700/50">
                  <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">রেফারেন্স</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">গ্রাহক</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">তারিখ</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">স্ট্যাটাস</th>
                  <th class="px-6 py-4 text-right text-xs font-semibold text-slate-400 uppercase">অ্যাকশন</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-700/30">
                <?php foreach ($bookings as $b): ?>
                  <tr class="hover:bg-slate-800/30 transition-all">
                    <td class="px-6 py-4">
                      <span class="font-mono text-orange-400 bg-orange-500/10 px-3 py-1.5 rounded-lg text-sm font-bold">
                        <?= e($b['ref_code']) ?>
                      </span>
                    </td>
                    <td class="px-6 py-4">
                      <div class="font-semibold"><?= e($b['customer_name']) ?></div>
                      <div class="text-xs text-slate-500"><?= e($b['customer_phone'] ?? '') ?></div>
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
                    <td class="px-6 py-4">
                      <div class="flex items-center justify-end gap-2">
                        <?php if ($b['status'] === 'Pending'): ?>
                          <a href="update-booking.php?action=approve&id=<?= $b['id'] ?>" class="btn-action btn-success text-xs">
                            <i class="fas fa-check"></i>
                          </a>
                          <a href="update-booking.php?action=cancel&id=<?= $b['id'] ?>" class="btn-action btn-danger text-xs">
                            <i class="fas fa-times"></i>
                          </a>
                        <?php else: ?>
                          <a href="bookings.php" class="btn-action bg-slate-700/50 text-slate-300 hover:bg-slate-600/50 text-xs">
                            <i class="fas fa-eye"></i>
                          </a>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </main>
</div>

<script>
document.getElementById('menuToggle')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('-translate-x-full');
});
</script>

</body>
</html>
