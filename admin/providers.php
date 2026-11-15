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
    try {
        db()->prepare("DELETE FROM providers WHERE id=?")->execute([(int)$_GET['delete']]);
        $_SESSION['flash'] = ['msg' => 'সেবাদাতা মুছে ফেলা হয়েছে।', 'type' => 'success'];
    } catch (Exception $e) {
        $_SESSION['flash'] = ['msg' => 'ত্রুটি।', 'type' => 'error'];
    }
    header('Location: providers.php');
    exit;
}

if (isset($_GET['verify'])) {
    db()->prepare("UPDATE providers SET verified=1 WHERE id=?")->execute([(int)$_GET['verify']]);
    $_SESSION['flash'] = ['msg' => '✓ যাচাই সম্পন্ন।', 'type' => 'success'];
    header('Location: providers.php');
    exit;
}

if (isset($_GET['unverify'])) {
    db()->prepare("UPDATE providers SET verified=0 WHERE id=?")->execute([(int)$_GET['unverify']]);
    $_SESSION['flash'] = ['msg' => 'যাচাই বাতিল হয়েছে।', 'type' => 'success'];
    header('Location: providers.php');
    exit;
}

if (isset($_GET['toggle'])) {
    $p = db()->prepare("SELECT available FROM providers WHERE id=?");
    $p->execute([(int)$_GET['toggle']]);
    $av = $p->fetchColumn();
    db()->prepare("UPDATE providers SET available=? WHERE id=?")->execute([$av ? 0 : 1, (int)$_GET['toggle']]);
    $_SESSION['flash'] = ['msg' => 'উপলব্ধতা পরিবর্তন হয়েছে।', 'type' => 'success'];
    header('Location: providers.php');
    exit;
}

$editProv = null;
if (isset($_GET['edit'])) {
    $ep = db()->prepare("SELECT p.*, u.name owner, u.email, u.phone FROM providers p JOIN users u ON u.id = p.user_id WHERE p.id=?");
    $ep->execute([(int)$_GET['edit']]);
    $editProv = $ep->fetch();
}

$err = $ok = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)($_POST['pid'] ?? 0);
    $company = trim($_POST['company_name'] ?? '');
    $svc = $_POST['service_type'] ?? '';
    $city = $_POST['city'] ?? '';
    $about = trim($_POST['about'] ?? '');
    $price = trim($_POST['price_range'] ?? '৳৫০০–৳২০০০');
    $ws = $_POST['work_start'] ?? '09:00';
    $we = $_POST['work_end'] ?? '18:00';
    $emoji = trim($_POST['emoji'] ?? '🔧');
    $verified = (int)($_POST['verified'] ?? 0);

    if (!$company || !$svc || !$city) {
        $err = 'কোম্পানির নাম, সেবা ও এলাকা আবশ্যক।';
    } else {
        try {
            db()->prepare("UPDATE providers SET company_name=?,service_type=?,city=?,about=?,price_range=?,work_start=?,work_end=?,emoji=?,verified=? WHERE id=?")
                ->execute([$company, $svc, $city, $about, $price, $ws, $we, $emoji, $verified, $pid]);
            $ok = 'সেবাদাতা আপডেট হয়েছে।';
            $ep = db()->prepare("SELECT p.*, u.name owner, u.email, u.phone FROM providers p JOIN users u ON u.id = p.user_id WHERE p.id=?");
            $ep->execute([$pid]);
            $editProv = $ep->fetch();
        } catch (Exception $e) {
            $err = 'ত্রুটি: ' . $e->getMessage();
        }
    }
}

$sq = trim($_GET['q'] ?? '');
$sf = $_GET['svc'] ?? '';
$w = [];
$p = [];
if ($sq) {
    $w[] = "(p.company_name LIKE ? OR u.name LIKE ?)";
    $p[] = "%$sq%";
    $p[] = "%$sq%";
}
if ($sf) {
    $w[] = "p.service_type=?";
    $p[] = $sf;
}
$wh = $w ? ' WHERE ' . implode(' AND ', $w) : '';
try {
    $providers = db()->prepare("SELECT p.*, u.name owner, u.email, (SELECT COUNT(*) FROM bookings WHERE provider_id = p.id) bc FROM providers p JOIN users u ON u.id = p.user_id $wh ORDER BY p.created_at DESC");
    $providers->execute($p);
    $providers = $providers->fetchAll();
} catch (Exception $e) {
    $providers = [];
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
  <title>সেবাদাতা - সেবা বন্ধু Admin</title>
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
    .btn-info { background: rgba(6, 182, 212, 0.2); color: #06B6D4; }
    .btn-info:hover { background: rgba(6, 182, 212, 0.3); }
    .btn-success { background: rgba(16, 185, 129, 0.2); color: #10B981; }
    .btn-success:hover { background: rgba(16, 185, 129, 0.3); }
    input, select, textarea { background: rgba(15, 23, 42, 0.6) !important; border: 1px solid rgba(71, 85, 105, 0.5) !important; color: white !important; border-radius: 12px; }
    input:focus, select:focus, textarea:focus { border-color: #8B5CF6 !important; outline: none; box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2); }
  </style>
</head>
<body class="bg-slate-950 text-white min-h-screen">

<!-- Sidebar -->
<aside id="sidebar" class="w-64 bg-gradient-to-b from-slate-900 to-slate-950 h-screen fixed left-0 top-0 border-r border-slate-800 z-50 flex flex-col">
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
  
  <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
    <a href="dashboard.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-gauge-high w-5 text-purple-400"></i>
      <span>ড্যাশবোর্ড</span>
    </a>
    <a href="users.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-users w-5"></i>
      <span>ব্যবহারকারী</span>
    </a>
    <a href="providers.php" class="sidebar-link active flex items-center gap-3 p-3 rounded-xl text-white">
      <i class="fas fa-user-tie w-5"></i>
      <span>সেবাদাতা</span>
    </a>
    <a href="bookings.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-calendar-check w-5"></i>
      <span>বুকিং</span>
      <?php if (adminPendingBookings() > 0): ?><span class="ml-auto bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"><?= adminPendingBookings() ?></span><?php endif; ?>
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

<button id="menuToggle" class="lg:hidden fixed top-4 left-4 z-50 p-3 bg-slate-800 rounded-xl shadow-lg">
  <i class="fas fa-bars w-5 h-5"></i>
</button>

<div class="lg:ml-64 min-h-screen">
  <header class="sticky top-0 z-40 glass-dark border-b border-slate-800/50">
    <div class="flex items-center justify-between p-4 md:p-6">
      <div class="hidden md:flex items-center gap-3 ml-12">
        <h1 class="text-xl md:text-2xl font-bold text-white">
          <i class="fas fa-user-tie mr-2 text-cyan-400"></i> সেবাদাতা ব্যবস্থাপনা
        </h1>
      </div>
      <div class="md:hidden flex items-center gap-2">
        <span class="text-lg font-bold gradient-text">সেবা বন্ধু</span>
      </div>
      <div class="flex items-center gap-3">
        <div class="relative" id="notifContainer">
          <button id="notifBtn" class="relative p-3 bg-slate-800/50 hover:bg-slate-700/50 rounded-xl transition-all">
            <i class="fas fa-bell w-5 text-purple-400"></i>
            <?php if ($unreadNotifs > 0): ?>
              <span id="notifBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold animate-pulse"><?= $unreadNotifs ?></span>
            <?php endif; ?>
          </button>
        </div>
      </div>
    </div>
  </header>

  <main class="p-4 md:p-8 max-w-7xl mx-auto">
    <?php if (isset($_SESSION['flash'])): ?>
      <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
      <div class="mb-6 p-4 rounded-xl <?= $flash['type'] === 'error' ? 'bg-red-500/20 text-red-400' : 'bg-emerald-500/20 text-emerald-400' ?> border <?= $flash['type'] === 'error' ? 'border-red-500/30' : 'border-emerald-500/30' ?>">
        <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>

    <?php if ($err): ?>
      <div class="mb-6 p-4 rounded-xl bg-red-500/20 text-red-400 border border-red-500/30">❌ <?= e($err) ?></div>
    <?php endif; ?>

    <?php if ($editProv): ?>
    <div class="card p-6 mb-8">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-blue-500 rounded-xl flex items-center justify-center shadow-lg">
          <i class="fas fa-edit text-white"></i>
        </div>
        <div>
          <h2 class="text-xl font-bold">✏️ সম্পাদনা: <?= e($editProv['company_name']) ?></h2>
          <p class="text-slate-500 text-sm"><?= e($editProv['owner']) ?> · <?= e($editProv['email']) ?></p>
        </div>
      </div>

      <form method="POST" class="space-y-5">
        <input type="hidden" name="_csrf" value="<?= csrf() ?>">
        <input type="hidden" name="pid" value="<?= $editProv['id'] ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">ব্যবসার নাম *</label>
            <input type="text" name="company_name" required value="<?= e($editProv['company_name']) ?>" class="w-full p-3">
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">ইমোজি</label>
            <input type="text" name="emoji" value="<?= e($editProv['emoji']) ?>" class="w-full p-3" style="max-width: 80px; font-size: 24px; text-align: center;">
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">সেবার ধরন *</label>
            <select name="service_type" required class="w-full p-3">
              <?php $adminServices = getServiceCategories(); foreach ($adminServices as $s): ?>
                <option value="<?= e($s['name_en']) ?>" <?= $editProv['service_type'] === $s['name_en'] ? 'selected' : '' ?>><?= $s['emoji'] . ' ' . e($s['name_bn']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">এলাকা *</label>
            <select name="city" required class="w-full p-3">
              <?php foreach (CITY_BN as $en => $bn): ?>
                <option value="<?= $en ?>" <?= $editProv['city'] === $en ? 'selected' : '' ?>><?= $bn ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">মূল্য পরিসীমা</label>
            <input type="text" name="price_range" value="<?= e($editProv['price_range']) ?>" class="w-full p-3">
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">কাজের শুরু</label>
            <input type="time" name="work_start" value="<?= substr($editProv['work_start'], 0, 5) ?>" class="w-full p-3">
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">কাজের শেষ</label>
            <input type="time" name="work_end" value="<?= substr($editProv['work_end'], 0, 5) ?>" class="w-full p-3">
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-semibold text-slate-300 mb-2">পরিচিতি</label>
          <textarea name="about" rows="3" class="w-full p-3"><?= e($editProv['about'] ?? '') ?></textarea>
        </div>
        
        <div class="flex items-center gap-4 pt-4 border-t border-slate-700/50">
          <button type="submit" class="btn-action btn-primary"><i class="fas fa-save"></i> আপডেট করুন</button>
          <a href="providers.php" class="btn-action bg-slate-700/50 text-slate-300 hover:bg-slate-600/50"><i class="fas fa-times"></i> বাতিল</a>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <!-- Providers List -->
    <div class="card overflow-hidden">
      <div class="p-6 border-b border-slate-700/50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-blue-500 rounded-xl flex items-center justify-center shadow-lg">
            <i class="fas fa-list text-white"></i>
          </div>
          <div>
            <h3 class="text-lg font-bold">সেবাদাতা তালিকা</h3>
            <p class="text-slate-500 text-sm">মোট <?= count($providers) ?> জন</p>
          </div>
        </div>
        
        <form method="GET" class="flex flex-wrap gap-3 items-center">
          <div class="relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
            <input type="text" name="q" value="<?= e($sq) ?>" placeholder="নাম / মালিক খুঁজুন…" class="pl-10 pr-4 py-2 w-48 md:w-64 text-sm">
          </div>
          <select name="svc" onchange="this.form.submit()" class="p-2 text-sm">
            <option value="">সব সেবা</option>
            <?php foreach (SERVICE_BN as $k => $v): ?>
              <option value="<?= $k ?>" <?= $sf === $k ? 'selected' : '' ?>><?= $v['emoji'] . ' ' . $v['label'] ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn-action btn-primary text-sm"><i class="fas fa-search"></i></button>
          <?php if ($sq || $sf): ?>
            <a href="providers.php" class="btn-action bg-slate-700/50 text-slate-300 text-sm"><i class="fas fa-times"></i></a>
          <?php endif; ?>
        </form>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-slate-800/50 border-b border-slate-700/50">
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">কোম্পানি</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">মালিক</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">সেবা / এলাকা</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">রেটিং</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">বুকিং</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">যাচাই</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">উপলব্ধ</th>
              <th class="px-6 py-4 text-right text-xs font-semibold text-slate-400 uppercase">অ্যাকশন</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-700/30">
            <?php foreach ($providers as $p2): ?>
              <tr class="hover:bg-slate-800/30 transition-all">
                <td class="px-6 py-4 font-semibold"><?= $p2['emoji'] . ' ' . e($p2['company_name']) ?></td>
                <td class="px-6 py-4 text-slate-400"><?= e($p2['owner']) ?></td>
                <td class="px-6 py-4">
                  <div class="font-semibold text-cyan-400"><?= svcLabel($p2['service_type']) ?></div>
                  <div class="text-xs text-slate-500"><?= cityBn($p2['city']) ?></div>
                </td>
                <td class="px-6 py-4">
                  <span class="text-amber-400 font-bold">★ <?= $p2['rating'] ?></span>
                  <span class="text-slate-500 text-xs">(<?= $p2['review_count'] ?>)</span>
                </td>
                <td class="px-6 py-4">
                  <span class="px-3 py-1.5 rounded-lg bg-emerald-500/20 text-emerald-400 text-xs font-bold"><?= $p2['bc'] ?></span>
                </td>
                <td class="px-6 py-4">
                  <?php if ($p2['verified']): ?>
                    <a href="?unverify=<?= $p2['id'] ?>" class="px-3 py-1.5 rounded-lg bg-emerald-500/20 text-emerald-400 text-xs font-bold hover:bg-emerald-500/30">✓ যাচাই</a>
                  <?php else: ?>
                    <a href="?verify=<?= $p2['id'] ?>" class="px-3 py-1.5 rounded-lg bg-amber-500/20 text-amber-400 text-xs font-bold hover:bg-amber-500/30">✗ অযাচাই</a>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4">
                  <a href="?toggle=<?= $p2['id'] ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold <?= $p2['available'] ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400' ?>">
                    <?= $p2['available'] ? '● অনলাইন' : '● অফলাইন' ?>
                  </a>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center justify-end gap-2">
                    <a href="?edit=<?= $p2['id'] ?>" class="btn-action btn-info text-sm"><i class="fas fa-edit"></i></a>
                    <a href="?delete=<?= $p2['id'] ?>" class="btn-action btn-danger text-sm" onclick="return confirm('<?= e($p2['company_name']) ?> মুছবেন?')"><i class="fas fa-trash"></i></a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
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
