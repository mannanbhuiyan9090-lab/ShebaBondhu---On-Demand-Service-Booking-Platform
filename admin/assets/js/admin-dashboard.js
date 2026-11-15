document.addEventListener('DOMContentLoaded', function() {
  // Notification system
  const notifBtn = document.getElementById('notifBtn');
  const notifDropdown = document.getElementById('notifDropdownTop');
  const notifList = document.getElementById('notifListTop');
  
  if (notifBtn) {
    notifBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      notifDropdown.classList.toggle('open');
    });
    
    // Close on outside click
    document.addEventListener('click', () => notifDropdown.classList.remove('open'));
    
    // Load notifications
    loadNotifications();
    
    // Auto refresh every 30s
    setInterval(loadNotifications, 30000);
  }

  // Booking actions AJAX
  window.BookingActions = {
    setStatus: async (id, status, select) => {
      const btnText = select.options[select.selectedIndex].text;
      select.disabled = true;
      select.innerHTML = '<option>আপডেট হচ্ছে...</option>';
      
      try {
        const res = await fetch('api.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=booking_status&id=${id}&status=${encodeURIComponent(status)}&_csrf=${document.querySelector('input[name="_csrf"]').value}`
        });
        const data = await res.json();
        
        if (data.ok) {
          select.className = `ajax-status-select status-${status.toLowerCase()}`;
          select.innerHTML = `<option>${btnText}</option>`;
          showToast(data.msg, 'success');
        } else {
          alert(data.msg);
          select.value = select.dataset.current;
        }
      } catch (e) {
        alert('Network error');
      } finally {
        select.disabled = false;
      }
    },
    
    quickAction: async (id, status, btn) => {
      if (!confirm(`বুকিং ${status} করবেন?`)) return;
      
      btn.disabled = true;
      btn.textContent = 'হচ্ছে...';
      
      try {
        const res = await fetch('api.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=booking_status&id=${id}&status=${status}&_csrf=${document.querySelector('input[name="_csrf"]').value}`
        });
        const data = await res.json();
        
        if (data.ok) {
          btn.closest('td').innerHTML = `<span class="badge badge-${status.toLowerCase()}">${data.statusBn}</span>`;
          showToast(data.msg, 'success');
        } else {
          alert(data.msg);
        }
      } catch (e) {
        alert('Network error');
      } finally {
        btn.disabled = false;
        btn.textContent = btn.dataset.originalText || btn.textContent;
      }
    },
    
    deleteBooking: async (id, btn) => {
      if (!confirm('বুকিং মুছবেন?')) return;
      
      btn.disabled = true;
      btn.innerHTML = 'হচ্ছে...';
      
      try {
        const res = await fetch('api.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=booking_delete&id=${id}&_csrf=${document.querySelector('input[name="_csrf"]').value}`
        });
        const data = await res.json();
        
        if (data.ok) {
          btn.closest('tr').style.animation = 'fadeOut 0.3s';
          setTimeout(() => btn.closest('tr').remove(), 300);
          showToast(data.msg, 'success');
        } else {
          alert(data.msg);
        }
      } catch (e) {
        alert('Network error');
      } finally {
        btn.disabled = false;
        btn.innerHTML = '🗑️';
      }
    }
  };

  // Notification functions
  async function loadNotifications() {
    try {
      const res = await fetch('api.php?action=notifications');
      const data = await res.json();
      
      if (data.ok) {
        notifList.innerHTML = data.items.map(notif => `
          <div class="notif-item ${notif.is_read ? 'read' : 'unread'}">
            <div class="notif-title">${notif.title}</div>
            <div class="notif-msg">${notif.message}</div>
            <div class="notif-time">${new Date(notif.created_at).toLocaleString('bn-BD')}</div>
            ${notif.link ? `<a href="${notif.link}" class="notif-link">→ দেখুন</a>` : ''}
          </div>
        `).join('') || '<div class="notif-empty">নতুন নোটিফিকেশন নেই</div>';
        
        document.getElementById('notifBadgeTop').style.display = data.unread > 0 ? 'block' : 'none';
        document.getElementById('notifBadgeTop').textContent = data.unread;
      }
    } catch (e) {
      console.error('Notifications load failed');
    }
  }

  // Mark all read
  document.getElementById('markAllNotifs')?.addEventListener('click', async () => {
    try {
      const res = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=mark_read&id=all&_csrf=' + document.querySelector('input[name="_csrf"]').value
      });
      await res.json();
      loadNotifications();
      showToast('সব নোটিফিকেশন পড়া চিহ্নিত', 'success');
    } catch (e) {}
  });

  // Stats animation
  const animateNumbers = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const val = entry.target.textContent.replace(/,/g, '');
        const target = parseInt(val);
        let start = 0;
        const duration = 1500;
        const increment = target / (duration / 16);
        
        const timer = setInterval(() => {
          start += increment;
          if (start >= target) {
            entry.target.textContent = target.toLocaleString();
            clearInterval(timer);
          } else {
            entry.target.textContent = Math.floor(start).toLocaleString();
          }
        }, 16);
        animateNumbers.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });

  document.querySelectorAll('.kpi-val').forEach(el => animateNumbers.observe(el));

  // Toast notifications
  window.showToast = (msg, type = 'success') => {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = msg;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 100);
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  };

  // Responsive sidebar toggle
  const sidebarToggle = document.createElement('button');
  sidebarToggle.innerHTML = '☰';
  sidebarToggle.className = 'sidebar-toggle';
  document.querySelector('.admin-topbar')?.prepend(sidebarToggle);
  sidebarToggle.addEventListener('click', () => document.querySelector('.dash-sidebar').classList.toggle('open'));
});

// CSS for this JS (injected)
const style = document.createElement('style');
style.textContent = `
  .notif-dropdown { 
    position: absolute; top: 100%; right: 0; background: hsl(var(--bg-card)); 
    border: 1px solid hsl(var(--border-color)); border-radius: var(--r-lg); 
    box-shadow: hsl(var(--shadow-lg)); min-width: 320px; max-height: 400px; overflow-y: auto; 
    opacity: 0; visibility: hidden; transform: translateY(-10px); transition: all 0.2s; z-index: 100;
  }
  .notif-dropdown.open { opacity: 1; visibility: visible; transform: translateY(0); }
  .notif-item { padding: 1rem; border-bottom: 1px solid hsl(var(--border-color)/0.5); cursor: pointer; transition: background 0.2s; }
  .notif-item:hover { background: hsl(var(--violet)/0.05); }
  .notif-item.unread { font-weight: 500; }
  .notif-title { font-weight: 600; margin-bottom: 0.25rem; }
  .notif-msg { color: hsl(var(--text-secondary)); font-size: 0.875rem; margin-bottom: 0.5rem; }
  .notif-time { font-size: 0.75rem; color: hsl(var(--text-muted)); }
  .toast { 
    position: fixed; top: 20px; right: 20px; padding: 1rem 1.5rem; border-radius: var(--r-lg); 
    color: white; font-weight: 500; box-shadow: hsl(var(--shadow-lg)); transform: translateX(400px);
    z-index: 1000; 
  }
  .toast-success { background: hsl(var(--approved)/0.95); }
  .toast-error { background: hsl(var(--cancelled)/0.95); }
  .toast.show { transform: translateX(0); }
  .sidebar-toggle { 
    display: none; background: none; border: none; font-size: 1.5rem; 
    color: hsl(var(--text-primary)); cursor: pointer; padding: 0.5rem; 
    border-radius: var(--r-sm);
  }
  @media (max-width: 1024px) { .sidebar-toggle { display: block; } }
  .dash-sidebar.open { transform: translateX(0) !important; }
`;
document.head.appendChild(style);

