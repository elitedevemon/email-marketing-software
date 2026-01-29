(() => {
  const $ = (s, el = document) => el.querySelector(s);

  const rowsEl = $('#rows');
  const metaEl = $('#meta');
  const searchEl = $('#search');
  const prevBtn = $('#prev');
  const nextBtn = $('#next');
  const addBtn = $('#btnAddSuppression');
  const form = $('#formSuppression');

  const state = { page: 1, lastPage: 1, search: '' };

  function skeleton() {
    rowsEl.innerHTML = Array.from({ length: 8 }).map(() => `
      <tr>
        <td class="px-4 py-3"><div class="h-4 w-48 bg-[var(--surface-2)] rounded"></div></td>
        <td class="px-4 py-3"><div class="h-4 w-56 bg-[var(--surface-2)] rounded"></div></td>
        <td class="px-4 py-3"><div class="h-4 w-24 bg-[var(--surface-2)] rounded"></div></td>
        <td class="px-4 py-3"><div class="h-4 w-28 bg-[var(--surface-2)] rounded"></div></td>
        <td class="px-4 py-3 text-right"><div class="h-4 w-16 bg-[var(--surface-2)] rounded ml-auto"></div></td>
      </tr>
    `).join('');
  }

  function render(items) {
    if (!items.length) {
      rowsEl.innerHTML = `
        <tr><td class="px-4 py-6 text-[var(--text-2)]" colspan="5">No suppression entries found.</td></tr>
      `;
      return;
    }
    rowsEl.innerHTML = items.map(it => `
      <tr>
        <td class="px-4 py-3 text-[var(--text-1)]">${escapeHtml(it.email)}</td>
        <td class="px-4 py-3 text-[var(--text-2)]">${escapeHtml(it.reason || '')}</td>
        <td class="px-4 py-3 text-[var(--text-2)]">${escapeHtml(it.source || '')}</td>
        <td class="px-4 py-3 text-[var(--text-2)]">${new Date(it.created_at).toLocaleString()}</td>
        <td class="px-4 py-3 text-right">
          <button class="px-3 py-1.5 rounded-lg border border-[var(--border)] hover:bg-[var(--surface-2)] text-sm"
                  data-action="remove" data-id="${it.id}">
            Remove
          </button>
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
    const url = new URL('/app/ajax/suppression', window.location.origin);
    url.searchParams.set('page', String(state.page));
    if (state.search) url.searchParams.set('search', state.search);

    const res = await window.App.fetchJson(url.toString());
    if (!res.ok) {
      window.App.toast(res.message || 'Failed to load', 'error');
      rowsEl.innerHTML = `<tr><td class="px-4 py-6 text-red-500" colspan="5">Load failed.</td></tr>`;
      return;
    }
    render(res.data.items || []);
    setMeta(res.data.meta || { current_page: 1, last_page: 1, total: 0 });
  }

  function clearErrors() {
    form.querySelectorAll('[data-err]').forEach(p => { p.classList.add('hidden'); p.textContent = ''; });
  }
  function showErrors(errors) {
    Object.keys(errors || {}).forEach(k => {
      const p = form.querySelector(`[data-err="${k}"]`);
      if (p) { p.textContent = (errors[k] || []).join(' '); p.classList.remove('hidden'); }
    });
  }

  addBtn?.addEventListener('click', () => {
    clearErrors();
    form.reset();
    window.App.modal.open('modalSuppression');
  });

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearErrors();
    const fd = new FormData(form);
    const payload = { email: fd.get('email'), reason: fd.get('reason') };

    const res = await window.App.fetchJson('/app/ajax/suppression', {
      method: 'POST',
      body: payload
    });

    if (!res.ok) {
      if (res.errors) showErrors(res.errors);
      window.App.toast(res.message || 'Failed', 'error');
      return;
    }

    window.App.toast('Suppressed', 'success');
    window.App.modal.close('modalSuppression');
    state.page = 1;
    load();
  });

  rowsEl?.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-action="remove"]');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    if (!id) return;

    const res = await window.App.fetchJson(`/app/ajax/suppression/${id}`, { method: 'DELETE' });
    if (!res.ok) {
      window.App.toast(res.message || 'Failed', 'error');
      return;
    }
    window.App.toast('Removed', 'success');
    load();
  });

  prevBtn?.addEventListener('click', () => {
    if (state.page > 1) { state.page--; load(); }
  });
  nextBtn?.addEventListener('click', () => {
    if (state.page < state.lastPage) { state.page++; load(); }
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

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, (c) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));
  }

  load();
})();