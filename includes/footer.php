</div><!-- .page-body -->

<footer class="site-footer">
  <div class="footer-inner">
    <div>
      <div class="footer-brand-name">সেবা<em>বন্ধু</em></div>
      <p class="footer-tagline">ঢাকার সবচেয়ে বিশ্বস্ত হোম সার্ভিস প্ল্যাটফর্ম। যেকোনো কাজের জন্য যাচাইকৃত পেশাদার খুঁজুন — যেকোনো সময়, যেকোনো এলাকায়।</p>
      <div class="footer-socials">
        <a class="soc-btn" title="ফেসবুক">📘</a>
        <a class="soc-btn" title="হোয়াটসঅ্যাপ">💬</a>
        <a class="soc-btn" title="ইউটিউব">▶️</a>
        <a class="soc-btn" title="ইনস্টাগ্রাম">📷</a>
      </div>
    </div>
    <div class="footer-col">
      <h4>সেবাসমূহ</h4>
      <a href="<?= SITE_URL ?>/services.php?type=Plumbing">🔧 পাইপলাইন</a>
      <a href="<?= SITE_URL ?>/services.php?type=Electricals">⚡ ইলেকট্রিক্যাল</a>
      <a href="<?= SITE_URL ?>/services.php?type=Cleaning">🧹 পরিষ্কার</a>
      <a href="<?= SITE_URL ?>/services.php?type=Carpentry">🪚 কাঠমিস্ত্রি</a>
      <a href="<?= SITE_URL ?>/services.php?type=Pest+Control">🐛 পেস্ট কন্ট্রোল</a>
      <a href="<?= SITE_URL ?>/services.php?type=Painter">🎨 রং মিস্ত্রি</a>
    </div>
    <div class="footer-col">
      <h4>কোম্পানি</h4>
      <a href="<?= SITE_URL ?>/about.php">আমাদের সম্পর্কে</a>
      <a href="<?= SITE_URL ?>/register-provider.php">সেবা নিবন্ধন</a>
      <a href="<?= SITE_URL ?>/contact.php">যোগাযোগ করুন</a>
      <a href="#">গোপনীয়তা নীতি</a>
      <a href="#">শর্তাবলী</a>
    </div>
    <div class="footer-col">
      <h4>অ্যাকাউন্ট</h4>
      <?php if (auth()): ?>
        <a href="<?= SITE_URL ?>/<?= auth()['role'] ?>/dashboard.php">ড্যাশবোর্ড</a>
        <a href="<?= SITE_URL ?>/logout.php">লগ আউট</a>
      <?php else: ?>
        <a href="<?= SITE_URL ?>/login.php">লগইন</a>
        <a href="<?= SITE_URL ?>/signup.php">অ্যাকাউন্ট তৈরি করুন</a>
      <?php endif; ?>
      <a href="<?= SITE_URL ?>/services.php">সেবা খুঁজুন</a>
      <a href="#">সাহায্য কেন্দ্র</a>
    </div>
  </div>
  <div class="footer-bottom">
    <span>© <?= date('Y') ?> সেবা বন্ধু — ঢাকার বিশ্বস্ত হোম সার্ভিস</span>
    <span>🇧🇩 Made with ❤️ in Bangladesh</span>
  </div>
</footer>

<div class="toast" id="toast"></div>

<script>
(function(){
  // ── Scroll nav shadow ──────────────────────────
  const nav = document.getElementById('mainNav');
  window.addEventListener('scroll', () => nav.classList.toggle('scrolled', window.scrollY > 20), {passive:true});

  // ── Mobile burger ──────────────────────────────
  const burger = document.getElementById('navBurger');
  const links  = document.getElementById('navLinks');
  if (burger) burger.addEventListener('click', () => links.classList.toggle('open'));

  // ── Toast helper ──────────────────────────────
  window.showToast = function(msg, dur=3500) {
    const t = document.getElementById('toast');
    t.textContent = msg; t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), dur);
  };

  // ── Scroll reveal ─────────────────────────────
  const ro = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); ro.unobserve(e.target); } });
  }, {threshold: 0.1});
  document.querySelectorAll('.reveal').forEach(el => ro.observe(el));

  // ── Provider live-search ───────────────────────
  const searchBox = document.getElementById('provSearch');
  const grid      = document.getElementById('provGrid');
  if (searchBox && grid) {
    searchBox.addEventListener('input', () => {
      const q = searchBox.value.toLowerCase();
      grid.querySelectorAll('.prov-card').forEach(c => {
        const txt = (c.dataset.name+' '+c.dataset.type+' '+c.dataset.city).toLowerCase();
        c.style.display = txt.includes(q) ? '' : 'none';
      });
    });
  }
})();
</script>

<!-- Chatbot Widget -->
<div class="chatbot-widget" id="chatbotWidget">
  <div class="chatbot-header" onclick="toggleChatbot()">
    <span>🤖 AI সহকারী</span>
    <span class="chatbot-toggle">▼</span>
  </div>
  <div class="chatbot-body" id="chatbotBody">
    <div class="chatbot-messages" id="chatbotMessages">
      <div class="chatbot-msg bot">হ্যালো! 👋 আমি সেবা বন্ধুর AI সহকারী। কিভাবে সাহায্য করতে পারি?</div>
      <div class="chatbot-quick">
        <button onclick="sendQuick('কতজন সেবাদাতা আছেন?')">📋 সেবাদাতা</button>
        <button onclick="sendQuick('কী কী সেবা আছে?')">🔧 সেবা তালিকা</button>
        <button onclick="sendQuick('শীর্ষ রেটেড সেবাদাতা')">⭐ শীর্ষ সেবাদাতা</button>
        <button onclick="sendQuick('কোথায় কোথায় সেবা পাওয়া যায়?')">📍 এলাকা</button>
      </div>
    </div>
    <div class="chatbot-input">
      <input type="text" id="chatbotInput" placeholder="আপনার প্রশ্ন লিখুন..." onkeypress="chatbotKeyPress(event)">
      <button onclick="sendChatbotMessage()">➤</button>
    </div>
  </div>
</div>

<style>
.chatbot-widget {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 9999;
  font-family: 'Hind Siliguri', sans-serif;
}
.chatbot-header {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white;
  padding: 12px 20px;
  border-radius: 25px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
  font-weight: 600;
}
.chatbot-body {
  display: none;
  position: absolute;
  bottom: 60px;
  right: 0;
  width: 350px;
  height: 400px;
  background: #1a1a2e;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0,0,0,0.5);
  border: 1px solid #2d2d42;
}
.chatbot-body.open { display: block; }
.chatbot-messages {
  height: 320px;
  overflow-y: auto;
  padding: 15px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.chatbot-msg {
  max: 85%;
  padding: 10px 14px;
  border-radius: 15px;
  font-size: 14px;
  line-height: 1.5;
}
.chatbot-msg.user {
  align-self: flex-end;
  background: #6366f1;
  color: white;
}
.chatbot-msg.bot {
  align-self: flex-start;
  background: #2d2d42;
  color: white;
}
.chatbot-input {
  display: flex;
  padding: 10px;
  border-top: 1px solid #2d2d42;
  gap: 8px;
}
.chatbot-input input {
  flex: 1;
  padding: 10px 14px;
  border: 1px solid #2d2d42;
  border-radius: 20px;
  background: #111118;
  color: white;
  font-family: inherit;
}
.chatbot-input button {
  background: #6366f1;
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 20px;
  cursor: pointer;
}
.chatbot-input button:hover { background: #8b5cf6; }
.chatbot-toggle { font-size: 12px; }
.chatbot-widget.open .chatbot-body { display: block; }
.chatbot-quick {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  padding: 8px 12px;
  border-bottom: 1px solid #2d2d42;
}
.chatbot-quick button {
  background: #2d2d42;
  color: white;
  border: none;
  padding: 6px 10px;
  border-radius: 15px;
  font-size: 11px;
  cursor: pointer;
  font-family: inherit;
}
.chatbot-quick button:hover { background: #6366f1; }
</style>

<script>
function toggleChatbot() {
  const w = document.getElementById('chatbotWidget');
  const b = document.getElementById('chatbotBody');
  w.classList.toggle('open');
  b.classList.toggle('open');
}
function chatbotKeyPress(e) {
  if (e.key === 'Enter') sendChatbotMessage();
}
function sendQuick(msg) {
  const input = document.getElementById('chatbotInput');
  input.value = msg;
  sendChatbotMessage();
}
function sendChatbotMessage() {
  const input = document.getElementById('chatbotInput');
  const msg = input.value.trim();
  if (!msg) return;
  
  const msgs = document.getElementById('chatbotMessages');
  msgs.innerHTML += '<div class="chatbot-msg user">' + msg + '</div>';
  input.value = '';
  msgs.scrollTop = msgs.scrollHeight;
  
  fetch('chatbot-api.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'message=' + encodeURIComponent(msg)
  })
  .then(r => r.json())
  .then(data => {
    msgs.innerHTML += '<div class="chatbot-msg bot">' + data.reply + '</div>';
    msgs.scrollTop = msgs.scrollHeight;
  })
  .catch(() => {
    msgs.innerHTML += '<div class="chatbot-msg bot">দুঃখিত, একটি সমস্যা হয়েছে।</div>';
    msgs.scrollTop = msgs.scrollHeight;
  });
}
</script>
</body>
</html>
