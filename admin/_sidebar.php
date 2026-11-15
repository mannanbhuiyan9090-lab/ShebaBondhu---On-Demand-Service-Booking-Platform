<?php
declare(strict_types=1);

function adminUnreadNotifications(): int
{
    try {
        $count = (int) db()->query("SELECT COUNT(*) FROM notification WHERE role_target='admin' AND is_read=0")->fetchColumn();
        return $count;
    } catch (Throwable $e) {
        return 0;
    }
}

function adminUnreadBookings(): int
{
    try {
        return (int) db()->query("SELECT COUNT(*) FROM notification WHERE role_target='admin' AND type='booking' AND is_read=0")->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

function adminNotificationBell(string $position = 'top'): void
{
    $unreadNotifs = adminUnreadNotifications();
    $wrapClass = $position === 'sidebar' ? 'notif-bell-top sidebar-bell' : 'notif-bell-top admin-top-bell';
    ?>
    <div class="<?= $wrapClass ?>">
      <button id="notifBtn" type="button" title="নোটিফিকেশন (<?= $unreadNotifs ?> অপঠিত)">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
          <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        <span id="notifBadgeTop" class="notif-badge-top" style="<?= $unreadNotifs > 0 ? '' : 'display:none' ?>"><?= $unreadNotifs ?></span>
      </button>
      <div class="notif-dropdown" id="notifDropdownTop">
        <div class="notif-dd-header">
          <strong>নোটিফিকেশন</strong>
          <button type="button" id="markAllNotifs" class="btn-xs btn-violet">সব পড়া</button>
        </div>
        <div id="notifListTop" class="notif-list-live">
          <div class="notif-empty">লোড হচ্ছে...</div>
        </div>
      </div>
    </div>
    <?php
}

function adminTopbar(string $title, string $subtitle = ''): void
{
    global $user;
    ?>
    <div class="admin-topbar">
      <div>
        <div class="admin-kicker">সেবা বন্ধু Admin</div>
        <h1><?= e($title) ?></h1>
        <?php if ($subtitle): ?><p><?= e($subtitle) ?></p><?php endif; ?>
      </div>
      <div class="admin-top-actions">
        <?php adminNotificationBell('top'); ?>
        <div class="admin-top-user">
          <span><?= strtoupper(mb_substr($user['name'] ?? 'AD', 0, 2)) ?></span>
          <div>
            <strong><?= e($user['name'] ?? 'Admin') ?></strong>
            <small>Super Admin</small>
          </div>
        </div>
      </div>
    </div>
    <?php
}

function adminNav(string $active = ''): void
{
    global $user, $s;
    $ap = (int)($s['ap'] ?? 0);
    $newBookings = adminUnreadBookings();
    ?>
<div class="dash-sidebar admin-sidebar-pro" id="adminSidebar">
  <div class="dash-user-block">
    <div class="dash-user-av" style="background:linear-gradient(135deg,#8B5CF6,#6D28D9)">
      <?= strtoupper(mb_substr($user['name'], 0, 2)) ?>
    </div>
    <div>
      <div class="dash-user-name"><?= e($user['name']) ?></div>
      <div class="dash-user-role" style="color:#A78BFA;font-size:12px;font-weight:700">সুপার অ্যাডমিন</div>
    </div>
  </div>

  <nav class="dash-nav">
    <a href="dashboard.php"    class="<?= $active==='dashboard'    ?'active':'' ?>"><span class="nav-ico">📊</span> ড্যাশবোর্ড</a>
    <a href="users.php"        class="<?= $active==='users'        ?'active':'' ?>"><span class="nav-ico">👥</span> ব্যবহারকারী</a>
    <a href="providers.php"    class="<?= $active==='providers'    ?'active':'' ?>"><span class="nav-ico">🔧</span> সেবাদাতা</a>
    <a href="bookings.php"     class="<?= $active==='bookings'     ?'active':'' ?>">
      <span class="nav-ico">📅</span> বুকিং
      <?php if ($newBookings > 0): ?><span class="notif-dot" style="background:#ef4444"><?= $newBookings ?></span><?php endif; ?>
    </a>
    <a href="payments.php"     class="<?= $active==='payments'     ?'active':'' ?>"><span class="nav-ico">💳</span> পেমেন্ট</a>
    <a href="services.php"     class="<?= $active==='services'     ?'active':'' ?>"><span class="nav-ico">🛠️</span> সেবা বিভাগ</a>
    <a href="reviews.php"      class="<?= $active==='reviews'      ?'active':'' ?>"><span class="nav-ico">⭐</span> গ্রাহক ফিডব্যাক</a>
    <a href="applications.php" class="<?= $active==='applications'  ?'active':'' ?>">
      <span class="nav-ico">📝</span> আবেদন
      <?php if ($ap > 0): ?><span class="notif-dot"><?= $ap ?></span><?php endif; ?>
    </a>
    <a href="analytics.php" class="<?= $active==='analytics' ?'active':'' ?>">
      <span class="nav-ico">📈</span> রিপোর্ট
    </a>
    <a href="notifications.php" class="<?= $active==='notifications' ?'active':'' ?>">
      <span class="nav-ico">🔔</span> নোটিফিকেশন
    </a>
    <a href="settings.php" class="<?= $active==='settings' ?'active':'' ?>">
      <span class="nav-ico">⚙️</span> সেটিংস
    </a>
    <a href="<?= SITE_URL ?>/index.php"><span class="nav-ico">🏠</span> সাইট দেখুন</a>
    <a href="<?= SITE_URL ?>/logout.php" class="logout"><span class="nav-ico">🚪</span> লগ আউট</a>
  </nav>
</div>
    <?php
}
