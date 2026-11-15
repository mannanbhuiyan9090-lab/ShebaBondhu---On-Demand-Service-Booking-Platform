<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';
sessionStart();
require_once __DIR__ . '/_stats.php';
requireAuth('admin');

require_once __DIR__ . '/_notif-scripts.php';

$user = auth();
$s = getAdminStats();

function gCsrfOk(): bool {
    $_POST['_csrf'] = $_GET['_csrf'] ?? '';
    return csrfCheck() ?? false; // Ensure a boolean value is returned
}

if (isset($_GET['action']) && $_GET['action'] === 'approve' && isset($_GET['id'])) {
    if (!gCsrfOk()) {
        $_SESSION['flash'] = ['msg' => 'নিরাপত্তা ত্রুটি।', 'type' => 'error'];
        header('Location: applications.php');
        exit;
    }
    $aid = (int)$_GET['id'];
    $app = db()->prepare('SELECT * FROM provider_applications WHERE id=? AND status=?');
    $app->execute([$aid, 'pending']);
    $app = $app->fetch();

    if ($app) {
        try {
            $tempPass = bin2hex(random_bytes(6));
            $hash = password_hash($tempPass, PASSWORD_DEFAULT);
            $name = trim($app['fname'] . ' ' . $app['lname']);

            db()->prepare('INSERT INTO users (name,email,password,phone,role) VALUES (?,?,?,?,?)')
                ->execute([$name, $app['email'], $hash, $app['phone'], 'provider']);
            $uid = (int)db()->lastInsertId();

            db()->prepare('INSERT INTO providers (user_id,company_name,service_type,city,about,work_start,work_end,verified) VALUES (?,?,?,?,?,?,?,1)')
                ->execute([$uid, $app['company_name'], $app['service_type'], $app['city'], $app['about'], $app['work_start'], $app['work_end']]);

            db()->prepare("UPDATE provider_applications SET status='approved' WHERE id=?")->execute([$aid]);

            $_SESSION['flash'] = ['msg' => "অনুমোদিত! সাময়িক পাসওয়ার্ড: $tempPass", 'type' => 'success'];
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['msg' => 'ত্রুটি: ' . $e->getMessage(), 'type' => 'error'];
        }
    }
    header('Location: applications.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'reject' && isset($_GET['id'])) {
    if (!gCsrfOk()) {
        $_SESSION['flash'] = ['msg' => 'নিরাপত্তা ত্রুটি।', 'type' => 'error'];
        header('Location: applications.php');
        exit;
    }
    db()->prepare("UPDATE provider_applications SET status='rejected' WHERE id=?")->execute([(int)$_GET['id']]);
    $_SESSION['flash'] = ['msg' => 'আবেদন প্রত্যাখ্যান হয়েছে।', 'type' => 'success'];
    header('Location: applications.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (!gCsrfOk()) {
        $_SESSION['flash'] = ['msg' => 'নিরাপত্তা ত্রুটি।', 'type' => 'error'];
        header('Location: applications.php');
        exit;
    }
    db()->prepare('DELETE FROM provider_applications WHERE id=?')->execute([(int)$_GET['id']]);
    $_SESSION['flash'] = ['msg' => 'আবেদন মুছে ফেলা হয়েছে।', 'type' => 'success'];
    header('Location: applications.php');
    exit;
}

$filterStatus = $_GET['status'] ?? '';
$stmt = $filterStatus !== ''
    ? db()->prepare('SELECT * FROM provider_applications WHERE status=? ORDER BY submitted_at DESC')
    : db()->prepare('SELECT * FROM provider_applications ORDER BY submitted_at DESC');
$filterStatus !== '' ? $stmt->execute([$filterStatus]) : $stmt->execute();
$apps = $stmt->fetchAll();

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
  <title>আবেদন - সেবা বন্ধু Pro Admin</title>
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
    .btn-danger { background: rgba(239, 68, 68, 0.2); color: #EF4444; }
    .btn-danger:hover { background: rgba(239, 68, 68, 0.3); }
    .btn-success { background: rgba(16, 185, 129, 0.2); color: #10B981; }
    .btn-success:hover { background: rgba(16, 185, 129, 0.3); }
    .btn-warning { background: rgba(245, 158, 11, 0.2); color: #F59E0B; }
    .btn-warning:hover { background: rgba(245, 158, 11, 0.3); }
    .btn-outline { background: rgba(71, 85, 105, 0.3); color: white; }
    .btn-outline:hover { background: rgba(71, 85, 105, 0.5); }
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
    <a href="bookings.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-calendar-check w-5"></i><span>বুকিং</span></a>
    <a href="payments.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-credit-card w-5"></i><span>পেমেন্ট</span></a>
    <a href="services.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-cogs w-5"></i><span>সেবা</span></a>
    <a href="reviews.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-star w-5"></i><span>রিভিউ</span></a>
    <a href="applications.php" class="sidebar-link active flex items-center gap-3 p-3 rounded-xl text-white relative">
    <i class="fas fa-file-signature w-5"></i><span>আবেদন</span>
    <a href="analytics.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-chart-line w-5"></i><span>রিপোর্ট</span></a>
    <a href="notifications.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-bell w-5"></i><span>নোটিফিকেশন</span></a>
    <a href="settings.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-gear w-5"></i><span>সেটিংস</span></a>
      <?php if (($s['ap'] ?? 0) > 0): ?><span class="absolute right-3 bg-red-500 text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold animate-pulse"><?= $s['ap'] ?></span><?php endif; ?>
    </a>
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
        <h1 class="text-xl md:text-2xl font-bold text-white"><i class="fas fa-file-signature mr-2 text-orange-400"></i> সেবাদাতার আবেদন</h1>
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

    <div class="card overflow-hidden">
      <div class="p-6 border-b border-slate-700/50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-amber-500 rounded-xl flex items-center justify-center shadow-lg">
            <i class="fas fa-list text-white"></i>
          </div>
          <div>
            <h3 class="text-lg font-bold">আবেদন তালিকা</h3>
            <p class="text-slate-500 text-sm">মোট <?= count($apps) ?>টি</p>
          </div>
        </div>
        <div class="flex flex-wrap gap-2">
          <a href="applications.php" class="btn-action <?= $filterStatus === '' ? 'btn-primary' : 'btn-outline' ?> text-sm">সব</a>
          <a href="?status=pending" class="btn-action <?= $filterStatus === 'pending' ? 'btn-warning' : 'btn-outline' ?> text-sm">⏳ অপেক্ষমান</a>
          <a href="?status=approved" class="btn-action <?= $filterStatus === 'approved' ? 'btn-success' : 'btn-outline' ?> text-sm">✓ অনুমোদিত</a>
          <a href="?status=rejected" class="btn-action <?= $filterStatus === 'rejected' ? 'btn-danger' : 'btn-outline' ?> text-sm">✗ প্রত্যাখ্যাত</a>
        </div>
      </div>

      <?php if (empty($apps)): ?>
        <div class="p-12 text-center text-slate-500">
          <i class="fas fa-file-circle-xmark text-4xl mb-3"></i>
          <p>কোনো আবেদন নেই</p>
        </div>
      <?php else: foreach ($apps as $a): ?>
        <div class="p-6 border-b border-slate-700/30 hover:bg-slate-800/20 transition-all <?= $a['status'] === 'pending' ? 'border-l-4 border-l-amber-500' : ($a['status'] === 'approved' ? 'border-l-4 border-l-emerald-500' : 'border-l-4 border-l-red-500') ?>">
          <div class="flex flex-col lg:flex-row lg:items-start gap-6">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-purple-500 rounded-xl flex items-center justify-center text-2xl"><?= svcEmoji($a['service_type']) ?></div>
                <div>
                  <div class="font-bold text-lg"><?= e($a['company_name']) ?></div>
                  <div class="text-sm text-slate-500"><?= e($a['fname'] . ' ' . $a['lname']) ?></div>
                </div>
                <span class="px-3 py-1.5 rounded-lg text-xs font-bold ml-auto <?= $a['status'] === 'pending' ? 'bg-amber-500/20 text-amber-400' : ($a['status'] === 'approved' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400') ?>">
                  <?= $a['status'] === 'pending' ? '⏳ অপেক্ষমান' : ($a['status'] === 'approved' ? '✓ অনুমোদিত' : '✗ প্রত্যাখ্যাত') ?>
                </span>
              </div>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                  <span class="text-slate-500">ইমেইল</span>
                  <div class="font-medium text-cyan-400"><?= e($a['email']) ?></div>
                </div>
                <div>
                  <span class="text-slate-500">ফোন</span>
                  <div class="font-medium"><?= e($a['phone']) ?></div>
                </div>
                <div>
                  <span class="text-slate-500">সেবা</span>
                  <div class="font-medium"><?= svcLabel($a['service_type']) ?></div>
                </div>
                <div>
                  <span class="text-slate-500">এলাকা</span>
                  <div class="font-medium"><?= cityBn($a['city']) ?></div>
                </div>
              </div>
              <?php if ($a['about']): ?>
                <div class="mt-4 p-3 bg-slate-800/50 rounded-xl text-sm text-slate-400 italic">"<?= e(mb_substr($a['about'], 0, 150)) ?>"</div>
              <?php endif; ?>
              <div class="mt-3 text-xs text-slate-500">জমা: <?= date('d M Y, H:i', strtotime($a['submitted_at'])) ?></div>
            </div>
            <div class="flex flex-col gap-2 lg:min-w-[150px]">
              <?php if ($a['status'] === 'pending'): ?>
                <a href="?action=approve&id=<?= (int)$a['id'] ?>&_csrf=<?= csrf() ?>" class="btn-action btn-success text-sm justify-center" onclick="return confirm('অনুমোদন করবেন? একটি অ্যাকাউন্ট তৈরি হবে।')">
                  <i class="fas fa-check"></i> অনুমোদন
                </a>
                <a href="?action=reject&id=<?= (int)$a['id'] ?>&_csrf=<?= csrf() ?>" class="btn-action btn-danger text-sm justify-center" onclick="return confirm('প্রত্যাখ্যান করবেন?')">
                  <i class="fas fa-times"></i> প্রত্যাখ্যান
                </a>
              <?php endif; ?>
              <a href="?action=delete&id=<?= (int)$a['id'] ?>&_csrf=<?= csrf() ?>" class="btn-action btn-outline text-sm justify-center" onclick="return confirm('মুছে ফেলবেন?')">
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
