<?php
require_once __DIR__ . '/../includes/config.php';
sessionStart();
requireAuth('admin');
require_once __DIR__ . '/_sidebar.php';

$user = auth();
$period = $_GET['period'] ?? 'all';
$year = (int)($_GET['year'] ?? date('Y'));

// Get date ranges
$today = date('Y-m-d');
$weekStart = date('Y-m-d', strtotime('-7 days'));
$monthStart = date('Y-m-01');
$yearStart = date('Y-01-01');

// New bookings count (Pending status)
$newBookings = (int)db()->query("SELECT COUNT(*) FROM bookings WHERE status='Pending'")->fetchColumn();

// Ensure amount column exists
try {
    db()->query("ALTER TABLE bookings ADD COLUMN amount DECIMAL(10,2) DEFAULT NULL AFTER payment_code");
} catch (Exception $e) {}

try {
    db()->query("ALTER TABLE bookings ADD COLUMN is_read TINYINT DEFAULT 0");
} catch (Exception $e) {}

// All time stats
$totalBookings = (int)db()->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$totalUsers = (int)db()->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalProviders = (int)db()->query("SELECT COUNT(*) FROM providers")->fetchColumn();

// Revenue with 15% service charge from completed and paid bookings
$totalAmount = (float)db()->query("SELECT COALESCE(SUM(amount), 0) FROM bookings WHERE status='Completed' AND payment_status='paid'")->fetchColumn();
$totalRevenue = $totalAmount * 0.15;

// Weekly stats
$weeklyBookings = (int)db()->query("SELECT COUNT(*) FROM bookings WHERE created_at >= '$weekStart'")->fetchColumn();
$weeklyAmount = (float)db()->query("SELECT COALESCE(SUM(amount), 0) FROM bookings WHERE status='Completed' AND payment_status='paid' AND created_at >= '$weekStart'")->fetchColumn();
$weeklyRevenue = $weeklyAmount * 0.15;

// Monthly stats
$monthlyBookings = (int)db()->query("SELECT COUNT(*) FROM bookings WHERE created_at >= '$monthStart'")->fetchColumn();
$monthlyAmount = (float)db()->query("SELECT COALESCE(SUM(amount), 0) FROM bookings WHERE status='Completed' AND payment_status='paid' AND created_at >= '$monthStart'")->fetchColumn();
$monthlyRevenue = $monthlyAmount * 0.15;

// Yearly stats
$yearlyBookings = (int)db()->query("SELECT COUNT(*) FROM bookings WHERE created_at >= '$yearStart'")->fetchColumn();
$yearlyAmount = (float)db()->query("SELECT COALESCE(SUM(amount), 0) FROM bookings WHERE status='Completed' AND payment_status='paid' AND created_at >= '$yearStart'")->fetchColumn();
$yearlyRevenue = $yearlyAmount * 0.15;

// Current period data
if ($period === 'weekly') {
    $currentBookings = $weeklyBookings;
    $currentRevenue = $weeklyRevenue;
} elseif ($period === 'monthly') {
    $currentBookings = $monthlyBookings;
    $currentRevenue = $monthlyRevenue;
} elseif ($period === 'yearly') {
    $currentBookings = $yearlyBookings;
    $currentRevenue = $yearlyRevenue;
} else {
    $currentBookings = $totalBookings;
    $currentRevenue = $totalRevenue;
}

// Status counts
$pending = (int)db()->query("SELECT COUNT(*) FROM bookings WHERE status='Pending'")->fetchColumn();
$approved = (int)db()->query("SELECT COUNT(*) FROM bookings WHERE status='Approved'")->fetchColumn();
$completed = (int)db()->query("SELECT COUNT(*) FROM bookings WHERE status='Completed'")->fetchColumn();
$cancelled = (int)db()->query("SELECT COUNT(*) FROM bookings WHERE status='Cancelled'")->fetchColumn();

// Top services
$topServices = db()->query("SELECT service_type, COUNT(*) as cnt FROM bookings GROUP BY service_type ORDER BY cnt DESC LIMIT 5")->fetchAll();

// Top providers
$topProviders = db()->query("
    SELECT pr.company_name, pr.city, pr.service_type, 
    COUNT(b.id) as total,
    SUM(CASE WHEN b.status='Completed' THEN 1 ELSE 0 END) as completed
    FROM providers pr 
    LEFT JOIN bookings b ON b.provider_id = pr.id 
    GROUP BY pr.id 
    ORDER BY total DESC 
    LIMIT 5
")->fetchAll();

// Recent bookings
$recentBookings = db()->query("
    SELECT b.*, u.name as customer_name, pr.company_name as provider_name
    FROM bookings b
    JOIN users u ON u.id = b.customer_id
    JOIN providers pr ON pr.id = b.provider_id
    ORDER BY b.created_at DESC LIMIT 10
")->fetchAll();

// Unread notifications
$unreadNotifs = (int)db()->query("SELECT COUNT(*) FROM notification WHERE role_target='admin' AND is_read=0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>রিপোর্ট - সেবা বন্ধু Admin</title>
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
      <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-cyan-500 rounded-xl flex items-center justify-center shadow-lg">
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
    <a href="analytics.php" class="sidebar-link active flex items-center gap-3 p-3 rounded-xl text-white"><i class="fas fa-chart-line w-5 text-purple-400"></i><span>রিপোর্ট</span></a>
    <a href="notifications.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-bell w-5"></i><span>নোটিফিকেশন</span></a>
    <a href="settings.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-gear w-5"></i><span>সেটিংস</span></a>
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
  <!-- Header with Notification Bell -->
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold mb-2">📊 রিপোর্ট ও অ্যানালিটিক্স</h1>
      <p class="text-slate-400">আপনার প্ল্যাটফর্মের সম্পূর্ণ ডেটা বিশ্লেষণ</p>
    </div>
    <div class="relative" id="notifContainer">
      <button id="notifBtn" class="relative p-3 bg-slate-800/50 hover:bg-slate-700/50 rounded-xl transition-all">
        <i class="fas fa-bell w-5 text-purple-400"></i>
        <?php if ($unreadNotifs > 0): ?><span id="notifBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold animate-pulse"><?= $unreadNotifs ?></span><?php endif; ?>
      </button>
      <div id="notifDropdown" class="absolute right-0 mt-3 w-80 glass rounded-2xl border border-slate-700/50 shadow-2xl hidden z-50">
        <div class="p-4 border-b border-slate-700/50">
          <h4 class="font-bold flex items-center gap-2"><i class="fas fa-bell text-purple-400"></i> নোটিফিকেশন</h4>
        </div>
        <div id="notifList" class="max-h-80 overflow-y-auto divide-y divide-slate-700/30">
          <div class="p-4 text-center text-slate-400">
            <i class="fas fa-bell-slash text-2xl mb-2"></i>
            <p class="text-sm">কোনো নোটিফিকেশন নেই</p>
          </div>
        </div>
      </div>
    </div>
  </div>
    
    <!-- Period Filter -->
    <div class="glass p-4 rounded-xl mb-6 flex flex-wrap items-center gap-3">
      <span class="text-slate-400">সময়কাল নির্বাচন করুন:</span>
      <a href="?period=all" class="px-4 py-2 rounded-lg <?= $period==='all'?'bg-purple-600':'bg-slate-700' ?> hover:bg-purple-500 transition">সব সময়</a>
      <a href="?period=weekly" class="px-4 py-2 rounded-lg <?= $period==='weekly'?'bg-purple-600':'bg-slate-700' ?> hover:bg-purple-500 transition">গত ৭ দিন</a>
      <a href="?period=monthly" class="px-4 py-2 rounded-lg <?= $period==='monthly'?'bg-purple-600':'bg-slate-700' ?> hover:bg-purple-500 transition">এই মাস</a>
      <a href="?period=yearly" class="px-4 py-2 rounded-lg <?= $period==='yearly'?'bg-purple-600':'bg-slate-700' ?> hover:bg-purple-500 transition">এই বছর</a>
      <span class="ml-auto text-slate-400">নির্বাচিত: <?= $period==='all'?'সব সময়':($period==='weekly'?'গত ৭ দিন':($period==='monthly'?'এই মাস':'এই বছর')) ?></span>
    </div>
    
    <!-- Current Period Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="glass p-5 rounded-xl">
        <div class="text-slate-400 text-sm mb-1">বর্তমান পিরিয়ড আয় (১৫% সার্ভিস চার্জ)</div>
        <div class="text-3xl font-bold text-emerald-400">৳<?= number_format((float)$currentRevenue, 2) ?></div>
      </div>
      <div class="glass p-5 rounded-xl">
        <div class="text-slate-400 text-sm mb-1">বর্তমান পিরিয়ড বুকিং</div>
        <div class="text-3xl font-bold text-purple-400"><?= $currentBookings ?></div>
      </div>
    </div>
    
    <!-- All Time Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="glass p-5 rounded-xl">
        <div class="text-slate-400 text-sm mb-1">মোট বুকিং</div>
        <div class="text-3xl font-bold text-purple-400"><?= $totalBookings ?></div>
      </div>
      <div class="glass p-5 rounded-xl">
        <div class="text-slate-400 text-sm mb-1">মোট গ্রাহক</div>
        <div class="text-3xl font-bold text-cyan-400"><?= $totalUsers ?></div>
      </div>
      <div class="glass p-5 rounded-xl">
        <div class="text-slate-400 text-sm mb-1">মোট সেবাদাতা</div>
        <div class="text-3xl font-bold text-emerald-400"><?= $totalProviders ?></div>
      </div>
      <div class="glass p-5 rounded-xl">
        <div class="text-slate-400 text-sm mb-1">মোট আয় (১৫% সার্ভিস চার্জ)</div>
        <div class="text-3xl font-bold text-yellow-400">৳<?= number_format((float)$totalRevenue, 2) ?></div>
        <div class="text-xs text-slate-500">মোট অ্যামাউন্ট: ৳<?= number_format($totalAmount, 2) ?></div>
      </div>
    </div>
    
    <!-- Weekly/Monthly/Yearly Breakdown -->
    <div class="grid md:grid-cols-3 gap-4 mb-6">
      <div class="glass p-5 rounded-xl">
        <h3 class="font-bold text-purple-400 mb-3">📅 গত ৭ দিন</h3>
        <div class="text-2xl font-bold text-green-400  mb-1"><?= $weeklyBookings ?></div>
        <div class="text-slate-400 text-sm">বুকিং</div>
        <div class="text-xl font-bold text-emerald-400 mt-2">৳<?= number_format((float)$weeklyRevenue, 2) ?></div>
        <div class="text-slate-400 text-sm">আয় (১৫% সার্ভিস চার্জ)</div>
      </div>
      <div class="glass p-5 rounded-xl">
        <h3 class="font-bold text-yellow-400 mb-3">📅 এই মাস</h3>
        <div class="text-2xl font-bold text-cyan-400 mb-1"><?= $monthlyBookings ?></div>
        <div class="text-slate-400 text-sm">বুকিং</div>
        <div class="text-xl font-bold text-cyan-400 mt-2">৳<?= number_format((float)$monthlyRevenue, 2) ?></div>
        <div class="text-slate-400 text-sm">আয় (১৫% সার্ভিস চার্জ)</div>
      </div>
      <div class="glass p-5 rounded-xl">
        <h3 class="font-bold text-emerald-400 mb-3">📅 এই বছর</h3>
        <div class="text-2xl font-bold text-purple-400 mb-1"><?= $yearlyBookings ?></div>
        <div class="text-slate-400 text-sm">বুকিং</div>
        <div class="text-xl font-bold text-purple-400 mt-2">৳<?= number_format((float)$yearlyRevenue, 2) ?></div>
        <div class="text-slate-400 text-sm">আয় (১৫% সার্ভিস চার্জ)</div>
      </div>
    </div>
    
    <!-- Booking Status -->
    <div class="grid md:grid-cols-2 gap-6 mb-6">
      <div class="glass p-5 rounded-xl">
        <h3 class="font-bold text-lg mb-4">📈 বুকিং স্ট্যাটাস</h3>
        <div class="space-y-3">
          <div class="flex justify-between items-center p-3 bg-yellow-500/10 rounded-lg"><span>অপেক্ষমান</span><span class="font-bold text-yellow-400"><?= $pending ?></span></div>
          <div class="flex justify-between items-center p-3 bg-blue-500/10 rounded-lg"><span>অনুমোদিত</span><span class="font-bold text-blue-400"><?= $approved ?></span></div>
          <div class="flex justify-between items-center p-3 bg-green-500/10 rounded-lg"><span>সম্পন্ন</span><span class="font-bold text-green-400"><?= $completed ?></span></div>
          <div class="flex justify-between items-center p-3 bg-red-500/10 rounded-lg"><span>বাতিল</span><span class="font-bold text-red-400"><?= $cancelled ?></span></div>
        </div>
      </div>
      
      <div class="glass p-5 rounded-xl">
        <h3 class="font-bold text-lg mb-4">🔧 জনপ্রিয় সেবা</h3>
        <?php if (empty($topServices)): ?>
          <p class="text-slate-500">কোনো ডাটা নেই</p>
        <?php else: ?>
          <div class="space-y-3">
            <?php foreach ($topServices as $i => $sv): ?>
              <div class="flex items-center gap-3">
                <span class="w-6 h-6 rounded-full bg-purple-500/20 text-purple-400 text-xs flex items-center justify-center"><?= $i+1 ?></span>
                <div class="flex-1"><div class="text-sm"><?= e($sv['service_type']) ?></div></div>
                <span class="text-purple-400 font-bold"><?= $sv['cnt'] ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
    
    <!-- Top Providers & Recent Bookings -->
    <div class="grid md:grid-cols-2 gap-6 mb-6">
      <div class="glass p-5 rounded-xl">
        <h3 class="font-bold text-lg mb-4">🏆 শীর্ষ সেবাদাতা</h3>
        <?php if (empty($topProviders)): ?>
          <p class="text-slate-500">কোনো ডাটা নেই</p>
        <?php else: ?>
          <div class="space-y-3">
            <?php foreach ($topProviders as $i => $p): ?>
              <div class="flex items-center gap-3 p-2 bg-slate-800/50 rounded-lg">
                <span class="w-6 h-6 rounded-full bg-gradient-to-br from-purple-500 to-cyan-500 flex items-center justify-center text-xs font-bold"><?= $i+1 ?></span>
                <div class="flex-1">
                  <div class="font-bold text-sm"><?= e($p['company_name']) ?></div>
                  <div class="text-xs text-slate-400"><?= e($p['service_type']) ?></div>
                </div>
                <div class="text-right">
                  <div class="text-emerald-400 font-bold"><?= $p['completed'] ?></div>
                  <div class="text-xs text-slate-500">সম্পন্ন</div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      
      <div class="glass p-5 rounded-xl">
        <h3 class="font-bold text-lg mb-4">📋 সাম্প্রতিক বুকিং</h3>
        <?php if (empty($recentBookings)): ?>
          <p class="text-slate-500">কোনো ডাটা নেই</p>
        <?php else: ?>
          <div class="space-y-2">
            <?php foreach ($recentBookings as $b): ?>
              <div class="flex items-center justify-between p-2 border-b border-slate-700">
                <div>
                  <div class="font-mono text-sm"><?= e($b['ref_code']) ?></div>
                  <div class="text-xs text-slate-400"><?= e($b['customer_name']) ?></div>
                </div>
                <span class="px-2 py-1 rounded text-xs <?= $b['status']==='Completed'?'bg-green-500/20 text-green-400':($b['status']==='Pending'?'bg-yellow-500/20 text-yellow-400':($b['status']==='Approved'?'bg-blue-500/20 text-blue-400':'bg-red-500/20 text-red-400')) ?>"><?= $b['status'] ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>

<script>
// Notification bell toggle
const notifBtn = document.getElementById('notifBtn');
const notifDropdown = document.getElementById('notifDropdown');

notifBtn?.addEventListener('click', (e) => {
  e.stopPropagation();
  notifDropdown.classList.toggle('hidden');
  if (!notifDropdown.classList.contains('hidden')) {
    loadNotifications();
  }
});

document.addEventListener('click', (e) => {
  if (!document.getElementById('notifContainer')?.contains(e.target)) {
    notifDropdown?.classList.add('hidden');
  }
});

function loadNotifications() {
  fetch('api.php?action=notifications')
    .then(res => res.json())
    .then(data => {
      const list = document.getElementById('notifList');
      if (data.notifs && data.notifs.length > 0) {
        list.innerHTML = data.notifs.map(n => `
          <a href="notifications.php" class="block p-3 hover:bg-slate-700/30 ${n.is_read ? '' : 'bg-slate-800/30'}">
            <div class="flex items-start gap-3">
              <i class="fas fa-bell text-purple-400 mt-1"></i>
              <div class="flex-1">
                <div class="font-bold text-sm">${n.title}</div>
                <div class="text-xs text-slate-400">${n.message}</div>
                <div class="text-xs text-slate-500 mt-1">${new Date(n.created_at).toLocaleDateString('bn-BD')}</div>
              </div>
            </div>
          </a>
        `).join('');
      } else {
        list.innerHTML = '<div class="p-4 text-center text-slate-400"><i class="fas fa-bell-slash text-2xl mb-2"></i><p class="text-sm">কোনো নোটিফিকেশন নেই</p></div>';
      }
    });
}

// Mark all read
document.getElementById('notifBtn')?.addEventListener('click', () => {
  fetch('api.php?action=mark_all_read', { method: 'POST' })
    .then(() => {
      const badge = document.getElementById('notifBadge');
      if (badge) badge.style.display = 'none';
      loadNotifications();
    });
});

// Auto-refresh
setInterval(() => {
  fetch('api.php?action=unread_notifs')
    .then(res => res.json())
    .then(data => {
      const badge = document.getElementById('notifBadge');
      if (data.unread > 0) {
        badge.textContent = data.unread;
        badge.style.display = 'flex';
      } else if (badge) {
        badge.style.display = 'none';
      }
    });
}, 30000);
</script>

</body>
</html>