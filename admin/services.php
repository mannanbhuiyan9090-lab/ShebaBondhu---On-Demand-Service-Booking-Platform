<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';
sessionStart();
require_once __DIR__ . '/_stats.php';
requireAuth('admin');

require_once __DIR__ . '/_notif-scripts.php';

$user = auth();
$s = getAdminStats();
$ok = '';
$err = '';
$editSvc = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM service_categories WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editSvc = $stmt->fetch() ?: null;
}

try {
    db()->exec("CREATE TABLE IF NOT EXISTS service_categories (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name_en VARCHAR(80) NOT NULL, name_bn VARCHAR(100) NOT NULL,
        emoji VARCHAR(10) NOT NULL DEFAULT '🔧', description TEXT, manpower INT DEFAULT 1,
        active TINYINT(1) NOT NULL DEFAULT 1, sort_order SMALLINT NOT NULL DEFAULT 0, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_name_en (name_en)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    db()->exec("ALTER TABLE service_categories ADD COLUMN manpower INT DEFAULT 1 AFTER description");
    $cnt = (int)db()->query('SELECT COUNT(*) FROM service_categories')->fetchColumn();
    if ($cnt === 0) {
        $ins = db()->prepare('INSERT IGNORE INTO service_categories (name_en,name_bn,emoji,sort_order) VALUES (?,?,?,?)');
        $i = 1;
        foreach (SERVICE_BN as $en => $v) {
            $ins->execute([$en, $v['label'], $v['emoji'], $i++]);
        }
    }
} catch (PDOException $e) {}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$showForm = $editSvc ? true : false;

if ($action === 'delete' && isset($_GET['id'])) {
    try {
        db()->prepare('DELETE FROM service_categories WHERE id=?')->execute([(int)$_GET['id']]);
        $_SESSION['flash'] = ['msg' => 'সেবা বিভাগ মুছে ফেলা হয়েছে।', 'type' => 'success'];
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['msg' => 'মুছতে ব্যর্থ।', 'type' => 'error'];
    }
    header('Location: services.php');
    exit;
}

if ($action === 'toggle' && isset($_GET['id'])) {
    try {
        db()->prepare('UPDATE service_categories SET active = NOT active WHERE id = ?')->execute([(int)$_GET['id']]);
        $_SESSION['flash'] = ['msg' => 'সেবা আপডেট হয়েছে।', 'type' => 'success'];
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['msg' => 'আপডেট ব্যর্থ।', 'type' => 'error'];
    }
    header('Location: services.php');
    exit;
}

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = (int)($_POST['sid'] ?? 0);
    $en = trim($_POST['name_en'] ?? '');
    $bn = trim($_POST['name_bn'] ?? '');
    $emoji = mb_substr(trim($_POST['emoji'] ?? '🔧'), 0, 10);
    $desc = trim($_POST['description'] ?? '');
    $manpower = (int)($_POST['manpower'] ?? 1);
    $active = (int)(bool)($_POST['active'] ?? 1);
    $sort = (int)($_POST['sort_order'] ?? 0);

    if ($en === '' || $bn === '') {
        $err = 'ইংরেজি ও বাংলা নাম আবশ্যক।';
    } else {
        try {
            if ($sid > 0) {
                db()->prepare('UPDATE service_categories SET name_en=?,name_bn=?,emoji=?,description=?,manpower=?,active=?,sort_order=? WHERE id=?')
                    ->execute([$en, $bn, $emoji, $desc, $manpower, $active, $sort, $sid]);
                $_SESSION['flash'] = ['msg' => 'সেবা বিভাগ আপডেট হয়েছে।', 'type' => 'success'];
                header('Location: services.php');
                exit;
            } else {
                db()->prepare('INSERT INTO service_categories (name_en,name_bn,emoji,description,manpower,active,sort_order) VALUES (?,?,?,?,?,?,?)')
                    ->execute([$en, $bn, $emoji, $desc, $manpower, $active, $sort]);
                $_SESSION['flash'] = ['msg' => 'নতুন সেবা বিভাগ যোগ হয়েছে।', 'type' => 'success'];
                header('Location: services.php');
                exit;
            }
            $_SESSION['flash'] = ['msg' => $ok, 'type' => 'success'];
            header('Location: services.php');
            exit;
        } catch (PDOException $e) {
            $err = 'ত্রুটি: ' . $e->getMessage();
        }
    }
}

$cats = db()->query('SELECT sc.*, COALESCE((SELECT COUNT(*) FROM providers p WHERE p.service_type = sc.name_en OR p.service_type = REPLACE(sc.name_en, \'s\', \'\') OR REPLACE(p.service_type, \'s\', \'\') = REPLACE(sc.name_en, \'s\', \'\')), 0) as pc FROM service_categories sc ORDER BY sc.sort_order ASC, sc.id ASC')->fetchAll();

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
  <title>সেবা বিভাগ - সেবা বন্ধু Admin</title>
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
    .btn-warning { background: rgba(245, 158, 11, 0.2); color: #F59E0B; }
    .btn-warning:hover { background: rgba(245, 158, 11, 0.3); }
    .btn-success { background: rgba(16, 185, 129, 0.2); color: #10B981; }
    .btn-success:hover { background: rgba(16, 185, 129, 0.3); }
    input, select, textarea { background: rgba(15, 23, 42, 0.6) !important; border: 1px solid rgba(71, 85, 105, 0.5) !important; color: white !important; border-radius: 12px; }
    input:focus, select:focus, textarea:focus { border-color: #8B5CF6 !important; outline: none; }
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
    <a href="services.php" class="sidebar-link active flex items-center gap-3 p-3 rounded-xl text-white"><i class="fas fa-cogs w-5 text-purple-400"></i><span>সেবা</span></a>
    <a href="reviews.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-star w-5"></i><span>রিভিউ</span></a>
    <a href="applications.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-file-signature w-5"></i><span>আবেদন</span></a>
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
        <h1 class="text-xl md:text-2xl font-bold text-white"><i class="fas fa-cogs mr-2 text-violet-400"></i> সেবা বিভাগ ব্যবস্থাপনা</h1>
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
    <?php if ($ok): ?><div class="mb-6 p-4 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">✅ <?= e($ok) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="mb-6 p-4 rounded-xl bg-red-500/20 text-red-400 border border-red-500/30">❌ <?= e($err) ?></div><?php endif; ?>

    <div class="card p-6 mb-8">
      <button type="button" id="toggleAddBtn" class="flex items-center gap-3 mb-6 w-full text-left <?= $showForm ? 'hidden' : '' ?>" onclick="toggleAddForm()">
        <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-purple-500 rounded-xl flex items-center justify-center shadow-lg">
          <i class="fas fa-plus text-white"></i>
        </div>
        <div>
          <h2 class="text-xl font-bold">➕ নতুন সেবা বিভাগ যোগ করুন</h2>
          <p class="text-slate-500 text-sm">নতুন সেবা বিভাগ তৈরি করুন</p>
        </div>
      </button>
      
      <div id="addFormContainer" class="<?= $showForm ? '' : 'hidden' ?>">
        <div class="flex items-center gap-3 mb-6">
          <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-purple-500 rounded-xl flex items-center justify-center shadow-lg">
            <i class="fas <?= $editSvc ? 'fa-edit' : 'fa-plus' ?> text-white"></i>
          </div>
          <div>
            <h2 class="text-xl font-bold"><?= $editSvc ? '✏️ সেবা বিভাগ সম্পাদনা' : '➕ নতুন সেবা বিভাগ যোগ করুন' ?></h2>
            <p class="text-slate-500 text-sm"><?= $editSvc ? 'বিদ্যমান সেবা পরিবর্তন করুন' : 'নতুন সেবা বিভাগ তৈরি করুন' ?></p>
          </div>
        </div>
        
        <form method="POST" class="space-y-5">
        <input type="hidden" name="_csrf" value="<?= csrf() ?>">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="sid" value="<?= $editSvc ? (int)$editSvc['id'] : 0 ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">ইংরেজি নাম *</label>
            <input type="text" name="name_en" required value="<?= e($editSvc['name_en'] ?? '') ?>" placeholder="Plumbing" class="w-full p-3">
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">বাংলা নাম *</label>
            <input type="text" name="name_bn" required value="<?= e($editSvc['name_bn'] ?? '') ?>" placeholder="পাইপলাইন" class="w-full p-3">
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">ইমোজি</label>
            <input type="text" name="emoji" value="<?= e($editSvc['emoji'] ?? '🔧') ?>" class="w-full p-3" style="font-size: 24px; max-width: 80px; text-align: center;">
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
          <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-slate-300 mb-2">বিবরণ</label>
            <textarea name="description" rows="2" placeholder="সংক্ষিপ্ত বিবরণ…" class="w-full p-3"><?= e($editSvc['description'] ?? '') ?></textarea>
          </div>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">ম্যানপাওয়ার (জন)</label>
              <input type="number" name="manpower" min="1" value="<?= (int)($editSvc['manpower'] ?? 1) ?>" class="w-full p-3">
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">স্ট্যাটাস</label>
              <select name="active" class="w-full p-3">
                <option value="1" <?= ($editSvc['active'] ?? 1) ? 'selected' : '' ?>>✓ সক্রিয়</option>
                <option value="0" <?= isset($editSvc) && !$editSvc['active'] ? 'selected' : '' ?>>✗ নিষ্ক্রিয়</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">সাজানোর ক্রম</label>
              <input type="number" name="sort_order" min="0" value="<?= (int)($editSvc['sort_order'] ?? 0) ?>" class="w-full p-3">
            </div>
          </div>
        </div>
        
        <div class="flex items-center gap-4 pt-4 border-t border-slate-700/50">
          <button type="submit" class="btn-action btn-primary"><i class="fas <?= $editSvc ? 'fa-save' : 'fa-plus' ?>"></i> <?= $editSvc ? 'আপডেট করুন' : 'যোগ করুন' ?></button>
          <?php if ($editSvc): ?><a href="services.php" class="btn-action bg-slate-700/50 text-slate-300 hover:bg-slate-600/50"><i class="fas fa-times"></i> বাতিল</a><?php else: ?><button type="button" onclick="toggleAddForm()" class="btn-action bg-slate-700/50 text-slate-300 hover:bg-slate-600/50"><i class="fas fa-times"></i> বাতিল</button><?php endif; ?>
        </div>
      </form>
      </div>
    </div>

    <div class="card p-6">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-purple-500 rounded-xl flex items-center justify-center shadow-lg">
          <i class="fas fa-layer-group text-white"></i>
        </div>
        <div>
          <h3 class="text-lg font-bold">সেবা বিভাগ সমূহ</h3>
          <p class="text-slate-500 text-sm">মোট <?= count($cats) ?>টি</p>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <?php foreach ($cats as $c): ?>
          <div class="p-5 rounded-2xl border transition-all hover:border-purple-500/50 <?= $c['active'] ? 'bg-gradient-to-br from-purple-500/10 to-transparent border-purple-500/20' : 'bg-slate-800/30 border-slate-700/50 opacity-60' ?>">
            <div class="text-4xl mb-3"><?= $c['emoji'] ?></div>
            <div class="font-bold text-lg mb-1"><?= e($c['name_bn']) ?></div>
            <div class="text-xs text-slate-500 mb-2"><?= e($c['name_en']) ?></div>
            <div class="flex items-center gap-2 text-sm mb-3">
              <span class="px-2 py-1 rounded-lg bg-violet-500/20 text-violet-400 font-semibold"><?= $c['pc'] ?> জন</span>
              <span class="px-2 py-1 rounded-lg <?= $c['active'] ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400' ?>"><?= $c['active'] ? 'সক্রিয়' : 'নিষ্ক্রিয়' ?></span>
            </div>
            <div class="flex items-center gap-2">
              <a href="?edit=<?= (int)$c['id'] ?>" class="btn-action btn-primary text-xs flex-1 justify-center"><i class="fas fa-edit"></i></a>
              <a href="?action=toggle&id=<?= (int)$c['id'] ?>&_csrf=<?= csrf() ?>" class="btn-action <?= $c['active'] ? 'btn-warning' : 'btn-success' ?> text-xs"><i class="fas fa-toggle-on"></i></a>
              <a href="?action=delete&id=<?= (int)$c['id'] ?>&_csrf=<?= csrf() ?>" class="btn-action btn-danger text-xs" onclick="return confirm('মুছে ফেলবেন?')"><i class="fas fa-trash"></i></a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </main>
</div>

<script>
document.getElementById('menuToggle')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('-translate-x-full');
});

function toggleAddForm() {
  const btn = document.getElementById('toggleAddBtn');
  const form = document.getElementById('addFormContainer');
  if (btn.classList.contains('hidden')) {
    btn.classList.remove('hidden');
    form.classList.add('hidden');
  } else {
    btn.classList.add('hidden');
    form.classList.remove('hidden');
  }
}
</script>

<?php renderAdminNotifScripts(); ?>

</body>
</html>
