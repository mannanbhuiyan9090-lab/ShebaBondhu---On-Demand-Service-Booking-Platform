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
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>উপলব্ধতা - সেবা বন্ধু প্রোভাইডার</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Hind Siliguri', sans-serif; }
  </style>
</head>
<body class="bg-gradient-to-br from-purple-900 via-slate-900 to-teal-900 text-white min-h-screen flex items-center justify-center p-4">
  <div class="w-full max-w-md">
    <div class="bg-white/10 backdrop-blur-xl rounded-3xl border border-purple-500/30 p-8 text-center">
      <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center <?= $provider['available'] ? 'bg-emerald-500/20' : 'bg-red-500/20' ?>">
        <i class="fas fa-toggle-on text-4xl <?= $provider['available'] ? 'text-emerald-400' : 'text-red-400' ?>"></i>
      </div>
      
      <h2 class="text-2xl font-bold mb-2">উপলব্ধতা</h2>
      <p class="text-slate-400 mb-6">আপনার বর্তমান অবস্থা</p>
      
      <div class="text-3xl font-bold mb-8 <?= $provider['available'] ? 'text-emerald-400' : 'text-red-400' ?>">
        <?= $provider['available'] ? '● উপলব্ধ আছি' : '● অনুপলব্ধ আছি' ?>
      </div>
      
      <div class="p-4 bg-slate-800/50 rounded-xl mb-6 text-left">
        <p class="text-sm text-slate-400 mb-2">গ্রাহকরা আপনাকে দেখতে পাবেন:</p>
        <p class="font-bold <?= $provider['available'] ? 'text-emerald-400' : 'text-red-400' ?>">
          <?= $provider['available'] ? '✓ হ্যাঁ, সবাই দেখতে পাবে' : '✕ না, কেউ দেখতে পাবে না' ?>
        </p>
      </div>
      
      <a href="toggle-availability.php" class="block w-full py-4 rounded-xl font-bold text-center <?= $provider['available'] ? 'bg-red-500/20 text-red-400 hover:bg-red-500/30' : 'bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30' ?> transition-all">
        <i class="fas fa-toggle-on mr-2"></i>
        <?= $provider['available'] ? 'অনুপলব্ধ করুন' : 'উপলব্ধ করুন' ?>
      </a>
      
      <a href="dashboard.php" class="block mt-4 py-4 rounded-xl bg-slate-700/50 text-slate-300 hover:bg-slate-600/50 text-center transition-all">
        <i class="fas fa-arrow-left mr-2"></i> ড্যাশবোর্ডে ফিরে যান
      </a>
    </div>
  </div>
</body>
</html>