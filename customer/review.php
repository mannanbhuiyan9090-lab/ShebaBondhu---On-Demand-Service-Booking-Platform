<?php
require_once __DIR__ . '/../includes/config.php';
sessionStart(); requireAuth('customer');
$user = auth();

$bookingId = (int)($_GET['booking'] ?? 0);
try {
  $stmt = db()->prepare("SELECT b.*,p.company_name,p.emoji,p.service_type FROM bookings b JOIN providers p ON p.id=b.provider_id WHERE b.id=? AND b.customer_id=? AND b.status='Completed'");
  $stmt->execute([$bookingId,$user['id']]); $booking=$stmt->fetch();
  if(!$booking){ redirect(SITE_URL.'/customer/bookings.php','বুকিং পাওয়া যায়নি অথবা রিভিউ দেওয়া সম্ভব নয়।','error'); }
  $already=db()->prepare("SELECT id FROM reviews WHERE booking_id=?"); $already->execute([$bookingId]);
  if($already->fetch()){ redirect(SITE_URL.'/customer/bookings.php','আপনি ইতিমধ্যে রিভিউ দিয়েছেন।','error'); }
} catch(Exception $e){ redirect(SITE_URL.'/customer/bookings.php','একটি ত্রুটি হয়েছে।','error'); }

$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $rating=(int)($_POST['rating']??0); $comment=trim($_POST['comment']??'');
  if($rating<1||$rating>5){ $error='১ থেকে ৫-এর মধ্যে রেটিং দিন।'; }
  else {
    try {
      db()->prepare("INSERT INTO reviews (booking_id,customer_id,provider_id,rating,comment) VALUES (?,?,?,?,?)")->execute([$bookingId,$user['id'],$booking['provider_id'],$rating,$comment]);
      db()->prepare("UPDATE providers SET rating=(SELECT ROUND(AVG(rating),2) FROM reviews WHERE provider_id=?),review_count=(SELECT COUNT(*) FROM reviews WHERE provider_id=?) WHERE id=?")->execute([$booking['provider_id'],$booking['provider_id'],$booking['provider_id']]);
      redirect(SITE_URL.'/customer/bookings.php','আপনার রিভিউর জন্য ধন্যবাদ! ⭐');
    } catch(Exception $e){ $error='রিভিউ সংরক্ষণে সমস্যা হয়েছে।'; }
  }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>রিভিউ দিন - সেবা বন্ধু</title>
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
    .gradient-text { background: linear-gradient(135deg, #10B981, #06B6D4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .btn-action { padding: 8px 16px; border-radius: 12px; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px; }
    .btn-primary { background: linear-gradient(135deg, #10B981, #059669); color: white; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4); }
    .btn-outline { background: rgba(71, 85, 105, 0.3); color: white; border: 1px solid rgba(71, 85, 105, 0.5); }
    .btn-outline:hover { background: rgba(71, 85, 105, 0.5); }
    .form-err { background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.3); color: #EF4444; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; }
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
        <?= strtoupper(mb_substr($user['name'] ?? '', 0, 2)) ?>
      </div>
      <div class="flex-1">
        <div class="font-semibold text-sm"><?= e(explode(' ', $user['name'] ?? '')[0]) ?></div>
        <div class="text-xs text-slate-500">গ্রাহক</div>
      </div>
      <i class="fas fa-right-from-bracket group-hover:rotate-180 transition-transform"></i>
    </a>
  </div>
</aside>

<button id="menuToggle" class="lg:hidden fixed top-4 left-4 z-50 p-3 bg-slate-800 rounded-xl shadow-lg">
  <i class="fas fa-bars w-5 h-5"></i>
</button>

<div class="lg:ml-64 min-h-screen flex items-center justify-center p-4">
  <div class="card w-full max-w-lg p-8">
    <div class="text-center mb-8">
      <div class="w-20 h-20 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-2xl flex items-center justify-center text-4xl mx-auto mb-4 shadow-lg">
        <?= $booking['emoji'] ?? '⭐' ?>
      </div>
      <h1 class="text-2xl font-bold mb-2">রিভিউ দিন</h1>
      <p class="text-slate-400">
        <?= e($booking['company_name'] ?? '') ?> · <?= svcLabel($booking['service_type'] ?? '') ?>
        <br><span class="text-sm text-slate-500">সেবার তারিখ: <?= !empty($booking['preferred_date']) ? date('d M Y', strtotime($booking['preferred_date'])) : 'N/A' ?></span>
      </p>
    </div>
    
    <?php if($error): ?><div class="form-err"><i class="fas fa-circle-exclamation mr-2"></i><?= e($error) ?></div><?php endif; ?>
    
    <form method="POST">
      <div class="mb-8">
        <label class="block text-center font-bold mb-4 text-lg">আপনার রেটিং *</label>
        <div style="display:flex;flex-direction:row-reverse;justify-content:center;gap:8px">
          <?php for($i=5;$i>=1;$i--): ?>
          <input type="radio" name="rating" id="s<?= $i ?>" value="<?= $i ?>" style="display:none" <?= ($_POST['rating']??0)==$i?'checked':'' ?>>
          <label for="s<?= $i ?>" style="font-size:48px;color:#D1D5DB;cursor:pointer;transition:color .1s" onmouseover="highlightStars(<?= $i ?>)" onmouseout="resetStars()" onclick="selectStar(<?= $i ?>)">★</label>
          <?php endfor; ?>
        </div>
        <div id="ratingText" class="text-center text-slate-400 mt-4 font-semibold">একটি রেটিং বেছে নিন</div>
      </div>
      
      <div class="mb-6">
        <label class="block font-bold mb-2">আপনার মন্তব্য (ঐচ্ছিক)</label>
        <textarea name="comment" class="w-full p-4 rounded-xl bg-slate-800/50 border border-slate-700/50 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none transition-all resize-none" placeholder="আপনার অভিজ্ঞতা সম্পর্কে লিখুন — কী ভালো লেগেছে, কী উন্নতি করা যেত…" rows="5"><?= e($_POST['comment']??'') ?></textarea>
      </div>
      
      <div class="flex gap-3">
        <a href="bookings.php" class="btn-action btn-outline flex-1 justify-center">
          <i class="fas fa-arrow-left"></i> ফিরুন
        </a>
        <button type="submit" class="btn-action btn-primary flex-1 justify-center">
          <i class="fas fa-paper-plane"></i> জমা দিন
        </button>
      </div>
    </form>
  </div>
</div>

<script>
const labels=['','খুব খারাপ','খারাপ','ঠিক আছে','ভালো','অসাধারণ!'];
const stars=document.querySelectorAll('label[for^="s"]');
let selected=<?= (int)($_POST['rating']??0) ?>;

function highlightStars(n){
  stars.forEach((s,i)=>{ s.style.color=i<n?'#F59E0B':'#D1D5DB'; });
  document.getElementById('ratingText').textContent=labels[n]||'';
}
function resetStars(){
  if(selected){ highlightStars(selected); document.getElementById('ratingText').textContent=labels[selected]; }
  else { stars.forEach(s=>s.style.color='#D1D5DB'); document.getElementById('ratingText').textContent='একটি রেটিং বেছে নিন'; }
}
function selectStar(n){ selected=n; highlightStars(n); }
if(selected) highlightStars(selected);

document.getElementById('menuToggle')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('-translate-x-full');
});
</script>

</body>
</html>
