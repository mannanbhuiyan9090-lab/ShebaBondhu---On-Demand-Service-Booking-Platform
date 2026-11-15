<?php
require_once __DIR__.'/../includes/config.php';
sessionStart();
requireAuth('customer');
$user = auth();

$bookingId = (int)($_GET['id'] ?? 0);
if (!$bookingId) {
  redirect(SITE_URL.'/customer/bookings.php','অবৈধ বুকিং আইডি।','error');
}

$error = '';
$success = '';

try {
  $stmt = db()->prepare("SELECT b.*, p.company_name, p.emoji, p.service_type, p.city FROM bookings b JOIN providers p ON p.id = b.provider_id WHERE b.id = ? AND b.customer_id = ?");
  $stmt->execute([$bookingId, $user['id']]);
  $booking = $stmt->fetch();

  if (!$booking) {
    redirect(SITE_URL.'/customer/bookings.php','বুকিং পাওয়া যায়নি।','error');
  }

  if ($booking['status'] !== 'Pending') {
    redirect(SITE_URL.'/customer/bookings.php','শুধুমাত্র অপেক্ষমান বুকিং সম্পাদনা করা যাবে।','error');
  }
} catch (Exception $e) {
  redirect(SITE_URL.'/customer/bookings.php','একটি ত্রুটি হয়েছে।','error');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $date = trim($_POST['preferred_date'] ?? '');
  $timeStart = trim($_POST['time_start'] ?? '');
  $timeEnd = trim($_POST['time_end'] ?? '');
  $address = trim($_POST['address'] ?? '');
  $description = trim($_POST['description'] ?? '');

  if (!$date || !$timeStart || !$timeEnd || !$address) {
    $error = 'সকল প্রয়োজনীয় ক্ষেত্র পূরণ করুন।';
  } elseif ($timeEnd <= $timeStart) {
    $error = 'শেষের সময় শুরুর সময়ের পরে হতে হবে।';
  } elseif (strtotime($date) < strtotime(date('Y-m-d'))) {
    $error = 'অতীতের তারিখ নির্বাচন করা যাবে না।';
  } else {
    try {
      db()->prepare("UPDATE bookings SET preferred_date = ?, time_start = ?, time_end = ?, address = ?, description = ? WHERE id = ? AND customer_id = ? AND status = 'Pending'")
        ->execute([$date, $timeStart, $timeEnd, $address, $description, $bookingId, $user['id']]);
      redirect(SITE_URL.'/customer/bookings.php','বুকিং সফলভাবে আপডেট হয়েছে।');
    } catch (Exception $e) {
      $error = 'আপডেট করতে ব্যর্থ হয়েছে। আবার চেষ্টা করুন।';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>বুকিং সম্পাদনা - সেবা বন্ধু</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Hind Siliguri', sans-serif; }
    .glass-dark { background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(20px); }
    .card { background: rgba(30, 41, 59, 0.5); backdrop-filter: blur(20px); border: 1px solid rgba(71, 85, 105, 0.3); border-radius: 1.5rem; }
    .sidebar-link { transition: all 0.2s ease; }
    .sidebar-link:hover { background: rgba(71, 85, 105, 0.3); transform: translateX(4px); }
    .sidebar-link.active { background: linear-gradient(90deg, rgba(16, 185, 129, 0.3), rgba(6, 182, 212, 0.1)); border-left: 3px solid #10B981; }
    .gradient-text { background: linear-gradient(135deg, #10B981, #06B6D4); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
    .btn-action { padding: 10px 20px; border-radius: 12px; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; }
    .btn-primary { background: linear-gradient(135deg, #10B981, #059669); color: white; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4); }
    .btn-secondary { background: rgba(71, 85, 105, 0.3); color: white; }
    .btn-secondary:hover { background: rgba(71, 85, 105, 0.5); }
    input, select, textarea { background: rgba(15, 23, 42, 0.6) !important; border: 1px solid rgba(71, 85, 105, 0.5) !important; color: white !important; border-radius: 12px; }
    input:focus, select:focus, textarea:focus { border-color: #10B981 !important; outline: none; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2); }
  </style>
</head>
<body class="bg-slate-950 text-white min-h-screen">

<aside id="sidebar" class="w-64 bg-gradient-to-b from-emerald-900/50 to-slate-900 h-screen fixed left-0 top-0 border-r border-slate-700/50 z-50 flex flex-col">
  <div class="p-6 border-b border-slate-700/50">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center shadow-lg">
        <i class="fas fa-bolt text-white"></i>
      </div>
      <div>
        <div class="text-xl font-bold gradient-text">সেবা বন্ধু</div>
        <div class="text-xs text-slate-500 uppercase tracking-wider">গ্রাহক প্যানেল</div>
      </div>
    </div>
  </div>
  
  <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
    <a href="dashboard.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-gauge-high w-5 text-emerald-400"></i>
      <span>ড্যাশবোর্ড</span>
    </a>
    <a href="bookings.php" class="sidebar-link active flex items-center gap-3 p-3 rounded-xl text-white">
      <i class="fas fa-calendar-check w-5"></i>
      <span>আমার বুকিং</span>
    </a>
    <a href="profile.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-user w-5"></i>
      <span>প্রোফাইল</span>
    </a>
    <a href="<?= SITE_URL ?>/services.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-search w-5"></i>
      <span>সেবা খুঁজুন</span>
    </a>
  </nav>

  <div class="p-4 border-t border-slate-700/50">
    <a href="<?= SITE_URL ?>/logout.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-red-500/10 text-slate-400 hover:text-red-400 transition-all group">
      <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center font-bold text-sm shadow-lg">
        <?= strtoupper(mb_substr($user['name'], 0, 2)) ?>
      </div>
      <div class="flex-1">
        <div class="font-semibold text-sm"><?= e(explode(' ', $user['name'])[0]) ?></div>
        <div class="text-xs text-slate-500">গ্রাহক</div>
      </div>
      <i class="fas fa-right-from-bracket group-hover:rotate-180 transition-transform"></i>
    </a>
  </div>
</aside>

<button id="menuToggle" class="lg:hidden fixed top-4 left-4 z-50 p-3 bg-slate-800 rounded-xl shadow-lg">
  <i class="fas fa-bars w-5 h-5"></i>
</button>

<div class="lg:ml-64 min-h-screen">
  <header class="sticky top-0 z-40 glass-dark border-b border-slate-700/50">
    <div class="flex items-center justify-between p-4 md:p-6">
      <div class="hidden md:flex items-center gap-3 ml-12">
        <h1 class="text-xl md:text-2xl font-bold text-white">
          <i class="fas fa-edit mr-2 text-emerald-400"></i> বুকিং সম্পাদনা
        </h1>
      </div>
      <div class="md:hidden flex items-center gap-2">
        <span class="text-lg font-bold gradient-text">সেবা বন্ধু</span>
      </div>
    </div>
  </header>

  <main class="p-4 md:p-8 max-w-2xl mx-auto">
    <?php if ($error): ?>
      <div class="mb-6 p-4 rounded-xl bg-red-500/20 text-red-400 border border-red-500/30">
        ❌ <?= e($error) ?>
      </div>
    <?php endif; ?>

    <div class="card p-8">
      <div class="flex items-center gap-4 mb-6">
        <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-2xl flex items-center justify-center text-3xl shadow-lg">
          <?= $booking['emoji'] ?? '🔧' ?>
        </div>
        <div>
          <h2 class="text-xl font-bold"><?= e($booking['company_name']) ?></h2>
          <p class="text-slate-400"><?= svcLabel($booking['service_type']) ?> • <?= cityBn($booking['city']) ?></p>
          <p class="text-emerald-400 text-sm font-mono"><?= e($booking['ref_code']) ?></p>
        </div>
      </div>

      <form method="POST" class="space-y-6">
        <div>
          <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
            <i class="fas fa-calendar text-emerald-400"></i> সেবার তারিখ ও সময়
          </h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">তারিখ *</label>
              <input type="date" name="preferred_date" required min="<?= date('Y-m-d') ?>" value="<?= e($_POST['preferred_date'] ?? $booking['preferred_date'] ?? '') ?>" class="w-full p-3">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-slate-300 mb-2">শুরুর সময় *</label>
                <input type="time" name="time_start" required value="<?= e($_POST['time_start'] ?? substr($booking['time_start'] ?? '09:00', 0, 5)) ?>" class="w-full p-3">
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-300 mb-2">শেষের সময় *</label>
                <input type="time" name="time_end" required value="<?= e($_POST['time_end'] ?? substr($booking['time_end'] ?? '18:00', 0, 5)) ?>" class="w-full p-3">
              </div>
            </div>
          </div>
        </div>

        <div class="border-t border-slate-700/50 pt-6">
          <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
            <i class="fas fa-map-marker-alt text-emerald-400"></i> ঠিকানা ও বিবরণ
          </h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">সেবার ঠিকানা *</label>
              <textarea name="address" rows="2" placeholder="আপনার সম্পূর্ণ ঠিকানা লিখুন..." required class="w-full p-3"><?= e($_POST['address'] ?? $booking['address'] ?? '') ?></textarea>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">বিবরণ (ঐচ্ছিক)</label>
              <textarea name="description" rows="2" placeholder="কোনো বিশেষ নির্দেশনা থাকলে লিখুন..." class="w-full p-3"><?= e($_POST['description'] ?? $booking['description'] ?? '') ?></textarea>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-4 pt-4 border-t border-slate-700/50">
          <button type="submit" class="btn-action btn-primary flex-1 justify-center">
            <i class="fas fa-save"></i> আপডেট করুন
          </button>
          <a href="bookings.php" class="btn-action btn-secondary flex-1 justify-center">
            <i class="fas fa-times"></i> বাতিল
          </a>
        </div>
      </form>
    </div>

    <a href="bookings.php" class="block text-center mt-6 text-slate-400 hover:text-white">
      <i class="fas fa-arrow-left"></i> বুকিং তালিকায় ফিরে যান
    </a>
  </main>
</div>

<script>
document.getElementById('menuToggle')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('-translate-x-full');
});
</script>

</body>
</html>

