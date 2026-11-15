<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle='আমাদের সম্পর্কে'; $activePage='about';
include __DIR__ . '/includes/header.php';
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

.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 5%;
}

/* ===== PAGE HEADER ===== */
.page-hdr {
  position: relative;
  padding: 140px 0 80px;
  text-align: center;
  overflow: hidden;
  background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 50%, #16213e 100%);
}

.header-bg {
  position: absolute;
  inset: 0;
  z-index: 0;
  background: 
    radial-gradient(ellipse at 20% 80%, rgba(131, 56, 236, 0.15) 0%, transparent 50%),
    radial-gradient(ellipse at 80% 20%, rgba(255, 0, 110, 0.15) 0%, transparent 50%);
}

.header-glow {
  position: absolute;
  width: 500px;
  height: 500px;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.5;
}

.header-glow-1 {
  background: linear-gradient(135deg, #ff006e, #ff4d94);
  top: -150px;
  right: -100px;
  animation: glow-pulse-1 4s ease-in-out infinite;
}

.header-glow-2 {
  background: linear-gradient(135deg, #8338ec, #a855f7);
  bottom: -150px;
  left: -100px;
  animation: glow-pulse-2 4s ease-in-out infinite;
}

@keyframes glow-pulse-1 {
  0%, 100% { opacity: 0.5; transform: scale(1); }
  50% { opacity: 0.8; transform: scale(1.1); }
}

@keyframes glow-pulse-2 {
  0%, 100% { opacity: 0.5; transform: scale(1); }
  50% { opacity: 0.85; transform: scale(1.15); }
}

.header-shapes {
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  overflow: hidden;
  pointer-events: none;
}

.header-shapes span {
  position: absolute;
  width: 6px;
  height: 6px;
  background: linear-gradient(135deg, #ff006e, #8338ec, #00b4d8);
  border-radius: 50%;
  opacity: 0.5;
  animation: float-particle 8s linear infinite;
}

.header-shapes span:nth-child(1) { left: 10%; top: 20%; animation-delay: 0s; }
.header-shapes span:nth-child(2) { left: 25%; top: 70%; animation-delay: 1s; width: 8px; height: 8px; }
.header-shapes span:nth-child(3) { left: 50%; top: 15%; animation-delay: 2s; }
.header-shapes span:nth-child(4) { left: 75%; top: 60%; animation-delay: 3s; width: 5px; height: 5px; }
.header-shapes span:nth-child(5) { left: 90%; top: 30%; animation-delay: 4s; }

@keyframes float-particle {
  0% { transform: translateY(0) rotate(0deg); opacity: 0; }
  10% { opacity: 0.5; }
  90% { opacity: 0.5; }
  100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
}

.page-hdr h1 {
  position: relative;
  font-family: var(--ff);
  font-size: clamp(2.5rem, 5vw, 3.5rem);
  font-weight: 900;
  background: linear-gradient(135deg, #ff006e, #8338ec, #00b4d8);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 14px;
}

.page-hdr p {
  position: relative;
  color: var(--text-secondary);
  font-size: 1.2rem;
}

/* ===== MISSION SECTION ===== */
.mission-section {
  padding: 80px 5%;
  position: relative;
}

.mission-card {
  max-width: 750px;
  margin: 0 auto;
  padding: 48px;
  background: linear-gradient(145deg, rgba(255,255,255,0.05), rgba(255,255,255,0.02));
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 24px;
  text-align: center;
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

.mission-icon {
  width: 80px;
  height: 80px;
  background: var(--accent-gradient);
  border-radius: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 24px;
  box-shadow: 0 8px 30px rgba(99, 102, 241, 0.4);
}

.mission-icon svg {
  width: 40px;
  height: 40px;
  stroke: white;
  fill: none;
  stroke-width: 2;
}

.mission-card h2 {
  font-family: var(--ff);
  font-size: 1.75rem;
  font-weight: 800;
  color: var(--text-primary);
  margin-bottom: 16px;
}

.mission-card p {
  color: var(--text-secondary);
  font-size: 1.05rem;
  line-height: 1.85;
}

/* ===== SECTION TAG ===== */
.section-tag {
  display: inline-block;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  background: var(--accent-gradient);
  color: white;
  padding: 8px 16px;
  border-radius: 100px;
  margin-bottom: 14px;
}

/* ===== STORY SECTION ===== */
.story-section {
  padding: 100px 5%;
  background: linear-gradient(180deg, transparent 0%, rgba(26, 26, 46, 0.5) 100%);
}

.story-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 80px;
  align-items: center;
}

.story-content .section-tag {
  margin-bottom: 12px;
}

.story-content h2 {
  font-family: var(--ff);
  font-size: clamp(1.8rem, 3vw, 2.2rem);
  font-weight: 800;
  color: var(--text-primary);
  margin-bottom: 24px;
  line-height: 1.2;
}

.story-content p {
  color: var(--text-secondary);
  font-size: 1.05rem;
  line-height: 1.85;
  margin-bottom: 18px;
}

.story-stats {
  display: flex;
  gap: 24px;
  margin-top: 32px;
  padding-top: 28px;
  border-top: 1px solid var(--border);
}

.stat-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 20px 24px;
  background: linear-gradient(145deg, rgba(255,255,255,0.05), rgba(255,255,255,0.02));
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 16px;
  transition: all 0.3s;
}

.stat-box:hover {
  border-color: var(--accent-primary);
  transform: translateY(-4px);
}

.stat-box strong {
  font-family: var(--ff);
  font-size: 1.8rem;
  font-weight: 900;
  background: linear-gradient(135deg, #ff006e, #8338ec);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.stat-box span {
  font-size: 13px;
  color: var(--text-tertiary);
}

.story-image {
  position: relative;
}

.image-wrapper {
  border-radius: 28px;
  overflow: hidden;
  box-shadow: 0 25px 50px rgba(0,0,0,0.3);
  border: 4px solid rgba(255,255,255,0.1);
}

.image-wrapper img {
  width: 100%;
  height: 450px;
  object-fit: cover;
}

/* ===== VALUES SECTION ===== */
.values-section {
  padding: 100px 5%;
  background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 50%, #16213e 100%);
  position: relative;
  overflow: hidden;
}

.values-bg {
  position: absolute;
  inset: 0;
  background: 
    radial-gradient(ellipse at 20% 50%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
    radial-gradient(ellipse at 80% 50%, rgba(217, 70, 239, 0.1) 0%, transparent 50%);
}

.values-section .section-tag {
  display: block;
  text-align: center;
  margin-bottom: 12px;
}

.values-section h2 {
  font-family: var(--ff);
  font-size: 2rem;
  font-weight: 800;
  color: var(--text-primary);
  margin-bottom: 50px;
  text-align: center;
}

.values-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
  position: relative;
}

.value-card {
  padding: 36px 24px;
  background: linear-gradient(145deg, rgba(255,255,255,0.05), rgba(255,255,255,0.02));
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 20px;
  text-align: center;
  transition: all 0.3s ease;
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

.value-card:hover {
  transform: translateY(-10px);
  border-color: rgba(255,0,110,0.5);
  background: linear-gradient(145deg, rgba(255,255,255,0.08), rgba(255,255,255,0.04));
  box-shadow: 0 20px 40px rgba(255, 0, 110, 0.15);
}

.value-icon {
  width: 64px;
  height: 64px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
}

.value-icon.v-green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
.value-icon.v-gold { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.value-icon.v-blue { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
.value-icon.v-purple { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }

.value-icon svg {
  width: 28px;
  height: 28px;
  stroke: white;
  fill: none;
  stroke-width: 2;
}

.value-card h3 {
  font-family: var(--ff);
  font-size: 1.15rem;
  font-weight: 800;
  color: var(--text-primary);
  margin-bottom: 10px;
}

.value-card p {
  font-size: 0.9rem;
  color: var(--text-tertiary);
  line-height: 1.6;
}

/* ===== TEAM SECTION ===== */
.team-section {
  padding: 100px 5%;
  background: linear-gradient(180deg, #16213e 0%, #0f0f1a 100%);
}

.team-section .section-tag {
  display: block;
  text-align: center;
  margin-bottom: 12px;
}

.team-section h2 {
  font-family: var(--ff);
  font-size: 2rem;
  font-weight: 800;
  color: var(--text-primary);
  text-align: center;
  margin-bottom: 50px;
}

.team-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 28px;
  max-width: 1000px;
  margin: 0 auto;
}

.team-card {
  padding: 28px;
  background: linear-gradient(145deg, rgba(255,255,255,0.05), rgba(255,255,255,0.02));
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 20px;
  text-align: center;
  transition: all 0.3s ease;
}

.team-card:hover {
  transform: translateY(-10px);
  border-color: var(--accent-primary);
  box-shadow: 0 20px 40px rgba(99, 102, 241, 0.2);
}

.team-img {
  width: 110px;
  height: 110px;
  border-radius: 50%;
  margin: 0 auto 18px;
  overflow: hidden;
  border: 3px solid rgba(255,255,255,0.15);
  box-shadow: 0 8px 20px rgba(0,0,0,0.3);
  transition: all 0.3s ease;
}

.team-card:hover .team-img {
  border-color: var(--accent-primary);
  box-shadow: 0 12px 28px rgba(99, 102, 241, 0.3);
}

.team-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.team-card h4 {
  font-family: var(--ff);
  font-size: 1.05rem;
  font-weight: 800;
  color: var(--text-primary);
  margin-bottom: 4px;
}

.team-card span {
  font-size: 0.85rem;
  color: var(--text-tertiary);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 900px) {
  .story-grid { grid-template-columns: 1fr; gap: 40px; }
  .story-image { order: -1; max-width: 500px; margin: 0 auto; }
  .values-grid { grid-template-columns: repeat(2, 1fr); }
  .team-grid { grid-template-columns: repeat(2, 1fr); }
  .mission-card { padding: 32px 24px; }
  .story-stats { flex-wrap: wrap; justify-content: center; }
}
@media (max-width: 600px) {
  .values-grid, .team-grid { grid-template-columns: 1fr; }
  .values-section, .story-section, .team-section { padding: 60px 5vw; }
  .page-hdr h1 { font-size: 2.2rem; }
}
</style>

<div class="page-hdr">
  <div class="header-bg">
    <div class="header-glow header-glow-1"></div>
    <div class="header-glow header-glow-2"></div>
    <div class="header-shapes">
      <span></span><span></span><span></span><span></span><span></span>
    </div>
  </div>
  <div class="container">
    <h1>আমাদের সম্পর্কে</h1>
    <p>সেবা বন্ধু — ঢাকার সবচেয়ে বিশ্বস্ত হোম সার্ভিস প্ল্যাটফর্ম</p>
  </div>
</div>

<section class="mission-section">
  <div class="container">
    <div class="mission-card">
      <div class="mission-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      </div>
      <h2>আমাদের লক্ষ্য</h2>
      <p>সেবা বন্ধু-র লক্ষ্য হলো ঢাকার প্রতিটি পরিবারকে বিশ্বস্ত, দক্ষ এবং সাশ্রয়ী মূল্যে হোম সার্ভিস প্রদান করা। আমরা বিশ্বাস করি, প্রতিটি ঘর একটি আশ্রয় — এবং সেই আশ্রয় সুরক্ষিত ও সুন্দর রাখার দায়িত্ব আমাদের।</p>
    </div>
  </div>
</section>

<section class="story-section">
  <div class="container">
    <div class="story-grid">
      <div class="story-content">
        <span class="section-tag">আমাদের যাত্রা</span>
        <h2>একটি স্বপ্ন থেকে বাস্তব</h2>
        <p>২০২৪ সালে ঢাকায় সেবা বন্ধু-র যাত্রা শুরু হয়। আমাদের প্রতিষ্ঠাতারা নিজেরা অনুভব করেছিলেন যে বিশ্বস্ত হোম সার্ভিস পাওয়া কতটা কঠিন। তাই তারা এমন একটি প্ল্যাটফর্ম তৈরি করলেন যেখানে গ্রাহক এবং সেবাদাতা উভয়ই উপকৃত হন।</p>
        <p>আজ আমাদের ১৮০+ যাচাইকৃত সেবাদাতা প্রতিদিন ঢাকার ৯টি এলাকায় হাজার হাজার পরিবারকে সেবা দিচ্ছেন।</p>
        <div class="story-stats">
          <div class="stat-box"><strong id="stat-providers">০</strong><span>যাচাইকৃত সেবাদাতা</span></div>
          <div class="stat-box"><strong id="stat-areas">০</strong><span>এলাকায় সেবা</span></div>
          <div class="stat-box"><strong id="stat-customers">০</strong><span>সন্তুষ্ট গ্রাহক</span></div>
        </div>
      </div>
      <div class="story-image">
        <div class="image-wrapper">
          <img src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=700&q=80" alt="সেবাবন্ধু পরিবার">
        </div>
      </div>
    </div>
  </div>
</section>

<section class="values-section">
  <div class="values-bg"></div>
  <div class="container">
    <span class="section-tag">আমাদের মূল্যবোধ</span>
    <h2>যা আমাদের চালিত করে</h2>
    <div class="values-grid">
      <div class="value-card">
        <div class="value-icon v-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></div>
        <h3>বিশ্বাস</h3>
        <p>প্রতিটি সেবাদাতা যাচাইকৃত এবং বিশ্বস্ত</p>
      </div>
      <div class="value-card">
        <div class="value-icon v-gold"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg></div>
        <h3>দক্ষতা</h3>
        <p>দ্রুত, দক্ষ এবং সময়মতো সেবা</p>
      </div>
      <div class="value-card">
        <div class="value-icon v-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
        <h3>সততা</h3>
        <p>স্বচ্ছ মূল্য, কোনো লুকানো চার্জ নেই</p>
      </div>
      <div class="value-card">
        <div class="value-icon v-purple"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
        <h3>মান</h3>
        <p>সর্বোচ্চ মানের সেবা নিশ্চিত করা</p>
      </div>
    </div>
  </div>
</section>

<section class="team-section">
  <div class="container">
    <span class="section-tag">আমাদের দল</span>
    <h2>প্রতিটি পরিবারের সেবায় নিয়োজিত</h2>
    <div class="team-grid">
      <div class="team-card">
        <div class="team-img"><img src="assets/img/ceo.jpg?w=300&h=300&fit=crop" alt="আহমেদ রাশেদ"></div>
        <h4>আহমেদ রাশেদ</h4>
        <span>CEO ও প্রতিষ্ঠাতা</span>
      </div>
      <div class="team-card">
        <div class="team-img"><img src="assets/img/coo.jpg?w=300&h=300&fit=crop" alt="ফারহানা ইসলাম"></div>
        <h4>ফারহানা ইসলাম</h4>
        <span>COO ও ধান পরিচালন কর্মকর্তা</span>
      </div>
      <div class="team-card">
        <div class="team-img"><img src="assets/img/cco.jpg?w=300&h=300&fit=crop" alt="তানভীর হোসেন"></div>
        <h4>তানভীর হোসেন</h4>
        <span>CTO-চিফ টেকনোলজি অফিসার</span>
      </div>
      <div class="team-card">
        <div class="team-img"><img src="assets/img/cto.jpg?w=300&h=300&fit=crop" alt="নুসরাত জাহান"></div>
        <h4>নুসরাত জাহান</h4>
        <span>গ্রাহক সেবা প্রধান</span>
      </div>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
  
  function toBn(num) {
    return num.toString().split('').map(d => /\d/.test(d) ? bn[parseInt(d)] : d).join('');
  }
  
  function animateCount(el, target, display, duration = 2000) {
    if (el.dataset.animated) return;
    el.dataset.animated = 'true';
    
    let current = 0;
    const increment = target / (duration / 16);
    
    const timer = setInterval(() => {
      current += increment;
      if (current >= target) {
        el.textContent = display;
        clearInterval(timer);
      } else {
        el.textContent = toBn(Math.floor(current));
      }
    }, 16);
  }
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const id = entry.target.id;
        
        if (id === 'stat-providers') animateCount(entry.target, 180, '১৮০+', 1500);
        else if (id === 'stat-areas') animateCount(entry.target, 9, '৯+', 1200);
        else if (id === 'stat-customers') animateCount(entry.target, 5000, '৫,০০০+', 2000);
      }
    });
  }, { threshold: 0.5 });
  
  document.querySelectorAll('#stat-providers, #stat-areas, #stat-customers').forEach(el => {
    observer.observe(el);
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>