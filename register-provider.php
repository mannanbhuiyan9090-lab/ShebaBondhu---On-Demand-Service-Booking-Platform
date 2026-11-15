<?php
require_once __DIR__ . '/includes/config.php';
sessionStart();
$pageTitle='সেবা নিবন্ধন'; $activePage='register'; $err=$ok='';
$preServiceType = $_GET['service_type'] ?? '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  csrfCheck();
  $d=[];
  foreach(['fname','lname','company_name','email','phone','service_type','city','about','work_start','work_end'] as $f) $d[$f]=trim($_POST[$f]??'');
  if(!$d['email']||!$d['phone']||!$d['company_name']||!$d['service_type']||!$d['city']) { $err='সকল প্রয়োজনীয় ক্ষেত্র পূরণ করুন।'; }
  else {
    try {
      db()->prepare("INSERT INTO provider_applications (fname,lname,company_name,email,phone,service_type,city,about,work_start,work_end) VALUES (?,?,?,?,?,?,?,?,?,?)")->execute(array_values($d));
      $ok='আবেদন সফলভাবে জমা হয়েছে! আমরা ৪৮ ঘণ্টার মধ্যে যোগাযোগ করব।';
    } catch(Exception $e) { $err='একটি ত্রুটি হয়েছে। আবার চেষ্টা করুন।'; }
  }
}
include __DIR__ . '/includes/header.php';
?>
<style>
.reg-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 50%, #16213e 100%);
  position: relative;
  overflow: hidden;
  padding: 120px 5% 80px;
}

.reg-bg-glow {
  position: absolute;
  width: 600px;
  height: 600px;
  border-radius: 50%;
  filter: blur(120px);
}

.reg-glow-1 {
  background: linear-gradient(135deg, #ff006e, #8338ec);
  top: -200px;
  right: -100px;
  opacity: 0.3;
}

.reg-glow-2 {
  background: linear-gradient(135deg, #00b4d8, #06b6d4);
  bottom: -200px;
  left: -100px;
  opacity: 0.25;
}

.reg-container {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 1.2fr;
  gap: 60px;
  align-items: start;
  position: relative;
  z-index: 1;
}

@media (max-width: 900px) {
  .reg-container {
    grid-template-columns: 1fr;
  }
}

.reg-header {
  text-align: center;
  margin-bottom: 48px;
}

.reg-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: linear-gradient(135deg, #ff006e, #8338ec);
  border-radius: 100px;
  font-size: 14px;
  font-weight: 700;
  color: white;
  margin-bottom: 24px;
  box-shadow: 0 4px 25px rgba(255, 0, 110, 0.4);
}

.reg-title {
  font-size: clamp(2.5rem, 5vw, 3.5rem);
  font-weight: 800;
  letter-spacing: -0.02em;
  margin-bottom: 16px;
  background: linear-gradient(135deg, #ff006e, #8338ec, #00b4d8);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.reg-subtitle {
  font-size: 1.25rem;
  color: rgba(255,255,255,0.7);
  max-width: 600px;
  margin: 0 auto;
}

/* Benefits Card */
.reg-benefits {
  background: linear-gradient(145deg, rgba(255,255,255,0.05), rgba(255,255,255,0.02));
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 24px;
  padding: 40px;
  backdrop-filter: blur(20px);
}

.reg-benefits h2 {
  font-size: 1.75rem;
  font-weight: 800;
  margin-bottom: 32px;
  background: linear-gradient(135deg, #fff);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.reg-benefit-item {
  display: flex;
  gap: 16px;
  padding: 20px;
  background: linear-gradient(145deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01));
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px;
  margin-bottom: 16px;
  transition: all 0.3s;
}

.reg-benefit-item:hover {
  background: linear-gradient(145deg, rgba(255,255,255,0.06), rgba(255,255,255,0.03));
  border-color: rgba(255,0,110,0.3);
  transform: translateX(8px);
}

.reg-benefit-icon {
  font-size: 32px;
  width: 56px;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, rgba(255,0,110,0.2), rgba(131,56,236,0.2));
  border-radius: 14px;
  flex-shrink: 0;
}

.reg-benefit-title {
  font-size: 16px;
  font-weight: 700;
  color: white;
  margin-bottom: 6px;
}

.reg-benefit-desc {
  font-size: 14px;
  color: rgba(255,255,255,0.6);
  line-height: 1.6;
}

.reg-cta-box {
  background: linear-gradient(135deg, #ff006e, #8338ec);
  border-radius: 20px;
  padding: 32px;
  text-align: center;
  margin-top: 24px;
}

.reg-cta-box h3 {
  font-size: 1.5rem;
  font-weight: 800;
  margin-bottom: 8px;
}

.reg-cta-box p {
  color: rgba(255,255,255,0.8);
  font-size: 14px;
}

/* Form */
.reg-form-card {
  background: linear-gradient(145deg, rgba(255,255,255,0.05), rgba(255,255,255,0.02));
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 24px;
  padding: 40px;
  backdrop-filter: blur(20px);
}

.reg-form-title {
  font-size: 1.5rem;
  font-weight: 800;
  margin-bottom: 8px;
  color: white;
}

.reg-form-subtitle {
  font-size: 14px;
  color: rgba(255,255,255,0.6);
  margin-bottom: 32px;
}

.reg-form-group {
  margin-bottom: 20px;
}

.reg-form-group label {
  display: block;
  font-size: 14px;
  font-weight: 600;
  color: rgba(255,255,255,0.8);
  margin-bottom: 8px;
}

.reg-form-group input,
.reg-form-group select,
.reg-form-group textarea {
  width: 100%;
  padding: 14px 18px;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 12px;
  color: white;
  font-size: 15px;
  font-family: inherit;
  transition: all 0.3s;
}

.reg-form-group input:focus,
.reg-form-group select:focus,
.reg-form-group textarea:focus {
  outline: none;
  border-color: #8338ec;
  background: rgba(255,255,255,0.08);
  box-shadow: 0 0 0 4px rgba(131,56,236,0.15);
}

.reg-form-group input::placeholder,
.reg-form-group textarea::placeholder {
  color: rgba(255,255,255,0.4);
}

.reg-form-group select option {
  background: #1a1a2e;
  color: white;
}

.reg-form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

@media (max-width: 500px) {
  .reg-form-row {
    grid-template-columns: 1fr;
  }
}

.reg-form-btn {
  width: 100%;
  padding: 18px;
  background: linear-gradient(135deg, #ff006e, #8338ec);
  border: none;
  border-radius: 14px;
  font-size: 16px;
  font-weight: 700;
  color: white;
  cursor: pointer;
  transition: all 0.3s;
  margin-top: 12px;
  font-family: inherit;
}

.reg-form-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 40px rgba(255, 0, 110, 0.4);
}

.reg-success {
  background: linear-gradient(135deg, rgba(16,185,129,0.2), rgba(5,150,105,0.2));
  border: 1px solid rgba(16,185,129,0.4);
  border-radius: 14px;
  padding: 20px;
  color: #10b981;
  font-weight: 600;
  margin-bottom: 24px;
  text-align: center;
}

.reg-error {
  background: linear-gradient(135deg, rgba(239,68,68,0.2), rgba(220,38,38,0.2));
  border: 1px solid rgba(239,68,68,0.4);
  border-radius: 14px;
  padding: 16px 20px;
  color: #ef4444;
  font-weight: 600;
  margin-bottom: 24px;
}
</style>

<div class="reg-page">
  <div class="reg-bg-glow reg-glow-1"></div>
  <div class="reg-bg-glow reg-glow-2"></div>
  
  <div class="reg-header">
    <div class="reg-badge">🔧 সেবা নিবন্ধন</div>
    <h1 class="reg-title">আপনার ব্যবসা অনলাইনে আনুন</h1>
    <p class="reg-subtitle">সেবা বন্ধু-এ যোগ দিন, নিজের দক্ষতাকে আয়ে রূপান্তর করুন। হাজার হাজার গ্রাহক আপনার অপেক্ষায়!</p>
  </div>
  
  <div class="reg-container">
    <!-- Benefits -->
    <div class="reg-benefits">
      <h2>কেন সেবা বন্ধু-তে যোগ দেবেন?</h2>
      
      <?php $benefits=[
        ['💰','বেশি আয় করুন','হাজার হাজার গ্রাহকের কাছে সেবা পৌঁছে দিন এবং আয় বাড়ান।'],
        ['📱','সহজ ব্যবস্থাপনা','ড্যাশবোর্ড থেকে সব বুকিং এক জায়গায় পরিচালনা করুন।'],
        ['⭐','সুনাম গড়ুন','গ্রাহকদের রিভিউ আপনার ব্যবসাকে আরও বড় করবে।'],
        ['🛡️','নিরাপদ পেমেন্ট','প্রতিটি কাজের মূল্য নিরাপদ ব্যবস্থায় পান।'],
        ['📅','নিজের সময়সূচি','নিজের সুবিধামতো কাজের সময় নির্ধারণ করুন।'],
      ]; foreach($benefits as [$icon,$title,$desc]): ?>
      <div class="reg-benefit-item">
        <div class="reg-benefit-icon"><?= $icon ?></div>
        <div>
          <div class="reg-benefit-title"><?= $title ?></div>
          <div class="reg-benefit-desc"><?= $desc ?></div>
        </div>
      </div>
      <?php endforeach; ?>
      
      <div class="reg-cta-box">
        <div style="font-size:40px;margin-bottom:8px">🏆</div>
        <h3>১৮০+ বিশেষজ্ঞ ইতিমধ্যে যোগ দিয়েছেন</h3>
        <p>আজই যোগ দিন এবং আপনার ব্যবসা বাড়ান</p>
      </div>
    </div>
    
    <!-- Form -->
    <div class="reg-form-card">
      <h2 class="reg-form-title">আবেদন ফর্ম</h2>
      <p class="reg-form-subtitle">নিচের তথ্যগুলো পূরণ করে আবেদন জমা দিন</p>
      
      <?php if($ok): ?>
      <div class="reg-success">✅ <?= e($ok) ?></div>
      <?php endif; ?>
      
      <?php if($err): ?>
      <div class="reg-error">❌ <?= e($err) ?></div>
<?php endif; ?>
      
      <form method="POST">
        <input type="hidden" name="csrf" value="<?= csrf() ?>">
         
        <div class="reg-form-row">
          <div class="reg-form-group">
            <label>প্রথম নাম *</label>
            <input type="text" name="fname" required value="<?= e($_POST['fname']??'') ?>" placeholder="আপনার নাম">
          </div>
          <div class="reg-form-group">
            <label>শেষ নাম</label>
            <input type="text" name="lname" value="<?= e($_POST['lname']??'') ?>" placeholder="শেষ নাম">
          </div>
        </div>
        
        <div class="reg-form-group">
          <label>ব্যবসার নাম *</label>
          <input type="text" name="company_name" required placeholder="যেমন: রয়্যাল প্লাম্বিং সার্ভিস" value="<?= e($_POST['company_name']??'') ?>">
        </div>
        
        <div class="reg-form-row">
          <div class="reg-form-group">
            <label>ইমেইল *</label>
            <input type="email" name="email" required value="<?= e($_POST['email']??'') ?>" placeholder="your@email.com">
          </div>
          <div class="reg-form-group">
            <label>মোবাইল নম্বর *</label>
            <input type="tel" name="phone" required placeholder="017XXXXXXXX" value="<?= e($_POST['phone']??'') ?>">
          </div>
        </div>
        
        <div class="reg-form-row">
          <div class="reg-form-group">
            <label>সেবার ধরন *</label>
            <select name="service_type" required>
              <option value="">বেছে নিন</option>
              <?php 
              $regServices = getServiceCategories();
              foreach($regServices as $s): ?>
                <option value="<?= e($s['name_en']) ?>" <?= ($preServiceType && $preServiceType===$s['name_en']) || ($_POST['service_type']??'')===$s['name_en']?'selected':'' ?>><?= $s['emoji'].' '.e($s['name_bn']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="reg-form-group">
            <label>এলাকা *</label>
            <select name="city" required>
              <option value="">বেছে নিন</option>
              <?php foreach(CITY_BN as $en=>$bn): ?>
                <option value="<?= $en ?>" <?= ($_POST['city']??'')===$en?'selected':'' ?>><?= $bn ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        
        <div class="reg-form-row">
          <div class="reg-form-group">
            <label>কাজের শুরু</label>
            <input type="time" name="work_start" value="<?= e($_POST['work_start']??'09:00') ?>">
          </div>
          <div class="reg-form-group">
            <label>কাজের শেষ</label>
            <input type="time" name="work_end" value="<?= e($_POST['work_end']??'18:00') ?>">
          </div>
        </div>
        
        <div class="reg-form-group">
          <label>আপনার সম্পর্কে</label>
          <textarea name="about" rows="4" placeholder="আপনার অভিজ্ঞতা ও সেবা সম্পর্কে লিখুন..."><?= e($_POST['about']??'') ?></textarea>
        </div>
        
        <button type="submit" class="reg-form-btn">
          <i class="fas fa-paper-plane"></i> আবেদন জমা দিন
        </button>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>