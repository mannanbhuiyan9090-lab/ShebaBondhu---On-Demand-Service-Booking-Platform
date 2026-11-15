<?php
require_once __DIR__ . '/../includes/config.php';
sessionStart();
requireAuth('customer');
$user = auth();

$full = db()->prepare("SELECT * FROM users WHERE id = ?");
$full->execute([$user['id']]);
$full = $full->fetch();
$error = $done = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  
  if (!$name) {
    $error = 'নাম আবশ্যক।';
  } else {
    $update = "UPDATE users SET name = ?, phone = ?";
    $params = [$name, $phone];
    
    if (!empty($_FILES['photo']['name'])) {
      $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
      if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
        $uploadDir = dirname(__DIR__).'/uploads';
        if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $photoPath = 'uploads/'.$user['id'].'_'.time().'.'.$ext;
        if(move_uploaded_file($_FILES['photo']['tmp_name'], dirname(__DIR__).'/'.$photoPath)) {
          $update .= ", photo = ?";
          $params[] = $photoPath;
        }
      }
    }
    
    $update .= " WHERE id = ?";
    $params[] = $user['id'];
    
    db()->prepare($update)->execute($params);
    $_SESSION['user']['name'] = $name;
    
    if (!empty($_POST['new_password'])) {
      if (strlen($_POST['new_password']) < 6) {
        $error = 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষর।';
      } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
        $error = 'পাসওয়ার্ড মিলছে না।';
      } else {
        db()->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([password_hash($_POST['new_password'], PASSWORD_DEFAULT), $user['id']]);
      }
    }
    
    if (!$error) {
      $_SESSION['profile_done'] = 'প্রোফাইল আপডেট হয়েছে।';
      header("Location: profile.php");
      exit;
    }
  }
}

$full = db()->prepare("SELECT * FROM users WHERE id = ?");
$full->execute([$user['id']]);
$full = $full->fetch();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>প্রোফাইল - সেবা বন্ধু</title>
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
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
    <a href="bookings.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-calendar-check w-5"></i>
      <span>আমার বুকিং</span>
    </a>
    <a href="profile.php" class="sidebar-link active flex items-center gap-3 p-3 rounded-xl text-white">
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
          <i class="fas fa-user mr-2 text-emerald-400"></i> প্রোফাইল সম্পাদনা
        </h1>
      </div>
      <div class="md:hidden flex items-center gap-2">
        <span class="text-lg font-bold gradient-text">সেবা বন্ধু</span>
      </div>
    </div>
  </header>

  <main class="p-4 md:p-8 max-w-2xl mx-auto">
    <?php if (isset($_SESSION['profile_done'])): ?>
      <div class="mb-6 p-4 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
        ✅ <?= e($_SESSION['profile_done']) ?>
      </div>
      <?php unset($_SESSION['profile_done']); ?>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="mb-6 p-4 rounded-xl bg-red-500/20 text-red-400 border border-red-500/30">
        ❌ <?= e($error) ?>
      </div>
    <?php endif; ?>

    <div class="card p-8">
      <div class="flex items-center gap-4 mb-8">
        <?php 
        $photoPath = $full['photo'] ?? '';
        if ($photoPath) {
          $imgSrc = SITE_URL.'/'.$photoPath;
        } else {
          $imgSrc = 'https://ui-avatars.com/api/?name='.urlencode($full['name']).'&background=6366f1&color=fff&size=128';
        }
        ?>
        <img id="profilePhoto" src="<?= e($imgSrc) ?>" alt="<?= e($full['name']) ?>" class="w-24 h-24 rounded-full object-cover border-4 border-slate-700">
        <div>
          <h2 class="text-2xl font-bold"><?= e($full['name']) ?></h2>
          <p class="text-slate-500 flex items-center gap-2">
            <i class="fas fa-user-tag"></i> গ্রাহক
          </p>
          <label class="bg-emerald-500/20 text-emerald-400 px-4 py-2 rounded-full cursor-pointer hover:bg-emerald-600 transition flex items-center gap-2 mt-2">
            <i class="fas fa-camera"></i> ছবি আপলোড করুন
            <input type="file" name="photo" accept="image/*" class="hidden" onchange="previewUpload(this)">
          </label>
        </div>
      </div>

      <form method="POST" enctype="multipart/form-data" class="space-y-6" id="profileForm">
        <div>
          <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
            <i class="fas fa-user text-emerald-400"></i> ব্যক্তিগত তথ্য
          </h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">পূর্ণ নাম *</label>
              <input type="text" name="name" required value="<?= e($full['name']) ?>" class="w-full p-3">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-slate-300 mb-2">ইমেইল</label>
                <input type="email" value="<?= e($full['email']) ?>" disabled class="w-full p-3 opacity-60 cursor-not-allowed">
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-300 mb-2">মোবাইল</label>
                <input type="tel" name="phone" value="<?= e($full['phone'] ?? '') ?>" placeholder="017XXXXXXXX" class="w-full p-3">
              </div>
            </div>
          </div>
        </div>

        <div class="border-t border-slate-700/50 pt-6">
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
            <i class="fas fa-save"></i> প্রোফাইল সংরক্ষণ করুন
          </button>
        </div>
      </form>
    </div>

    <?php
    $myBookings = db()->prepare("SELECT b.*, p.company_name, p.emoji FROM bookings b JOIN providers p ON p.id = b.provider_id WHERE b.customer_id = ? ORDER BY b.created_at DESC LIMIT 5");
    $myBookings->execute([$user['id']]);
    $myBookings = $myBookings->fetchAll();
    ?>
    <?php if ($myBookings): ?>
    <div class="card p-6 mt-8">
      <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
        <i class="fas fa-history text-purple-400"></i> সাম্প্রতিক বুকিং
      </h3>
      <div class="space-y-3">
        <?php foreach ($myBookings as $b): ?>
        <div class="p-4 bg-slate-800/50 rounded-xl flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="text-2xl"><?= $b['emoji'] ?></div>
            <div>
              <div class="font-bold"><?= e($b['company_name']) ?></div>
              <div class="text-sm text-slate-500"><?= date('d M Y', strtotime($b['preferred_date'])) ?> · <?= e($b['ref_code']) ?></div>
            </div>
          </div>
          <div class="text-right">
            <?php
            $statusClass = ['Pending' => 'bg-amber-500/20 text-amber-400', 'Approved' => 'bg-blue-500/20 text-blue-400', 'Completed' => 'bg-emerald-500/20 text-emerald-400', 'Cancelled' => 'bg-red-500/20 text-red-400'];
            $statusLabel = ['Pending' => '⏳ অপেক্ষমান', 'Approved' => '✓ অ��ুমোদিত', 'Completed' => '✓ সম্পন্ন', 'Cancelled' => '✕ বাতিল'];
            ?>
            <span class="px-3 py-1 rounded-lg text-xs font-bold <?= $statusClass[$b['status']] ?? 'bg-slate-500/20' ?>"><?= $statusLabel[$b['status']] ?? $b['status'] ?></span>
            <?php if ($b['payment_method'] === 'cash_on_delivery' && $b['status'] === 'Completed'): ?>
              <div class="mt-2 p-2 bg-emerald-500/20 rounded-lg">
                <div class="text-xs text-emerald-400">পেমেন্ট কোড</div>
                <div class="font-mono font-bold text-white"><?= e($b['payment_code'] ?? '') ?></div>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </main>
</div>

<script>
document.getElementById('menuToggle')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('-translate-x-full');
});
function previewUpload(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('profilePhoto').src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>

</body>
</html>