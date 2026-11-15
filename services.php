<?php
require_once __DIR__ . '/includes/config.php';
sessionStart();
$pageTitle  = 'সেবাসমূহ';
$activePage = 'services';

include __DIR__ . '/includes/header.php';

$type = trim($_GET['type'] ?? '');
$city = trim($_GET['city'] ?? '');
$sort = $_GET['sort'] ?? 'rating';
$search = trim($_GET['q'] ?? '');

try {
  $where = ['p.verified = 1']; $params = [];
  if ($type) { $where[] = 'p.service_type = :type'; $params[':type'] = $type; }
  if ($city) { $where[] = 'p.city = :city'; $params[':city'] = $city; }
  if ($search) {
    $where[] = '(p.company_name LIKE :search1 OR p.description LIKE :search2 OR p.service_type LIKE :search3)';
    $params[':search1'] = '%' . $search . '%';
    $params[':search2'] = '%' . $search . '%';
    $params[':search3'] = '%' . $search . '%';
  }
  $orderBy = match($sort) { 'name'=>'p.company_name ASC','reviews'=>'p.review_count DESC',default=>'p.rating DESC,p.review_count DESC' };
  $stmt = db()->prepare("SELECT p.*,u.name AS owner_name FROM providers p JOIN users u ON u.id=p.user_id WHERE ".implode(' AND ',$where)." ORDER BY $orderBy");
  $stmt->execute($params); $providers = $stmt->fetchAll();
} catch(Exception $e) { $providers = []; }

try {
  $svcStmt = db()->query("SELECT * FROM service_categories WHERE active = 1 ORDER BY sort_order ASC, id ASC");
  $serviceCategories = $svcStmt->fetchAll();
} catch(Exception $e) { $serviceCategories = []; }
?>

<style>
:root {
  --bg-primary: #030712;
  --bg-secondary: #0a0a0f;
  --bg-card: #111118;
  --bg-card-hover: #1a1a24;
  --border: #1f1f2e;
  --border-hover: #2d2d42;
  --text-primary: #ffffff;
  --text-secondary: #a1a1aa;
  --text-tertiary: #71717a;
  --accent-primary: #6366f1;
  --accent-secondary: #8b5cf6;
  --accent-tertiary: #d946ef;
  --accent-gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
  --ff: 'Hind Siliguri', -apple-system, BlinkMacSystemFont, sans-serif;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
  font-family: var(--ff);
  background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 50%, #16213e 100%);
  color: var(--text-primary);
  line-height: 1.6;
  overflow-x: hidden;
}

.container { max-width: 1400px; margin: 0 auto; padding: 0 5%; }

/* Hero */
.svc-hero {
  position: relative;
  padding: 120px 5% 60px;
  text-align: center;
  overflow: hidden;
  background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 50%, #16213e 100%);
}

.svc-hero-bg {
  position: absolute;
  inset: 0;
  background: 
    radial-gradient(ellipse at 20% 80%, rgba(131, 56, 236, 0.12) 0%, transparent 50%),
    radial-gradient(ellipse at 80% 20%, rgba(255, 0, 110, 0.12) 0%, transparent 50%);
}

.hero-glow {
  position: absolute;
  width: 400px;
  height: 400px;
  border-radius: 50%;
  filter: blur(80px);
}

.hero-glow-1 {
  background: linear-gradient(135deg, #ff006e, #ff4d94);
  top: -100px;
  right: -100px;
  opacity: 0.4;
  animation: glow-pulse 4s ease-in-out infinite;
}

.hero-glow-2 {
  background: linear-gradient(135deg, #8338ec, #a855f7);
  bottom: -100px;
  left: -100px;
  opacity: 0.4;
  animation: glow-pulse 4s ease-in-out infinite 2s;
}

@keyframes glow-pulse {
  0%, 100% { opacity: 0.4; transform: scale(1); }
  50% { opacity: 0.7; transform: scale(1.1); }
}

.svc-hero h1 {
  position: relative;
  font-size: clamp(2rem, 4vw, 2.8rem);
  font-weight: 900;
  background: linear-gradient(135deg, #ff006e, #8338ec, #00b4d8);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 12px;
}

.svc-hero p {
  position: relative;
  color: var(--text-secondary);
  font-size: 1.1rem;
}

/* Filter */
.filter-section {
  background: rgba(255,255,255,0.03);
  border-bottom: 1px solid rgba(255,255,255,0.08);
  padding: 20px 5%;
  position: sticky;
  top: 72px;
  z-index: 100;
}

.filter-container {
  max-width: 1400px;
  margin: 0 auto;
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  align-items: center;
}

.filter-search {
  flex: 1;
  min-width: 250px;
  position: relative;
}

.filter-search input {
  width: 100%;
  padding: 14px 20px 14px 48px;
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: 14px;
  color: var(--text-primary);
  font-family: var(--ff);
  font-size: 15px;
  outline: none;
}

.filter-search input::placeholder { color: var(--text-tertiary); }
.filter-search input:focus {
  border-color: var(--accent-primary);
  background: rgba(255,255,255,0.12);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

.filter-search i {
  position: absolute;
  left: 18px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-tertiary);
}

.filter-select {
  padding: 14px 20px;
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: 14px;
  color: var(--text-primary);
  font-family: var(--ff);
  font-size: 15px;
  cursor: pointer;
  min-width: 160px;
}

.filter-select option { background: #1a1a2e; color: white; }

.filter-reset {
  padding: 14px 24px;
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: 14px;
  color: var(--text-secondary);
  font-family: var(--ff);
  font-size: 15px;
  font-weight: 600;
  text-decoration: none;
}

.filter-reset:hover {
  background: rgba(255,255,255,0.15);
  color: white;
}

/* Categories */
.category-section {
  max-width: 1400px;
  margin: 0 auto;
  padding: 40px 5%;
}

.cat-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  justify-content: center;
}

.cat-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 22px;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.12);
  border-radius: 100px;
  color: var(--text-secondary);
  font-size: 14px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s;
}

.cat-pill:hover, .cat-pill.active {
  background: var(--accent-gradient);
  border-color: transparent;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
}

/* Cards Grid */
.cards-section {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 5% 80px;
}

.results-info {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
  padding: 16px 24px;
  background: rgba(255,255,255,0.05);
  border-radius: 16px;
  border: 1px solid rgba(255,255,255,0.08);
}

.results-count { color: var(--text-secondary); font-size: 15px; font-weight: 600; }
.results-count span { color: #a78bfa; font-weight: 800; }

.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
  gap: 24px;
}

/* Provider Card */
.pro-card {
  position: relative;
  background: linear-gradient(145deg, rgba(255,255,255,0.05), rgba(255,255,255,0.02));
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 24px;
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.pro-card:hover {
  transform: translateY(-8px);
  border-color: var(--accent-primary);
  box-shadow: 0 20px 50px rgba(99, 102, 241, 0.2);
}

.pro-card-img {
  position: relative;
  height: 200px;
  overflow: hidden;
}

.pro-card-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s;
}

.pro-card:hover .pro-card-img img { transform: scale(1.08); }

.pro-card-badges {
  position: absolute;
  top: 16px;
  left: 16px;
  right: 16px;
  display: flex;
  justify-content: space-between;
  z-index: 2;
}

.pro-card-badge {
  padding: 8px 14px;
  border-radius: 100px;
  font-size: 12px;
  font-weight: 700;
  backdrop-filter: blur(10px);
}

.pro-card-badge.verified {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
}

.pro-card-badge.available {
  background: rgba(16, 185, 129, 0.9);
  color: white;
}

.pro-card-badge.unavailable {
  background: rgba(239, 68, 68, 0.9);
  color: white;
}

.pro-card-body { padding: 24px; }

.pro-card-service {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  background: rgba(99, 102, 241, 0.15);
  border-radius: 100px;
  font-size: 12px;
  font-weight: 700;
  color: #a78bfa;
  margin-bottom: 12px;
}

.pro-card-title {
  font-size: 1.25rem;
  font-weight: 800;
  color: var(--text-primary);
  margin-bottom: 16px;
  line-height: 1.3;
}

.pro-card-meta {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 20px;
}

.pro-card-meta-item {
  display: flex;
  align-items: center;
  gap: 12px;
  color: var(--text-secondary);
  font-size: 14px;
}

.pro-card-meta-item i {
  width: 36px;
  height: 36px;
  background: rgba(255,255,255,0.05);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--accent-secondary);
  font-size: 14px;
}

.pro-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 16px;
  border-top: 1px solid rgba(255,255,255,0.1);
}

.pro-card-price {
  display: flex;
  flex-direction: column;
}

.pro-card-price-label { font-size: 12px; color: var(--text-tertiary); }

.pro-card-price-value {
  font-size: 18px;
  font-weight: 800;
  background: var(--accent-gradient);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.pro-card-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 14px 24px;
  background: var(--accent-gradient);
  border-radius: 14px;
  color: white;
  font-size: 14px;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.3s;
}

.pro-card-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
}

/* Details Panel */
.pro-card-details {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(180deg, rgba(17,17,24,0.95), rgba(17,17,24,1));
  backdrop-filter: blur(20px);
  border-top: 1px solid rgba(255,255,255,0.1);
  padding: 0;
  max-height: 0;
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.pro-card:hover .pro-card-details {
  max-height: 300px;
  padding: 24px;
}

.pro-card-details-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  margin-bottom: 20px;
}

.pro-card-detail-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px;
  background: rgba(255,255,255,0.05);
  border-radius: 12px;
}

.pro-card-detail-item i {
  width: 40px;
  height: 40px;
  background: rgba(99, 102, 241, 0.2);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #a78bfa;
  font-size: 16px;
}

.pro-card-detail-label { font-size: 12px; color: var(--text-tertiary); }

.pro-card-detail-value {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-primary);
}

.pro-card-details-actions { display: flex; gap: 12px; }

.pro-card-details-btn {
  flex: 1;
  padding: 14px 20px;
  border-radius: 14px;
  font-size: 14px;
  font-weight: 700;
  text-align: center;
  text-decoration: none;
  transition: all 0.3s;
}

.pro-card-details-btn.primary {
  background: var(--accent-gradient);
  color: white;
}

.pro-card-details-btn.secondary {
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.15);
  color: var(--text-primary);
}

/* Empty */
.no-results {
  grid-column: 1 / -1;
  text-align: center;
  padding: 80px 40px;
  background: rgba(255,255,255,0.03);
  border-radius: 24px;
  border: 2px dashed rgba(255,255,255,0.1);
}

.no-results-icon { font-size: 80px; margin-bottom: 20px; }

.no-results h3 {
  font-size: 1.5rem;
  font-weight: 800;
  color: var(--text-primary);
  margin-bottom: 10px;
}

.no-results p { color: var(--text-tertiary); font-size: 16px; }

.no-results a {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-top: 20px;
  padding: 14px 28px;
  background: var(--accent-gradient);
  border-radius: 14px;
  color: white;
  font-weight: 700;
  text-decoration: none;
}

/* Responsive */
@media (max-width: 1100px) {
  .cards-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
  .cards-grid { grid-template-columns: 1fr; gap: 20px; }
  .filter-container { flex-direction: column; }
  .filter-search, .filter-select { width: 100%; }
  .pro-card-details-grid { grid-template-columns: 1fr; }
}
</style>

<div class="svc-hero">
  <div class="svc-hero-bg">
    <div class="hero-glow hero-glow-1"></div>
    <div class="hero-glow hero-glow-2"></div>
  </div>
  <h1>
    <?php if($type): 
      $typeLabel = $type; $typeEmoji = '🔧';
      foreach($serviceCategories as $cat) { if($cat['name_en'] === $type) { $typeLabel = $cat['name_bn']; $typeEmoji = $cat['emoji']; break; } }
    ?>
      <?= $typeEmoji ?> <?= e($typeLabel) ?> সেবা
      <?php if($city): ?><span style="font-weight:500;opacity:.75"> — <?= cityBn($city) ?></span><?php endif; ?>
    <?php else: ?>
      🔍 ঢাকার সেরা সেবাদাতা
    <?php endif; ?>
  </h1>
  <p><?= count($providers) ?> জন যাচাইকৃত পেশাদার আপনার সেবায়</p>
</div>

<!-- Filter -->
<div class="filter-section">
  <form class="filter-container" method="GET">
    <?php if($type): ?><input type="hidden" name="type" value="<?= e($type) ?>"><?php endif; ?>
    <?php if($city): ?><input type="hidden" name="city" value="<?= e($city) ?>"><?php endif; ?>
    
    <div class="filter-search">
      <i class="fas fa-search"></i>
      <input type="text" id="provSearch" name="q" placeholder="সেবাদাতার নাম/সেবা খুঁজুন…" autocomplete="off" value="<?= e($search) ?>">
    </div>
    
    <select class="filter-select" name="city" onchange="this.form.submit()">
      <option value="">📍 সব এলাকা</option>
      <option value="mirpur" <?= $city==='mirpur'?'selected':'' ?>>মিরপুর</option>
      <option value="gulshan" <?= $city==='gulshan'?'selected':'' ?>>গুলশান</option>
      <option value="banani" <?= $city==='banani'?'selected':'' ?>>বনানী</option>
      <option value="uttara" <?= $city==='uttara'?'selected':'' ?>>উত্তরা</option>
      <option value="baridhara" <?= $city==='baridhara'?'selected':'' ?>>বারিধারা</option>
      <option value="dhanmondi" <?= $city==='dhanmondi'?'selected':'' ?>>ধানমন্ডি</option>
      <option value="mohakhali" <?= $city==='mohakhali'?'selected':'' ?>>মোহাখালী</option>
      <option value="badda" <?= $city==='badda'?'selected':'' ?>>বাড্ডা</option>
      <option value="rampura" <?= $city==='rampura'?'selected':'' ?>>রামপুরা</option>
      <option value="savar" <?= $city==='savar'?'selected':'' ?>>সাভার</option>
      <option value="tongi" <?= $city==='tongi'?'selected':'' ?>>টংগী</option>
      <option value="keraniganj" <?= $city==='keraniganj'?'selected':'' ?>>কেরানীগঞ্জ</option>
    </select>
    
    <select class="filter-select" name="sort" onchange="this.form.submit()">
      <option value="rating" <?= $sort==='rating'?'selected':'' ?>>⭐ সর্বোচ্চ রেটিং</option>
      <option value="reviews" <?= $sort==='reviews'?'selected':'' ?>>📝 সর্বাধিক রিভিউ</option>
      <option value="name" <?= $sort==='name'?'selected':'' ?>>🔤 নাম অনুযায়ী</option>
    </select>
    
    <?php if($type || $city || $sort !== 'rating'): ?>
      <a href="services.php" class="filter-reset"><i class="fas fa-times"></i> ফিল্টার মুক্ত</a>
    <?php endif; ?>
  </form>
</div>

<!-- Categories -->
<div class="category-section">
  <div class="cat-pills">
    <a href="services.php<?= $city?'?city='.urlencode($city):'' ?>" class="cat-pill <?= !$type?'active':'' ?>">
      <span>🌟</span><span>সব সেবা</span>
    </a>
    <?php foreach($serviceCategories as $cat): ?>
      <a href="services.php?type=<?= urlencode($cat['name_en']) ?><?= $city?'&city='.urlencode($city):'' ?><?= $sort!=='rating'?'&sort='.urlencode($sort):'' ?>" class="cat-pill <?= $type===$cat['name_en']?'active':'' ?>">
        <span><?= $cat['emoji'] ?></span><span><?= e($cat['name_bn']) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<!-- Cards -->
<div class="cards-section">
  <div class="results-info">
    <div class="results-count">
      <span><?= count($providers) ?></span> জন সেবাদাতা পাওয়া গেছে
      <?php if($type): ?> — <span><?= svcLabel($type) ?></span><?php endif; ?>
      <?php if($city): ?> — <span><?= cityBn($city) ?></span><?php endif; ?>
    </div>
  </div>

  <div class="cards-grid" id="provGrid">
    <?php if(empty($providers)): ?>
      <div class="no-results">
        <div class="no-results-icon">🔍</div>
        <h3>কোনো সেবাদাতা পাওয়া যায়নি</h3>
        <p>ফিল্টার পরিবর্তন করে আবার চেষ্টা করুন</p>
        <a href="services.php"><i class="fas fa-redo"></i> সব দেখুন</a>
      </div>
      <?php foreach($serviceCategories as $cat): ?>
      <div class="pro-card" data-name="<?= strtolower($cat['name_en']) ?>" data-type="<?= strtolower($cat['name_en']) ?>" data-type-bn="<?= e($cat['name_bn']) ?>">
        <div class="pro-card-img">
          <img src="<?= svcImg($cat['name_en']) ?>" alt="<?= e($cat['name_bn']) ?>" loading="lazy">
        </div>
        <div class="pro-card-body">
          <div class="pro-card-service">
            <i class="fas <?= svcIcon($cat['name_en']) ?>" title="<?= e($cat['name_bn']) ?>"></i> <?= e($cat['name_bn']) ?>
          </div>
          <h3 class="pro-card-title"><?= e($cat['name_bn']) ?></h3>
          <div class="pro-card-meta">
            <div class="pro-card-meta-item">
              <i class="fas fa-user-plus" style="color:#6366f1"></i>
              <span><strong>সেবাদাতা:</strong> এখনও নেই</span>
            </div>
            <div class="pro-card-meta-item">
              <i class="fas fa-info-circle" style="color:#f59e0b"></i>
              <span>এই সেবায় যোগ দিন</span>
            </div>
          </div>
          <div class="pro-card-footer">
            <a href="register-provider.php?service_type=<?= urlencode($cat['name_en']) ?>" class="pro-card-btn">
              <i class="fas fa-user-plus"></i> যোগ দিন
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php else: ?>
      <?php foreach($providers as $p): ?>
      <div class="pro-card"
           data-name="<?= e(strtolower($p['company_name'])) ?>"
           data-type="<?= e(strtolower($p['service_type'])) ?>"
           data-type-bn="<?= e(svcLabel($p['service_type'])) ?>"
           data-city="<?= e(strtolower($p['city'])) ?>">
        <div class="pro-card-img">
          <img src="<?= svcImg($p['service_type']) ?>" alt="<?= e($p['company_name']) ?>" loading="lazy">
          <div class="pro-card-badges">
            <?php if($p['verified']): ?>
              <div class="pro-card-badge verified"><i class="fas fa-check-circle"></i> যাচাইকৃত</div>
            <?php endif; ?>
            <div class="pro-card-badge <?= $p['available']?'available':'unavailable' ?>">
              <?= $p['available']?'● উপলব্ধ':'● অনুপলব্ধ' ?>
            </div>
          </div>
        </div>
        
        <div class="pro-card-body">
          <div class="pro-card-service">
            <i class="fas <?= svcIcon($p['service_type']) ?>" title="<?= svcLabel($p['service_type']) ?>"></i> <?= svcLabel($p['service_type']) ?>
          </div>
          <h3 class="pro-card-title"><?= e($p['company_name']) ?></h3>
          
          <div class="pro-card-meta">
            <div class="pro-card-meta-item">
              <i class="fas fa-map-marker-alt"></i>
              <span><strong>এলাকা:</strong> <?= cityBn($p['city']) ?></span>
            </div>
            <div class="pro-card-meta-item">
              <i class="fas fa-star" style="color:#f59e0b"></i>
              <span><strong>রেটিং:</strong> <?= $p['rating'] ?> ★ (<?= $p['review_count'] ?> রিভিউ)</span>
            </div>
            <div class="pro-card-meta-item">
              <i class="fas fa-clock"></i>
              <span><strong>কর্মসময়:</strong> <?= substr($p['work_start'],0,5) ?> – <?= substr($p['work_end'],0,5) ?></span>
            </div>
            <div class="pro-card-meta-item">
              <i class="fas fa-money-bill-wave"></i>
              <span><strong>মূল্য:</strong> <?= e($p['price_range']) ?></span>
            </div>
          </div>
          
          <div class="pro-card-footer">
            <div class="pro-card-price">
              <span class="pro-card-price-label">মূল্য:</span>
              <span class="pro-card-price-value"><?= e($p['price_range']) ?></span>
            </div>
            <a href="booking.php?provider=<?= $p['id'] ?>" class="pro-card-btn">
              <i class="fas fa-calendar-check"></i> বুক করুন
            </a>
          </div>
        </div>
        
        <!-- Slide-up Details -->
        <div class="pro-card-details">
          <div class="pro-card-details-grid">
            <div class="pro-card-detail-item">
              <i class="fas fa-star" style="color:#f59e0b"></i>
              <div>
                <div class="pro-card-detail-label">রেটিং</div>
                <div class="pro-card-detail-value"><?= $p['rating'] ?> ★ (<?= $p['review_count'] ?> রিভিউ)</div>
              </div>
            </div>
            <div class="pro-card-detail-item">
              <i class="fas fa-clock" style="color:#6366f1"></i>
              <div>
                <div class="pro-card-detail-label">কর্মসময়</div>
                <div class="pro-card-detail-value"><?= substr($p['work_start'],0,5) ?> – <?= substr($p['work_end'],0,5) ?></div>
              </div>
            </div>
            <div class="pro-card-detail-item">
              <i class="fas fa-money-bill-wave" style="color:#10b981"></i>
              <div>
                <div class="pro-card-detail-label">মূল্য সীমা</div>
                <div class="pro-card-detail-value">৳ <?= number_format($p['price_min'] ?? 500) ?> - ৳ <?= number_format($p['price_max'] ?? (int)$p['price_range'] ?? 2000) ?></div>
              </div>
            </div>
            <div class="pro-card-detail-item">
              <i class="fas fa-map-marker-alt" style="color:#ef4444"></i>
              <div>
                <div class="pro-card-detail-label">এলাকা</div>
                <div class="pro-card-detail-value"><?= cityBn($p['city']) ?></div>
              </div>
            </div>
          </div>
          <div class="pro-card-details-actions">
            <a href="booking.php?provider=<?= $p['id'] ?>" class="pro-card-details-btn primary">
              <i class="fas fa-calendar-check"></i> বুক করুন
            </a>
            <a href="provider.php?id=<?= $p['id'] ?>" class="pro-card-details-btn secondary">
              <i class="fas fa-info-circle"></i> বিস্তারিত
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<script>
document.getElementById('provSearch')?.addEventListener('input', function(e) {
  const searchTerm = e.target.value.toLowerCase().trim();
  const cards = document.querySelectorAll('.pro-card');
  let visibleCount = 0;
  
  cards.forEach(card => {
    const name = card.dataset.name || '';
    const type = card.dataset.type || '';
    const typeBn = card.dataset.typeBn || '';
    const city = card.dataset.city || '';
    
    const match = name.includes(searchTerm) || type.includes(searchTerm) || typeBn.includes(searchTerm) || city.includes(searchTerm);
    
    if (searchTerm === '' || match) {
      card.style.display = 'block';
      visibleCount++;
    } else {
      card.style.display = 'none';
    }
  });
  
  const resultsCount = document.querySelector('.results-count span');
  if (resultsCount) {
    resultsCount.textContent = visibleCount;
  }
  
  const noResults = document.querySelector('.no-results');
  if (noResults) {
    noResults.style.display = visibleCount === 0 ? 'grid' : 'none';
  }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>