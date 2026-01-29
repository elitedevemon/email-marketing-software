(() => {
  const $ = (s, el = document) => el.querySelector(s);
  const rowsEl = $('#rows');
  const metaEl = $('#meta');
  const searchEl = $('#search');
  const typeEl = $('#type');
  const fromEl = $('#from');
  const toEl = $('#to');
  const prevBtn = $('#prev');
  const nextBtn = $('#next');

  const mOpens = $('#mOpens');
  const mClicks = $('#mClicks');
  const mLinks = $('#mLinks');

  const state = { page: 1, lastPage: 1, search: '', type: '', from: '', to: '' };

  function skeleton() {
    rowsEl.innerHTML = Array.from({ length: 10 }).map(() => `
      <tr>
        <td class="px-4 py-3"><div class="h-4 w-28 bg-[var(--surface-2)] rounded"></div></td>
        <td class="px-4 py-3"><div class="h-4 w-16 bg-[var(--surface-2)] rounded"></div></td>
        <td class="px-4 py-3"><div class="h-4 w-56 bg-[var(--surface-2)] rounded"></div></td>
        <td class="px-4 py-3"><div class="h-4 w-24 bg-[var(--surface-2)] rounded"></div></td>
        <td class="px-4 py-3"><div class="h-4 w-64 bg-[var(--surface-2)] rounded"></div></td>
      </tr>
    `).join('');
  }

  function badge(type) {
    const cls = type === 'open'
      ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/20'
      : 'bg-sky-500/15 text-sky-300 border-sky-500/20';
    return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs border ${cls}">${escapeHtml(type)}</span>`;
  }

  function render(items) {
    if (!items.length) {
      rowsEl.innerHTML = `<tr><td class="px-4 py-6 text-[var(--text-2)]" colspan="5">No events found.</td></tr>`;
      return;
    }
    rowsEl.innerHTML = items.map(it => `
      <tr class="hover:bg-[var(--surface-2)]">
        <td class="px-4 py-3 text-[var(--text-2)]">${formatTime(it.occurred_at || it.created_at)}</td>
        <td class="px-4 py-3">${badge(it.type)}</td>
        <td class="px-4 py-3">
          <button class="text-left text-[var(--primary)] hover:underline"
                  data-action="open-outbound" data-uuid="${escapeHtml(it.outbound_uuid || '')}">
            <span class="font-mono text-xs">${escapeHtml((it.outbound_uuid || '').slice(0, 18))}…</span>
          </button>
        </td>
        <td class="px-4 py-3 text-[var(--text-2)] font-mono text-xs">${escapeHtml(it.ip || '')}</td>
        <td class="px-4 py-3 text-[var(--text-2)]">
          <div class="max-w-[520px] truncate" title="${escapeHtml(it.user_agent || '')}">
            ${escapeHtml(it.user_agent || '')}
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
    const url = new URL('/app/ajax/tracking/events', window.location.origin);
    url.searchParams.set('page', String(state.page));
    if (state.search) url.searchParams.set('search', state.search);
    if (state.type) url.searchParams.set('type', state.type);
    if (state.from) url.searchParams.set('from', state.from);
    if (state.to) url.searchParams.set('to', state.to);

    const res = await window.App.fetchJson(url.toString());
    if (!res.ok) {
      window.App.toast(res.message || 'Failed to load', 'error');
      rowsEl.innerHTML = `<tr><td class="px-4 py-6 text-red-500" colspan="5">Load failed.</td></tr>`;
      return;
    }
    render(res.data.items || []);
    setMeta(res.data.meta || { current_page: 1, last_page: 1, total: 0 });
  }

  rowsEl?.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-action="open-outbound"]');
    if (!btn) return;
    const uuid = btn.getAttribute('data-uuid') || '';
    if (!uuid) return;

    mOpens.textContent = '…';
    mClicks.textContent = '…';
    mLinks.innerHTML = `<tr><td class="px-4 py-3 text-[var(--text-2)]" colspan="2">Loading…</td></tr>`;
    window.App.modal.open('modalOutboundTracking');

    const url = new URL(`/app/ajax/tracking/outbound/${uuid}`, window.location.origin);
    if (state.from) url.searchParams.set('from', state.from);
    if (state.to) url.searchParams.set('to', state.to);
    const res = await window.App.fetchJson(url.toString());
    if (!res.ok) {
      window.App.toast(res.message || 'Failed to load outbound', 'error');
      mLinks.innerHTML = `<tr><td class="px-4 py-3 text-red-500" colspan="2">Failed.</td></tr>`;
      return;
    }
    mOpens.textContent = String(res.data.opens ?? 0);
    mClicks.textContent = String(res.data.clicks ?? 0);

    const links = res.data.links || [];
    if (!links.length) {
      mLinks.innerHTML = `<tr><td class="px-4 py-3 text-[var(--text-2)]" colspan="2">No links found.</td></tr>`;
      return;
    }
    mLinks.innerHTML = links.map(l => `
      <tr>
        <td class="px-4 py-3 text-[var(--text-2)]">
          <div class="max-w-[520px] truncate" title="${escapeHtml(l.url || '')}">
            ${escapeHtml(l.url || '')}
          </div>
        </td>
        <td class="px-4 py-3 text-right text-[var(--text-1)] font-semibold">${l.clicks ?? 0}</td>
      </tr>
    `).join('');
  });

  let t = null;
  searchEl?.addEventListener('input', () => {
    window.clearTimeout(t);
    t = window.setTimeout(() => {
      state.search = (searchEl.value || '').trim();
      state.page = 1;
      load();
    }, 300);
  });
  typeEl?.addEventListener('change', () => {
    state.type = typeEl.value || '';
    state.page = 1;
    load();
  });
  fromEl?.addEventListener('change', () => { state.from = fromEl.value || ''; state.page = 1; load(); });
  toEl?.addEventListener('change', () => { state.to = toEl.value || ''; state.page = 1; load(); });
  prevBtn?.addEventListener('click', () => { if (state.page > 1) { state.page--; load(); } });
  nextBtn?.addEventListener('click', () => { if (state.page < state.lastPage) { state.page++; load(); } });

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, (c) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));
  }
  function formatTime(iso) { try { return new Date(iso).toLocaleString(); } catch { return iso || ''; } }

  load();
})();