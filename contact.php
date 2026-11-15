<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'যোগাযোগ করুন';
$activePage = 'contact';

$success = '';
$errors = ['name'=>'', 'email'=>'', 'message'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    $has_error = false;
    
    if (!$name) {
        $errors['name'] = 'নাম আবশ্যক';
        $has_error = true;
    }
    if (!$email) {
        $errors['email'] = 'ইমেইল আবশ্যক';
        $has_error = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'সঠিক ইমেইল দিন';
        $has_error = true;
    }
    if (!$message) {
        $errors['message'] = 'মেসেজ আবশ্যক';
        $has_error = true;
    }
    
    if (!$has_error) {
        try {
            db()->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())")->execute([$name, $email, $phone, $subject, $message]);
        } catch (Exception $e) {}
        
        try {
            $fullMsg = "$name\nইমেইল: $email\nফোন: $phone\nবিষয়: $subject\n\nমেসেজ: $message";
            db()->prepare("INSERT INTO notification (user_id, role_target, type, title, message, is_read, created_at) VALUES (0, 'admin', 'contact', 'নতুন যোগাযোগ বার্তা', ?, 0, NOW())")->execute([$fullMsg]);
        } catch (Exception $e) {}
        
        $success = 'আপনার বার্তা সফলভাবে পাঠানো হয়েছে। আমরা শীঘ্রই যোগাযোগ করব।';
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> - সেবা বন্ধু</title>
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Hind Siliguri', sans-serif;
      min-height: 100vh;
      background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
    }
    
    /* Hero */
    .contact-hero {
      background: linear-gradient(135deg, rgba(124, 58, 237, 0.9), rgba(219, 39, 119, 0.8));
      padding: 80px 5vw;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .contact-hero::before {
      content: '';
      position: absolute;
      width: 400px;
      height: 400px;
      background: radial-gradient(circle, rgba(255,255,255,0.15), transparent 70%);
      top: -100px;
      right: -100px;
      border-radius: 50%;
    }
    .contact-hero h1 {
      font-size: clamp(2rem, 4vw, 3rem);
      font-weight: 900;
      color: white;
      margin-bottom: 12px;
      position: relative;
      z-index: 1;
    }
    .contact-hero p {
      color: rgba(255,255,255,0.85);
      font-size: 1.1rem;
      position: relative;
      z-index: 1;
    }
    
    /* Contact Container */
    .contact-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 60px 5vw;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
    }
    
    /* Info Cards */
    .contact-info {
      display: flex;
      flex-direction: column;
      gap: 24px;
    }
    .info-card {
      background: rgba(255,255,255,0.95);
      border-radius: 20px;
      padding: 28px;
      display: flex;
      align-items: flex-start;
      gap: 20px;
      transition: all 0.3s;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    .info-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 40px rgba(0,0,0,0.3);
    }
    .info-icon {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #7c3aed, #db2777);
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      color: white;
      flex-shrink: 0;
    }
    .info-content h3 {
      font-size: 1.1rem;
      font-weight: 700;
      color: #1a1a2e;
      margin-bottom: 6px;
    }
    .info-content p {
      color: #6b7280;
      font-size: 15px;
      line-height: 1.6;
    }
    
    /* Contact Form */
    .contact-form-wrapper {
      background: rgba(255,255,255,0.95);
      border-radius: 24px;
      padding: 40px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }
    .form-title {
      font-size: 1.5rem;
      font-weight: 800;
      color: #1a1a2e;
      margin-bottom: 24px;
      text-align: center;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      font-size: 14px;
      font-weight: 700;
      color: #1a1a2e;
      margin-bottom: 8px;
    }
    .form-group label i {
      color: #7c3aed;
      margin-right: 6px;
    }
    .form-group input,
    .form-group textarea,
    .form-group select {
      width: 100%;
      padding: 14px 18px;
      border: 2px solid #e5e7eb;
      border-radius: 14px;
      font-family: 'Hind Siliguri', sans-serif;
      font-size: 15px;
      color: #1a1a2e;
      outline: none;
      transition: all 0.3s;
      background: white;
    }
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
      border-color: #7c3aed;
      box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1);
    }
    .form-group input.has-error,
    .form-group textarea.has-error {
      border-color: #dc2626;
      background: #fef2f2;
    }
    .form-group textarea {
      resize: vertical;
      min-height: 120px;
    }
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }
    
    .error-msg {
      background: #fef2f2;
      border: 2px solid #fecaca;
      color: #dc2626;
      padding: 14px 18px;
      border-radius: 14px;
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .success-msg {
      background: #f0fdf4;
      border: 2px solid #bbf7d0;
      color: #16a34a;
      padding: 14px 18px;
      border-radius: 14px;
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .field-error {
      color: #dc2626;
      font-size: 12px;
      font-weight: 600;
      margin-top: 6px;
      display: block;
    }
    .form-group input.error, 
    .form-group textarea.error {
      border-color: #dc2626 !important;
    }
    
    .submit-btn {
      width: 100%;
      padding: 18px;
      background: linear-gradient(135deg, #7c3aed, #db2777);
      color: white;
      border: none;
      border-radius: 16px;
      font-family: 'Hind Siliguri', sans-serif;
      font-size: 18px;
      font-weight: 800;
      cursor: pointer;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }
    .submit-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 30px rgba(124, 58, 237, 0.4);
    }
    
    /* Social Links */
    .social-section {
      margin-top: 40px;
      text-align: center;
    }
    .social-title {
      font-size: 1rem;
      font-weight: 700;
      color: #1a1a2e;
      margin-bottom: 16px;
    }
    .social-links {
      display: flex;
      justify-content: center;
      gap: 16px;
    }
    .social-link {
      width: 50px;
      height: 50px;
      background: linear-gradient(135deg, #7c3aed, #db2777);
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 20px;
      text-decoration: none;
      transition: all 0.3s;
    }
    .social-link:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(124, 58, 237, 0.4);
    }
    
    @media (max-width: 900px) {
      .contact-container {
        grid-template-columns: 1fr;
      }
    }
    @media (max-width: 480px) {
      .form-row {
        grid-template-columns: 1fr;
      }
      .contact-form-wrapper {
        padding: 24px;
      }
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<!-- Hero -->
<div class="contact-hero">
  <h1><i class="fas fa-headset"></i> আমাদের সাথে যোগাযোগ করুন</h1>
  <p>যেকোনো প্রশ্ন বা সহায়তার জন্য আমরা এখানে</p>
</div>

<!-- Contact Section -->
<div class="contact-container">
  <!-- Info Cards -->
  <div class="contact-info">
    <div class="info-card">
      <div class="info-icon">
        <i class="fas fa-map-marker-alt"></i>
      </div>
      <div class="info-content">
        <h3>আমাদের ঠিকানা</h3>
        <p>বাড়ি ১২, রোড ৫, ব্লক বি<br>বনানী, ঢাকা-১২১৩, বাংলাদেশ</p>
      </div>
    </div>
    
    <div class="info-card">
      <div class="info-icon">
        <i class="fas fa-phone-alt"></i>
      </div>
      <div class="info-content">
        <h3>ফোন নম্বর</h3>
        <p>+880 1700-000000<br>+880 1800-000000</p>
      </div>
    </div>
    
    <div class="info-card">
      <div class="info-icon">
        <i class="fas fa-envelope"></i>
      </div>
      <div class="info-content">
        <h3>ইমেইল</h3>
        <p>info@handygo.com<br>support@handygo.com</p>
      </div>
    </div>
    
    <div class="info-card">
      <div class="info-icon">
        <i class="fas fa-clock"></i>
      </div>
      <div class="info-content">
        <h3>কর্মঘণ্টা</h3>
        <p>শনিবার - বৃহস্পতিবার: সকাল ৯টা - রাত ৯টা<br>শুক্রবার: সকাল ৯টা - বিকেল ৫টা</p>
      </div>
    </div>
    
    <div class="social-section">
      <div class="social-title">সোশ্যাল মিডিয়ায় আমাদের ফলো করুন</div>
      <div class="social-links">
        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
        <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
      </div>
    </div>
  </div>
  
  <!-- Contact Form -->
  <div class="contact-form-wrapper">
    <h2 class="form-title"><i class="fas fa-paper-plane"></i> বার্তা পাঠান</h2>
    
    <?php if ($success): ?>
      <div class="success-msg">
        <i class="fas fa-check-circle"></i> <?= e($success) ?>
      </div>
    <?php endif; ?>
    
    <form method="POST">
      <div class="form-row">
        <div class="form-group">
          <label><i class="fas fa-user"></i> আপনার নাম *</label>
          <input type="text" name="name" placeholder="নাম লিখুন" value="<?= e($_POST['name'] ?? '') ?>" class="<?= $errors['name'] ? 'has-error' : '' ?>">
          <?php if ($errors['name']): ?><small class="field-error"><?= e($errors['name']) ?></small><?php endif; ?>
        </div>
        <div class="form-group">
          <label><i class="fas fa-envelope"></i> ইমেইল *</label>
          <input type="email" name="email" placeholder="email@example.com" value="<?= e($_POST['email'] ?? '') ?>" class="<?= $errors['email'] ? 'has-error' : '' ?>">
          <?php if ($errors['email']): ?><small class="field-error"><?= e($errors['email']) ?></small><?php endif; ?>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label><i class="fas fa-phone"></i> ফোন নম্বর</label>
          <input type="tel" name="phone" placeholder="01XXX-XXXXXX" value="<?= e($_POST['phone'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label><i class="fas fa-tag"></i> বিষয়</label>
          <select name="subject">
            <option value="">বিষয় নির্বাচন করুন</option>
            <option value="সাধারণ প্রশ্ন" <?= ($_POST['subject'] ?? '') === 'সাধারণ প্রশ্ন' ? 'selected' : '' ?>>সাধারণ প্রশ্ন</option>
            <option value="সেবা সম্পর্কে" <?= ($_POST['subject'] ?? '') === 'সেবা সম্পর্কে' ? 'selected' : '' ?>>সেবা সম্পর্কে</option>
            <option value="বিলিং সমস্যা" <?= ($_POST['subject'] ?? '') === 'বিলিং সমস্যা' ? 'selected' : '' ?>>বিলিং সমস্যা</option>
            <option value="প্রোভাইডার হতে চান" <?= ($_POST['subject'] ?? '') === 'প্রোভাইডার হতে চান' ? 'selected' : '' ?>>প্রোভাইডার হতে চান</option>
            <option value="অন্যান্য" <?= ($_POST['subject'] ?? '') === 'অন্যান্য' ? 'selected' : '' ?>>অন্যান্য</option>
          </select>
        </div>
      </div>
      
      <div class="form-group">
        <label><i class="fas fa-comment-alt"></i> আপনার বার্তা *</label>
        <textarea name="message" placeholder="আপনার বার্তা এখানে লিখুন..." class="<?= $errors['message'] ? 'has-error' : '' ?>"><?= e($_POST['message'] ?? '') ?></textarea>
        <?php if ($errors['message']): ?><small class="field-error"><?= e($errors['message']) ?></small><?php endif; ?>
      </div>
      
      <button type="submit" class="submit-btn">
        <i class="fas fa-paper-plane"></i> বার্তা পাঠান
      </button>
    </form>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
