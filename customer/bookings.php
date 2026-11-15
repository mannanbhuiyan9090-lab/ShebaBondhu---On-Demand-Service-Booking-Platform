<?php
require_once __DIR__.'/../includes/config.php';
sessionStart();
requireAuth('customer');
$user = auth();

$status = $_GET['status'] ?? '';
$where = 'b.customer_id = ?';
$params = [$user['id']];
if ($status) {
  $where .= ' AND b.status = ?';
  $params[] = $status;
}

try {
  $stmt = db()->prepare("SELECT b.*, p.company_name, p.emoji, p.service_type, p.city, (SELECT COUNT(*) FROM reviews r WHERE r.booking_id = b.id) AS has_review FROM bookings b JOIN providers p ON p.id = b.provider_id WHERE $where ORDER BY b.created_at DESC");
  $stmt->execute($params);
  $bookings = $stmt->fetchAll();
} catch(Exception $e) {
  $bookings = [];
}

// Count by status
try {
  $countStmt = db()->prepare("SELECT status, COUNT(*) as cnt FROM bookings WHERE customer_id = ? GROUP BY status");
  $countStmt->execute([$user['id']]);
  $statusCounts = [];
  foreach ($countStmt->fetchAll() as $row) $statusCounts[$row['status']] = (int)$row['cnt'];
} catch(Exception $e) { $statusCounts = []; }
$totalCount = array_sum($statusCounts);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>আমার বুকিং - সেবা বন্ধু</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Hind Siliguri', sans-serif; }
    .glass-dark { background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(20px); }
    .sidebar-link { transition: all 0.2s ease; }
    .sidebar-link:hover { background: rgba(71, 85, 105, 0.3); transform: translateX(4px); }
    .sidebar-link.active { background: linear-gradient(90deg, rgba(16, 185, 129, 0.3), rgba(6, 182, 212, 0.1)); border-left: 3px solid #10B981; }
    .gradient-text { background: linear-gradient(135deg, #10B981, #06B6D4); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }

    /* Booking Card */
    .booking-card {
      background: linear-gradient(145deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.5));
      backdrop-filter: blur(20px);
      border: 1px solid rgba(71, 85, 105, 0.3);
      border-radius: 1.25rem;
      transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
      overflow: hidden;
    }
    .booking-card:hover {
      transform: translateY(-4px);
      border-color: rgba(16, 185, 129, 0.4);
      box-shadow: 0 20px 40px rgba(0,0,0,0.3), 0 0 30px rgba(16, 185, 129, 0.08);
    }
    .booking-card-header {
      padding: 20px 24px;
      border-bottom: 1px solid rgba(71, 85, 105, 0.2);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }
    .booking-card-body { padding: 20px 24px; }
    .booking-card-footer {
      padding: 16px 24px;
      border-top: 1px solid rgba(71, 85, 105, 0.2);
      background: rgba(15, 23, 42, 0.3);
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
    }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 14px;
    }
    @media (min-width: 768px) { .info-grid { grid-template-columns: repeat(4, 1fr); } }
    .info-item {
      padding: 14px;
      background: rgba(255,255,255,0.03);
      border-radius: 12px;
      border: 1px solid rgba(71, 85, 105, 0.15);
    }
    .info-item-label { font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
    .info-item-value { font-size: 14px; font-weight: 700; color: #e2e8f0; }

    /* Status Badges */
    .status-badge {
      padding: 6px 14px;
      border-radius: 100px;
      font-size: 12px;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .status-pending { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
    .status-approved { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
    .status-completed { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
    .status-cancelled { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }

    /* Filter Tabs */
    .filter-tab {
      padding: 10px 20px;
      border-radius: 100px;
      font-size: 13px;
      font-weight: 700;
      transition: all 0.25s;
      background: rgba(71, 85, 105, 0.2);
      color: #94a3b8;
      border: 1px solid transparent;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    .filter-tab:hover { background: rgba(71, 85, 105, 0.4); color: white; }
    .filter-tab.active {
      background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(6, 182, 212, 0.2));
      color: #34d399;
      border-color: rgba(16, 185, 129, 0.4);
    }
    .filter-tab .count {
      background: rgba(255,255,255,0.1);
      padding: 2px 8px;
      border-radius: 100px;
      font-size: 11px;
    }
    .filter-tab.active .count { background: rgba(16, 185, 129, 0.3); }

    /* Action buttons */
    .btn-action { padding: 8px 16px; border-radius: 12px; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px; font-size: 13px; text-decoration: none; }
    .btn-primary { background: linear-gradient(135deg, #10B981, #059669); color: white; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4); }
    .btn-outline { background: rgba(71, 85, 105, 0.3); color: #cbd5e1; border: 1px solid rgba(71, 85, 105, 0.4); }
    .btn-outline:hover { background: rgba(71, 85, 105, 0.5); color: white; }
    .btn-danger { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
    .btn-danger:hover { background: rgba(239, 68, 68, 0.25); }
    .btn-edit { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
    .btn-edit:hover { background: rgba(59, 130, 246, 0.25); }
    .btn-review { background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(234, 88, 12, 0.2)); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
    .btn-review:hover { background: linear-gradient(135deg, rgba(245, 158, 11, 0.3), rgba(234, 88, 12, 0.3)); }

    /* Payment code card */
    .payment-code-box {
      background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(6, 182, 212, 0.1));
      border: 1px solid rgba(16, 185, 129, 0.3);
      border-radius: 12px;
      padding: 10px 16px;
      display: inline-flex;
      align-items: center;
      gap: 10px;
    }

    @keyframes pulse-dot { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
    .pulse-dot { animation: pulse-dot 1.5s ease-in-out infinite; }
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
        <?= strtoupper(mb_substr($user['name'], 0, 2)) ?>
      </div>
      <div class="flex-1">
        <div class="font-semibold text-sm"><?= e(explode(' ', $user['name'])[0]) ?></div>
        <div class="text-xs text-slate-500">গ্রাহক</div>
      </div>
      <i class="fas fa-right-from-bracket group-hover:rotate-180 transition-transform"></i>
    </a>
  </div>
</aside>

<button id="menuToggle" class="lg:hidden fixed top-4 left-4 z-50 p-3 bg-slate-800 rounded-xl shadow-lg">
  <i class="fas fa-bars w-5 h-5"></i>
</button>

<div class="lg:ml-64 min-h-screen">
  <header class="sticky top-0 z-40 glass-dark border-b border-slate-700/50">
    <div class="flex items-center justify-between p-4 md:p-6">
      <div class="hidden md:flex items-center gap-3 ml-12">
        <h1 class="text-xl md:text-2xl font-bold text-white">
          <i class="fas fa-calendar-check mr-2 text-emerald-400"></i> আমার বুকিং সমূহ
        </h1>
      </div>
      <div class="md:hidden flex items-center gap-2">
        <span class="text-lg font-bold gradient-text">সেবা বন্ধু</span>
      </div>
      <a href="<?= SITE_URL ?>/services.php" class="btn-action btn-primary">
        <i class="fas fa-plus"></i> নতুন বুকিং
      </a>
    </div>
  </header>

  <main class="p-4 md:p-8 max-w-6xl mx-auto">

    <?php if (isset($_SESSION['flash'])): ?>
      <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
      <div class="mb-6 p-4 rounded-xl <?= ($flash['type'] ?? '') === 'error' ? 'bg-red-500/20 text-red-400 border border-red-500/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' ?>">
        <?= e($flash['msg'] ?? $flash) ?>
      </div>
    <?php endif; ?>

    <!-- Filter Tabs -->
    <div class="flex flex-wrap gap-3 mb-8">
      <a href="?status=" class="filter-tab <?= $status === '' ? 'active' : '' ?>">
        <i class="fas fa-layer-group"></i> সব
        <span class="count"><?= $totalCount ?></span>
      </a>
      <a href="?status=Pending" class="filter-tab <?= $status === 'Pending' ? 'active' : '' ?>">
        <span class="w-2 h-2 rounded-full bg-amber-400 pulse-dot inline-block"></span> অপেক্ষমান
        <span class="count"><?= $statusCounts['Pending'] ?? 0 ?></span>
      </a>
      <a href="?status=Approved" class="filter-tab <?= $status === 'Approved' ? 'active' : '' ?>">
        <i class="fas fa-check-circle text-blue-400"></i> অনুমোদিত
        <span class="count"><?= $statusCounts['Approved'] ?? 0 ?></span>
      </a>
      <a href="?status=Completed" class="filter-tab <?= $status === 'Completed' ? 'active' : '' ?>">
        <i class="fas fa-trophy text-emerald-400"></i> সম্পন্ন
        <span class="count"><?= $statusCounts['Completed'] ?? 0 ?></span>
      </a>
      <a href="?status=Cancelled" class="filter-tab <?= $status === 'Cancelled' ? 'active' : '' ?>">
        <i class="fas fa-ban text-red-400"></i> বাতিল
        <span class="count"><?= $statusCounts['Cancelled'] ?? 0 ?></span>
      </a>
    </div>

    <?php if (empty($bookings)): ?>
      <!-- Empty State -->
      <div class="text-center py-20">
        <div class="w-24 h-24 bg-slate-800/50 rounded-full flex items-center justify-center mx-auto mb-6">
          <i class="fas fa-calendar-xmark text-5xl text-slate-600"></i>
        </div>
        <h3 class="text-2xl font-bold mb-3">কোনো বুকিং নেই</h3>
        <p class="text-slate-500 mb-6 max-w-md mx-auto">আপনার কোনো <?= $status ? STATUS_BN[$status] ?? '' : '' ?> বুকিং পাওয়া যায়নি। নতুন সেবা বুক করতে নীচে ক্লিক করুন।</p>
        <a href="<?= SITE_URL ?>/services.php" class="btn-action btn-primary text-base px-8 py-3">
          <i class="fas fa-search"></i> সেবা খুঁজুন
        </a>
      </div>
    <?php else: ?>
      <!-- Booking Cards -->
      <div class="space-y-5">
        <?php foreach ($bookings as $b): ?>
          <?php
            $statusMap = [
              'Completed' => ['class' => 'status-completed', 'icon' => 'fa-check-circle', 'text' => '✓ সম্পন্ন'],
              'Approved'  => ['class' => 'status-approved',  'icon' => 'fa-thumbs-up',    'text' => '✓ অনুমোদিত'],
              'Pending'   => ['class' => 'status-pending',   'icon' => 'fa-clock',        'text' => '⏳ অপেক্ষমান'],
              'Cancelled' => ['class' => 'status-cancelled', 'icon' => 'fa-times-circle', 'text' => '✕ বাতিল'],
            ];
            $sc = $statusMap[$b['status']] ?? ['class' => 'status-pending', 'icon' => 'fa-question', 'text' => $b['status']];

            $amt = $b['amount'] ?? $b['estimated_price'] ?? 0;
            if (!$amt && !empty($b['price_range'])) $amt = (int)preg_replace('/[^0-9]/', '', $b['price_range']);

            $pmLabels = ['ssl' => '💳 SSL Commercial', 'mobile_banking' => '📱 মোবাইল ব্যাংকিং', 'cash_on_delivery' => '💵 ক্যাশ অন ডেলিভারি'];
          ?>
          <div class="booking-card">
            <!-- Header -->
            <div class="booking-card-header">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-xl flex items-center justify-center text-xl shadow-lg flex-shrink-0">
                  <?= $b['emoji'] ?>
                </div>
                <div>
                  <div class="font-bold text-lg leading-tight"><?= e($b['company_name']) ?></div>
                  <div class="text-sm text-slate-400"><?= svcLabel($b['service_type']) ?> · <?= cityBn($b['city']) ?></div>
                </div>
              </div>
              <div class="flex items-center gap-3 flex-shrink-0">
                <span class="font-mono text-emerald-400 bg-emerald-500/10 px-3 py-1.5 rounded-lg text-sm font-bold"><?= e($b['ref_code']) ?></span>
                <span class="status-badge <?= $sc['class'] ?>"><i class="fas <?= $sc['icon'] ?>"></i> <?= $sc['text'] ?></span>
              </div>
            </div>

            <!-- Body - Info Grid -->
            <div class="booking-card-body">
              <div class="info-grid">
                <div class="info-item">
                  <div class="info-item-label"><i class="fas fa-calendar-day mr-1"></i> তারিখ</div>
                  <div class="info-item-value"><?= date('d M Y', strtotime($b['preferred_date'])) ?></div>
                </div>
                <div class="info-item">
                  <div class="info-item-label"><i class="fas fa-clock mr-1"></i> সময়</div>
                  <div class="info-item-value">
                    <?= substr($b['time_start'], 0, 5) ?> – <span class="text-red-400"><?= substr($b['time_end'], 0, 5) ?></span>
                  </div>
                </div>
                <div class="info-item">
                  <div class="info-item-label"><i class="fas fa-bangladeshi-taka-sign mr-1"></i> মূল্য</div>
                  <div class="info-item-value text-emerald-400">৳<?= number_format($amt) ?></div>
                </div>
                <div class="info-item">
                  <div class="info-item-label"><i class="fas fa-credit-card mr-1"></i> পেমেন্ট</div>
                  <div class="info-item-value text-sm">
                    <?php if ($b['payment_method']): ?>
                      <?= $pmLabels[$b['payment_method']] ?? $b['payment_method'] ?>
                      <?php if ($b['payment_status'] === 'paid'): ?>
                        <span class="text-emerald-400 text-xs ml-1">✓</span>
                      <?php elseif ($b['payment_status'] === 'pending_verification'): ?>
                        <span class="text-amber-400 text-xs ml-1">⏳</span>
                      <?php else: ?>
                        <span class="text-red-400 text-xs ml-1">✕</span>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="text-slate-500">—</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <?php if (!empty($b['address'])): ?>
              <div class="mt-3 p-3 bg-slate-800/30 rounded-xl text-sm text-slate-400">
                <i class="fas fa-map-marker-alt text-red-400 mr-2"></i><?= e($b['address']) ?>
              </div>
              <?php endif; ?>

              <?php if (!empty($b['description'])): ?>
              <div class="mt-2 p-3 bg-slate-800/30 rounded-xl text-sm text-slate-400">
                <i class="fas fa-comment-dots text-blue-400 mr-2"></i><?= e($b['description']) ?>
              </div>
              <?php endif; ?>

              <?php if (!empty($b['provider_note'])): ?>
              <div class="mt-2 p-3 bg-purple-500/10 rounded-xl text-sm text-purple-300 border border-purple-500/20">
                <i class="fas fa-user-tie text-purple-400 mr-2"></i><strong>সেবাদাতার নোট:</strong> <?= e($b['provider_note']) ?>
              </div>
              <?php endif; ?>
            </div>

            <!-- Footer -->
            <div class="booking-card-footer">
              <div class="flex items-center gap-2 flex-wrap">
                <a href="<?= SITE_URL ?>/provider.php?id=<?= $b['provider_id'] ?>" class="btn-action btn-outline">
                  <i class="fas fa-eye"></i> সেবাদাতা
                </a>
                <?php if ($b['status'] === 'Pending'): ?>
                  <a href="edit-booking.php?id=<?= $b['id'] ?>" class="btn-action btn-edit">
                    <i class="fas fa-edit"></i> সম্পাদনা
                  </a>
                  <a href="cancel-booking.php?id=<?= $b['id'] ?>" class="btn-action btn-danger" onclick="return confirm('বুকিং বাতিল করতে চান?')">
                    <i class="fas fa-times"></i> বাতিল
                  </a>
                <?php elseif ($b['status'] === 'Completed' && !$b['has_review']): ?>
                  <a href="review.php?booking=<?= $b['id'] ?>" class="btn-action btn-review">
                    <i class="fas fa-star"></i> রিভিউ দিন
                  </a>
                <?php endif; ?>
              </div>

              <?php if ($b['payment_method'] === 'cash_on_delivery' && $b['status'] === 'Completed' && !empty($b['payment_code'])): ?>
                <div class="payment-code-box">
                  <i class="fas fa-key text-emerald-400"></i>
                  <div>
                    <div class="text-[10px] text-emerald-400 uppercase font-bold tracking-wider">পেমেন্ট কোড</div>
                    <div class="font-mono text-lg font-bold text-white"><?= e($b['payment_code']) ?></div>
                  </div>
                </div>
              <?php endif; ?>

              <div class="text-xs text-slate-500">
                <i class="fas fa-clock mr-1"></i><?= date('d M Y, h:i A', strtotime($b['created_at'])) ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>

<script>
document.getElementById('menuToggle')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('-translate-x-full');
});
</script>

</body>
</html>
