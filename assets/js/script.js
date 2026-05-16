/**
 * Ambulance Locator - Main JavaScript
 * Handles: navbar scroll, page loader, AJAX requests,
 *          form validation, sidebar toggle, animations
 */

/* ============================================================
   PAGE LOADER
   ============================================================ */
window.addEventListener('load', () => {
  const loader = document.getElementById('pageLoader');
  if (loader) {
    setTimeout(() => loader.classList.add('hidden'), 400);
  }
});

/* ============================================================
   NAVBAR SCROLL EFFECT
   ============================================================ */
const navbar = document.getElementById('mainNavbar');
if (navbar) {
  window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 50);
  });
}

/* ============================================================
   SCROLL ANIMATIONS (fade-up)
   ============================================================ */
const observerOptions = { threshold: 0.15 };
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      observer.unobserve(entry.target);
    }
  });
}, observerOptions);

document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

/* ============================================================
   COUNTER ANIMATION (stats section)
   ============================================================ */
function animateCounter(el) {
  const target = parseInt(el.dataset.target, 10);
  const duration = 1800;
  const step = target / (duration / 16);
  let current = 0;

  const timer = setInterval(() => {
    current += step;
    if (current >= target) {
      el.textContent = target.toLocaleString() + (el.dataset.suffix || '');
      clearInterval(timer);
    } else {
      el.textContent = Math.floor(current).toLocaleString() + (el.dataset.suffix || '');
    }
  }, 16);
}

const counterObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      animateCounter(entry.target);
      counterObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.5 });

document.querySelectorAll('[data-target]').forEach(el => counterObserver.observe(el));

/* ============================================================
   ADMIN / USER SIDEBAR TOGGLE
   ============================================================ */
const sidebarToggleBtn  = document.getElementById('sidebarToggle');
const adminSidebar      = document.getElementById('adminSidebar');
const sidebarOverlay    = document.getElementById('sidebarOverlay');
const sidebarCloseBtn   = document.getElementById('sidebarClose');

function openSidebar() {
  adminSidebar?.classList.add('open');
  sidebarOverlay?.classList.add('show');
  document.body.style.overflow = 'hidden';
}

function closeSidebar() {
  adminSidebar?.classList.remove('open');
  sidebarOverlay?.classList.remove('show');
  document.body.style.overflow = '';
}

sidebarToggleBtn?.addEventListener('click', openSidebar);
sidebarCloseBtn?.addEventListener('click', closeSidebar);
sidebarOverlay?.addEventListener('click', closeSidebar);

/* ============================================================
   FORM VALIDATION (generic)
   ============================================================ */
document.querySelectorAll('form[data-validate]').forEach(form => {
  form.addEventListener('submit', function (e) {
    if (!form.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
    }
    form.classList.add('was-validated');
  });
});

/* ============================================================
   AJAX HELPER
   ============================================================ */
/**
 * sendAjax(url, data, successCb, errorCb)
 * Sends a POST request with JSON body and calls callbacks.
 */
function sendAjax(url, data, successCb, errorCb) {
  const formData = new FormData();
  Object.entries(data).forEach(([k, v]) => formData.append(k, v));

  fetch(url, { method: 'POST', body: formData })
    .then(res => res.json())
    .then(json => successCb(json))
    .catch(err => {
      console.error('AJAX error:', err);
      if (errorCb) errorCb(err);
    });
}

/* ============================================================
   AMBULANCE REQUEST (user panel)
   ============================================================ */
function requestAmbulance(ambulanceId) {
  const location = document.getElementById('userLocation')?.value?.trim();
  if (!location) {
    showToast('Please enter your location first.', 'warning');
    return;
  }

  const btn = document.querySelector(`[data-amb-id="${ambulanceId}"]`);
  if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Requesting...'; }

  sendAjax('request_handler.php', { ambulance_id: ambulanceId, location },
    (res) => {
      if (res.success) {
        showToast('Ambulance requested successfully! Help is on the way.', 'success');
        if (btn) { btn.innerHTML = '<i class="fas fa-check me-1"></i>Requested'; btn.classList.replace('btn-danger', 'btn-success'); }
      } else {
        showToast(res.message || 'Request failed. Try again.', 'danger');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-phone me-1"></i>Request'; }
      }
    },
    () => {
      showToast('Network error. Please try again.', 'danger');
      if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-phone me-1"></i>Request'; }
    }
  );
}

/* ============================================================
   SEARCH FILTER (ambulance list)
   ============================================================ */
const searchInput = document.getElementById('ambulanceSearch');
if (searchInput) {
  searchInput.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.ambulance-card-wrap').forEach(card => {
      const text = card.textContent.toLowerCase();
      card.style.display = text.includes(q) ? '' : 'none';
    });
  });
}

/* ============================================================
   STATUS FILTER (admin requests table)
   ============================================================ */
const statusFilter = document.getElementById('statusFilter');
if (statusFilter) {
  statusFilter.addEventListener('change', function () {
    const val = this.value.toLowerCase();
    document.querySelectorAll('tbody tr[data-status]').forEach(row => {
      row.style.display = (!val || row.dataset.status === val) ? '' : 'none';
    });
  });
}

/* ============================================================
   TOAST NOTIFICATION
   ============================================================ */
function showToast(message, type = 'info') {
  const colors = {
    success: '#10b981',
    danger:  '#ff3b3b',
    warning: '#f59e0b',
    info:    '#2563eb'
  };

  const toast = document.createElement('div');
  toast.style.cssText = `
    position:fixed; bottom:24px; right:24px; z-index:9999;
    background:${colors[type] || colors.info};
    color:#fff; padding:.85rem 1.5rem;
    border-radius:10px; font-weight:600; font-size:.92rem;
    box-shadow:0 8px 30px rgba(0,0,0,.25);
    transform:translateY(20px); opacity:0;
    transition:all .3s ease; max-width:340px;
  `;
  toast.textContent = message;
  document.body.appendChild(toast);

  requestAnimationFrame(() => {
    toast.style.transform = 'translateY(0)';
    toast.style.opacity   = '1';
  });

  setTimeout(() => {
    toast.style.transform = 'translateY(20px)';
    toast.style.opacity   = '0';
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}

/* ============================================================
   CONFIRM DELETE
   ============================================================ */
document.querySelectorAll('[data-confirm]').forEach(btn => {
  btn.addEventListener('click', function (e) {
    if (!confirm(this.dataset.confirm || 'Are you sure?')) {
      e.preventDefault();
    }
  });
});

/* ============================================================
   AUTO-DISMISS ALERTS
   ============================================================ */
document.querySelectorAll('.alert-auto-dismiss').forEach(alert => {
  setTimeout(() => {
    alert.style.transition = 'opacity .5s';
    alert.style.opacity = '0';
    setTimeout(() => alert.remove(), 500);
  }, 4000);
});

/* ============================================================
   ADMIN: AJAX STATUS UPDATE (requests table)
   ============================================================ */
document.querySelectorAll('.status-select').forEach(sel => {
  sel.addEventListener('change', function () {
    const requestId = this.dataset.id;
    const newStatus = this.value;

    sendAjax('update_status.php', { id: requestId, status: newStatus },
      (res) => {
        showToast(res.success ? 'Status updated.' : (res.message || 'Update failed.'),
                  res.success ? 'success' : 'danger');
      }
    );
  });
});
