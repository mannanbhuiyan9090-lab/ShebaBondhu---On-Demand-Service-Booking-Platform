<?php
require_once __DIR__ . '/includes/config.php';
sessionStart();
requireAuth('customer');

try {
    db()->exec("ALTER TABLE bookings ADD COLUMN service_charge INT DEFAULT 0 AFTER payment_code");
    db()->exec("ALTER TABLE bookings ADD COLUMN price_range VARCHAR(50) DEFAULT NULL AFTER service_charge");
    db()->exec("ALTER TABLE bookings ADD COLUMN estimated_price INT DEFAULT 0 AFTER price_range");
    db()->exec("ALTER TABLE bookings ADD COLUMN revenue INT DEFAULT 0 AFTER estimated_price");
    db()->exec("ALTER TABLE bookings ADD COLUMN amount DECIMAL(10,2) DEFAULT NULL AFTER revenue");
} catch (Exception $e) {}

$user = auth();
$providerId = (int)($_GET['provider'] ?? 0);

if (!$providerId) {
    redirect(SITE_URL . '/services.php', 'সেবাদাতা নির্বাচন করুন।', 'error');
}

try {
    $stmt = db()->prepare("SELECT p.*, u.name as owner_name FROM providers p JOIN users u ON u.id = p.user_id WHERE p.id = ?");
    $stmt->execute([$providerId]);
    $provider = $stmt->fetch();
    
    if (!$provider) {
        redirect(SITE_URL . '/services.php', 'সেবাদাতা পাওয়া যায়নি।', 'error');
    }
} catch(Exception $e) {
    redirect(SITE_URL . '/services.php', 'কিছু সমস্যা হয়েছে।', 'error');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = trim($_POST['preferred_date'] ?? '');
    $timeStart = trim($_POST['time_start'] ?? '');
    $timeEnd = trim($_POST['time_end'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    $paymentMethod = $_POST['payment_method'] ?? '';
    $servicePrice = (int)filter_var($provider['price_range'] ?? '', FILTER_SANITIZE_NUMBER_INT) ?: 500;
    
    if (!$date || !$timeStart || !$timeEnd || !$address) {
        $error = 'সকল প্রয়োজনীয় ক্ষেত্র পূরণ করুন।';
    } elseif (!$paymentMethod) {
        $error = 'পেমেন্ট পদ্ধতি নির্বাচন করুন।';
    } else {
        try {
            $serviceChargePercent = 15;
            $estimatedPrice = (int)($_POST['price_budget'] ?? 1550);
            $serviceCharge = (int)(($estimatedPrice * $serviceChargePercent) / 100);
            $revenue = $estimatedPrice - $serviceCharge;
            $priceBudget = $estimatedPrice;
            $priceBudget = $estimatedPrice;
            
            $refCode = 'HG' . strtoupper(substr(md5(time() . rand()), 0, 6));
            $paymentStatus = 'unpaid';
            $paymentCode = '';
            
            if ($paymentMethod === 'cash_on_delivery') {
                $paymentCode = 'COD' . strtoupper(substr(md5(time() . rand()), 0, 4));
                $paymentStatus = 'pending_verification';
            }
            
            $cid = (int)$user['id'];
            $pid = (int)$providerId;
            $svc = addslashes($provider['service_type']);
            $addr = addslashes($address);
            $desc = addslashes($description);
            $pm = addslashes($paymentMethod);
            $ps = addslashes($paymentStatus);
            $pc = addslashes($paymentCode);
            
            $sql = "INSERT INTO bookings SET 
                    ref_code = '$refCode',
                    customer_id = $cid,
                    provider_id = $pid,
                    service_type = '$svc',
                    preferred_date = '$date',
                    time_start = '$timeStart',
                    time_end = '$timeEnd',
                    address = '$addr',
                    description = '$desc',
                    status = 'Pending',
                    payment_method = '$pm',
                    payment_status = '$ps',
                    payment_code = '$pc',
                    service_charge = $serviceCharge,
                    price_range = '$priceBudget',
                    estimated_price = $estimatedPrice,
                    revenue = $revenue";
            
            db()->exec($sql);
            
            try {
                db()->prepare("INSERT INTO notification (user_id, role_target, type, title, message, link, is_read, created_at) VALUES (0, 'admin', 'booking', 'নতুন বুকিং!', 'একটি নতুন বুকিং হয়েছে। রেফারেন্স: $refCode', 'admin/bookings.php', 0, NOW())")->execute();
            } catch (Exception $e) {}
            
            redirect(SITE_URL . '/customer/bookings.php', 'বুকিং সফল হয়েছে! রেফারেন্স: ' . $refCode);
        } catch (Exception $e) {
            $error = 'বুকিং সম্পন্ন হয়নি। আবার চেষ্টা করুন।';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>বুকিং করুন - সেবা বন্ধু</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Hind Siliguri', sans-serif; }
    .glass-dark { background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(20px); }
    .card { background: rgba(30, 41, 59, 0.5); backdrop-filter: blur(20px); border: 1px solid rgba(71, 85, 105, 0.3); border-radius: 1.5rem; }
    .sidebar-link { transition: all 0.2s ease; }
    .sidebar-link:hover { background: rgba(71, 85, 105, 0.3); transform: translateX(4px); }
    .btn-action { padding: 10px 20px; border-radius: 12px; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; }
    .btn-primary { background: linear-gradient(135deg, #10B981, #059669); color: white; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4); }
    input, select, textarea { background: rgba(15, 23, 42, 0.6) !important; border: 1px solid rgba(71, 85, 105, 0.5) !important; color: white !important; border-radius: 12px; }
    input:focus, select:focus, textarea:focus { border-color: #10B981 !important; outline: none; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2); }
    .gradient-text { background: linear-gradient(135deg, #10B981, #06B6D4); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
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
    <a href="customer/dashboard.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-gauge-high w-5 text-emerald-400"></i>
      <span>ড্যাশবোর্ড</span>
    </a>
    <a href="customer/bookings.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-calendar-check w-5"></i>
      <span>আমার বুকিং</span>
    </a>
    <a href="customer/profile.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
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
          <i class="fas fa-calendar-check mr-2 text-emerald-400"></i> বুকিং করুন
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
          <?= $provider['emoji'] ?? '🔧' ?>
        </div>
        <div>
          <h2 class="text-xl font-bold"><?= e($provider['company_name']) ?></h2>
          <p class="text-slate-400"><?= svcLabel($provider['service_type']) ?> • <?= cityBn($provider['city']) ?></p>
          <p class="text-amber-400 text-sm"><?= $provider['rating'] ?> ★ (<?= $provider['review_count'] ?> রিভিউ)</p>
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
              <input type="date" name="preferred_date" required min="<?= date('Y-m-d') ?>" value="<?= e($_POST['preferred_date'] ?? '') ?>" class="w-full p-3">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-slate-300 mb-2">শুরুর সময় *</label>
                <input type="time" name="time_start" required value="<?= e($_POST['time_start'] ?? '09:00') ?>" class="w-full p-3">
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-300 mb-2">শেষের সময় *</label>
                <input type="time" name="time_end" required value="<?= e($_POST['time_end'] ?? '18:00') ?>" class="w-full p-3">
              </div>
            </div>
          </div>
        </div>

        <div class="border-t border-slate-700/50 pt-6">
          <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
            <i class="fas fa-map-marker-alt text-emerald-400"></i> ঠিকানা
          </h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">সেবার ঠিকানা *</label>
              <textarea name="address" rows="2" placeholder="আপনার সম্পূর্ণ ঠিকানা লিখুন..." required class="w-full p-3"><?= e($_POST['address'] ?? '') ?></textarea>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">বিবরণ (ঐচ্ছিক)</label>
              <textarea name="description" rows="2" placeholder="কোনো বিশেষ নির্দেশনা থাকলে লিখুন..." class="w-full p-3"><?= e($_POST['description'] ?? '') ?></textarea>
            </div>
          </div>
        </div>

        <div class="border-t border-slate-700/50 pt-6">
          <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
            <i class="fas fa-money-bill-wave text-emerald-400"></i> আপনার বাজেট: ৳ <span id="priceValue"><?= number_format(1250) ?></span>
          </h3>
          <div>
             <input type="range" id="priceSlider" name="price_budget" min="<?= $provider['price_min'] ?? 500 ?>" max="<?= $provider['price_max'] ?? $provider['price_range'] ?? 2000 ?>" step="250" value="1250" style="width:100%;height:10px;-webkit-appearance:none;appearance:none;background:#e5e7eb;cursor:pointer;" oninput="updatePriceSlider(this.value)">
            <div style="display:flex;justify-content:space-between;font-size:12px;color:#9ca3af;margin-top:4px;">
              <span>৳ <?= number_format($provider['price_min'] ?? 500) ?></span>
              <span>৳ <?= number_format($provider['price_max'] ?? $provider['price_range'] ?? 2000) ?></span>
            </div>
            <input type="number" id="priceInput" name="price_budget" value="1250" min="<?= $provider['price_min'] ?? 500 ?>" max="<?= $provider['price_max'] ?? $provider['price_range'] ?? 2000 ?>" placeholder="আপনার বাজেট লিখুন" class="w-full p-3 mt-3" oninput="updatePriceInput(this.value)">
          </div>
        </div>

        <div class="border-t border-slate-700/50 pt-6">
          <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
            <i class="fas fa-credit-card text-emerald-400"></i> পেমেন্ট পদ্ধতি *
          </h3>
          <div class="space-y-3">
            <label class="flex items-center gap-3 p-4 bg-slate-800/50 rounded-xl cursor-pointer hover:bg-slate-700/50">
              <input type="radio" name="payment_method" value="ssl" <?= ($_POST['payment_method'] ?? '') === 'ssl' ? 'checked' : '' ?> class="w-5 h-5">
              <i class="fas fa-credit-card text-purple-400"></i>
              <span>SSL Commercial</span>
            </label>
            <label class="flex items-center gap-3 p-4 bg-slate-800/50 rounded-xl cursor-pointer hover:bg-slate-700/50">
              <input type="radio" name="payment_method" value="mobile_banking" <?= ($_POST['payment_method'] ?? '') === 'mobile_banking' ? 'checked' : '' ?> class="w-5 h-5">
              <i class="fas fa-mobile-alt text-blue-400"></i>
              <span>Mobile Banking</span>
            </label>
            <label class="flex items-center gap-3 p-4 bg-slate-800/50 rounded-xl cursor-pointer hover:bg-slate-700/50">
              <input type="radio" name="payment_method" value="cash_on_delivery" <?= ($_POST['payment_method'] ?? '') === 'cash_on_delivery' ? 'checked' : '' ?> class="w-5 h-5">
              <i class="fas fa-money-bill-wave text-green-400"></i>
              <span>Cash on Delivery</span>
            </label>
          </div>
        </div>

        <div class="pt-4">
          <button type="submit" class="btn-action btn-primary w-full justify-center text-lg py-4">
            <i class="fas fa-check-circle"></i> বুকিং নিশ্চিত করুন
          </button>
        </div>
      </form>
    </div>

    <a href="<?= SITE_URL ?>/services.php" class="block text-center mt-6 text-slate-400 hover:text-white">
      <i class="fas fa-arrow-left"></i> ফিরে যান
    </a>
  </main>
</div>

<script>
  function updatePriceSlider(value) {
    const pct = (value - 500) / 1500 * 100;
    const slider = document.getElementById('priceSlider');
    const priceValue = document.getElementById('priceValue');
    const priceInput = document.getElementById('priceInput');

    slider.style.background = `linear-gradient(to right, #f97316 0%, #f97316 ${pct}%, #e5e7eb ${pct}%, #e5e7eb 100%)`;
    priceValue.innerText = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    priceInput.value = value;
  }

  function updatePriceInput(value) {
    const pct = (value - 500) / 1500 * 100;
    const slider = document.getElementById('priceSlider');
    const priceValue = document.getElementById('priceValue');

    slider.style.background = `linear-gradient(to right, #f97316 0%, #f97316 ${pct}%, #e5e7eb ${pct}%, #e5e7eb 100%)`;
    priceValue.innerText = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  }

document.getElementById('menuToggle')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('-translate-x-full');
});
</script>

</body>
</html>