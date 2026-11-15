<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';
sessionStart();
require_once __DIR__ . '/_stats.php';
requireAuth('admin');

require_once __DIR__ . '/_notif-scripts.php';

$user = auth();
$s = getAdminStats();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $r = db()->prepare('SELECT provider_id FROM reviews WHERE id=?');
        $r->execute([$id]);
        $row = $r->fetch();
        db()->prepare('DELETE FROM reviews WHERE id=?')->execute([$id]);
        if ($row) {
            db()->prepare("UPDATE providers SET rating = IFNULL((SELECT ROUND(AVG(rating),2) FROM reviews WHERE provider_id=?),5.00), review_count = (SELECT COUNT(*) FROM reviews WHERE provider_id=?) WHERE id = ?")
                ->execute([$row['provider_id'], $row['provider_id'], $row['provider_id']]);
        }
        $_SESSION['flash'] = ['msg' => 'রিভিউ মুছে ফেলা হয়েছে।', 'type' => 'success'];
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['msg' => 'ত্রুটি: ' . $e->getMessage(), 'type' => 'error'];
    }
    header('Location: reviews.php');
    exit;
}

$filterQ = trim($_GET['q'] ?? '');
$filterR = (int)($_GET['rating'] ?? 0);
$w = [];
$p = [];
if ($filterQ !== '') {
    $w[] = '(u.name LIKE ? OR pr.company_name LIKE ? OR r.comment LIKE ?)';
    $p[] = "%$filterQ%";
    $p[] = "%$filterQ%";
    $p[] = "%$filterQ%";
}
if ($filterR > 0) {
    $w[] = 'r.rating = ?';
    $p[] = $filterR;
}
$wh = $w ? 'WHERE ' . implode(' AND ', $w) : '';
$stmt = db()->prepare("SELECT r.*, u.name cname, pr.company_name, pr.service_type, b.preferred_date FROM reviews r JOIN users u ON u.id = r.customer_id JOIN providers pr ON pr.id = r.provider_id JOIN bookings b ON b.id = r.booking_id $wh ORDER BY r.created_at DESC");
$stmt->execute($p);
$reviews = $stmt->fetchAll();

$unreadNotifs = 0;
try {
    $unreadNotifs = (int)db()->query("SELECT COUNT(*) FROM notification WHERE role_target='admin' AND is_read=0")->fetchColumn();
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>রিভিউ - সেবা বন্ধু Pro Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Hind Siliguri', sans-serif; }
    .glass-dark { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(20px); }
    .card { background: rgba(30, 41, 59, 0.5); backdrop-filter: blur(20px); border: 1px solid rgba(71, 85, 105, 0.3); border-radius: 1.5rem; }
    .sidebar-link { transition: all 0.2s ease; }
    .sidebar-link:hover { background: rgba(71, 85, 105, 0.3); transform: translateX(4px); }
    .sidebar-link.active { background: linear-gradient(90deg, rgba(124, 58, 237, 0.2), rgba(6, 182, 212, 0.1)); border-left: 3px solid #8B5CF6; }
    .gradient-text { background: linear-gradient(135deg, #8B5CF6, #06B6D4); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
    .btn-action { padding: 8px 16px; border-radius: 12px; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px; }
    .btn-primary { background: linear-gradient(135deg, #8B5CF6, #7C3AED); color: white; }
    .btn-danger { background: rgba(239, 68, 68, 0.2); color: #EF4444; }
    .btn-danger:hover { background: rgba(239, 68, 68, 0.3); }
    .review-card { background: linear-gradient(135deg, rgba(251, 191, 36, 0.05), rgba(217, 119, 6, 0.02)); border: 1px solid rgba(251, 191, 36, 0.16); border-radius: 1.5rem; padding: 24px; }
    input, select { background: rgba(15, 23, 42, 0.6) !important; border: 1px solid rgba(71, 85, 105, 0.5) !important; color: white !important; border-radius: 12px; }
    input:focus, select:focus { border-color: #8B5CF6 !important; outline: none; }
  </style>
</head>
<body class="bg-slate-950 text-white min-h-screen">

<aside id="sidebar" class="w-64 bg-gradient-to-b from-slate-900 to-slate-950 h-screen fixed left-0 top-0 border-r border-slate-800 z-50 flex flex-col">
  <div class="p-6 border-b border-slate-800">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-cyan-500 rounded-xl flex items-center justify-center shadow-lg"><i class="fas fa-bolt text-white"></i></div>
      <div><div class="text-xl font-bold gradient-text">সেবা বন্ধু</div><div class="text-xs text-slate-500 uppercase tracking-wider">Admin Panel</div></div>
    </div>
  </div>
  <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
    <a href="dashboard.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-gauge-high w-5 text-purple-400"></i><span>ড্যাশবোর্ড</span></a>
    <a href="users.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-users w-5"></i><span>ব্যবহারকারী</span></a>
    <a href="providers.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-user-tie w-5"></i><span>সেবাদাতা</span></a>
    <a href="bookings.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-calendar-check w-5"></i><span>বুকিং</span><?php renderBookingBadge(); ?></a>
    <a href="payments.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-credit-card w-5"></i><span>পেমেন্ট</span></a>
    <a href="services.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-cogs w-5"></i><span>সেবা</span></a>
    <a href="reviews.php" class="sidebar-link active flex items-center gap-3 p-3 rounded-xl text-white"><i class="fas fa-star w-5"></i><span>রিভিউ</span></a>
    <a href="applications.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white relative">
      <i class="fas fa-file-signature w-5"></i><span>আবেদন</span>
      <?php if (($s['ap'] ?? 0) > 0): ?><span class="absolute right-3 bg-red-500 text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold animate-pulse"><?= $s['ap'] ?></span><?php endif; ?>
    </a>
    <a href="analytics.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-chart-line w-5"></i><span>রিপোর্ট</span></a>
    <a href="notifications.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-bell w-5"></i><span>নোটিফিকেশন</span></a>
    <a href="settings.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-gear w-5"></i><span>সেটিংস</span></a>
  </nav>
  <div class="p-4 border-t border-slate-800">
    <a href="<?= SITE_URL ?>/logout.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-red-500/10 text-slate-400 hover:text-red-400 transition-all group">
      <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-cyan-500 rounded-xl flex items-center justify-center font-bold text-sm shadow-lg"><?= strtoupper(mb_substr($user['name'] ?? 'AD', 0, 2)) ?></div>
      <div class="flex-1"><div class="font-semibold text-sm"><?= e($user['name'] ?? 'অ্যাডমিন') ?></div><div class="text-xs text-slate-500">সুপার অ্যাডমিন</div></div>
      <i class="fas fa-right-from-bracket group-hover:rotate-180 transition-transform"></i>
    </a>
  </div>
</aside>

<button id="menuToggle" class="lg:hidden fixed top-4 left-4 z-50 p-3 bg-slate-800 rounded-xl shadow-lg"><i class="fas fa-bars w-5 h-5"></i></button>

<div class="lg:ml-64 min-h-screen">
  <header class="sticky top-0 z-40 glass-dark border-b border-slate-800/50">
    <div class="flex items-center justify-between p-4 md:p-6">
      <div class="hidden md:flex items-center gap-3 ml-12">
        <h1 class="text-xl md:text-2xl font-bold text-white"><i class="fas fa-star mr-2 text-amber-400"></i> গ্রাহক ফিডব্যাক ও রিভিউ</h1>
      </div>
      <div class="md:hidden flex items-center gap-2"><span class="text-lg font-bold gradient-text">সেবা বন্ধু</span></div>
      <div class="flex items-center gap-3">
        <div class="relative" id="notifContainer">
          <button id="notifBtn" class="relative p-3 bg-slate-800/50 hover:bg-slate-700/50 rounded-xl transition-all">
            <i class="fas fa-bell w-5 text-purple-400"></i>
            <?php if ($unreadNotifs > 0): ?><span id="notifBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold animate-pulse"><?= $unreadNotifs ?></span><?php endif; ?>
          </button>
        </div>
      </div>
    </div>
  </header>

  <main class="p-4 md:p-8 max-w-7xl mx-auto">
    <?php if (isset($_SESSION['flash'])): ?>
      <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
      <div class="mb-6 p-4 rounded-xl <?= $flash['type'] === 'error' ? 'bg-red-500/20 text-red-400' : 'bg-emerald-500/20 text-emerald-400' ?> border <?= $flash['type'] === 'error' ? 'border-red-500/30' : 'border-emerald-500/30' ?>"><?= e($flash['msg']) ?></div>
    <?php endif; ?>

    <div class="card p-6 mb-8">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
            <i class="fas fa-list text-white"></i>
          </div>
          <div>
            <h3 class="text-lg font-bold">রিভিউ তালিকা</h3>
            <p class="text-slate-500 text-sm">মোট <?= count($reviews) ?>টি</p>
          </div>
        </div>
        <form method="GET" class="flex flex-wrap gap-3 items-center">
          <div class="relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
            <input type="text" name="q" value="<?= e($filterQ) ?>" placeholder="গ্রাহক / সেবাদাতা…" class="pl-10 pr-4 py-2 w-48 md:w-64 text-sm">
          </div>
          <select name="rating" onchange="this.form.submit()" class="p-2 text-sm">
            <option value="0">সব রেটিং</option>
            <?php for ($i = 5; $i >= 1; $i--): ?>
              <option value="<?= $i ?>" <?= $filterR === $i ? 'selected' : '' ?>><?= str_repeat('★', $i) ?> <?= $i ?> তারকা</option>
            <?php endfor; ?>
          </select>
          <button type="submit" class="btn-action btn-primary text-sm"><i class="fas fa-search"></i></button>
          <?php if ($filterQ || $filterR > 0): ?><a href="reviews.php" class="btn-action bg-slate-700/50 text-slate-300 text-sm"><i class="fas fa-times"></i></a><?php endif; ?>
        </form>
      </div>

      <?php if (empty($reviews)): ?>
        <div class="text-center py-12 text-slate-500">
          <i class="fas fa-star text-4xl mb-3"></i>
          <p>কোনো রিভিউ নেই</p>
        </div>
      <?php else: foreach ($reviews as $r): ?>
        <div class="review-card mb-4 hover:border-amber-500/30 transition-all">
          <div class="flex flex-col lg:flex-row lg:items-start gap-6">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center font-bold">
                  <?= strtoupper(mb_substr($r['cname'], 0, 2)) ?>
                </div>
                <div>
                  <div class="font-semibold"><?= e($r['cname']) ?></div>
                  <div class="text-xs text-slate-500"><?= svcLabel($r['service_type']) ?> → <?= e($r['company_name']) ?></div>
                </div>
              </div>
              <div class="flex items-center gap-4 mb-3">
                <div class="text-2xl text-amber-400 tracking-wider">
                  <?= str_repeat('★', (int)$r['rating']) ?><?= str_repeat('☆', 5 - (int)$r['rating']) ?>
                </div>
                <span class="text-amber-400 font-bold"><?= (int)$r['rating'] ?>/৫</span>
                <span class="text-slate-500 text-sm"><?= date('d M Y', strtotime($r['created_at'])) ?></span>
              </div>
              <?php if ($r['comment']): ?>
                <div class="p-4 bg-slate-800/50 rounded-xl border-l-4 border-amber-500/50">
                  <p class="text-slate-300 italic">"<?= e($r['comment']) ?>"</p>
                </div>
              <?php else: ?>
                <p class="text-slate-500 italic text-sm">কোনো মন্তব্য নেই।</p>
              <?php endif; ?>
            </div>
            <div class="flex items-center gap-2">
              <a href="?delete=<?= (int)$r['id'] ?>&_csrf=<?= csrf() ?>" class="btn-action btn-danger text-sm" onclick="return confirm('এই রিভিউ মুছবেন?')">
                <i class="fas fa-trash"></i> মুছুন
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </main>
</div>

<script>
document.getElementById('menuToggle')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('-translate-x-full');
});
</script>

<?php renderAdminNotifScripts(); ?>

</body>
</html>
