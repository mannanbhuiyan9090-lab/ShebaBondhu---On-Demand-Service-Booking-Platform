<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';
sessionStart();
require_once __DIR__ . '/_stats.php';
requireAuth('admin');

require_once __DIR__ . '/_notif-scripts.php';

$user = auth();
$s = getAdminStats();

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        db()->prepare("DELETE FROM users WHERE id=? AND role!='admin'")->execute([$id]);
        $_SESSION['flash'] = ['msg' => 'ব্যবহারকারী মুছে ফেলা হয়েছে।', 'type' => 'success'];
    } catch (Exception $e) {
        $_SESSION['flash'] = ['msg' => 'ত্রুটি: ' . $e->getMessage(), 'type' => 'error'];
    }
    header('Location: users.php');
    exit;
}

$editUser = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $editUser = $stmt->fetch();
}

$showForm = $editUser ? true : false;

$err = $ok = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = (int)($_POST['uid'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'customer';
    $pass = $_POST['password'] ?? '';
    
    if (!$name || !$email) {
        $err = 'নাম ও ইমেইল আবশ্যক।';
    } else {
        try {
            if ($uid > 0) {
                if ($pass) {
                    db()->prepare("UPDATE users SET name=?,email=?,phone=?,password=?,role=? WHERE id=?")
                        ->execute([$name, $email, $phone, password_hash($pass, PASSWORD_DEFAULT), $role, $uid]);
                } else {
                    db()->prepare("UPDATE users SET name=?,email=?,phone=?,role=? WHERE id=?")
                        ->execute([$name, $email, $phone, $role, $uid]);
                }
                $_SESSION['flash'] = ['msg' => 'ব্যবহারকারী আপডেট হয়েছে।', 'type' => 'success'];
            } else {
                if (!$pass) {
                    $err = 'পাসওয়ার্ড আবশ্যক।';
                } else {
                    $chk = db()->prepare("SELECT id FROM users WHERE email=?");
                    $chk->execute([$email]);
                    if ($chk->fetch()) {
                        $err = 'এই ইমেইলে ইতিমধ্যে অ্যাকাউন্ট আছে।';
                    } else {
                        db()->prepare("INSERT INTO users (name,email,phone,password,role) VALUES (?,?,?,?,?)")
                            ->execute([$name, $email, $phone, password_hash($pass, PASSWORD_DEFAULT), $role]);
                        $_SESSION['flash'] = ['msg' => 'নতুন ব্যবহারকারী যোগ হয়েছে।', 'type' => 'success'];
                    }
                }
            }
            if (!$err) {
                header('Location: users.php');
                exit;
            }
        } catch (Exception $e) {
            $err = 'ত্রুটি: ' . $e->getMessage();
        }
    }
}

$rf = $_GET['role'] ?? '';
$sq = trim($_GET['q'] ?? '');
$w = [];
$p = [];
if ($rf) {
    $w[] = "role=?";
    $p[] = $rf;
}
if ($sq) {
    $w[] = "(name LIKE ? OR email LIKE ?)";
    $p[] = "%$sq%";
    $p[] = "%$sq%";
}
$wh = $w ? ' WHERE ' . implode(' AND ', $w) : '';
$stmt = db()->prepare("SELECT * FROM users $wh ORDER BY created_at DESC");
$stmt->execute($p);
$users = $stmt->fetchAll();

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
  <title>ব্যবহারকারী - সেবা বন্ধু Pro Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Hind Siliguri', sans-serif; }
    .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(20px); }
    .glass-dark { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(20px); }
    .card { background: rgba(30, 41, 59, 0.5); backdrop-filter: blur(20px); border: 1px solid rgba(71, 85, 105, 0.3); border-radius: 1.5rem; }
    .stat-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3); }
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
    .pulse-dot { animation: pulse 2s infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
    input, select, textarea { background: rgba(15, 23, 42, 0.6) !important; border: 1px solid rgba(71, 85, 105, 0.5) !important; color: white !important; border-radius: 12px; }
    input:focus, select:focus, textarea:focus { border-color: #8B5CF6 !important; outline: none; box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2); }
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: #1e293b; }
    ::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
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
    <a href="users.php" class="sidebar-link active flex items-center gap-3 p-3 rounded-xl text-white">
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

<!-- Mobile Menu Toggle -->
<button id="menuToggle" class="lg:hidden fixed top-4 left-4 z-50 p-3 bg-slate-800 rounded-xl shadow-lg">
  <i class="fas fa-bars w-5 h-5"></i>
</button>

<!-- Main Content -->
<div class="lg:ml-64 min-h-screen">
  <!-- Topbar -->
  <header class="sticky top-0 z-40 glass-dark border-b border-slate-800/50">
    <div class="flex items-center justify-between p-4 md:p-6">
      <div class="hidden md:flex items-center gap-3 ml-12">
        <h1 class="text-xl md:text-2xl font-bold text-white">
          <i class="fas fa-users mr-2 text-purple-400"></i> ব্যবহারকারী ব্যবস্থাপনা
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

  <!-- Main Container -->
  <main class="p-4 md:p-8 max-w-7xl mx-auto">
    
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash'])): ?>
      <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
      <div class="mb-6 p-4 rounded-xl <?= $flash['type'] === 'error' ? 'bg-red-500/20 text-red-400 border border-red-500/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' ?>">
        <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>

    <?php if ($ok): ?>
      <div class="mb-6 p-4 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
        ✅ <?= e($ok) ?>
      </div>
    <?php endif; ?>
    <?php if ($err): ?>
      <div class="mb-6 p-4 rounded-xl bg-red-500/20 text-red-400 border border-red-500/30">
        ❌ <?= e($err) ?>
      </div>
    <?php endif; ?>

    <!-- Add Button -->
    <div class="card p-6 mb-8">
      <button type="button" id="toggleAddBtn" class="flex items-center gap-3 w-full text-left <?= $showForm ? 'hidden' : '' ?>" onclick="toggleUserForm()">
        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-cyan-500 rounded-xl flex items-center justify-center shadow-lg">
          <i class="fas fa-user-plus text-white"></i>
        </div>
        <div>
          <h2 class="text-xl font-bold">➕ নতুন ব্যবহারকারী যোগ করুন</h2>
          <p class="text-slate-500 text-sm">নতুন ব্যবহারকারী অ্যাকাউন্ট তৈরি করুন</p>
        </div>
      </button>
      
      <div id="userFormContainer" class="<?= $showForm ? '' : 'hidden' ?>">
        <div class="flex items-center gap-3 mb-6">
          <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-cyan-500 rounded-xl flex items-center justify-center shadow-lg">
            <i class="fas <?= $editUser ? 'fa-edit' : 'fa-user-plus' ?> text-white"></i>
          </div>
          <div>
            <h2 class="text-xl font-bold"><?= $editUser ? '✏️ ব্যবহারকারী সম্পাদনা' : '➕ নতুন ব্যবহারকারী যোগ করুন' ?></h2>
          <p class="text-slate-500 text-sm"><?= $editUser ? 'বিদ্যমান অ্যাকাউন্ট পরিবর্তন করুন' : 'নতুন গ্রাহক বা সেবাদাতা অ্যাকাউন্ট তৈরি করুন' ?></p>
        </div>
      </div>
      
      <form method="POST" action="" class="space-y-5">
        <input type="hidden" name="_csrf" value="<?= csrf() ?>">
        <input type="hidden" name="uid" value="<?= $editUser ? $editUser['id'] : 0 ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">পূর্ণ নাম *</label>
            <input type="text" name="name" required value="<?= e($editUser['name'] ?? '') ?>" placeholder="যেমন: রাহেলা বেগম" class="w-full p-3">
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">ইমেইল *</label>
            <input type="email" name="email" required value="<?= e($editUser['email'] ?? '') ?>" placeholder="example@email.com" class="w-full p-3">
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">মোবাইল নম্বর</label>
            <input type="tel" name="phone" value="<?= e($editUser['phone'] ?? '') ?>" placeholder="017XXXXXXXX" class="w-full p-3">
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">ভূমিকা *</label>
            <select name="role" class="w-full p-3">
              <option value="customer" <?= ($editUser['role'] ?? 'customer') === 'customer' ? 'selected' : '' ?>>👤 গ্রাহক</option>
              <option value="provider" <?= ($editUser['role'] ?? '') === 'provider' ? 'selected' : '' ?>>🔧 সেবাদাতা</option>
              <option value="admin" <?= ($editUser['role'] ?? '') === 'admin' ? 'selected' : '' ?>>⚙️ অ্যাডমিন</option>
            </select>
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-semibold text-slate-300 mb-2">
            <?= $editUser ? 'নতুন পাসওয়ার্ড (খালি = অপরিবর্তিত)' : 'পাসওয়ার্ড *' ?>
          </label>
          <input type="password" name="password" placeholder="কমপক্ষে ৬ অক্ষর" <?= !$editUser ? 'required' : '' ?> class="w-full p-3">
        </div>
        
        <div class="flex items-center gap-4 pt-4 border-t border-slate-700/50">
          <button type="submit" class="btn-action btn-primary">
            <i class="fas <?= $editUser ? 'fa-save' : 'fa-plus' ?>"></i>
            <?= $editUser ? 'আপডেট করুন' : 'যোগ করুন' ?>
          </button>
          <?php if ($editUser): ?>
            <a href="users.php" class="btn-action bg-slate-700/50 text-slate-300 hover:bg-slate-600/50">
              <i class="fas fa-times"></i> বাতিল
            </a>
          <?php else: ?>
            <button type="button" onclick="toggleUserForm()" class="btn-action bg-slate-700/50 text-slate-300 hover:bg-slate-600/50">
              <i class="fas fa-times"></i> বাতিল
            </button>
          <?php endif; ?>
        </div>
      </form>
      </div>
    </div>

    <!-- Users List -->
    <div class="card overflow-hidden">
      <div class="p-6 border-b border-slate-700/50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center shadow-lg">
            <i class="fas fa-list text-white"></i>
          </div>
          <div>
            <h3 class="text-lg font-bold">ব্যবহারকারী তালিকা</h3>
            <p class="text-slate-500 text-sm">মোট <?= count($users) ?> জন</p>
          </div>
        </div>
        
        <form method="GET" class="flex flex-wrap gap-3 items-center">
          <div class="relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
            <input type="text" name="q" value="<?= e($sq) ?>" placeholder="নাম / ইমেইল খুঁজুন…" class="pl-10 pr-4 py-2 w-48 md:w-64 text-sm">
          </div>
          <select name="role" onchange="this.form.submit()" class="p-2 text-sm">
            <option value="">সব ভূমিকা</option>
            <option value="customer" <?= $rf === 'customer' ? 'selected' : '' ?>>গ্রাহক</option>
            <option value="provider" <?= $rf === 'provider' ? 'selected' : '' ?>>সেবাদাতা</option>
            <option value="admin" <?= $rf === 'admin' ? 'selected' : '' ?>>অ্যাডমিন</option>
          </select>
          <button type="submit" class="btn-action btn-primary text-sm"><i class="fas fa-search"></i></button>
          <?php if ($rf || $sq): ?>
            <a href="users.php" class="btn-action bg-slate-700/50 text-slate-300 text-sm"><i class="fas fa-times"></i></a>
          <?php endif; ?>
        </form>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-slate-800/50 border-b border-slate-700/50">
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">#</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">ব্যবহারকারী</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">যোগাযোগ</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">ভূমিকা</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">যোগদান</th>
              <th class="px-6 py-4 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">অ্যাকশন</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-700/30">
            <?php if (empty($users)): ?>
              <tr>
                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                  <i class="fas fa-users text-4xl mb-3"></i>
                  <p>কোনো ব্যবহারকারী পাওয়া যায়নি।</p>
                </td>
              </tr>
            <?php else: $sn = 1; foreach ($users as $u2): ?>
              <?php
              $bc = 0;
              try {
                  $bcStmt = db()->prepare("SELECT COUNT(*) FROM bookings WHERE customer_id=?");
                  $bcStmt->execute([$u2['id']]);
                  $bc = (int)$bcStmt->fetchColumn();
              } catch (Exception $e) {}
              ?>
              <tr class="hover:bg-slate-800/30 transition-all">
                <td class="px-6 py-4 text-slate-500 font-bold"><?= $sn++ ?></td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br <?= $u2['role'] === 'admin' ? 'from-red-500 to-pink-500' : ($u2['role'] === 'provider' ? 'from-cyan-500 to-blue-500' : 'from-emerald-500 to-teal-500') ?> rounded-xl flex items-center justify-center font-bold text-sm">
                      <?= strtoupper(mb_substr($u2['name'], 0, 2)) ?>
                    </div>
                    <div>
                      <div class="font-semibold"><?= e($u2['name']) ?></div>
                      <div class="text-xs text-slate-500"><?= e($u2['email']) ?></div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-slate-400"><?= e($u2['phone'] ?? '—') ?></td>
                <td class="px-6 py-4">
                  <span class="px-3 py-1.5 rounded-lg text-xs font-bold <?= $u2['role'] === 'admin' ? 'bg-red-500/20 text-red-400' : ($u2['role'] === 'provider' ? 'bg-cyan-500/20 text-cyan-400' : 'bg-emerald-500/20 text-emerald-400') ?>">
                    <?= match ($u2['role']) { 'admin' => '⚙️ অ্যাডমিন', 'provider' => '🔧 সেবাদাতা', default => '👤 গ্রাহক' } ?>
                  </span>
                </td>
                <td class="px-6 py-4 text-slate-500 text-sm"><?= date('d M Y', strtotime($u2['created_at'])) ?></td>
                <td class="px-6 py-4">
                  <div class="flex items-center justify-end gap-2">
                    <a href="?edit=<?= $u2['id'] ?>" class="btn-action btn-info text-sm">
                      <i class="fas fa-edit"></i>
                    </a>
                    <?php if ($u2['role'] !== 'admin'): ?>
                      <a href="?delete=<?= $u2['id'] ?>" class="btn-action btn-danger text-sm" onclick="return confirm('<?= e($u2['name']) ?> মুছে ফেলবেন?')">
                        <i class="fas fa-trash"></i>
                      </a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<script>
// Mobile sidebar toggle
document.getElementById('menuToggle')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('-translate-x-full');
});

// Notification dropdown
const notifBtn = document.getElementById('notifBtn');
const notifDropdown = notifBtn?.parentElement?.querySelector('.notif-dropdown');

notifBtn?.addEventListener('click', (e) => {
  e.stopPropagation();
  notifDropdown?.classList.toggle('hidden');
});

document.addEventListener('click', (e) => {
  if (!document.getElementById('notifContainer')?.contains(e.target)) {
    notifDropdown?.classList.add('hidden');
  }
});

function toggleUserForm() {
  const btn = document.getElementById('toggleAddBtn');
  const form = document.getElementById('userFormContainer');
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
