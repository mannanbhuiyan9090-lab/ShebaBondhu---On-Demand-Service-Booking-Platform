<?php
require_once __DIR__ . '/../includes/config.php';
sessionStart();
requireAuth('provider');
$user = auth();

try {
  $prov = db()->prepare("SELECT * FROM providers WHERE user_id = ?");
  $prov->execute([$user['id']]);
  $prov = $prov->fetch();
  $full = db()->prepare("SELECT * FROM users WHERE id = ?");
  $full->execute([$user['id']]);
  $full = $full->fetch();
} catch(Exception $e) {
  $prov = null;
  $full = $user;
}

$error = $done = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name    = trim($_POST['name'] ?? '');
  $company = trim($_POST['company_name'] ?? '');
  $city    = $_POST['city'] ?? '';
  $service = $_POST['service_type'] ?? '';
  $about   = trim($_POST['about'] ?? '');
  $price   = trim($_POST['price_range'] ?? '');
  $wS      = $_POST['work_start'] ?? '09:00';
  $wE      = $_POST['work_end'] ?? '18:00';
  $phone   = trim($_POST['phone'] ?? '');
  $emoji   = trim($_POST['emoji'] ?? '🔧');

  if (!$name || !$company || !$city || !$service) {
    $error = 'সকল প্রয়োজনীয় ক্ষেত্র পূরণ করুন।';
  } else {
    try {
      db()->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?")->execute([$name, $phone, $user['id']]);
      
      if ($prov) {
        db()->prepare("UPDATE providers SET company_name = ?, service_type = ?, city = ?, about = ?, price_range = ?, work_start = ?, work_end = ?, emoji = ? WHERE id = ?")
          ->execute([$company, $service, $city, $about, $price, $wS, $wE, $emoji, $prov['id']]);
        
        if (!empty($_FILES['photo']['name'])) {
          $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
          if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $uploadDir = dirname(__DIR__).'/uploads';
            if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $photoPath = 'uploads/p'.$prov['id'].'_'.time().'.'.$ext;
            if(move_uploaded_file($_FILES['photo']['tmp_name'], dirname(__DIR__).'/'.$photoPath)) {
              db()->prepare("UPDATE providers SET photo = ? WHERE id = ?")->execute([$photoPath, $prov['id']]);
            }
          }
        }
      }
      
      $_SESSION['user']['name'] = $name;
      
      if (!empty($_POST['new_password'])) {
        if (strlen($_POST['new_password']) < 6) {
          $error = 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষর হতে হবে।';
        } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
          $error = 'পাসওয়ার্ড মিলছে না।';
        } else {
          db()->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([password_hash($_POST['new_password'], PASSWORD_DEFAULT), $user['id']]);
        }
      }
      
      if (!$error) {
        $done = 'প্রোফাইল সফলভাবে আপডেট হয়েছে।';
      }
    } catch(Exception $e) {
      $error = 'আপডেটে সমস্যা হয়েছে।';
    }
  }

  $prov = db()->prepare("SELECT * FROM providers WHERE user_id = ?");
  $prov->execute([$user['id']]);
  $prov = $prov->fetch();
  $full = db()->prepare("SELECT * FROM users WHERE id = ?");
  $full->execute([$user['id']]);
  $full = $full->fetch();
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>প্রোফাইল - সেবা বন্ধু প্রোভাইডার</title>
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
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
    <a href="services.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-tools w-5 text-orange-400"></i>
      <span>আমার সেবা</span>
    </a>
    <a href="profile.php" class="sidebar-link active flex items-center gap-3 p-3 rounded-xl text-white">
      <i class="fas fa-user w-5"></i>
      <span>প্রোফাইল</span>
    </a>
    <a href="availability.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-toggle-on w-5"></i>
      <span>উপলব্ধতা</span>
    </a>
  </nav>

  <?php if ($prov): ?>
    <div class="p-4 border-t border-slate-700/50">
      <div class="p-3 rounded-xl <?= $prov['available'] ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400' ?> text-center">
        <div class="flex items-center justify-center gap-2 font-bold">
          <span class="w-2 h-2 rounded-full <?= $prov['available'] ? 'bg-emerald-400 pulse-dot' : 'bg-red-400' ?>"></span>
          <?= $prov['available'] ? 'এখন উপলব্ধ' : 'এখন অনুপলব্ধ' ?>
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
          <i class="fas fa-user mr-2 text-orange-400"></i> প্রোফাইল সম্পাদনা
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
      <div class="flex items-center gap-4 mb-8">
        <?php 
        $photoPath = $prov['photo'] ?? '';
        if ($photoPath) {
          $imgSrc = SITE_URL.'/'.$photoPath;
        } else {
          $imgSrc = 'https://ui-avatars.com/api/?name='.urlencode($full['name']).'&background=8b5cf6&color=fff&size=128';
        }
        ?>
        <img id="providerPhoto" src="<?= e($imgSrc) ?>" class="w-24 h-24 rounded-full object-cover shadow-xl border-4 border-purple-500/30">
        <div>
          <h2 class="text-2xl font-bold"><?= e($full['name']) ?></h2>
          <p class="text-slate-500 flex items-center gap-2">
            <i class="fas fa-tools text-orange-400"></i> <?= e($prov['company_name'] ?? 'সেবাদাতা') ?>
          </p>
          <label class="bg-purple-500/30 text-purple-300 px-4 py-2 rounded-full cursor-pointer hover:bg-purple-500/50 transition flex items-center gap-2 mt-2">
            <i class="fas fa-camera"></i> ছবি আপলোড করুন
            <input type="file" name="photo" accept="image/*" class="hidden" onchange="previewProviderPhoto(this)">
          </label>
        </div>
      </div>

      <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <div>
          <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
            <i class="fas fa-user text-emerald-400"></i> ব্যক্তিগত তথ্য
          </h3>
          <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-slate-300 mb-2">পূর্ণ নাম *</label>
                <input type="text" name="name" required value="<?= e($full['name'] ?? '') ?>" class="w-full p-3">
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-300 mb-2">মোবাইল</label>
                <input type="tel" name="phone" value="<?= e($full['phone'] ?? '') ?>" placeholder="017XXXXXXXX" class="w-full p-3">
              </div>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">ইমেইল</label>
              <input type="email" value="<?= e($full['email'] ?? '') ?>" disabled class="w-full p-3 opacity-60 cursor-not-allowed">
            </div>
          </div>
        </div>

        <?php if ($prov): ?>
        <div class="border-t border-purple-500/30 pt-6">
          <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
            <i class="fas fa-tools text-orange-400"></i> সেবার তথ্য
          </h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">ব্যবসার নাম *</label>
              <input type="text" name="company_name" required value="<?= e($prov['company_name']) ?>" class="w-full p-3">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-slate-300 mb-2">সেবার ধরন *</label>
                <select name="service_type" required class="w-full p-3">
                  <?php $provServices = getServiceCategories(); foreach ($provServices as $s): ?>
                    <option value="<?= e($s['name_en']) ?>" <?= $prov['service_type'] === $s['name_en'] ? 'selected' : '' ?>><?= $s['emoji'] . ' ' . e($s['name_bn']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-300 mb-2">এলাকা *</label>
                <select name="city" required class="w-full p-3">
                  <?php foreach (CITY_BN as $en => $bn): ?>
                    <option value="<?= $en ?>" <?= $prov['city'] === $en ? 'selected' : '' ?>><?= $bn ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-slate-300 mb-2">মূল্য পরিসীমা</label>
                <input type="text" name="price_range" value="<?= e($prov['price_range']) ?>" placeholder="৳২০০০" class="w-full p-3">
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-300 mb-2">আইকন</label>
                <input type="text" name="emoji" value="<?= e($prov['emoji']) ?>" class="w-full p-3 text-center" style="font-size: 24px; max-width: 80px;">
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-slate-300 mb-2">কাজের শুরু</label>
                <input type="time" name="work_start" value="<?= substr($prov['work_start'], 0, 5) ?>" class="w-full p-3">
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-300 mb-2">কাজের শেষ</label>
                <input type="time" name="work_end" value="<?= substr($prov['work_end'], 0, 5) ?>" class="w-full p-3">
              </div>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">আপনার সম্পর্কে</label>
              <textarea name="about" rows="3" placeholder="আপনার সেবা সম্পর্কে বিস্তারিত লিখুন..." class="w-full p-3"><?= e($prov['about'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <div class="border-t border-purple-500/30 pt-6">
          <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
            <i class="fas fa-lock text-amber-400"></i> পাসওয়ার্ড পরিবর্তন
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">নতুন পাসওয়ার্ড</label>
              <input type="password" name="new_password" placeholder="কমপক্ষে ৬ অক্ষর" class="w-full p-3">
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">নিশ্চিত করুন</label>
              <input type="password" name="confirm_password" placeholder="আবার লিখুন" class="w-full p-3">
            </div>
          </div>
        </div>

        <div class="pt-4">
          <button type="submit" class="btn-action btn-primary w-full justify-center text-lg py-4">
            <i class="fas fa-save"></i> প্রোফাইল আপডেট করুন
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
function previewProviderPhoto(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      var img = document.getElementById('providerPhoto');
      if(img) img.src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>

</body>
</html>