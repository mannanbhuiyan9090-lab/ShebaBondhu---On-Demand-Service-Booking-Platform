<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';
sessionStart();
require_once __DIR__ . '/_stats.php';
requireAuth('admin');

require_once __DIR__ . '/_notif-scripts.php';

$user = auth();
$s = getAdminStats();

// Ensure amount column exists
try {
    db()->query("ALTER TABLE bookings ADD COLUMN amount DECIMAL(10,2) DEFAULT NULL AFTER payment_code");
} catch (Exception $e) {}
$ok = '';
$err = '';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'update_payment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $ps = $_POST['payment_status'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $validPStatuses = ['unpaid', 'paid', 'pending_verification'];
    if (in_array($ps, $validPStatuses, true) && $id > 0) {
        db()->prepare('UPDATE bookings SET payment_status=? WHERE id=?')->execute([$ps, $id]);
        $_SESSION['flash'] = ['msg' => 'পেমেন্ট স্ট্যাটাস আপডেট হয়েছে।', 'type' => 'success'];
    }
    header('Location: payments.php');
    exit;
}

$filterPm = $_GET['pm'] ?? '';
$filterPs = $_GET['ps'] ?? '';
$filterBs = $_GET['bs'] ?? '';
$filterQ = trim($_GET['q'] ?? '');
$where = ['1=1'];
$params = [];

if ($filterPm !== '') {
    $where[] = 'b.payment_method = ?';
    $params[] = $filterPm;
}
if ($filterPs !== '') {
    $where[] = 'b.payment_status = ?';
    $params[] = $filterPs;
}
if ($filterBs !== '') {
    $where[] = 'b.status = ?';
    $params[] = $filterBs;
}
if ($filterQ !== '') {
    $where[] = '(b.ref_code LIKE ? OR b.payment_code LIKE ? OR u.name LIKE ? OR pr.company_name LIKE ?)';
    $params[] = "%$filterQ%";
    $params[] = "%$filterQ%";
    $params[] = "%$filterQ%";
    $params[] = "%$filterQ%";
}
$wh = 'WHERE ' . implode(' AND ', $where);

$pmStats = [];
try {
    $stmt = db()->query("
        SELECT 
            b.payment_method,
            b.payment_status,
            COUNT(*) as cnt
        FROM bookings b
        WHERE b.payment_method IS NOT NULL
        GROUP BY b.payment_method, b.payment_status
    ");
    while ($row = $stmt->fetch()) {
        $pmStats[$row['payment_method']][$row['payment_status']] = $row['cnt'];
    }
} catch (Exception $e) {}

$stmt = db()->prepare("SELECT b.*, u.name cname, u.phone cphone, u.email cemail, pr.company_name, pr.service_type, pr.city FROM bookings b JOIN users u ON u.id = b.customer_id JOIN providers pr ON pr.id = b.provider_id $wh ORDER BY b.created_at DESC");
$stmt->execute($params);
$payments = $stmt->fetchAll();

$paidAmount = 0;
$pendingAmount = 0;
$unpaidAmount = 0;
$totalRevenue = 0;
$totalAmount = 0;
$completedRevenue = 0;
$completedAmount = 0;

try {
    $revenueData = db()->query("
        SELECT 
            COALESCE(SUM(amount), 0) as total_amt,
            COUNT(CASE WHEN payment_status='paid' AND status='Completed' THEN 1 END) as paid_cnt,
            COUNT(CASE WHEN payment_status='pending_verification' AND status='Completed' THEN 1 END) as pending_cnt,
            COUNT(CASE WHEN payment_status='unpaid' AND status='Completed' THEN 1 END) as unpaid_cnt,
            COALESCE(SUM(CASE WHEN payment_status='paid' AND status='Completed' THEN amount ELSE 0 END), 0) as completed_amt
        FROM bookings b
    ")->fetch();
    $completedAmount = (float)($revenueData['completed_amt'] ?? 0);
    $paidAmount = (int)($revenueData['paid_cnt'] ?? 0);
    $pendingAmount = (int)($revenueData['pending_cnt'] ?? 0);
    $unpaidAmount = (int)($revenueData['unpaid_cnt'] ?? 0);
    $totalRevenue = $completedAmount * 0.15;
    
    $completedRevenue = $completedAmount * 0.15;
    $completedAmount = (float)($revenueData['completed_amt'] ?? 0);
} catch (Exception $e) {}

$unreadNotifs = 0;
try {
    $unreadNotifs = (int)db()->query("SELECT COUNT(*) FROM notification WHERE role_target='admin' AND is_read=0")->fetchColumn();
} catch (Exception $e) {}

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
  <title>পেমেন্ট - সেবা বন্ধু Pro Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Hind Siliguri', sans-serif; }
    .glass-dark { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(20px); }
    .card { background: rgba(30, 41, 59, 0.5); backdrop-filter: blur(20px); border: 1px solid rgba(71, 85, 105, 0.3); border-radius: 1.5rem; }
    .sidebar-link { transition: all 0.2s ease; }
    .sidebar-link:hover { background: rgba(71, 85, 105, 0.3); transform: translateX(4px); }
    .sidebar-link.active { background: linear-gradient(90deg, rgba(16, 185, 129, 0.3), rgba(6, 182, 212, 0.1)); border-left: 3px solid #10B981; }
    .gradient-text { background: linear-gradient(135deg, #10B981, #06B6D4); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
    .btn-action { padding: 8px 16px; border-radius: 12px; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px; }
    .btn-primary { background: linear-gradient(135deg, #10B981, #059669); color: white; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4); }
    .btn-danger { background: rgba(239, 68, 68, 0.2); color: #EF4444; }
    .btn-danger:hover { background: rgba(239, 68, 68, 0.3); }
    input, select { background: rgba(15, 23, 42, 0.6) !important; border: 1px solid rgba(71, 85, 105, 0.5) !important; color: white !important; border-radius: 12px; }
    input:focus, select:focus { border-color: #10B981 !important; outline: none; }
    select option { background: #1e293b; color: white; }
  </style>
</head>
<body class="bg-slate-950 text-white min-h-screen">

<aside id="sidebar" class="w-64 bg-gradient-to-b from-slate-900 to-slate-950 h-screen fixed left-0 top-0 border-r border-slate-800 z-50 flex flex-col">
  <div class="p-6 border-b border-slate-800">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center shadow-lg">
        <i class="fas fa-bolt text-white"></i>
      </div>
      <div>
        <div class="text-xl font-bold gradient-text">সেবা বন্ধু</div>
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
      <?php if (adminPendingBookings() > 0): ?><span class="ml-auto bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"><?= adminPendingBookings() ?></span><?php endif; ?>
    </a>
    <a href="payments.php" class="sidebar-link active flex items-center gap-3 p-3 rounded-xl text-white"><i class="fas fa-credit-card w-5 text-emerald-400"></i><span>পেমেন্ট</span></a>
    <a href="services.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-cogs w-5"></i><span>সেবা</span></a>
    <a href="reviews.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-star w-5"></i><span>রিভিউ</span></a>
    <a href="applications.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-file-signature w-5"></i><span>আবেদন</span></a>
    <a href="analytics.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-chart-line w-5"></i><span>রিপোর্ট</span></a>
    <a href="notifications.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-bell w-5"></i><span>নোটিফিকেশন</span></a>
    <a href="settings.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-gear w-5"></i><span>সেটিংস</span></a>
  </nav>
  <div class="p-4 border-t border-slate-800">
    <a href="<?= SITE_URL ?>/logout.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-red-500/10 text-slate-400 hover:text-red-400 transition-all group">
      <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center font-bold text-sm shadow-lg"><?= strtoupper(mb_substr($user['name'] ?? 'AD', 0, 2)) ?></div>
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
        <h1 class="text-xl md:text-2xl font-bold text-white"><i class="fas fa-credit-card mr-2 text-emerald-400"></i> পেমেন্ট ব্যবস্থাপনা</h1>
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

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div class="stat-card glass rounded-2xl border border-slate-700/50 p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center">
            <i class="fas fa-check-double text-xl text-emerald-400"></i>
          </div>
        </div>
        <div class="text-3xl font-bold text-emerald-400 mb-1"><?= $paidAmount + $pendingAmount + $unpaidAmount ?></div>
        <div class="text-slate-400 text-sm font-medium">সম্পন্ন বুকিং</div>
        <div class="mt-3 flex items-center gap-1 text-slate-400 text-xs">
          <span class="text-emerald-400">✓ পরিশোধ: <?= $paidAmount ?></span> ·
          <span class="text-amber-400">⏳ অপেক্ষায়: <?= $pendingAmount ?></span> ·
          <span class="text-red-400">✕ বাকি: <?= $unpaidAmount ?></span>
        </div>
      </div>

      <div class="stat-card glass rounded-2xl border border-slate-700/50 p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center">
            <i class="fas fa-money-bill text-xl text-purple-400"></i>
          </div>
        </div>
        <div class="text-3xl font-bold text-purple-400 mb-1">৳<?= number_format((float)$completedAmount, 2) ?></div>
        <div class="text-slate-400 text-sm font-medium">মোট অ্যামাউন্ট</div>
        <div class="mt-3 text-xs text-slate-400">সম্পন্ন বুকিং থেকে</div>
      </div>

      <div class="stat-card glass rounded-2xl border border-slate-700/50 p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="w-12 h-12 bg-amber-500/20 rounded-xl flex items-center justify-center">
            <i class="fas fa-chart-line text-xl text-amber-400"></i>
          </div>
        </div>
        <div class="text-3xl font-bold text-amber-400 mb-1">৳<?= number_format((float)$completedRevenue, 2) ?></div>
        <div class="text-slate-400 text-sm">আয় (১৫% সার্ভিস চার্জ)</div>
        <div class="mt-3 text-xs text-slate-400">অ্যাডমিন কমিশন</div>
      </div>

      <div class="stat-card glass rounded-2xl border border-slate-700/50 p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="w-12 h-12 bg-cyan-500/20 rounded-xl flex items-center justify-center">
            <i class="fas fa-user-tie text-xl text-cyan-400"></i>
          </div>
        </div>
        <div class="text-3xl font-bold text-cyan-400 mb-1">৳<?= number_format((float)($completedAmount - $completedRevenue), 2) ?></div>
        <div class="text-slate-400 text-sm font-medium">প্রোভাইডার পায়</div>
        <div class="mt-3 text-xs text-slate-400">(৮৫% - সার্ভিস চার্জ বাদ)</div>
      </div>
    </div>

    <div class="card overflow-hidden">
      <div class="p-6 border-b border-slate-700/50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center shadow-lg">
            <i class="fas fa-list text-white"></i>
          </div>
          <div>
            <h3 class="text-lg font-bold">পেমেন্ট তালিকা</h3>
            <p class="text-slate-500 text-sm">মোট <?= count($payments) ?>টি</p>
          </div>
        </div>
        <form method="GET" class="flex flex-wrap gap-3 items-center">
          <select name="bs" onchange="this.form.submit()" class="p-2 text-sm">
            <option value="">সব বুকিং স্ট্যাটাস</option>
            <option value="Completed" <?= $filterBs === 'Completed' ? 'selected' : '' ?>>✓ সম্পন্ন</option>
            <option value="Approved" <?= $filterBs === 'Approved' ? 'selected' : '' ?>>✓ অনুমোদিত</option>
            <option value="Pending" <?= $filterBs === 'Pending' ? 'selected' : '' ?>>⏳ অপেক্ষমান</option>
            <option value="Cancelled" <?= $filterBs === 'Cancelled' ? 'selected' : '' ?>>✕ বাতিল</option>
          </select>
          <select name="ps" onchange="this.form.submit()" class="p-2 text-sm">
            <option value="">সব পেমেন্ট স্ট্যাটাস</option>
            <option value="paid" <?= $filterPs === 'paid' ? 'selected' : '' ?>>✓ পরিশোধিত</option>
            <option value="pending_verification" <?= $filterPs === 'pending_verification' ? 'selected' : '' ?>>⏳ সার্চ অপেক্ষায়</option>
            <option value="unpaid" <?= $filterPs === 'unpaid' ? 'selected' : '' ?>>✕ অপরিশোধ</option>
          </select>
          <div class="relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
            <input type="text" name="q" value="<?= e($filterQ) ?>" placeholder="রেফারেন্স / গ্রাহক…" class="pl-10 pr-4 py-2 w-48 md:w-64 text-sm">
          </div>
          <button type="submit" class="btn-action btn-primary text-sm"><i class="fas fa-search"></i></button>
          <?php if ($filterPm || $filterPs || $filterBs || $filterQ): ?><a href="payments.php" class="btn-action bg-slate-700/50 text-slate-300 text-sm"><i class="fas fa-times"></i></a><?php endif; ?>
        </form>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-slate-800/50 border-b border-slate-700/50">
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">রেফারেন্স</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">গ্রাহক</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">সেবাদাতা</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">অ্যামাউন্ট</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">বুকিং স্ট্যাটাস</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">পেমেন্ট স্ট্যাটাস</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">অ্যাকশন</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-700/30">
            <?php if (empty($payments)): ?>
              <tr><td colspan="7" class="px-6 py-12 text-center text-slate-500"><i class="fas fa-credit-card text-4xl mb-3"></i><p>কোনো পেমেন্ট পাওয়া যায়নি।</p></td></tr>
            <?php else: foreach ($payments as $p): ?>
              <tr class="hover:bg-slate-800/30 transition-all">
                <td class="px-6 py-4">
                  <div class="font-mono font-bold text-purple-400"><?= e($p['ref_code']) ?></div>
                  <div class="text-xs text-slate-500"><?= date('d M Y', strtotime($p['created_at'])) ?></div>
                </td>
                <td class="px-6 py-4">
                  <div class="font-semibold"><?= e($p['cname']) ?></div>
                  <div class="text-xs text-slate-500"><?= e($p['cphone'] ?? '') ?></div>
                </td>
                <td class="px-6 py-4">
                  <div class="font-semibold text-cyan-400"><?= e($p['company_name']) ?></div>
                  <div class="text-xs text-slate-500"><?= svcLabel($p['service_type']) ?></div>
                </td>
                <?php
                  $displayAmt = $p['amount'] ?? $p['estimated_price'] ?? 0;
                  if (empty($displayAmt) && !empty($p['price_range'])) {
                      $displayAmt = (int)preg_replace('/[^0-9]/', '', $p['price_range']);
                  }
                ?>
                <td class="px-6 py-4">
                  <div class="font-bold">৳<?= number_format((float)$displayAmt, 2) ?></div>
                </td>
                <td class="px-6 py-4">
                  <?php
                  $statusConfig = [
                    'Completed' => ['bg-emerald-500/20 text-emerald-400', '✓ সম্পন্ন'],
                    'Approved' => ['bg-blue-500/20 text-blue-400', '✓ অনুমোদিত'],
                    'Pending' => ['bg-amber-500/20 text-amber-400', '⏳ অপেক্ষমান'],
                    'Cancelled' => ['bg-red-500/20 text-red-400', '✕ বাতিল'],
                  ];
                  $bs = $statusConfig[$p['status']] ?? ['bg-slate-500/20 text-slate-400', $p['status']];
                  ?>
                  <span class="px-2 py-1 rounded-lg text-xs font-bold <?= $bs[0] ?>"><?= $bs[1] ?></span>
                </td>
                <td class="px-6 py-4">
                  <?php
                  $psConfig = [
                    'paid' => ['bg-emerald-500/20 text-emerald-400', '✓ পরিশোধ'],
                    'pending_verification' => ['bg-amber-500/20 text-amber-400', '⏳ সার্চ'],
                    'unpaid' => ['bg-red-500/20 text-red-400', '✕ বাকি'],
                  ];
                  $ps = $psConfig[$p['payment_status']] ?? ['bg-slate-500/20 text-slate-400', $p['payment_status']];
                  ?>
                  <form method="POST" class="inline-flex">
                    <input type="hidden" name="action" value="update_payment">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <select name="payment_status" onchange="this.form.submit()" class="px-2 py-1.5 rounded-lg text-xs font-bold <?= $ps[0] ?> bg-transparent border-0 cursor-pointer">
                      <option value="unpaid" <?= ($p['payment_status'] ?? 'unpaid') === 'unpaid' ? 'selected' : '' ?>>✕ বাকি</option>
                      <option value="paid" <?= ($p['payment_status'] ?? 'unpaid') === 'paid' ? 'selected' : '' ?>>✓ পরিশোধ</option>
                      <option value="pending_verification" <?= ($p['payment_status'] ?? 'unpaid') === 'pending_verification' ? 'selected' : '' ?>>⏳ সার্চ</option>
                    </select>
                  </form>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center justify-end gap-2">
                    <button onclick="showPaymentDetails(<?= (int)$p['id'] ?>, '<?= e($p['ref_code']) ?>', '<?= e($p['payment_code'] ?? '') ?>', '<?= e($p['cname']) ?>', '<?= e($p['cphone'] ?? '') ?>', '<?= e($p['cemail'] ?? '') ?>', '<?= e($p['company_name']) ?>', '<?= e($p['service_type']) ?>', '<?= e($p['city']) ?>', '<?= e($p['payment_method']) ?>', '<?= e($p['payment_status'] ?? '') ?>', '<?= date('d M Y h:i A', strtotime($p['created_at'])) ?>')" class="btn-action bg-cyan-500/20 text-cyan-400 text-sm"><i class="fas fa-eye"></i></button>
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
document.getElementById('menuToggle')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('-translate-x-full');
});

function showPaymentDetails(id, refCode, paymentCode, customerName, customerPhone, customerEmail, providerName, serviceType, city, paymentMethod, paymentStatus, createdAt) {
  const pmLabels = {'ssl': '💳 SSL Commercial', 'mobile_banking': '📱 Mobile Banking', 'cash_on_delivery': '💵 COD'};
  const psLabels = {'paid': '<span class="text-emerald-400">✓ পরিশোধ</span>', 'pending_verification': '<span class="text-amber-400">⏳ সার্চ অপেক্ষায়</span>', 'unpaid': '<span class="text-red-400">✕ অপরিশোধ</span>'};
  const modalHtml = `
    <div id="paymentDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closePaymentDetails()"></div>
      <div class="relative bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-lg p-6 shadow-2xl">
        <button onclick="closePaymentDetails()" class="absolute top-4 right-4 text-slate-400 hover:text-white"><i class="fas fa-times text-xl"></i></button>
        <h3 class="text-xl font-bold mb-4 flex items-center gap-2"><i class="fas fa-credit-card text-emerald-400"></i> পেমেন্ট বিবরণ</h3>
        <div class="space-y-3">
          <div class="flex justify-between"><span class="text-slate-400">রেফারেন্স কোড:</span><span class="font-mono font-bold text-purple-400">${refCode}</span></div>
          <div class="flex justify-between"><span class="text-slate-400">পেমেন্ট কোড:</span><span class="font-mono font-bold text-amber-400">${paymentCode || '-'}</span></div>
          <div class="flex justify-between"><span class="text-slate-400">তারিখ:</span><span>${createdAt}</span></div>
          <hr class="border-slate-700">
          <div class="flex justify-between"><span class="text-slate-400">গ্রাহক:</span><span>${customerName}</span></div>
          <div class="flex justify-between"><span class="text-slate-400">ফোন:</span><span>${customerPhone}</span></div>
          <div class="flex justify-between"><span class="text-slate-400">ইমেইল:</span><span>${customerEmail || '-'}</span></div>
          <hr class="border-slate-700">
          <div class="flex justify-between"><span class="text-slate-400">সেবাদাতা:</span><span class="text-cyan-400">${providerName}</span></div>
          <div class="flex justify-between"><span class="text-slate-400">সেবার ধরন:</span><span>${serviceType}</span></div>
          <div class="flex justify-between"><span class="text-slate-400">শহর:</span><span>${city}</span></div>
          <hr class="border-slate-700">
          <div class="flex justify-between"><span class="text-slate-400">পেমেন্ট পদ্ধতি:</span><span>${pmLabels[paymentMethod] || paymentMethod}</span></div>
          <div class="flex justify-between"><span class="text-slate-400">স্ট্যাটাস:</span><span>${psLabels[paymentStatus] || paymentStatus}</span></div>
        </div>
        <div class="mt-6 flex gap-3">
          <a href="bookings.php?edit=${id}" class="flex-1 btn-action bg-cyan-500/20 text-cyan-400 justify-center"><i class="fas fa-edit"></i> এডিট</a>
          <button onclick="closePaymentDetails()" class="flex-1 btn-action bg-slate-700/50 text-slate-300 justify-center">বন্ধ</button>
        </div>
      </div>
    </div>
  `;
  document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closePaymentDetails() {
  document.getElementById('paymentDetailModal')?.remove();
}
</script>

<?php renderAdminNotifScripts(); ?>

</body>
</html>