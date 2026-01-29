(() => {
  const $ = (s, el = document) => el.querySelector(s);

  const rowsEl = $('#rows');
  const metaEl = $('#meta');
  const searchEl = $('#search');
  const statusEl = $('#status');
  const prevBtn = $('#prev');
  const nextBtn = $('#next');

  const state = { page: 1, lastPage: 1, search: '', status: '' };

  function skeleton() {
    rowsEl.innerHTML = Array.from({ length: 10 }).map(() => `
      <tr>
        <td class="px-4 py-3"><div class="h-4 w-28 bg-[var(--surface-2)] rounded"></div></td>
        <td class="px-4 py-3"><div class="h-4 w-44 bg-[var(--surface-2)] rounded"></div></td>
        <td class="px-4 py-3"><div class="h-4 w-64 bg-[var(--surface-2)] rounded"></div></td>
        <td class="px-4 py-3"><div class="h-4 w-20 bg-[var(--surface-2)] rounded"></div></td>
        <td class="px-4 py-3"><div class="h-4 w-10 bg-[var(--surface-2)] rounded"></div></td>
        <td class="px-4 py-3"><div class="h-4 w-16 bg-[var(--surface-2)] rounded"></div></td>
        <td class="px-4 py-3"><div class="h-4 w-72 bg-[var(--surface-2)] rounded"></div></td>
      </tr>
    `).join('');
  }

  function badge(status) {
    const cls = status === 'success'
      ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/20'
      : status === 'failed'
        ? 'bg-red-500/15 text-red-300 border-red-500/20'
        : status === 'skipped'
          ? 'bg-amber-500/15 text-amber-300 border-amber-500/20'
          : 'bg-sky-500/15 text-sky-300 border-sky-500/20';
    return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs border ${cls}">${escapeHtml(status)}</span>`;
  }

  function render(items) {
    if (!items.length) {
      rowsEl.innerHTML = `<tr><td class="px-4 py-6 text-[var(--text-2)]" colspan="7">No logs found.</td></tr>`;
      return;
    }
    rowsEl.innerHTML = items.map(it => `
      <tr>
        <td class="px-4 py-3 text-[var(--text-2)]">${formatTime(it.created_at)}</td>
        <td class="px-4 py-3 text-[var(--text-1)]">${escapeHtml(it.to_email || '')}</td>
        <td class="px-4 py-3 text-[var(--text-2)]">${escapeHtml(it.subject || '')}</td>
        <td class="px-4 py-3">${badge(it.status)}</td>
        <td class="px-4 py-3 text-[var(--text-2)]">${it.attempt ?? ''}</td>
        <td class="px-4 py-3 text-[var(--text-2)]">${it.duration_ms ? (it.duration_ms + 'ms') : ''}</td>
        <td class="px-4 py-3 text-[var(--text-2)]">
          <div class="max-w-[520px] truncate" title="${escapeHtml(it.error_message || '')}">
            ${escapeHtml(it.error_message || '')}
          </div>
        </td>
      </tr>
    `).join('');
  }

  function setMeta(meta) {
    metaEl.textContent = `Page ${meta.current_page} of ${meta.last_page} • ${meta.total} total`;
    state.lastPage = meta.last_page;
    prevBtn.disabled = state.page <= 1;
    nextBtn.disabled = state.page >= state.lastPage;
    prevBtn.classList.toggle('opacity-50', prevBtn.disabled);
    nextBtn.classList.toggle('opacity-50', nextBtn.disabled);
  }

  async function load() {
    skeleton();
    const url = new URL('/app/ajax/sending/logs', window.location.origin);
    url.searchParams.set('page', String(state.page));
    if (state.search) url.searchParams.set('search', state.search);
    if (state.status) url.searchParams.set('status', state.status);

    const res = await window.App.fetchJson(url.toString());
    if (!res.ok) {
      window.App.toast(res.message || 'Failed to load', 'error');
      rowsEl.innerHTML = `<tr><td class="px-4 py-6 text-red-500" colspan="7">Load failed.</td></tr>`;
      return;
    }
    render(res.data.items || []);
    setMeta(res.data.meta || { current_page: 1, last_page: 1, total: 0 });
  }

  let t = null;
  searchEl?.addEventListener('input', () => {
    window.clearTimeout(t);
    t = window.setTimeout(() => {
      state.search = (searchEl.value || '').trim();
      state.page = 1;
      load();
    }, 300);
  });

  statusEl?.addEventListener('change', () => {
    state.status = statusEl.value || '';
    state.page = 1;
    load();
  });

  prevBtn?.addEventListener('click', () => {
    if (state.page > 1) { state.page--; load(); }
  });
  nextBtn?.addEventListener('click', () => {
    if (state.page < state.lastPage) { state.page++; load(); }
  });

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, (c) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));
  }
  function formatTime(iso) {
    try { return new Date(iso).toLocaleString(); } catch { return iso || ''; }
  }

  load();
})();