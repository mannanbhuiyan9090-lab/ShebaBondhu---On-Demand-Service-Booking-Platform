<?php
require_once __DIR__ . '/includes/config.php';
sessionStart();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: services.php'); exit; }

try {
  $stmt = db()->prepare("SELECT p.*,u.name AS owner_name,u.email AS owner_email,u.phone AS owner_phone FROM providers p JOIN users u ON u.id=p.user_id WHERE p.id=?");
  $stmt->execute([$id]); $p = $stmt->fetch();
  if (!$p) { header('Location: services.php'); exit; }

  $reviews = db()->prepare("SELECT r.*,u.name AS customer_name,b.preferred_date FROM reviews r JOIN users u ON u.id=r.customer_id JOIN bookings b ON b.id=r.booking_id WHERE r.provider_id=? ORDER BY r.created_at DESC LIMIT 10");
  $reviews->execute([$id]); $reviews = $reviews->fetchAll();
} catch(Exception $e) { header('Location: services.php'); exit; }

$bookingError = ''; $bookingSuccess = false; $bookingRef = '';

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['book'])) {
  $user = auth();
  if (!$user || $user['role']!=='customer') { header('Location: login.php?redirect='.urlencode("provider.php?id=$id")); exit; }
  $date  = $_POST['preferred_date'] ?? '';
  $tS    = $_POST['time_start']     ?? '';
  $tE    = $_POST['time_end']       ?? '';
  $addr  = trim($_POST['address']   ?? '');
  $desc  = trim($_POST['description'] ?? '');
  if (!$date||!$tS||!$tE||!$addr) { $bookingError = 'সকল প্রয়োজনীয় ক্ষেত্র পূরণ করুন।'; }
  elseif ($date < date('Y-m-d'))   { $bookingError = 'ভবিষ্যতের তারিখ বেছে নিন।'; }
  else {
    try {
      $ref = generateRef();
      db()->prepare("INSERT INTO bookings (ref_code,customer_id,provider_id,service_type,preferred_date,time_start,time_end,address,description,status,payment_status) VALUES (?,?,?,?,?,?,?,?,?,'Pending','unpaid')")->execute([$ref,$user['id'],$id,$p['service_type'],$date,$tS,$tE,$addr,$desc]);
      $bookingSuccess = true; $bookingRef = $ref;
    } catch(Exception $e) { $bookingError = 'বুকিং সংরক্ষণে সমস্যা হয়েছে। আবার চেষ্টা করুন।'; }
  }
}

$pageTitle  = $p['company_name'];
$activePage = 'services';
include __DIR__ . '/includes/header.php';
?>

<!-- Provider hero -->
<div class="prov-detail-hero">
  <div class="container">
    <a href="services.php?type=<?= urlencode($p['service_type']) ?>" style="display:inline-flex;align-items:center;gap:6px;font-size:14px;color:rgba(255,255,255,.7);margin-bottom:20px;font-weight:600">← <?= svcLabel($p['service_type']) ?> সেবায় ফিরুন</a>
    <div class="prov-detail-card">
      <div class="prov-detail-emo">
        <img src="<?= svcImg($p['service_type']) ?>" alt="<?= e($p['company_name']) ?>">
      </div>
      <div>
        <div class="detail-svc-tag"><?= svcEmoji($p['service_type']).' '.svcLabel($p['service_type']) ?></div>
        <div class="detail-name"><?= e($p['company_name']) ?></div>
        <div class="detail-meta-row">
          <span>📍 <?= cityBn($p['city']) ?></span>
          <span><?= starsHtml($p['rating']) ?> <?= $p['rating'] ?> (<?= $p['review_count'] ?> রিভিউ)</span>
          <span>🕐 <?= substr($p['work_start'],0,5) ?>–<?= substr($p['work_end'],0,5) ?></span>
          <span style="color:<?= $p['available']?'var(--green)':'var(--danger)' ?>;font-weight:800"><?= $p['available']?'● উপলব্ধ':'● অনুপলব্ধ' ?></span>
          <?php if($p['verified']): ?><span style="color:var(--green);font-weight:800">✓ যাচাইকৃত</span><?php endif; ?>
        </div>
        <?php if($p['about']): ?>
          <p style="margin-top:14px;font-size:14px;color:var(--ink-2);line-height:1.8;max-width:640px"><?= e($p['about']) ?></p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="container" style="padding-top:32px;padding-bottom:80px">
  <div style="display:grid;grid-template-columns:1fr 380px;gap:28px;align-items:start" id="book-section">

    <!-- Reviews -->
    <div>
      <h2 style="font-family:var(--ff-h);font-size:1.4rem;font-weight:800;margin-bottom:24px;color:var(--ink)">
        গ্রাহক রিভিউ <span style="font-family:var(--ff);font-size:14px;color:var(--ink-3);font-weight:500">(<?= count($reviews) ?> টি)</span>
      </h2>
      <?php if(empty($reviews)): ?>
        <div class="empty" style="border-radius:var(--r-lg)">
          <div class="empty-icon">⭐</div>
          <h3>এখনো কোনো রিভিউ নেই</h3>
          <p>প্রথম বুক করুন এবং রিভিউ দিন!</p>
        </div>
      <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:16px">
          <?php foreach($reviews as $r): ?>
          <div class="content-card" style="margin-bottom:0">
            <div class="rv-header" style="margin-bottom:10px">
              <div class="rv-avatar"><?= strtoupper(substr($r['customer_name'],0,2)) ?></div>
              <div>
                <div class="rv-name"><?= e($r['customer_name']) ?></div>
                <div class="rv-area"><?= date('d M Y', strtotime($r['created_at'])) ?></div>
              </div>
              <div style="margin-left:auto"><?= starsHtml($r['rating']) ?></div>
            </div>
            <?php if($r['comment']): ?><p class="rv-text" style="font-style:normal">"<?= e($r['comment']) ?>"</p><?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Booking panel -->
    <div>
      <div class="booking-panel">
        <?php if($bookingSuccess): ?>
          <div class="booking-success">
            <div style="font-size:56px;margin-bottom:12px">🎉</div>
            <h2 style="font-family:var(--ff-h);font-size:1.4rem;font-weight:900;margin-bottom:8px">বুকিং নিশ্চিত!</h2>
            <p style="font-size:14px;color:var(--ink-2);line-height:1.8;margin-bottom:16px"><strong><?= e($p['company_name']) ?></strong>-এ আপনার বুকিং জমা হয়েছে। শীঘ্রই নিশ্চিত করা হবে।</p>
            <div class="book-ref"><?= $bookingRef ?></div>
            <a class="btn btn-green btn-block btn-lg" href="customer/bookings.php">আমার বুকিং দেখুন →</a>
          </div>
        <?php else: ?>
          <h2 style="font-family:var(--ff-h);font-size:1.2rem;font-weight:900;margin-bottom:4px">এই সেবা বুক করুন</h2>
          <p style="font-size:13px;color:var(--ink-3);margin-bottom:22px">মূল্য: ৳ <?= number_format($p['price_min'] ?? 500) ?> - ৳ <?= number_format($p['price_max'] ?? (int)$p['price_range'] ?? 2000) ?> · <?= cityBn($p['city']) ?></p>
          <?php if($bookingError): ?><div class="form-err">⚠ <?= e($bookingError) ?></div><?php endif; ?>
          <?php $authUser = auth(); if(!$authUser || $authUser['role']!=='customer'): ?>
            <div style="text-align:center;padding:24px;background:var(--surface-2);border-radius:var(--r);margin-bottom:20px">
              <p style="font-size:14px;color:var(--ink-2);margin-bottom:14px">বুকিং দিতে গ্রাহক হিসেবে লগইন করুন।</p>
              <a class="btn btn-green btn-block" href="login.php?redirect=<?= urlencode("provider.php?id=$id") ?>">লগইন করুন →</a>
              <p style="font-size:12px;color:var(--ink-3);margin-top:10px">অ্যাকাউন্ট নেই? <a href="signup.php" style="color:var(--green);font-weight:700">বিনামূল্যে তৈরি করুন</a></p>
            </div>
          <?php else: ?>
          <form method="POST">
            <input type="hidden" name="book" value="1">
            <div class="form-group">
              <label>পছন্দের তারিখ *</label>
              <input type="date" name="preferred_date" min="<?= date('Y-m-d') ?>" required value="<?= e($_POST['preferred_date']??'') ?>">
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>শুরুর সময় *</label>
                <input type="time" name="time_start" value="<?= e($_POST['time_start']??substr($p['work_start'],0,5)) ?>" required>
              </div>
              <div class="form-group">
                <label>শেষের সময় *</label>
                <input type="time" name="time_end" value="<?= e($_POST['time_end']??substr($p['work_end'],0,5)) ?>" required>
              </div>
            </div>
            <div class="form-group">
              <label>আপনার ঠিকানা *</label>
              <input type="text" name="address" placeholder="ফ্ল্যাট নং, রোড, এলাকা…" required value="<?= e($_POST['address']??'') ?>">
            </div>
            <div class="form-group">
              <label>সমস্যার বিবরণ</label>
              <textarea name="description" placeholder="কী ধরনের কাজ দরকার তা বিস্তারিত লিখুন…" maxlength="400"><?= e($_POST['description']??'') ?></textarea>
            </div>
            <button type="submit" class="form-submit">বুকিং নিশ্চিত করুন →</button>
          </form>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<style>
@media(max-width:768px){
  #book-section{display:block!important}
  #book-section>div:last-child{margin-top:28px}
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
