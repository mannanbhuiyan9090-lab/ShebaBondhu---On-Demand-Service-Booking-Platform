<?php
require_once __DIR__ . '/../includes/config.php';
sessionStart();
requireAuth('provider');
$user = auth();

$prov = db()->prepare("SELECT * FROM providers WHERE user_id = ?");
$prov->execute([$user['id']]);
$provider = $prov->fetch();

if (!$provider) {
  header('Location: dashboard.php');
  exit;
}

$status = $_GET['status'] ?? '';
$where = 'b.provider_id = ?';
$params = [$provider['id']];
if ($status) {
  $where .= ' AND b.status = ?';
  $params[] = $status;
}

$stmt = db()->prepare("SELECT b.*, u.name AS customer_name, u.phone AS customer_phone, u.email AS customer_email FROM bookings b JOIN users u ON u.id = b.customer_id WHERE $where ORDER BY b.preferred_date ASC, b.created_at DESC");
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$stats = db()->prepare("SELECT 
  COUNT(*) as total_count,
  SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
  SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_count,
  SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved_count
FROM bookings WHERE provider_id = ?");
$stats->execute([$provider['id']]);
$stats = $stats->fetch();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>বুকিং সমূহ - সেবা বন্ধু প্রোভাইডার</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Hind Siliguri', sans-serif; }
    .glass-dark { background: rgba(88, 28, 135, 0.6); backdrop-filter: blur(20px); }
    .card { background: rgba(88, 28, 135, 0.3); backdrop-filter: blur(20px); border: 1px solid rgba(167, 139, 250, 0.3); border-radius: 1.5rem; }
    .sidebar-link { transition: all 0.2s ease; }
    .sidebar-link:hover { background: rgba(71, 85, 105, 0.3); transform: translateX(4px); }
    .sidebar-link.active { background: linear-gradient(90deg, rgba(167, 139, 250, 0.3), rgba(45, 212, 191, 0.1)); border-left: 3px solid #a78bfa; }
    .gradient-text { background: linear-gradient(135deg, #a78bfa, #2dd4bf); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
    .btn-action { padding: 8px 16px; border-radius: 12px; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px; }
    .btn-primary { background: linear-gradient(135deg, #a78bfa, #2dd4bf); color: white; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(167, 139, 250, 0.4); }
    .btn-success { background: rgba(16, 185, 129, 0.2); color: #10B981; }
    .btn-success:hover { background: rgba(16, 185, 129, 0.3); }
    .btn-danger { background: rgba(239, 68, 68, 0.2); color: #EF4444; }
    .btn-danger:hover { background: rgba(239, 68, 68, 0.3); }
    .filter-btn { padding: 8px 16px; border-radius: 12px; font-weight: 600; transition: all 0.2s; background: rgba(71, 85, 105, 0.3); color: white; }
    .filter-btn:hover { background: rgba(71, 85, 105, 0.5); }
    .filter-btn.active { background: linear-gradient(135deg, #a78bfa, #2dd4bf); }
  </style>
</head>
<body class="bg-gradient-to-br from-purple-900 via-slate-900 to-teal-900 text-white min-h-screen">

<aside id="sidebar" class="w-64 bg-gradient-to-b from-purple-900/60 to-teal-900/40 h-screen fixed left-0 top-0 border-r border-purple-500/30 z-50 flex flex-col">
  <div class="p-6 border-b border-purple-500/30">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-teal-500 rounded-xl flex items-center justify-center shadow-lg">
        <i class="fas fa-bolt text-white"></i>
      </div>
      <div>
        <div class="text-xl font-bold gradient-text">সেবা বন্ধু</div>
        <div class="text-xs text-slate-500 uppercase tracking-wider">সেবাদাতা প্যানেল</div>
      </div>
    </div>
  </div>
  
  <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
    <a href="dashboard.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-gauge-high w-5 text-orange-400"></i>
      <span>ড্যাশবোর্ড</span>
    </a>
    <a href="bookings.php" class="sidebar-link active flex items-center gap-3 p-3 rounded-xl text-white">
      <i class="fas fa-calendar-check w-5"></i>
      <span>বুকিং সমূহ</span>
    </a>
    <a href="services.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-tools w-5 text-orange-400"></i>
      <span>আমার সেবা</span>
    </a>
    <a href="profile.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-user w-5"></i>
      <span>প্রোফাইল</span>
    </a>
    <a href="availability.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl text-slate-400 hover:text-white">
      <i class="fas fa-toggle-on w-5"></i>
      <span>উপলব্ধতা</span>
    </a>
  </nav>

  <?php if ($provider): ?>
    <div class="p-4 border-t border-slate-700/50">
      <div class="p-3 rounded-xl <?= $provider['available'] ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400' ?> text-center">
        <div class="flex items-center justify-center gap-2 font-bold">
          <span class="w-2 h-2 rounded-full <?= $provider['available'] ? 'bg-emerald-400 pulse-dot' : 'bg-red-400' ?>"></span>
          <?= $provider['available'] ? 'এখন উপলব্ধ' : 'এখন অনুপলব্ধ' ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="p-4 border-t border-slate-700/50">
    <a href="<?= SITE_URL ?>/logout.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-red-500/10 text-slate-400 hover:text-red-400 transition-all group">
      <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-teal-500 rounded-xl flex items-center justify-center font-bold text-sm shadow-lg">
        <?= strtoupper(mb_substr($user['name'], 0, 2)) ?>
      </div>
      <div class="flex-1">
        <div class="font-semibold text-sm"><?= e(explode(' ', $user['name'])[0]) ?></div>
        <div class="text-xs text-orange-400">সেবাদাতা</div>
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
          <i class="fas fa-calendar-check mr-2 text-orange-400"></i> বুকিং সমূহ
        </h1>
      </div>
      <div class="md:hidden flex items-center gap-2">
        <span class="text-lg font-bold gradient-text">সেবা বন্ধু</span>
      </div>
    </div>
  </header>

  <main class="p-4 md:p-8 max-w-7xl mx-auto">
    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div class="stat-card card p-5 text-center cursor-pointer hover:border-orange-500/50">
        <div class="text-2xl font-bold"><?= number_format($stats['total_count'] ?? 0) ?></div>
        <div class="text-slate-400 text-sm">মোট</div>
      </div>
      <div class="stat-card card p-5 text-center cursor-pointer hover:border-amber-500/50">
        <div class="text-2xl font-bold text-amber-400"><?= number_format($stats['pending_count'] ?? 0) ?></div>
        <div class="text-slate-400 text-sm">অপেক্ষমান</div>
      </div>
      <div class="stat-card card p-5 text-center cursor-pointer hover:border-emerald-500/50">
        <div class="text-2xl font-bold text-emerald-400"><?= number_format($stats['completed_count'] ?? 0) ?></div>
        <div class="text-slate-400 text-sm">সম্পন্ন</div>
      </div>
      <div class="stat-card card p-5 text-center cursor-pointer hover:border-blue-500/50">
        <div class="text-2xl font-bold text-blue-400"><?= number_format($stats['approved_count'] ?? 0) ?></div>
        <div class="text-slate-400 text-sm">অনুমোদিত</div>
      </div>
    </div>

    <div class="card overflow-hidden">
      <div class="p-6 border-b border-slate-700/50">
        <div class="flex flex-wrap gap-2">
          <?php
          $filters = [
            '' => 'সব',
            'Pending' => '⏳ অপেক্ষমান',
            'Approved' => '✓ অনুমোদিত',
            'Completed' => '🏆 সম্পন্ন',
            'Cancelled' => '✕ বাতিল'
          ];
          foreach ($filters as $v => $lbl):
          ?>
            <a href="?status=<?= $v ?>" class="filter-btn <?= $status === $v ? 'active' : '' ?>">
              <?= $lbl ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <?php if (empty($bookings)): ?>
        <div class="p-12 text-center">
          <div class="w-20 h-20 bg-slate-800/50 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-calendar-xmark text-4xl text-slate-600"></i>
          </div>
          <h3 class="text-xl font-bold mb-2">কোনো বুকিং নেই</h3>
          <p class="text-slate-500">এই তালিকায় কোনো বুকিং পাওয়া যায়নি।</p>
        </div>
      <?php else: ?>
        <div class="divide-y divide-slate-700/30">
          <?php foreach ($bookings as $b): ?>
            <div class="p-6 hover:bg-slate-800/30 transition-all">
              <div class="flex flex-col lg:flex-row lg:items-start gap-4">
                <div class="flex items-center gap-4 flex-1">
                  <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-teal-500 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-user text-white text-xl"></i>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                      <span class="font-mono text-orange-400 bg-orange-500/10 px-3 py-1 rounded-lg text-sm font-bold">
                        <?= e($b['ref_code']) ?>
                      </span>
                      <?php
                      $statusConfig = [
                        'Completed' => ['bg-emerald-500/20 text-emerald-400', '✓ সম্পন্ন'],
                        'Approved' => ['bg-blue-500/20 text-blue-400', '✓ অনুমোদিত'],
                        'Pending' => ['bg-amber-500/20 text-amber-400', '⏳ অপেক্ষমান'],
                        'Cancelled' => ['bg-red-500/20 text-red-400', '✕ বাতিল'],
                      ];
                      $config = $statusConfig[$b['status']] ?? ['bg-slate-500/20 text-slate-400', $b['status']];
                      ?>
                      <span class="px-3 py-1.5 rounded-lg text-xs font-bold <?= $config[0] ?>"><?= $config[1] ?></span>
                    </div>
                    <div class="font-bold text-lg mb-1"><?= e($b['customer_name']) ?></div>
                    <div class="flex flex-wrap gap-4 text-sm text-slate-500">
                      <span><i class="fas fa-phone mr-1"></i><?= e($b['customer_phone'] ?? 'N/A') ?></span>
                      <span><i class="fas fa-envelope mr-1"></i><?= e($b['customer_email'] ?? 'N/A') ?></span>
                    </div>
                  </div>
                </div>
                
                <div class="flex flex-col lg:items-end gap-3">
                  <div class="text-right">
                    <div class="text-sm text-slate-400">তারিখ ও সময়</div>
                    <div class="font-semibold"><?= date('d M Y', strtotime($b['preferred_date'])) ?></div>
                    <div class="text-sm text-slate-500"><?= substr($b['time_start'], 0, 5) ?> – <?= substr($b['time_end'], 0, 5) ?></div>
                  </div>
                  
                  <?php 
                    $totalAmount = $b['amount'] ?? $b['estimated_price'] ?? 0;
                    $serviceCharge = $totalAmount > 0 ? (int)($totalAmount * 15 / 100) : 0;
                    $revenue = $totalAmount - $serviceCharge;
                  ?>
                  
                  <div class="text-right">
                    <div class="text-sm text-slate-400">সার্ভিস চার্জ (১৫%)</div>
                    <div class="font-bold text-orange-400 text-lg">-৳ <?= number_format($serviceCharge) ?></div>
                  </div>
                  
                  <div class="text-right">
                    <div class="text-sm text-slate-400">আপনার আয়</div>
                    <div class="font-bold text-purple-400 text-lg">৳ <?= number_format($revenue) ?></div>
                  </div>
                  
                  <div class="text-right">
                    <div class="text-sm text-slate-400">মোট মূল্য</div>
                    <div class="font-bold text-emerald-400 text-lg">৳ <?= number_format($totalAmount) ?></div>
                  </div>
                  
                  <?php if ($b['address']): ?>
                    <div class="text-right">
                      <div class="text-sm text-slate-400">ঠিকানা</div>
                      <div class="text-sm"><?= e($b['address']) ?></div>
                    </div>
                  <?php endif; ?>
                  
                  <?php if ($b['description']): ?>
                    <div class="text-right">
                      <div class="text-sm text-slate-400">বিবরণ</div>
                      <div class="text-sm"><?= e($b['description']) ?></div>
                    </div>
                  <?php endif; ?>
                  
                  <div class="flex items-center gap-2 mt-2">
                    <?php if ($b['status'] === 'Pending'): ?>
                      <a href="update-booking.php?action=approve&id=<?= $b['id'] ?>" class="btn-action btn-success" onclick="return confirm('বুকিং অনুমোদন করবেন?')">
                        <i class="fas fa-check"></i> অনুমোদন
                      </a>
                      <a href="update-booking.php?action=cancel&id=<?= $b['id'] ?>" class="btn-action btn-danger" onclick="return confirm('বুকিং বাতিল করবেন?')">
                        <i class="fas fa-times"></i> বাতিল
                      </a>
                    <?php elseif ($b['status'] === 'Approved'): ?>
                      <button type="button" class="btn-action btn-primary" onclick="showAmountModal(<?= $b['id'] ?>)">
                        <i class="fas fa-check-double"></i> সম্পন্ন
                      </button>
                    <?php elseif ($b['status'] === 'Completed'): ?>
                      <button type="button" class="btn-action bg-blue-500/20 text-blue-400" onclick="showEditAmountModal(<?= $b['id'] ?>, <?= $b['amount'] ?? 0 ?>)">
                        <i class="fas fa-edit"></i> অ্যামাউন্ট এডিট
                      </button>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>
</div>

<script>
document.getElementById('menuToggle')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('-translate-x-full');
});

function showAmountModal(bookingId) {
  const modal = document.createElement('div');
  modal.id = 'amountModal';
  modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4';
  modal.innerHTML = `
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeAmountModal()"></div>
    <div class="relative bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-md p-6 shadow-2xl">
      <button onclick="closeAmountModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white"><i class="fas fa-times text-xl"></i></button>
      <h3 class="text-xl font-bold mb-4 flex items-center gap-2"><i class="fas fa-money-bill-wave text-emerald-400"></i> অ্যামাউন্ট যোগ করুন</h3>
      <form method="POST" action="update-booking.php?action=complete&id=${bookingId}">
        <div class="mb-4">
          <label class="block text-sm font-semibold text-slate-300 mb-2">সার্ভিস অ্যামাউন্ট (৳)</label>
          <input type="number" name="amount" step="0.01" min="0" placeholder="0.00" required class="w-full p-3 text-black" autofocus>
        </div>
        <p class="text-xs text-slate-500 mb-4">সার্ভিস সম্পন্ন হওয়ার পর অ্যামাউন্ট যোগ করুন।</p>
        <div class="flex gap-3">
          <button type="submit" class="flex-1 btn-action btn-primary justify-center">
            <i class="fas fa-check"></i> সম্পন্ন করুন
          </button>
          <button type="button" onclick="closeAmountModal()" class="flex-1 btn-action bg-slate-700/50 text-slate-300 justify-center">
            বাতিল
          </button>
        </div>
      </form>
    </div>
  `;
  document.body.appendChild(modal);
}

function closeAmountModal() {
  document.getElementById('amountModal')?.remove();
}

function showEditAmountModal(bookingId, currentAmount) {
  const modal = document.createElement('div');
  modal.id = 'editAmountModal';
  modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4';
  modal.innerHTML = `
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeEditAmountModal()"></div>
    <div class="relative bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-md p-6 shadow-2xl">
      <button onclick="closeEditAmountModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white"><i class="fas fa-times text-xl"></i></button>
      <h3 class="text-xl font-bold mb-4 flex items-center gap-2"><i class="fas fa-edit text-blue-400"></i> অ্যামাউন্ট এডিট করুন</h3>
      <form method="POST" action="update-amount.php">
        <input type="hidden" name="id" value="${bookingId}">
        <div class="mb-4">
          <label class="block text-sm font-semibold text-slate-300 mb-2">নতুন অ্যামাউন্ট (৳)</label>
          <input type="number" name="amount" step="0.01" min="0" value="${currentAmount}" style="color: black;" placeholder="0.00" required class="w-full p-3" autofocus>
        </div>
        <p class="text-xs text-slate-500 mb-4">আপনি অ্যামাউন্ট পরিবর্তন করতে পারবেন।</p>
        <div class="flex gap-3">
          <button type="submit" class="flex-1 btn-action btn-primary justify-center">
            <i class="fas fa-save"></i> সেভ করুন
          </button>
          <button type="button" onclick="closeEditAmountModal()" class="flex-1 btn-action bg-slate-700/50 text-slate-300 justify-center">
            বাতিল
          </button>
        </div>
      </form>
    </div>
  `;
  document.body.appendChild(modal);
}

function closeEditAmountModal() {
  document.getElementById('editAmountModal')?.remove();
}
</script>

</body>
</html>
