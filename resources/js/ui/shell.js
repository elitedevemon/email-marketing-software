import { toast } from './toast';

function setTheme(theme) {
  const root = document.documentElement;
  if (theme === 'dark') root.classList.add('dark');
  else root.classList.remove('dark');
  localStorage.setItem('theme', theme);
}

function initTheme() {
  const saved = localStorage.getItem('theme');
  if (saved === 'dark' || saved === 'light') {
    setTheme(saved);
    return;
  }
  // default: light (can be changed later to system)
  setTheme('light');
}

function toggleTheme() {
  const isDark = document.documentElement.classList.contains('dark');
  setTheme(isDark ? 'light' : 'dark');
}

function applySidebarCollapsed(isCollapsed) {
  const sidebar = document.getElementById('appSidebar');
  if (!sidebar) return;

  const labels = sidebar.querySelectorAll('.sidebar-label');
  if (isCollapsed) {
    sidebar.classList.remove('w-72');
    sidebar.classList.add('w-20');
    labels.forEach((el) => el.classList.add('hidden'));
  } else {
    sidebar.classList.remove('w-20');
    sidebar.classList.add('w-72');
    labels.forEach((el) => el.classList.remove('hidden'));
  }
}

function initSidebar() {
  const saved = localStorage.getItem('sidebar_collapsed') === '1';
  applySidebarCollapsed(saved);

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-sidebar-toggle]');
    if (!btn) return;
    const now = !(localStorage.getItem('sidebar_collapsed') === '1');
    localStorage.setItem('sidebar_collapsed', now ? '1' : '0');
    applySidebarCollapsed(now);
  });
}

function initModal() {
  document.addEventListener('click', (e) => {
    const openBtn = e.target.closest('[data-modal-open]');
    if (openBtn) {
      const id = openBtn.getAttribute('data-modal-open');
      const modal = document.querySelector(`[data-modal="${id}"]`);
      modal?.classList.remove('hidden');
      return;
    }
    const closeBtn = e.target.closest('[data-modal-close]');
    if (closeBtn) {
      const modal = e.target.closest('[data-modal]');
      modal?.classList.add('hidden');
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    const open = document.querySelector('[data-modal]:not(.hidden)');
    open?.classList.add('hidden');
  });
}

function initComingSoon() {
  document.addEventListener('click', (e) => {
    const el = e.target.closest('[data-coming-soon]');
    if (!el) return;
    e.preventDefault();
    toast(`${el.getAttribute('data-coming-soon')} — coming in next steps.`);
  });
}

export function initShell() {
  initTheme();
  initSidebar();
  initModal();
  initComingSoon();

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-theme-toggle]');
    if (!btn) return;
    toggleTheme();
    toast(`Theme switched to ${document.documentElement.classList.contains('dark') ? 'Dark' : 'Light'}.`);
  });
}