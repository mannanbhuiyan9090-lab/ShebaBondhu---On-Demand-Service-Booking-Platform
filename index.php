<?php
require_once __DIR__ . '/includes/config.php';
sessionStart();

$pageTitle  = 'হোম';
$activePage = 'home';

try {
  $stats = db()->query("SELECT
    (SELECT COUNT(*) FROM users WHERE role='customer') AS customers,
    (SELECT COUNT(*) FROM providers WHERE verified=1)  AS providers,
    (SELECT COUNT(*) FROM bookings)                    AS bookings
  ")->fetch();
  $featured = db()->query("SELECT p.*, u.name AS owner_name FROM providers p JOIN users u ON u.id=p.user_id WHERE p.verified=1 ORDER BY p.rating DESC, p.review_count DESC LIMIT 4")->fetchAll();
} catch(Exception $e) {
  $stats    = ['customers'=>2400,'providers'=>180,'bookings'=>8500];
  $featured = [];
}

include __DIR__ . '/includes/header.php';
?>

<script>
document.addEventListener("DOMContentLoaded", function () {

  const elements = document.querySelectorAll(
    ".hero-stat strong, .stat-item strong"
  );

  const animated = new Set();

  function animateCount(el) {
    let originalText = el.innerText;

    // Skip non-numeric (ratings etc.)
    if (!/[0-9]/.test(originalText)) return;

    let endValue = parseInt(originalText.replace(/[^0-9]/g, ''));
    if (isNaN(endValue)) return;

    let duration = 2000;
    let startTime = null;

    function animate(timestamp) {
      if (!startTime) startTime = timestamp;

      let progress = timestamp - startTime;
      let value = Math.min(
        Math.floor((progress / duration) * endValue),
        endValue
      );

      el.innerText = value.toLocaleString() + "+";

      if (progress < duration) {
        requestAnimationFrame(animate);
      } else {
        el.innerText = endValue.toLocaleString() + "+";
      }
    }

    requestAnimationFrame(animate);
  }

  // Intersection Observer (detect when visible)
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting && !animated.has(entry.target)) {
        animated.add(entry.target);
        animateCount(entry.target);
      }
    });
  }, { threshold: 0.4 });

  elements.forEach(el => observer.observe(el));

});
</script>

<!-- ═══ HERO ═══════════════════════════════════════ -->
<section class="hero">
  <div class="hero-bg">
    <img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1600&q=80" alt="" loading="eager">
  </div>
  <div class="hero-blob1"></div>
  <div class="hero-blob2"></div>

  <div class="hero-inner">
    <!-- Left: copy -->
    <div>
      <div class="hero-pill">
        <span class="pulse-dot"></span>
        ঢাকার #১ হোম সার্ভিস প্ল্যাটফর্ম
      </div>

      <h1>
        আপনার বাড়ির যেকোনো কাজে<br>
        <mark>বিশ্বস্ত বিশেষজ্ঞ</mark> খুঁজুন
      </h1>
      <p class="hero-desc">পাইপলাইন লিক থেকে ইলেকট্রিক ফল্ট — নিকটস্থ যাচাইকৃত ও দক্ষ পেশাদার খুঁজুন। মিনিটেই বুক করুন, কাজ হোক নিখুঁতভাবে।</p>

      <!-- Search -->
      <form class="hero-search" action="services.php" method="GET">
        <div class="hs-field">
          <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          <select name="city">
            <option value="">এলাকা বেছে নিন…</option>
            <?php foreach(CITY_BN as $en=>$bn): ?>
              <option value="<?= $en ?>"><?= $bn ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="hs-field">
          <svg viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
          <select name="type">
            <option value="">সেবা বেছে নিন…</option>
            <?php foreach(SERVICE_BN as $key=>$v): ?>
              <option value="<?= $key ?>"><?= $v['emoji'].' '.$v['label'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="hs-btn">
          <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          সেবা খুঁজুন
        </button>
      </form>

      <!-- Stats -->
      <div class="hero-stats">
        <div class="hero-stat">
          <strong id="customers"><?= number_format(max($stats['customers'],2400)) ?>+</strong>
          <span>সন্তুষ্ট গ্রাহক</span>
        </div>
        <div class="hero-stat">
          <strong id="providers"><?= number_format(max($stats['providers'],180)) ?>+</strong>
          <span>যাচাইকৃত বিশেষজ্ঞ</span>
        </div>
        <div class="hero-stat"><strong>৪.৮★</strong><span>গড় রেটিং</span></div>
        <div class="hero-stat"><strong>৯টি এলাকা</strong><span>ঢাকা জুড়ে</span></div>
      </div>
    </div>

    <!-- Right: visual -->
    <div class="hero-visual">
      <div class="hero-img-wrap">
        <img src="https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=700&q=80" alt="পেশাদার সেবা" loading="eager">
        <div class="hero-img-caption">
          <div>
            <p>সেরা রেটেড বিশেষজ্ঞ</p>
            <small>ঢাকায় ১৮০+ যাচাইকৃত সেবাদাতা</small>
          </div>
          <span class="rating-chip">★ ৪.৯</span>
        </div>
      </div>

      <div class="hero-cards-row">
        <?php
        $miniCards = [
          ['🔧','পাইপলাইন','দ্রুত সমাধান','Plumbing'],
          ['⚡','ইলেকট্রিক্যাল','লাইসেন্সপ্রাপ্ত','Electricals'],
          ['🧹','পরিষ্কার','পরিবেশবান্ধব','Cleaning'],
          ['🎨','রং মিস্ত্রি','দক্ষ কারিগর','Painter'],
        ];
        foreach($miniCards as [$icon,$title,$sub,$type]): ?>
        <a class="hero-mini" href="services.php?type=<?= urlencode($type) ?>">
          <div class="hm-icon"><?= $icon ?></div>
          <div class="hm-title"><?= $title ?></div>
          <div class="hm-sub"><?= $sub ?></div>
          <div class="avail-tag">উপলব্ধ</div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ═══ HOW IT WORKS ═══════════════════════════════ -->
<section class="how-section reveal">
  <div class="container">
    <div style="text-align:center;margin-bottom:48px">
      <h2 class="section-title">কীভাবে কাজ করে?</h2>
      <p class="section-sub" style="max-width:440px;margin:8px auto 0">মাত্র ৪টি সহজ ধাপে আপনার কাজ সম্পন্ন করুন</p>
    </div>
    <div class="how-grid">
      <?php $steps=[
        ['১','🔍','সেবা খুঁজুন','আপনার এলাকা ও প্রয়োজনীয় সেবা বেছে নিন।'],
        ['২','👷','বিশেষজ্ঞ বেছে নিন','রেটিং ও রিভিউ দেখে সেরাজনকে বেছে নিন।'],
        ['৩','📅','বুকিং দিন','তারিখ ও সময় নির্ধারণ করুন — মিনিটেই হবে।'],
        ['৪','✅','কাজ সম্পন্ন','বিশেষজ্ঞ আসবেন, কাজ করবেন, রিভিউ দিন।'],
      ]; foreach($steps as [$num,$icon,$title,$desc]): ?>
      <div class="how-step">
        <div class="step-circle"><?= $num ?></div>
        <div class="step-icon"><?= $icon ?></div>
        <h3><?= $title ?></h3>
        <p><?= $desc ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══ CATEGORIES ══════════════════════════════════ -->
<section class="cats-section reveal">
  <div class="container">
    <div class="section-head">
      <div>
        <h2 class="section-title">সেবার ধরন বেছে নিন</h2>
        <p class="section-sub">৭ ধরনের পেশাদার সেবা, একটাই অ্যাপ</p>
      </div>
      <a class="section-link" href="services.php">সব সেবা দেখুন →</a>
    </div>
    <div class="cat-grid">
      <?php foreach(SERVICE_BN as $key=>$v): ?>
        <a class="cat-tile" href="services.php?type=<?= urlencode($key) ?>">
          <span class="cat-emoji"><?= $v['emoji'] ?></span>
          <span class="cat-label"><?= $v['label'] ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══ GALLERY MARQUEE ══════════════════════════════ -->
<section class="gallery-section">
  <div class="gallery-head container">
    <div class="section-head">
      <div>
        <h2 class="section-title">আমাদের কাজের নমুনা</h2>
        <p class="section-sub">বাস্তব প্রজেক্ট থেকে ছবি</p>
      </div>
    </div>
  </div>
  <div class="gallery-track">
    <?php
    $galleryItems=[
      ['https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=500&q=80','পাইপলাইন মেরামত'],
      ['https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=500&q=80','ইলেকট্রিক কাজ'],
      ['https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=500&q=80','পরিষ্কার সেবা'],
      ['https://images.unsplash.com/photo-1504148455328-c376907d081c?w=500&q=80','কাঠমিস্ত্রি'],
      ['https://images.unsplash.com/photo-1562259929-b4e1fd3aef09?w=500&q=80','রং মিস্ত্রি'],
      ['https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=500&q=80','পেস্ট কন্ট্রোল'],
      ['https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=500&q=80','মেকানিক সেবা'],
      ['https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=500&q=80','পাইপলাইন মেরামত'],
      ['https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=500&q=80','ইলেকট্রিক কাজ'],
      ['https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=500&q=80','পরিষ্কার সেবা'],
      ['https://images.unsplash.com/photo-1504148455328-c376907d081c?w=500&q=80','কাঠমিস্ত্রি'],
      ['https://images.unsplash.com/photo-1562259929-b4e1fd3aef09?w=500&q=80','রং মিস্ত্রি'],
      ['https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=500&q=80','পেস্ট কন্ট্রোল'],
      ['https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=500&q=80','মেকানিক সেবা'],
    ];
    foreach($galleryItems as [$img,$lbl]): ?>
    <div class="g-item">
      <img src="<?= $img ?>" alt="<?= $lbl ?>" loading="lazy">
      <div class="g-label"><?= $lbl ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ═══ STATS ════════════════════════════════════════ -->
<section class="stats-section reveal">
  <div class="stats-grid">
    <?php $statItems=[
      ['🏠',number_format(max($stats['customers'],2400)).'+'  ,'সন্তুষ্ট গ্রাহক'],
      ['👷',number_format(max($stats['providers'],180)).'+' ,'যাচাইকৃত বিশেষজ্ঞ'],
      ['✅',number_format(max($stats['bookings'],8500)).'+' ,'সম্পন্ন বুকিং'],
      ['⭐','৪.৮/৫','গড় রেটিং'],
    ]; foreach($statItems as [$em,$val,$lbl]): ?>
    <div class="stat-item">
      <span class="stat-emoji"><?= $em ?></span>
      <strong><?= $val ?></strong>
      <span><?= $lbl ?></span>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ═══ WHY HANDYGO ══════════════════════════════════ -->
<section class="why-section reveal">
  <div class="container">
    <div style="text-align:center;margin-bottom:48px">
      <h2 class="section-title" style="color:white">কেন সেবাবন্ধু-কে বেছে নেবেন?</h2>
      <p style="color:rgba(255,255,255,.65);font-size:14px;margin-top:8px">বিশ্বাস ও গুণমানের উপর ভিত্তি করে তৈরি</p>
    </div>
    <div class="why-grid">
      <?php $whys=[
        ['✅','যাচাইকৃত পেশাদার','প্রতিটি সেবাদাতার ব্যাকগ্রাউন্ড চেক ও দক্ষতা যাচাই করা হয় প্ল্যাটফর্মে যোগ দেওয়ার আগেই।'],
        ['⚡','মিনিটেই বুক করুন','খুঁজুন, তুলনা করুন এবং বিশ্বস্ত সেবা মাত্র ২ মিনিটে বুক করুন — যেকোনো ডিভাইস থেকে।'],
        ['🛡️','১০০% সন্তুষ্টি গ্যারান্টি','সন্তুষ্ট না হলে আমরা সমাধান করব। আমাদের সাপোর্ট টিম সপ্তাহে ৭ দিন সক্রিয়।'],
        ['💰','স্বচ্ছ মূল্য নির্ধারণ','আগেই মূল্য দেখুন। কোনো লুকানো চার্জ নেই, কোনো চমক নেই।'],
      ]; foreach($whys as [$icon,$title,$desc]): ?>
      <div class="why-card">
        <div class="why-icon"><?= $icon ?></div>
        <h3><?= $title ?></h3>
        <p><?= $desc ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══ REVIEWS ══════════════════════════════════════ -->
<section class="reviews-section reveal">
  <div class="container">
    <div style="text-align:center;margin-bottom:40px">
      <h2 class="section-title">গ্রাহকরা কী বলছেন</h2>
      <p class="section-sub" style="margin-top:8px">আমাদের সন্তুষ্ট গ্রাহকদের সত্যিকারের অভিজ্ঞতা</p>
    </div>
    <div class="review-grid">
      <?php $reviews=[
['https://i.pravatar.cc/100?img=12','রাহুল ইসলাম','ধানমন্ডি','★★★★★','HandyGo থেকে ইলেকট্রিশিয়ান বুক করেছিলাম। খুব দ্রুত এসে সব ঠিক করে দিলেন। দাম একদম সঠিক ছিল।'],
['https://i.pravatar.cc/100?img=36','সুমাইয়া আক্তার','মিরপুর','★★★★★','পরিষ্কার সেবা নিয়েছিলাম। ঘর একেবারে চকচকে হয়ে গেল। অসাধারণ সেবা পেয়েছি!'],
['https://i.pravatar.cc/100?img=47','নাফিসা বেগম','বনানী','★★★★★','কাঠমিস্ত্রি সেবা অসাধারণ ছিল। খুব সুন্দর কাজ করেছেন।'],
['https://i.pravatar.cc/100?img=8','তানভীর আহমেদ','বসুন্ধরা','★★★★★','রং মিস্ত্রির কাজ খুব সুন্দর হয়েছে। HandyGo-র মাধ্যমে বুকিং দেওয়া অনেক সহজ।'],
['https://i.pravatar.cc/100?img=20','মৌসুমি রহমান','মোহাম্মদপুর','★★★★★','পেস্ট কন্ট্রোল সেবা নিয়েছিলাম। বাসার সব পোকামাকড় একদম চলে গেছে। চমৎকার!'],
['https://i.pravatar.cc/100?img=52','মেহেদী হাসান','উত্তরা','★★★★★','HandyGo এর সার্ভিস খুবই ভালো লেগেছে। নির্দিষ্ট সময়ে এসে কাজ শেষ করেছে এবং আচরণও ছিল খুবই ভদ্র।'],
]; foreach($reviews as [$img,$name,$area,$stars,$text]): ?>
      <div class="review-card">
        <div class="rv-header">
          <div class="rv-avatar"><img src="<?= $img ?>" alt="<?= $name ?>"></div>
          <div>
            <div class="rv-name"><?= $name ?></div>
            <div class="rv-area">📍 <?= $area ?>, ঢাকা</div>
          </div>
        </div>
        <div class="rv-stars"><?= $stars ?></div>
        <p class="rv-text">"<?= $text ?>"</p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══ CTA ══════════════════════════════════════════ -->
<section class="cta-section reveal">
  <h2>আপনি কি একজন পেশাদার সেবাদাতা?</h2>
  <p>HandyGo-তে যোগ দিন এবং ঢাকার হাজার হাজার গ্রাহকের কাছে পৌঁছান। বিনামূল্যে নিবন্ধন, তাৎক্ষণিক বুকিং।</p>
  <div class="cta-btns">
    <a class="btn btn-white btn-lg" href="register-provider.php">সেবা নিবন্ধন করুন →</a>
    <a class="btn btn-outline btn-lg" href="about.php" style="border-color:rgba(255,255,255,.5);color:white">আরও জানুন</a>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>