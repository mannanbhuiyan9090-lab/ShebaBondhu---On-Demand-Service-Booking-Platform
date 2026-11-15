<?php
function renderAdminNotifScripts(): void {
    ?>
<script>
// Notification bell toggle
const notifBtn = document.getElementById('notifBtn');
const notifDropdown = document.getElementById('notifDropdown');

notifBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    notifDropdown.classList.toggle('hidden');
    if (!notifDropdown.classList.contains('hidden')) {
        loadNotifications();
    }
});

document.addEventListener('click', (e) => {
    if (!document.getElementById('notifContainer')?.contains(e.target)) {
        notifDropdown?.classList.add('hidden');
    }
});

function loadNotifications() {
    fetch('api.php?action=notifications')
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById('notifList');
            if (data.notifs && data.notifs.length > 0) {
                list.innerHTML = data.notifs.map(n => `
                    <a href="notifications.php" class="block p-3 hover:bg-slate-700/30 ${n.is_read ? '' : 'bg-slate-800/30'}">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-bell text-purple-400 mt-1"></i>
                            <div class="flex-1">
                                <div class="font-bold text-sm">${n.title}</div>
                                <div class="text-xs text-slate-400">${n.message}</div>
                                <div class="text-xs text-slate-500 mt-1">${new Date(n.created_at).toLocaleDateString('bn-BD')}</div>
                            </div>
                        </div>
                    </a>
                `).join('');
            } else {
                list.innerHTML = '<div class="p-4 text-center text-slate-400"><i class="fas fa-bell-slash text-2xl mb-2"></i><p class="text-sm">কোনো নোটিফিকেশন নেই</p></div>';
            }
        });
}

// Mark all read when bell clicked
notifBtn?.addEventListener('click', () => {
    fetch('api.php?action=mark_all_read', { method: 'POST' })
        .then(() => {
            const badge = document.getElementById('notifBadge');
            if (badge) badge.style.display = 'none';
            loadNotifications();
        });
});

// Auto-refresh every 1 minute
setInterval(() => {
    fetch('api.php?action=unread_notifs')
        .then(res => res.json())
        .then(data => {
            const badge = document.getElementById('notifBadge');
            if (data.unread > 0) {
                badge.textContent = data.unread;
                badge.style.display = 'flex';
            } else if (badge) {
                badge.style.display = 'none';
            }
        });
}, 60000);
</script>
    <?php
}