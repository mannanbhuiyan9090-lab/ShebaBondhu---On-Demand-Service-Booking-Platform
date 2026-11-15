<?php
require_once __DIR__ . '/../includes/config.php';
sessionStart();
requireAuth('provider');
$user = auth();

$prov = db()->prepare("SELECT * FROM providers WHERE user_id = ?");
$prov->execute([$user['id']]);
$provider = $prov->fetch();

if (!$provider) {
    redirect(SITE_URL . '/provider/dashboard.php', 'প্রোফাইল তৈরি করুন।', 'error');
}

$error = $done = '';

$checkCols = db()->query("SHOW COLUMNS FROM providers LIKE 'price_min'");
if ($checkCols->rowCount() === 0) {
    db()->exec("ALTER TABLE providers ADD COLUMN price_min INT DEFAULT 500");
    db()->exec("ALTER TABLE providers ADD COLUMN price_max INT DEFAULT 2000");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company = trim($_POST['company_name'] ?? '');
    $service = $_POST['service_type'] ?? '';
    $city = $_POST['city'] ?? '';
    $about = trim($_POST['about'] ?? '');
    $priceMin = (int)($_POST['price_min'] ?? 500);
    $priceMax = (int)($_POST['price_max'] ?? 2000);
    $price = (int)($_POST['price'] ?? $priceMax);
    $wS = $_POST['work_start'] ?? '09:00';
    $wE = $_POST['work_end'] ?? '18:00';
    $emoji = trim($_POST['emoji'] ?? '🔧');
    
    if (!$company || !$service || !$city) {
        $error = 'সকল প্রয়োজনীয় ক্ষেত্র পূরণ করুন।';
    } else {
        try {
            db()->prepare("UPDATE providers SET company_name = ?, service_type = ?, city = ?, about = ?, price_range = ?, price_min = ?, price_max = ?, work_start = ?, work_end = ?, emoji = ? WHERE id = ?")
                ->execute([$company, $service, $city, $about, $price, $priceMin, $priceMax, $wS, $wE, $emoji, $provider['id']]);
            
            $done = 'সেবার তথ্য আপডেট হয়েছে।';
            
            $prov = db()->prepare("SELECT * FROM providers WHERE user_id = ?");
            $prov->execute([$user['id']]);
            $provider = $prov->fetch();
        } catch(Exception $e) {
            $error = 'আপডেটে সমস্যা হয়েছে।';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>আমার সেবা - সেবা বন্ধু প্রোভাইডার</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Hind Siliguri', sans-serif; }
    .glass-dark { background: rgba(88, 28, 135, 0.6); backdrop-filter: blur(20px); }
    .card { background: rgba(88, 28, 135, 0.3); backdrop-filter: blur(20px); border: 1px solid rgba(167, 139, 250, 0.3); border-radius: 1.5rem; }
    .sidebar-link { transition: all 0.2s ease; }
    .sidebar-link:hover { background: rgba(71, 85, 105, 0.3); transform: translateX(4px); }
    .sidebar-link.active { background: linear-gradient(90deg, rgba(167, 139, 250, 0.3), rgba(45, 212, 191, 0.1)); border-left: 3px solid #a78bfa; }
    .gradient-text { background: linear-gradient(135deg, #a78bfa, #2dd4bf); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
    .btn-action { padding: 10px 20px; border-radius: 12px; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; }
    .btn-primary { background: linear-gradient(135deg, #a78bfa, #2dd4bf); color: white; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(167, 139, 250, 0.4); }
    input, select, textarea { background: rgba(88, 28, 135, 0.3) !important; border: 1px solid rgba(167, 139, 250, 0.5) !important; color: white !important; border-radius: 12px; }
    input:focus, select:focus, textarea:focus { border-color: #a78bfa !important; outline: none; box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.2); }
  </style>
</head>
<body class="bg-gradient-to-br from-purple-900 via-slate-900 to-teal-900 text-white min-h-screen">

<aside id="sidebar" class="w-64 bg-gradient-to-b from-purple-900/60 to-teal-900/40 h-screen fixed left-0 top-0 border-r border-purple-500/30 z-50 flex flex-col">
  <div class="p-6 border-b border-purple-500/30">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-teal-500 rounded-xl flex items-center justify-center shadow-lg">
        <i class="fas fa-bolt text-white"></i>
      </div>
      <div>
        <div class="text-xl font-bold gradient-text">সেবা বন্ধু</div>
        <div class="text-xs text-slate-500 uppercase tracking-wider">সেবাদাতা প্যানেল</div>
      </div>
    </div>
  </div>
  
  <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
    <a href="dashboard.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-gauge-high w-5 text-purple-400"></i>
      <span>ড্যাশবোর্ড</span>
    </a>
    <a href="bookings.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-calendar-check w-5"></i>
      <span>বুকিং সমূহ</span>
    </a>
    <a href="services.php" class="sidebar-link active flex items-center gap-3 p-3 rounded-xl text-white">
      <i class="fas fa-tools w-5 text-orange-400"></i>
      <span>আমার সেবা</span>
    </a>
    <a href="profile.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-user w-5"></i>
      <span>প্রোফাইল</span>
    </a>
    <a href="availability.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-toggle-on w-5"></i>
      <span>উপলব্ধতা</span>
    </a>
  </nav>

  <?php if ($provider): ?>
    <div class="p-4 border-t border-slate-700/50">
      <div class="p-3 rounded-xl <?= $provider['available'] ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400' ?> text-center">
        <div class="flex items-center justify-center gap-2 font-bold">
          <span class="w-2 h-2 rounded-full <?= $provider['available'] ? 'bg-emerald-400 pulse-dot' : 'bg-red-400' ?>"></span>
          <?= $provider['available'] ? 'এখন উপলব্ধ' : 'এখন অনুপলব্ধ' ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="p-4 border-t border-slate-700/50">
    <a href="<?= SITE_URL ?>/logout.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-red-500/10 text-slate-400 hover:text-red-400 transition-all group">
      <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-teal-500 rounded-xl flex items-center justify-center font-bold text-sm shadow-lg">
        <?= strtoupper(mb_substr($user['name'], 0, 2)) ?>
      </div>
      <div class="flex-1">
        <div class="font-semibold text-sm"><?= e(explode(' ', $user['name'])[0]) ?></div>
        <div class="text-xs text-orange-400">সেবাদাতা</div>
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
          <i class="fas fa-tools mr-2 text-orange-400"></i> আমার সেবা পরিচালনা
        </h1>
      </div>
      <div class="md:hidden flex items-center gap-2">
        <span class="text-lg font-bold gradient-text">সেবা বন্ধু</span>
      </div>
    </div>
  </header>

  <main class="p-4 md:p-8 max-w-3xl mx-auto">
    <?php if ($done): ?>
      <div class="mb-6 p-4 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
        ✅ <?= e($done) ?>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="mb-6 p-4 rounded-xl bg-red-500/20 text-red-400 border border-red-500/30">
        ❌ <?= e($error) ?>
      </div>
    <?php endif; ?>

    <div class="card p-8">
      <form method="POST" class="space-y-6">
        <div>
          <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
            <i class="fas fa-store text-purple-400"></i> ব্যবসার তথ্য
          </h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">ব্যবসার নাম *</label>
              <input type="text" name="company_name" required value="<?= e($provider['company_name'] ?? '') ?>" class="w-full p-3">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-slate-300 mb-2">সেবার ধরন *</label>
                <select name="service_type" required class="w-full p-3">
                  <?php $services = getServiceCategories(); foreach ($services as $s): ?>
                    <option value="<?= e($s['name_en']) ?>" <?= ($provider['service_type'] ?? '') === $s['name_en'] ? 'selected' : '' ?>><?= $s['emoji'] . ' ' . e($s['name_bn']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-300 mb-2">এলাকা *</label>
                <select name="city" required class="w-full p-3">
                  <?php foreach (CITY_BN as $en => $bn): ?>
                    <option value="<?= $en ?>" <?= ($provider['city'] ?? '') === $en ? 'selected' : '' ?>><?= $bn ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div class="border-t border-purple-500/30 pt-6">
          <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
            <i class="fas fa-tags text-orange-400"></i> মূল্য পরিসীমা
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">সর্বনিম্ন (৳)</label>
              <input type="number" name="price_min" id="priceMin" value="<?= $provider['price_min'] ?? 500 ?>" class="w-full p-3" min="100" onchange="updatePriceValues()">
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">সর্বোচ্চ (৳)</label>
              <input type="number" name="price_max" id="priceMax" value="<?= $provider['price_max'] ?? 2000 ?>" class="w-full p-3" min="100" onchange="updatePriceValues()">
            </div>
          </div>
          <div class="mt-4">
            <label class="block text-sm font-semibold text-slate-300 mb-2">আপনার মূল্য (৳)</label>
            <input type="number" name="price" id="priceInput" value="<?= $provider['price_range'] ?? $provider['price_max'] ?? 2000 ?>" class="w-full p-3" min="100">
            <p class="text-xs text-slate-500 mt-1">* মূল্য সর্বনিম্ন ও সর্বোচ্চ এর মধ্যে হতে হবে।</p>
          </div>
          <div class="mt-4">
            <label class="block text-sm font-semibold text-slate-300 mb-2">সার্ভিস চার্জ (১৫%)</label>
            <div class="w-full p-3 bg-slate-800/50 rounded-xl text-emerald-400 text-center font-bold">
              <span id="serviceChargeAmount">৳ <?= number_format((int)((int)($provider['price_range'] ?? 2000) * 15 / 100)) ?></span> (১৫%)
            </div>
            <p class="text-xs text-slate-500 mt-1">* সার্ভিস চার্জ সবসময় ১৫% কাটা হবে।</p>
          </div>
        </div>

        <div class="border-t border-purple-500/30 pt-6">
          <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
            <i class="fas fa-clock text-amber-400"></i> কাজের সময়
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">কাজের শুরু</label>
              <input type="time" name="work_start" value="<?= substr($provider['work_start'] ?? '09:00', 0, 5) ?>" class="w-full p-3">
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">কাজের শেষ</label>
              <input type="time" name="work_end" value="<?= substr($provider['work_end'] ?? '18:00', 0, 5) ?>" class="w-full p-3">
            </div>
          </div>
        </div>

        <div class="border-t border-purple-500/30 pt-6">
          <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
            <i class="fas fa-info-circle text-blue-400"></i> অতিরিক্ত তথ্য
          </h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">আইকন</label>
              <input type="text" name="emoji" value="<?= e($provider['emoji'] ?? '🔧') ?>" class="w-full p-3 text-center" style="font-size: 24px; max-width: 80px;">
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">আপনার সম্পর্কে</label>
              <textarea name="about" rows="3" placeholder="আপনার সেবা সম্পর্কে বিস্তারিত লিখুন..." class="w-full p-3"><?= e($provider['about'] ?? '') ?></textarea>
            </div>
          </div>
        </div>

        <div class="pt-4">
          <button type="submit" class="btn-action btn-primary w-full justify-center text-lg py-4">
            <i class="fas fa-save"></i> সেবার তথ্য আপডেট করুন
          </button>
        </div>
      </form>
    </div>
  </main>
</div>

<script>
document.getElementById('menuToggle')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('-translate-x-full');
});

function updatePriceValues() {
  const priceMin = parseInt(document.getElementById('priceMin').value) || 500;
  const priceMax = parseInt(document.getElementById('priceMax').value) || 2000;
  const priceInput = document.getElementById('priceInput');
  
  priceInput.min = priceMin;
  priceInput.max = priceMax;
  
  if (parseInt(priceInput.value) < priceMin) priceInput.value = priceMin;
  if (parseInt(priceInput.value) > priceMax) priceInput.value = priceMax;
  
  updateServiceCharge();
}

function updateServiceCharge() {
  const price = parseInt(document.getElementById('priceInput').value) || 0;
  const serviceCharge = Math.round(price * 15 / 100);
  document.getElementById('serviceChargeAmount').textContent = '৳ ' + serviceCharge.toLocaleString();
}

document.getElementById('priceInput')?.addEventListener('input', updateServiceCharge);
document.getElementById('priceMin')?.addEventListener('change', updatePriceValues);
document.getElementById('priceMax')?.addEventListener('change', updatePriceValues);
</script>

</body>
</html>