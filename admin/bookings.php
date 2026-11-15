<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';
sessionStart();
require_once __DIR__ . '/_stats.php';
requireAuth('admin'); 

require_once __DIR__ . '/_notif-scripts.php'; 

$user = auth();
$s = getAdminStats();
$err = '';

// Ensure all needed columns exist (each in its own try-catch)
try { db()->exec("ALTER TABLE bookings ADD COLUMN is_read TINYINT DEFAULT 0"); } catch (Exception $e) {}
try { db()->exec("ALTER TABLE bookings ADD COLUMN service_charge INT DEFAULT 0"); } catch (Exception $e) {}
try { db()->exec("ALTER TABLE bookings ADD COLUMN price_range VARCHAR(50) DEFAULT NULL"); } catch (Exception $e) {}
try { db()->exec("ALTER TABLE bookings ADD COLUMN estimated_price INT DEFAULT 0"); } catch (Exception $e) {}
try { db()->exec("ALTER TABLE bookings ADD COLUMN revenue INT DEFAULT 0"); } catch (Exception $e) {}
try { db()->exec("ALTER TABLE bookings ADD COLUMN amount DECIMAL(10,2) DEFAULT NULL"); } catch (Exception $e) {}
try { db()->exec("ALTER TABLE bookings ADD COLUMN provider_note TEXT DEFAULT NULL"); } catch (Exception $e) {}
try { db()->exec("ALTER TABLE notification ADD COLUMN link VARCHAR(255) DEFAULT NULL"); } catch (Exception $e) {}

try { db()->prepare("UPDATE notification SET is_read = 1 WHERE role_target = 'admin' AND type = 'booking' AND is_read = 0")->execute();
} catch (Exception $e) {}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'delete' && isset($_GET['id'])) {
    try {
        db()->prepare('DELETE FROM bookings WHERE id = ?')->execute([(int)$_GET['id']]);
        $_SESSION['flash'] = ['msg' => 'বুকিং মুছে ফেলা হয়েছে।', 'type' => 'success'];
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['msg' => 'মুছতে ব্যর্থ: ' . $e->getMessage(), 'type' => 'error'];
    }
    header('Location: bookings.php');
    exit;
}

if ($action === 'setstatus' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $ns = $_POST['status'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $validStatuses = array_keys(STATUS_BN);
    if (in_array($ns, $validStatuses, true) && $id > 0) {
        db()->prepare('UPDATE bookings SET status=?, is_read=1 WHERE id=?')->execute([$ns, $id]);
    }
    $_SESSION['flash'] = ['msg' => 'স্ট্যাটাস আপডেট হয়েছে।', 'type' => 'success'];
    header('Location: bookings.php');
    exit;
}

if ($action === 'setpayment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $ps = $_POST['payment_status'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $validPStatuses = ['unpaid', 'paid', 'pending_verification'];
    if (in_array($ps, $validPStatuses, true) && $id > 0) {
        db()->prepare('UPDATE bookings SET payment_status=?, is_read=1 WHERE id=?')->execute([$ps, $id]);
        $_SESSION['flash'] = ['msg' => 'পেমেন্ট স্ট্যাটাস আপডেট হয়েছে।', 'type' => 'success'];
    }
    header('Location: bookings.php');
    exit;
}

$editBook = null;
$editMode = isset($_GET['edit']);
if ($editMode) {
    $stmt = db()->prepare("SELECT b.*, u.name cname, u.email cemail, u.phone cphone, pr.company_name, pr.service_type FROM bookings b LEFT JOIN users u ON u.id = b.customer_id LEFT JOIN providers pr ON pr.id = b.provider_id WHERE b.id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editBook = $stmt->fetch() ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    $bid = (int)($_POST['bid'] ?? 0);
    $status = $_POST['status'] ?? 'Pending';
    $addr = trim($_POST['address'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $note = trim($_POST['provider_note'] ?? '');
    $date = $_POST['preferred_date'] ?? '';
    $ts = $_POST['time_start'] ?? '';
    $te = $_POST['time_end'] ?? '';
    $amount = !empty($_POST['amount']) ? (float)$_POST['amount'] : null;
    $payment_status = $_POST['payment_status'] ?? 'unpaid';
    $payment_method = empty($_POST['payment_method']) ? null : $_POST['payment_method'];
    $payment_code = trim($_POST['payment_code'] ?? '');

    if (!in_array($status, array_keys(STATUS_BN), true)) {
        $err = 'অবৈধ স্ট্যাটাস।';
    } elseif ($addr === '') {
        $err = 'ঠিকানা আবশ্যক।';
    } else {
        try {
            if ($amount !== null) {
                db()->prepare("UPDATE bookings SET status=?, address=?, description=?, provider_note=?, preferred_date=?, time_start=?, time_end=?, amount=?, payment_status=?, payment_method=?, payment_code=?, is_read=1 WHERE id=?")
                    ->execute([$status, $addr, $desc, $note, $date, $ts, $te, $amount, $payment_status, $payment_method, $payment_code, $bid]);
            } else {
                db()->prepare("UPDATE bookings SET status=?, address=?, description=?, provider_note=?, preferred_date=?, time_start=?, time_end=?, payment_status=?, payment_method=?, payment_code=?, is_read=1 WHERE id=?")
                    ->execute([$status, $addr, $desc, $note, $date, $ts, $te, $payment_status, $payment_method, $payment_code, $bid]);
            }
            $_SESSION['flash'] = ['msg' => 'বুকিং আপডেট হয়েছে।', 'type' => 'success'];
            header('Location: bookings.php');
            exit;
        } catch (PDOException $e) {
            $err = 'আপডেট ব্যর্থ: ' . $e->getMessage();
        }
    }
}

$filterStatus = $_GET['status'] ?? '';
$filterQ = trim($_GET['q'] ?? '');
$where = [];
$params = [];
if ($filterStatus !== '') {
    $where[] = 'b.status = ?';
    $params[] = $filterStatus;
}
if ($filterQ !== '') {
    $where[] = '(b.ref_code LIKE ? OR u.name LIKE ? OR pr.company_name LIKE ?)';
    $params[] = "%$filterQ%";
    $params[] = "%$filterQ%";
    $params[] = "%$filterQ%";
}
$wh = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$stmt = db()->prepare("SELECT b.*, u.name cname, u.phone cphone, pr.company_name, pr.service_type AS pr_service_type, pr.city FROM bookings b LEFT JOIN users u ON u.id = b.customer_id LEFT JOIN providers pr ON pr.id = b.provider_id $wh ORDER BY b.created_at DESC");
$stmt->execute($params);
$bookings = $stmt->fetchAll();
foreach ($bookings as &$bk) {
    if (empty($bk['service_type']) && !empty($bk['pr_service_type'])) $bk['service_type'] = $bk['pr_service_type'];
}
unset($bk);

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
  <title>বুকিং - সেবা বন্ধু Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Hind Siliguri', sans-serif; box-sizing: border-box; }
    *, *::before, *::after { box-sizing: border-box; }
    .glass-dark { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(20px); }
    .card { background: rgba(30, 41, 59, 0.5); backdrop-filter: blur(20px); border: 1px solid rgba(71, 85, 105, 0.3); border-radius: 1.5rem; width: 100%; }
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
    input, select, textarea { background: rgba(15, 23, 42, 0.6) !important; border: 1px solid rgba(71, 85, 105, 0.5) !important; color: white !important; border-radius: 12px; }
    input:focus, select:focus, textarea:focus { border-color: #8B5CF6 !important; outline: none; }
    select option { background: #1e293b; color: white; }
  </style>
</head>
<body class="bg-slate-950 text-white min-h-screen">

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
<a href="dashboard.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-gauge-high w-5"></i><span>ড্যাশবোর্ড</span></a>
    <a href="users.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-users w-5"></i><span>ব্যবহারকারী</span></a>
    <a href="providers.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-user-tie w-5"></i><span>সেবাদাতা</span></a>
    <a href="bookings.php" class="sidebar-link active flex items-center gap-3 p-3 rounded-xl text-white"><i class="fas fa-calendar-check w-5 text-purple-400"></i><span>বুকিং</span></a>
    <a href="payments.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-credit-card w-5"></i><span>পেমেন্ট</span></a>
    <a href="services.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white"><i class="fas fa-cogs w-5"></i><span>সেবা</span></a>
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
        <h1 class="text-xl md:text-2xl font-bold text-white"><i class="fas fa-calendar-check mr-2 text-emerald-400"></i> বুকিং ব্যবস্থাপনা</h1>
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
    <?php if ($err && !$editMode): ?><div class="mb-6 p-4 rounded-xl bg-red-500/20 text-red-400 border border-red-500/30">❌ <?= e($err) ?></div><?php endif; ?>

    <?php if ($editMode): ?>
    <div class="card p-6 mb-8">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center shadow-lg">
          <i class="fas fa-edit text-white"></i>
        </div>
        <div>
          <h2 class="text-xl font-bold">✏️ বুকিং সম্পাদনা</h2>
          <p class="text-sm font-mono text-purple-400"><?= e($editBook['ref_code'] ?? 'N/A') ?></p>
        </div>
      </div>
      
      <?php if (!$editBook): ?>
        <div class="p-6 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 text-center">
          <i class="fas fa-exclamation-triangle text-3xl mb-3"></i>
          <p class="font-semibold">বুকিং খুঁজে পাওয়া যায়নি (ID: <?= (int)($_GET['edit'] ?? 0) ?>)</p>
          <a href="bookings.php" class="mt-4 inline-flex btn-action bg-slate-700/50 text-slate-300"><i class="fas fa-arrow-left"></i> ফিরে যান</a>
        </div>
      <?php else: ?>
      <?php if ($err): ?><div class="mb-4 p-4 rounded-xl bg-red-500/20 text-red-400 border border-red-500/30">❌ <?= e($err) ?></div><?php endif; ?>
      <form method="POST" action="bookings.php">
        <input type="hidden" name="_csrf" value="<?= csrf() ?>">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="bid" value="<?= (int)$editBook['id'] ?>">
        
        <div class="p-4 bg-slate-800/50 rounded-xl text-sm mb-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <div><strong>গ্রাহক:</strong> <?= e($editBook['cname'] ?? 'N/A') ?></div>
              <div><strong>ফোন:</strong> <?= e($editBook['cphone'] ?? 'N/A') ?></div>
              <div><strong>ইমেইল:</strong> <?= e($editBook['cemail'] ?? 'N/A') ?></div>
            </div>
            <div>
              <div><strong>সেবাদাতা:</strong> <?= e($editBook['company_name'] ?? 'N/A') ?></div>
              <div><strong>সেবা:</strong> <?= svcLabel($editBook['service_type'] ?? '') ?></div>
              <div><strong>চার্জ:</strong> ৳<?= (int)($editBook['service_charge'] ?? 0) ?></div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">পেমেন্ট পদ্ধতি</label>
            <select name="payment_method" class="w-full p-3">
              <option value="">-- নির্বাচন করুন --</option>
              <option value="ssl" <?= ($editBook['payment_method'] ?? '') === 'ssl' ? 'selected' : '' ?>>💳 SSL Commercial</option>
              <option value="mobile_banking" <?= ($editBook['payment_method'] ?? '') === 'mobile_banking' ? 'selected' : '' ?>>📱 Mobile Banking</option>
              <option value="cash_on_delivery" <?= ($editBook['payment_method'] ?? '') === 'cash_on_delivery' ? 'selected' : '' ?>>💵 Cash on Delivery</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">পেমেন্ট কোড</label>
            <input type="text" name="payment_code" value="<?= e($editBook['payment_code'] ?? '') ?>" placeholder="Payment Reference" class="w-full p-3">
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">তারিখ *</label>
            <input type="date" name="preferred_date" required value="<?= e($editBook['preferred_date'] ?? date('Y-m-d')) ?>" class="w-full p-3">
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">স্ট্যাটাস *</label>
            <select name="status" class="w-full p-3">
              <?php foreach (STATUS_BN as $v => $bn): ?>
              <option value="<?= $v ?>" <?= ($editBook['status'] ?? 'Pending') === $v ? 'selected' : '' ?>><?= $bn ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">অ্যামাউন্ট (৳)</label>
            <?php 
              $displayAmt = $editBook['amount'] ?? $editBook['estimated_price'] ?? 0;
              if (empty($displayAmt) && !empty($editBook['price_range'])) {
                  $displayAmt = (int)preg_replace('/[^0-9]/', '', $editBook['price_range']);
              }
            ?>
            <input type="number" name="amount" step="0.01" min="0" value="<?= e($displayAmt) ?>" placeholder="0.00" class="w-full p-3">
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">পেমেন্ট স্ট্যাটাস</label>
            <select name="payment_status" class="w-full p-3">
              <option value="unpaid" <?= ($editBook['payment_status'] ?? 'unpaid') === 'unpaid' ? 'selected' : '' ?>>✕ অপরিশোধ</option>
              <option value="paid" <?= ($editBook['payment_status'] ?? 'unpaid') === 'paid' ? 'selected' : '' ?>>✓ পরিশোধিত</option>
              <option value="pending_verification" <?= ($editBook['payment_status'] ?? 'unpaid') === 'pending_verification' ? 'selected' : '' ?>>⏳ সার্চ অপেক্ষায়</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">শুরু</label>
            <input type="time" name="time_start" value="<?= substr($editBook['time_start'] ?? '09:00', 0, 5) ?>" class="w-full p-3">
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">শেষ</label>
            <input type="time" name="time_end" value="<?= substr($editBook['time_end'] ?? '18:00', 0, 5) ?>" class="w-full p-3">
          </div>
        </div>

        <div class="mt-5">
          <label class="block text-sm font-semibold text-slate-300 mb-2">ঠিকানা *</label>
          <input type="text" name="address" required value="<?= e($editBook['address'] ?? '') ?>" class="w-full p-3">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">সমস্যার বিবরণ</label>
            <textarea name="description" rows="2" class="w-full p-3"><?= e($editBook['description'] ?? '') ?></textarea>
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">সেবাদাতার নোট</label>
            <textarea name="provider_note" rows="2" class="w-full p-3"><?= e($editBook['provider_note'] ?? '') ?></textarea>
          </div>
        </div>

        <div class="flex items-center gap-4 pt-4 mt-5 border-t border-slate-700/50">
          <button type="submit" class="btn-action btn-primary"><i class="fas fa-save"></i> আপডেট করুন</button>
          <a href="bookings.php" class="btn-action bg-slate-700/50 text-slate-300 hover:bg-slate-600/50"><i class="fas fa-times"></i> বাতিল</a>
        </div>
      </form>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="card overflow-hidden">
      <div class="p-6 border-b border-slate-700/50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center shadow-lg">
            <i class="fas fa-list text-white"></i>
          </div>
          <div>
            <h3 class="text-lg font-bold">বুকিং তালিকা</h3>
            <p class="text-slate-500 text-sm">মোট <?= count($bookings) ?>টি</p>
          </div>
        </div>
        <form method="GET" class="flex flex-wrap gap-3 items-center">
          <div class="relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
            <input type="text" name="q" value="<?= e($filterQ) ?>" placeholder="রেফারেন্স / গ্রাহক…" class="pl-10 pr-4 py-2 w-48 md:w-64 text-sm">
          </div>
          <select name="status" onchange="this.form.submit()" class="p-2 text-sm">
            <option value="">সব স্ট্যাটাস</option>
            <?php foreach (STATUS_BN as $v => $bn): ?>
              <option value="<?= $v ?>" <?= $filterStatus === $v ? 'selected' : '' ?>><?= $bn ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn-action btn-primary text-sm"><i class="fas fa-search"></i></button>
          <?php if ($filterStatus || $filterQ): ?><a href="bookings.php" class="btn-action bg-slate-700/50 text-slate-300 text-sm"><i class="fas fa-times"></i></a><?php endif; ?>
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
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">তারিখ</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">পেমেন্ট</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-400 uppercase">স্ট্যাটাস</th>
              <th class="px-6 py-4 text-right text-xs font-semibold text-slate-400 uppercase">অ্যাকশন</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-700/30">
            <?php if (empty($bookings)): ?>
              <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500"><i class="fas fa-calendar-xmark text-4xl mb-3"></i><p>কোনো বুকিং পাওয়া যায়নি।</p></td></tr>
            <?php else: foreach ($bookings as $b): ?>
              <?php $isUnread = !array_key_exists('is_read', $b) || empty($b['is_read']); ?>
              <tr class="hover:bg-slate-800/30 transition-all <?= $isUnread ? 'bg-red-500/10' : '' ?>">
                <td class="px-6 py-4">
                  <?php if ($isUnread): ?><span class="inline-block w-2 h-2 bg-red-500 rounded-full mr-2 animate-pulse"></span><?php endif; ?>
                  <span class="font-mono font-bold text-purple-400"><?= e($b['ref_code']) ?></span>
                </td>
                <td class="px-6 py-4">
                  <div class="font-semibold"><?= e($b['cname']) ?></div>
                  <div class="text-xs text-slate-500"><?= e($b['cphone'] ?? '') ?></div>
                </td>
                <td class="px-6 py-4">
                  <div class="font-semibold text-cyan-400"><?= e($b['company_name']) ?></div>
                  <div class="text-xs text-slate-500"><?= svcLabel($b['service_type']) ?></div>
                </td>
                <td class="px-6 py-4">
                  <div class="font-bold">৳<?php 
                    $amt = $b['estimated_price'] ?? 0;
                    if (!$amt && !empty($b['price_range'])) {
                        $amt = (int)preg_replace('/[^0-9]/', '', $b['price_range']);
                    }
                    if (!$amt && !empty($b['amount'])) {
                        $amt = (int)$b['amount'];
                    }
                    echo number_format($amt, 0); 
                  ?></div>
                </td>
                <td class="px-6 py-4 text-slate-400 text-sm"><?= date('d M Y', strtotime($b['preferred_date'])) ?></td>
                <td class="px-6 py-4">
                  <?php
                  $pm = $b['payment_method'] ?? '';
                  $ps = $b['payment_status'] ?? 'unpaid';
                  if ($pm) {
                    $pmLabels = ['ssl' => '💳 SSL Commercial', 'mobile_banking' => '📱 Mobile Banking', 'cash_on_delivery' => '💵 Cash on Delivery'];
                    $psLabels = ['paid' => 'bg-emerald-500/20 text-emerald-400|✓ পরিশোধ', 'pending_verification' => 'bg-amber-500/20 text-amber-400|⏳ সার্চ', 'unpaid' => 'bg-red-500/20 text-red-400|✕ বাকি'];
                    $pmLabel = $pmLabels[$pm] ?? $pm;
                    $psConfig = explode('|', $psLabels[$ps] ?? 'bg-slate-500/20 text-slate-400|অজানা');
                    echo '<div class="text-xs font-semibold text-purple-400">' . $pmLabel . '</div>';
                    echo '<div class="mt-1"><span class="px-2 py-1 rounded text-xs font-bold ' . $psConfig[0] . '">' . $psConfig[1] . '</span></div>';
                    if ($ps === 'paid' && $b['payment_code']) {
                      echo '<div class="text-xs text-slate-500 mt-1 font-mono">' . $b['payment_code'] . '</div>';
                    }
                  } else {
                    echo '<span class="text-xs text-slate-500">-</span>';
                  }
                  ?>
                </td>
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
                    <a href="?edit=<?= (int)$b['id'] ?>" class="btn-action bg-cyan-500/20 text-cyan-400 text-sm"><i class="fas fa-edit"></i></a>
                    <a href="?action=delete&id=<?= (int)$b['id'] ?>&_csrf=<?= csrf() ?>" class="btn-action btn-danger text-sm" onclick="return confirm('মুছে ফেলবেন?')"><i class="fas fa-trash"></i></a>
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
</script>

<?php renderAdminNotifScripts(); ?>

</body>
</html>
