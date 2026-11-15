<?php
require_once __DIR__ . '/includes/config.php';
http_response_code(404);
$pageTitle='পৃষ্ঠা পাওয়া যায়নি'; $activePage='';
include __DIR__ . '/includes/header.php';
?>
<div style="min-height:70vh;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:60px 5vw">
  <div style="font-size:96px;margin-bottom:20px">🔍</div>
  <h1 style="font-family:var(--ff-h);font-size:3rem;font-weight:900;color:var(--ink);margin-bottom:12px">৪০৪</h1>
  <h2 style="font-family:var(--ff-h);font-size:1.5rem;font-weight:800;margin-bottom:12px">পৃষ্ঠা পাওয়া যায়নি</h2>
  <p style="color:var(--ink-3);font-size:15px;margin-bottom:32px;max-width:400px">আপনি যে পৃষ্ঠাটি খুঁজছেন সেটি সরানো হয়েছে বা আর পাওয়া যাচ্ছে না।</p>
  <div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:center">
    <a href="<?= SITE_URL ?>/index.php" class="btn btn-green btn-lg">হোম পেজে যান</a>
    <a href="<?= SITE_URL ?>/services.php" class="btn btn-outline btn-lg">সেবা দেখুন</a>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
