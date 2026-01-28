const els = {
  tbody: document.getElementById('catTbody'),
  meta: document.getElementById('catMeta'),
  prev: document.getElementById('catPrev'),
  next: document.getElementById('catNext'),
  search: document.getElementById('catSearch'),
  status: document.getElementById('catStatus'),
  refresh: document.getElementById('catRefresh'),

  form: document.getElementById('catForm'),
  id: document.getElementById('catId'),
  name: document.getElementById('catName'),
  color: document.getElementById('catColor'),
  colorPicker: document.getElementById('catColorPicker'),
  sort: document.getElementById('catSort'),
  active: document.getElementById('catActive'),
  save: document.getElementById('catSave'),
};

if (!els.tbody) {
  // Not on this page.
  // eslint-disable-next-line no-unused-vars
  const noop = 0;
}
const state = {
  page: 1,
  lastPage: 1,
  q: '',
  status: 'all',
  sort: 'sort_order',
  dir: 'asc',
};

const cache = new Map(); // id -> row

function openModal(id) {
  const modal = document.querySelector('[data-modal="categoryModal"]');
  modal?.classList.remove('hidden');
}

function closeModal() {
  const modal = document.querySelector('[data-modal="categoryModal"]');
  modal?.classList.add('hidden');
}

function setSaving(isSaving) {
  els.save.disabled = isSaving;
  els.save.classList.toggle('opacity-70', isSaving);
  els.save.classList.toggle('cursor-not-allowed', isSaving);
  els.save.textContent = isSaving ? 'Saving…' : 'Save';
}

function clearErrors() {
  document.querySelectorAll('[data-err]').forEach((el) => {
    el.classList.add('hidden');
    el.textContent = '';
  });
  [els.name, els.color, els.sort].forEach((el) => {
    el.classList.remove('border-danger/60');
  });
}

function applyErrors(errors) {
  clearErrors();
  Object.entries(errors || {}).forEach(([field, msgs]) => {
    const el = document.querySelector(`[data-err="${field}"]`);
    if (el) {
      el.textContent = (msgs || []).join(' ');
      el.classList.remove('hidden');
    }
    if (field === 'name') els.name.classList.add('border-danger/60');
    if (field === 'color') els.color.classList.add('border-danger/60');
    if (field === 'sort_order') els.sort.classList.add('border-danger/60');
  });
}

function escHtml(s) {
  return String(s ?? '').replace(/[&<>"']/g, (m) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
  }[m]));
}

function renderSkeleton() {
  els.tbody.innerHTML = Array.from({ length: 6 }).map(() => `
        <tr>
            <td class="px-4 py-4">
                <div class="h-4 w-48 bg-muted/60 rounded"></div>
            </td>
            <td class="px-4 py-4">
                <div class="h-4 w-20 bg-muted/60 rounded"></div>
            </td>
            <td class="px-4 py-4">
                <div class="h-4 w-12 bg-muted/60 rounded"></div>
            </td>
            <td class="px-4 py-4">
                <div class="h-4 w-28 bg-muted/60 rounded"></div>
            </td>
            <td class="px-4 py-4 text-right">
                <div class="h-8 w-16 bg-muted/60 rounded-xl inline-block"></div>
            </td>
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
                    <div class="text-sm font-semibold">No categories found</div>
                    <div class="text-sm text-muted-fg mt-1">Try a different search/filter.</div>
                </td>
            </tr>
        `;
  } else {
    els.tbody.innerHTML = rows.map((r) => {
      const dot = r.color ? `style="background:${escHtml(r.color)}"` : '';
      const badge = r.is_active
        ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] border border-border/60 bg-muted/40">Active</span>`
        : `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] border border-border/60 bg-bg/40 text-muted-fg">Inactive</span>`;
      const created = r.created_at ? new Date(r.created_at).toLocaleString() : '—';

      return `
                <tr class="hover:bg-muted/20 transition">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <span class="h-3 w-3 rounded-full border border-border/60 ${r.color ? '' : 'bg-muted/50'}" ${dot}></span>
                            <div class="font-semibold">${escHtml(r.name)}</div>
                        </div>
                    </td>
                    <td class="px-4 py-3">${badge}</td>
                    <td class="px-4 py-3 text-muted-fg">${escHtml(r.sort_order)}</td>
                    <td class="px-4 py-3 text-muted-fg">${escHtml(created)}</td>
                    <td class="px-4 py-3 text-right">
                        <details class="inline-block relative">
                            <summary class="list-none cursor-pointer select-none h-9 px-3 rounded-xl border border-border/60 bg-bg/40 hover:bg-muted/40 transition inline-flex items-center justify-center text-sm font-semibold">
                                ⋯
                            </summary>
                            <div class="absolute right-0 mt-2 w-40 rounded-2xl border border-border bg-card shadow-xl overflow-hidden z-10">
                                <button type="button" data-action="edit" data-id="${escHtml(r.id)}"
                                    class="w-full text-left px-3 py-2 text-sm hover:bg-muted/40 transition">Edit</button>
                                <button type="button" data-action="delete" data-id="${escHtml(r.id)}"
                                    class="w-full text-left px-3 py-2 text-sm hover:bg-muted/40 transition text-danger">Delete</button>
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
    status: state.status,
    sort: state.sort,
    dir: state.dir,
  });
  const res = await window.App.fetchJson(`/app/ajax/categories?${qs.toString()}`);
  if (!res?.ok) {
    window.App.toast(res?.message || 'Failed to load categories', 'danger');
    els.meta.textContent = 'Failed to load';
    return;
  }
  renderRows(res.data.rows || [], res.data.pagination || { page: 1, last_page: 1, total: 0 });
}

function resetForm() {
  clearErrors();
  els.id.value = '';
  els.name.value = '';
  els.color.value = '';
  els.colorPicker.value = '#3b82f6';
  els.sort.value = '0';
  els.active.checked = true;
}

function fillForm(row) {
  clearErrors();
  els.id.value = String(row.id);
  els.name.value = row.name ?? '';
  els.color.value = row.color ?? '';
  els.colorPicker.value = row.color ?? '#3b82f6';
  els.sort.value = String(row.sort_order ?? 0);
  els.active.checked = !!row.is_active;
}

async function saveCategory() {
  clearErrors();
  setSaving(true);
  const id = els.id.value.trim();
  const payload = {
    name: els.name.value.trim(),
    color: els.color.value.trim() || null,
    sort_order: Number(els.sort.value || 0),
    is_active: els.active.checked,
  };

  const url = id ? `/app/ajax/categories/${encodeURIComponent(id)}` : '/app/ajax/categories';
  const method = id ? 'PATCH' : 'POST';
  const res = await window.App.fetchJson(url, { method, body: JSON.stringify(payload) });

  setSaving(false);

  if (!res?.ok) {
    if (res?.errors) applyErrors(res.errors);
    window.App.toast(res?.message || 'Save failed', 'danger');
    return;
  }

  window.App.toast(res?.message || 'Saved', 'success');
  closeModal();
  await load();
}

async function deleteCategory(id) {
  const row = cache.get(String(id));
  const ok = window.confirm(`Delete category "${row?.name ?? id}"?`);
  if (!ok) return;

  const res = await window.App.fetchJson(`/app/ajax/categories/${encodeURIComponent(id)}`, { method: 'DELETE' });
  if (!res?.ok) {
    window.App.toast(res?.message || 'Delete failed', 'danger');
    return;
  }
  window.App.toast(res?.message || 'Deleted', 'success');
  await load();
}

function debounce(fn, ms) {
  let t = null;
  return (...args) => {
    if (t) clearTimeout(t);
    t = setTimeout(() => fn(...args), ms);
  };
}

function bind() {
  els.colorPicker.addEventListener('input', () => {
    els.color.value = els.colorPicker.value;
  });

  els.search.addEventListener('input', debounce(() => {
    state.q = els.search.value.trim();
    state.page = 1;
    load();
  }, 250));

  els.status.addEventListener('change', () => {
    state.status = els.status.value;
    state.page = 1;
    load();
  });
  els.refresh.addEventListener('click', () => load());
  els.prev.addEventListener('click', () => {
    if (state.page > 1) {
      state.page--;
      load();
    }
  });
  els.next.addEventListener('click', () => {
    if (state.page < state.lastPage) {
      state.page++;
      load();
    }
  });

  document.getElementById('btnNewCategory')?.addEventListener('click', () => {
    resetForm();
    openModal();
  });

  els.form.addEventListener('submit', (e) => {
    e.preventDefault();
    saveCategory();
  });

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;
    const action = btn.getAttribute('data-action');
    const id = btn.getAttribute('data-id');
    if (!id) return;

    if (action === 'edit') {
      const row = cache.get(String(id));
      if (!row) return;
      fillForm(row);
      openModal();
    }
    if (action === 'delete') {
      deleteCategory(id);
    }
  });
}

bind();
load();