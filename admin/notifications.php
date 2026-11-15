<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';
sessionStart();
requireAuth('admin');
require_once __DIR__ . '/_stats.php';

require_once __DIR__ . '/_notif-scripts.php';

$user = auth();
$s = getAdminStats();

$notifs = [];
try {
    $notifs = db()->query("SELECT * FROM notification WHERE role_target='admin' ORDER BY created_at DESC")->fetchAll();
} catch (PDOException $e) {
    $notifs = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'mark_read') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                db()->prepare("UPDATE notification SET is_read=1 WHERE id=?")->execute([$id]);
            } catch (PDOException $e) {}
        }
    } elseif ($action === 'mark_all') {
        try {
            db()->exec("UPDATE notification SET is_read=1 WHERE role_target='admin'");
        } catch (PDOException $e) {}
    }
    header("Location: notifications.php");
    exit;
}

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
  <title>নোটিফিকেশন - সেবা বন্ধু Admin</title>
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
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(139, 92, 246, 0.4); }
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
    <a href="dashboard.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-gauge-high w-5"></i><span>ড্যাশবোর্ড</span></a>
    <a href="users.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-users w-5"></i><span>ব্যবহারকারী</span></a>
    <a href="providers.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-user-tie w-5"></i><span>সেবাদাতা</span></a>
    <a href="bookings.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-calendar-check w-5"></i><span>বুকিং</span></a>
    <a href="payments.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-credit-card w-5"></i><span>পেমেন্ট</span></a>
    <a href="services.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-cogs w-5"></i><span>সেবা</span></a>
    <a href="reviews.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-star w-5"></i><span>রিভিউ</span></a>
    <a href="applications.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-file-signature w-5"></i><span>আবেদন</span></a>
    <a href="analytics.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-chart-line w-5"></i><span>রিপোর্ট</span></a>
    <a href="notifications.php" class="sidebar-link active flex items-center gap-3 p-3 rounded-xl text-white"><i class="fas fa-bell w-5 text-purple-400"></i><span>নোটিফিকেশন</span></a>
    <a href="settings.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-gear w-5"></i><span>সেটিংস</span></a>
  </nav>

  <div class="p-4 border-t border-slate-800">
    <a href="<?= SITE_URL ?>/logout.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-red-500/10 text-slate-400 hover:text-red-400 transition-all group">
      <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-cyan-500 rounded-xl flex items-center justify-center font-bold text-sm shadow-lg"><?= strtoupper(mb_substr($user['name'] ?? 'AD', 0, 2)) ?></div>
      <div class="flex-1"><div class="font-semibold text-sm"><?= e($user['name'] ?? 'অ্যাডমিন') ?></div><div class="text-xs text-slate-500">সুপার অ্যাডমিন</div></div>
      <i class="fas fa-right-from-bracket group-hover:rotate-180 transition-transform"></i>
    </a>
</aside>

<button id="menuToggle" class="lg:hidden fixed top-4 left-4 z-50 p-3 bg-slate-800 rounded-xl shadow-lg">
  <i class="fas fa-bars w-5 h-5"></i>
</button>

<div class="lg:ml-64 min-h-screen">
  <header class="sticky top-0 z-40 glass-dark border-b border-slate-800/50">
    <div class="flex items-center justify-between p-4 md:p-6">
      <div class="hidden md:flex items-center gap-3 ml-12">
        <h1 class="text-xl md:text-2xl font-bold text-white"><i class="fas fa-bell mr-2 text-purple-400"></i> নোটিফিকেশন</h1>
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
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold">🔔 নোটিফিকেশন</h2>
        <p class="text-slate-400">সকল বার্তা</p>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="mark_all">
        <button type="submit" class="btn-action btn-primary">
          <i class="fas fa-check-double"></i> সব পড়া চিহ্নিত
        </button>
      </form>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
      <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
      <div class="mb-6 p-4 rounded-xl <?= $flash['type'] === 'error' ? 'bg-red-500/20 text-red-400' : 'bg-emerald-500/20 text-emerald-400' ?> border <?= $flash['type'] === 'error' ? 'border-red-500/30' : 'border-emerald-500/30' ?>"><?= e($flash['msg']) ?></div>
    <?php endif; ?>

    <?php if (empty($notifs)): ?>
      <div class="card p-12 text-center">
        <i class="fas fa-bell-slash text-6xl text-slate-600 mb-4"></i>
        <p class="text-slate-500 text-lg">কোনো নোটিফিকেশন নেই</p>
      </div>
    <?php else: ?>
      <div class="space-y-4">
        <?php foreach ($notifs as $n): ?>
          <div class="card p-5 <?= $n['is_read'] ? 'opacity-60' : 'border-l-4 border-l-purple-500' ?>">
            <div class="flex items-start justify-between">
              <div class="flex items-start gap-4 flex-1">
                <div class="w-12 h-12 rounded-full flex items-center justify-center <?= $n['is_read'] ? 'bg-slate-700 text-slate-500' : 'bg-purple-500/20 text-purple-400' ?>">
                  <i class="fas fa-bell"></i>
                </div>
                <div class="flex-1">
                  <div class="font-bold <?= $n['is_read'] ? 'text-slate-400' : 'text-white' ?>"><?= e($n['title']) ?></div>
                  <?php 
                  $msg = $n['message'] ?? '';
                  if (!empty($msg)):
                  ?>
                    <div class="bg-slate-800/60 border border-purple-500/30 rounded-xl p-4 text-slate-300 text-sm mt-3" id="msg-full-<?= $n['id'] ?>" style="display:none; white-space: pre-wrap;"><?= e($msg) ?></div>
                    <button onclick="toggleMsg(<?= $n['id'] ?>)" id="btn-<?= $n['id'] ?>" class="text-purple-400 text-sm font-bold mt-3 inline-flex items-center gap-1 bg-slate-800 hover:bg-slate-700 px-3 py-2 rounded-lg border border-purple-500/30 transition-all">
                      আরও দেখুন <i class="fas fa-chevron-down"></i>
                    </button>
                  <?php endif; ?>
                  <div class="text-slate-500 text-xs mt-3"><?= $n['created_at'] ?></div>
                </div>
              </div>
              <?php if (empty($n['is_read'])): ?>
                <form method="POST">
                  <input type="hidden" name="action" value="mark_read">
                  <input type="hidden" name="id" value="<?= $n['id'] ?>">
                  <button type="submit" class="text-sm text-purple-400 hover:text-purple-300 px-4 py-2 rounded-lg hover:bg-purple-500/20">
                    পড়া
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>

<script>
document.getElementById('menuToggle')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('-translate-x-full');
});

function toggleMsg(id) {
  var fullEl = document.getElementById('msg-full-' + id);
  var btn = document.getElementById('btn-' + id);
  
  if (fullEl.style.display === 'none') {
    fullEl.style.display = 'block';
    btn.innerHTML = 'কম দেখুন <i class="fas fa-chevron-up"></i>';
  } else {
    fullEl.style.display = 'none';
    btn.innerHTML = 'আরও দেখুন <i class="fas fa-chevron-down"></i>';
  }
}
</script>

<?php renderAdminNotifScripts(); ?>

</body>
</html>