const els = {
  tbody: document.getElementById('fjTbody'),
  meta: document.getElementById('fjMeta'),
  prev: document.getElementById('fjPrev'),
  next: document.getElementById('fjNext'),
  search: document.getElementById('fjSearch'),
  queue: document.getElementById('fjQueue'),
  refresh: document.getElementById('fjRefresh'),

  // modal
  detailMeta: document.getElementById('fjDetailMeta'),
  exception: document.getElementById('fjException'),
  payload: document.getElementById('fjPayload'),
};

if (!els.tbody) {
  // not on this page
  // eslint-disable-next-line no-unused-vars
  const noop = 0;
}

const state = { page: 1, lastPage: 1, q: '', queue: '' };
const cache = new Map();

function escHtml(s) {
  return String(s ?? '').replace(/[&<>"']/g, (m) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
  }[m]));
}
function openModal(id) { document.querySelector(`[data-modal="${id}"]`)?.classList.remove('hidden'); }

function debounce(fn, ms) {
  let t = null;
  return (...args) => { if (t) clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
}

function renderSkeleton() {
  els.tbody.innerHTML = Array.from({ length: 6 }).map(() => `
    <tr>
      <td class="px-4 py-4"><div class="h-4 w-28 bg-muted/60 rounded"></div></td>
      <td class="px-4 py-4"><div class="h-4 w-28 bg-muted/60 rounded"></div></td>
      <td class="px-4 py-4"><div class="h-4 w-28 bg-muted/60 rounded"></div></td>
      <td class="px-4 py-4"><div class="h-4 w-64 bg-muted/60 rounded"></div></td>
      <td class="px-4 py-4 text-right"><div class="h-8 w-16 bg-muted/60 rounded-xl inline-block"></div></td>
    </tr>
  `).join('');
  els.meta.textContent = 'Loading…';
}

function renderRows(rows, pagination) {
  cache.clear();
  rows.forEach((r) => cache.set(String(r.id), r));

  if (!rows.length) {
    els.tbody.innerHTML = `
      <tr>
        <td colspan="5" class="px-4 py-10 text-center">
          <div class="text-sm font-semibold">No failed jobs</div>
          <div class="text-sm text-muted-fg mt-1">Looks good.</div>
        </td>
      </tr>
    `;
  } else {
    els.tbody.innerHTML = rows.map((r) => {
      const when = r.failed_at ? new Date(r.failed_at).toLocaleString() : '—';
      return `
        <tr class="hover:bg-muted/20 transition">
          <td class="px-4 py-3 text-muted-fg">${escHtml(when)}</td>
          <td class="px-4 py-3 text-muted-fg">${escHtml(r.connection)}</td>
          <td class="px-4 py-3 text-muted-fg">${escHtml(r.queue)}</td>
          <td class="px-4 py-3">
            <div class="text-xs text-muted-fg line-clamp-2">${escHtml(r.exception_snippet)}</div>
          </td>
          <td class="px-4 py-3 text-right">
            <details class="inline-block relative">
              <summary class="list-none cursor-pointer select-none h-9 px-3 rounded-xl border border-border/60 bg-bg/40 hover:bg-muted/40 transition inline-flex items-center justify-center text-sm font-semibold">
                ⋯
              </summary>
              <div class="absolute right-0 mt-2 w-44 rounded-2xl border border-border bg-card shadow-xl overflow-hidden z-10">
                <button type="button" data-action="view" data-id="${escHtml(r.id)}"
                  class="w-full text-left px-3 py-2 text-sm hover:bg-muted/40 transition">View</button>
                <button type="button" data-action="retry" data-id="${escHtml(r.id)}"
                  class="w-full text-left px-3 py-2 text-sm hover:bg-muted/40 transition">Retry (admin)</button>
                <button type="button" data-action="forget" data-id="${escHtml(r.id)}"
                  class="w-full text-left px-3 py-2 text-sm hover:bg-muted/40 transition text-danger">Forget (admin)</button>
              </div>
            </details>
          </td>
        </tr>
      `;
    }).join('');
  }

  state.page = pagination.page;
  state.lastPage = pagination.last_page;
  els.prev.disabled = state.page <= 1;
  els.next.disabled = state.page >= state.lastPage;
  els.prev.classList.toggle('opacity-60', els.prev.disabled);
  els.next.classList.toggle('opacity-60', els.next.disabled);
  els.meta.textContent = `Page ${pagination.page} of ${pagination.last_page} • Total ${pagination.total}`;
}

async function load() {
  renderSkeleton();
  const qs = new URLSearchParams({
    page: String(state.page),
    q: state.q,
    queue: state.queue,
  });
  const res = await window.App.fetchJson(`/app/ajax/failed-jobs?${qs.toString()}`);
  if (!res?.ok) {
    window.App.toast(res?.message || 'Failed to load failed jobs', 'danger');
    els.meta.textContent = 'Failed to load';
    return;
  }
  renderRows(res.data.rows || [], res.data.pagination || { page: 1, last_page: 1, total: 0 });
}

async function viewJob(id) {
  const res = await window.App.fetchJson(`/app/ajax/failed-jobs/${encodeURIComponent(id)}`);
  if (!res?.ok) {
    window.App.toast(res?.message || 'Failed to load job', 'danger');
    return;
  }
  const d = res.data;
  els.detailMeta.textContent = `${d.connection} • ${d.queue} • ${new Date(d.failed_at).toLocaleString()}`;
  els.exception.textContent = d.exception || '';
  els.payload.textContent = d.payload || '';
  openModal('fjModal');
}

async function retryJob(id) {
  const ok = window.confirm('Retry this job? (Admin only)');
  if (!ok) return;
  const res = await window.App.fetchJson(`/app/ajax/failed-jobs/${encodeURIComponent(id)}/retry`, { method: 'POST' });
  if (!res?.ok) {
    window.App.toast(res?.message || 'Retry failed', 'danger');
    return;
  }
  window.App.toast(res?.message || 'Retried', 'success');
  load();
}

async function forgetJob(id) {
  const ok = window.confirm('Remove this failed job record? (Admin only)');
  if (!ok) return;
  const res = await window.App.fetchJson(`/app/ajax/failed-jobs/${encodeURIComponent(id)}`, { method: 'DELETE' });
  if (!res?.ok) {
    window.App.toast(res?.message || 'Forget failed', 'danger');
    return;
  }
  window.App.toast(res?.message || 'Removed', 'success');
  load();
}

function bind() {
  els.search.addEventListener('input', debounce(() => {
    state.q = els.search.value.trim();
    state.page = 1;
    load();
  }, 250));
  els.queue.addEventListener('input', debounce(() => {
    state.queue = els.queue.value.trim();
    state.page = 1;
    load();
  }, 250));
  els.refresh.addEventListener('click', () => load());
  els.prev.addEventListener('click', () => { if (state.page > 1) { state.page--; load(); } });
  els.next.addEventListener('click', () => { if (state.page < state.lastPage) { state.page++; load(); } });

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;
    const action = btn.getAttribute('data-action');
    const id = btn.getAttribute('data-id');
    if (!id) return;
    if (action === 'view') viewJob(id);
    if (action === 'retry') retryJob(id);
    if (action === 'forget') forgetJob(id);
  });
}

bind();
load();