const els = {
  tbody: document.getElementById('clientTbody'),
  meta: document.getElementById('clientMeta'),
  prev: document.getElementById('clientPrev'),
  next: document.getElementById('clientNext'),
  search: document.getElementById('clientSearch'),
  status: document.getElementById('clientStatus'),
  category: document.getElementById('clientCategory'),
  tag: document.getElementById('clientTag'),
  refresh: document.getElementById('clientRefresh'),
  btnNew: document.getElementById('btnNewClient'),

  form: document.getElementById('clientForm'),
  id: document.getElementById('clientId'),
  business: document.getElementById('clientBusiness'),
  contact: document.getElementById('clientContact'),
  email: document.getElementById('clientEmail'),
  website: document.getElementById('clientWebsite'),
  city: document.getElementById('clientCity'),
  country: document.getElementById('clientCountry'),
  categoryId: document.getElementById('clientCategoryId'),
  statusVal: document.getElementById('clientStatusVal'),
  tags: document.getElementById('clientTags'),
  save: document.getElementById('clientSave'),

  // notes
  notesClientId: document.getElementById('notesClientId'),
  notesHeader: document.getElementById('notesHeader'),
  notesList: document.getElementById('notesList'),
  noteForm: document.getElementById('noteForm'),
  noteBody: document.getElementById('noteBody'),
  noteErr: document.getElementById('noteErr'),
  noteSave: document.getElementById('noteSave'),

  // competitors
  compClientId: document.getElementById('compClientId'),
  compId: document.getElementById('compId'),
  compHeader: document.getElementById('compHeader'),
  compList: document.getElementById('compList'),
  compForm: document.getElementById('compForm'),
  compName: document.getElementById('compName'),
  compWebsite: document.getElementById('compWebsite'),
  compSummary: document.getElementById('compSummary'),
  compNotes: document.getElementById('compNotes'),
  compSave: document.getElementById('compSave'),
  compReset: document.getElementById('compReset'),
};

if (!els.tbody) {
  // not on this page
  // eslint-disable-next-line no-unused-vars
  const noop = 0;
}

const state = {
  page: 1,
  lastPage: 1,
  q: '',
  status: 'all',
  category_id: '',
  tag: '',
};

const cache = new Map(); // id -> row

function escHtml(s) {
  return String(s ?? '').replace(/[&<>"']/g, (m) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
  }[m]));
}

function openModal(id) {
  document.querySelector(`[data-modal="${id}"]`)?.classList.remove('hidden');
}

function closeModal(id) {
  document.querySelector(`[data-modal="${id}"]`)?.classList.add('hidden');
}

function clearErrors() {
  document.querySelectorAll('[data-err]').forEach((el) => {
    el.classList.add('hidden');
    el.textContent = '';
  });
  [els.business, els.email, els.website, els.tags].forEach((el) => el?.classList.remove('border-danger/60'));
}

function applyErrors(errors) {
  clearErrors();
  Object.entries(errors || {}).forEach(([field, msgs]) => {
    const el = document.querySelector(`[data-err="${field}"]`);
    if (el) {
      el.textContent = (msgs || []).join(' ');
      el.classList.remove('hidden');
    }
    if (field === 'business_name') els.business.classList.add('border-danger/60');
    if (field === 'email') els.email.classList.add('border-danger/60');
    if (field === 'website_url') els.website.classList.add('border-danger/60');
    if (field === 'tags') els.tags.classList.add('border-danger/60');
  });
}

function setSaving(isSaving) {
  els.save.disabled = isSaving;
  els.save.classList.toggle('opacity-70', isSaving);
  els.save.classList.toggle('cursor-not-allowed', isSaving);
  els.save.textContent = isSaving ? 'Saving…' : 'Save';
}

function renderSkeleton() {
  els.tbody.innerHTML = Array.from({ length: 6 }).map(() => `
    <tr>
      <td class="px-4 py-4"><div class="h-4 w-56 bg-muted/60 rounded"></div></td>
      <td class="px-4 py-4"><div class="h-4 w-24 bg-muted/60 rounded"></div></td>
      <td class="px-4 py-4"><div class="h-4 w-20 bg-muted/60 rounded"></div></td>
      <td class="px-4 py-4"><div class="h-4 w-32 bg-muted/60 rounded"></div></td>
      <td class="px-4 py-4"><div class="h-4 w-10 bg-muted/60 rounded"></div></td>
      <td class="px-4 py-4"><div class="h-4 w-10 bg-muted/60 rounded"></div></td>
      <td class="px-4 py-4 text-right"><div class="h-8 w-16 bg-muted/60 rounded-xl inline-block"></div></td>
    </tr>
  `).join('');
  els.meta.textContent = 'Loading…';
}

function badgeStatus(status) {
  const map = {
    prospect: 'bg-muted/40 border-border/60',
    engaged: 'bg-primary/10 border-primary/30',
    paused: 'bg-bg/40 border-border/60 text-muted-fg',
    suppressed: 'bg-danger/10 border-danger/30',
    archived: 'bg-bg/40 border-border/60 text-muted-fg',
  };
  const cls = map[status] || 'bg-muted/40 border-border/60';
  return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] border ${cls}">${escHtml(status)}</span>`;
}

function renderRows(rows, pagination) {
  cache.clear();
  rows.forEach((r) => cache.set(String(r.id), r));

  if (!rows.length) {
    els.tbody.innerHTML = `
      <tr>
        <td colspan="6" class="px-4 py-10 text-center">
          <div class="text-sm font-semibold">No clients found</div>
          <div class="text-sm text-muted-fg mt-1">Try search/filter or add a new client.</div>
        </td>
      </tr>
    `;
  } else {
    els.tbody.innerHTML = rows.map((r) => {
      const cat = r.category?.name ?? '—';
      const tags = (r.tags || []).slice(0, 3).map(t =>
        `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] border border-border/60 bg-bg/40 text-muted-fg">${escHtml(t)}</span>`
      ).join(' ') + ((r.tags || []).length > 3 ? ` <span class="text-xs text-muted-fg">+${(r.tags || []).length - 3}</span>` : '');

      return `
        <tr class="hover:bg-muted/20 transition">
          <td class="px-4 py-3">
            <div class="font-semibold">${escHtml(r.business_name)}</div>
            <div class="text-xs text-muted-fg mt-0.5">${escHtml(r.email)}${r.city ? ` • ${escHtml(r.city)}` : ''}</div>
          </td>
          <td class="px-4 py-3 text-muted-fg">${escHtml(cat)}</td>
          <td class="px-4 py-3">${badgeStatus(r.status)}</td>
          <td class="px-4 py-3">${tags || '<span class="text-xs text-muted-fg">—</span>'}</td>
          <td class="px-4 py-3 text-muted-fg">${escHtml(r.competitors_count ?? 0)}</td>
          <td class="px-4 py-3 text-muted-fg">${escHtml(r.notes_count ?? 0)}</td>
          <td class="px-4 py-3 text-right">
            <details class="inline-block relative">
              <summary class="list-none cursor-pointer select-none h-9 px-3 rounded-xl border border-border/60 bg-bg/40 hover:bg-muted/40 transition inline-flex items-center justify-center text-sm font-semibold">
                ⋯
              </summary>
              <div class="absolute right-0 mt-2 w-44 rounded-2xl border border-border bg-card shadow-xl overflow-hidden z-10">
                <button type="button" data-action="edit" data-id="${escHtml(r.id)}"
                  class="w-full text-left px-3 py-2 text-sm hover:bg-muted/40 transition">Edit</button>
                <button type="button" data-action="competitors" data-id="${escHtml(r.id)}"
                  class="w-full text-left px-3 py-2 text-sm hover:bg-muted/40 transition">Competitors</button>
                <button type="button" data-action="notes" data-id="${escHtml(r.id)}"
                  class="w-full text-left px-3 py-2 text-sm hover:bg-muted/40 transition">Notes</button>
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
    category_id: state.category_id,
    tag: state.tag,
  });
  const res = await window.App.fetchJson(`/app/ajax/clients?${qs.toString()}`);
  if (!res?.ok) {
    window.App.toast(res?.message || 'Failed to load clients', 'danger');
    els.meta.textContent = 'Failed to load';
    return;
  }
  renderRows(res.data.rows || [], res.data.pagination || { page: 1, last_page: 1, total: 0 });
}

function resetForm() {
  clearErrors();
  els.id.value = '';
  els.business.value = '';
  els.contact.value = '';
  els.email.value = '';
  els.website.value = '';
  els.city.value = '';
  els.country.value = '';
  els.categoryId.value = '';
  els.statusVal.value = 'prospect';
  els.tags.value = '';
}

function fillForm(r) {
  clearErrors();
  els.id.value = String(r.id);
  els.business.value = r.business_name ?? '';
  els.contact.value = r.contact_name ?? '';
  els.email.value = r.email ?? '';
  els.website.value = r.website_url ?? '';
  els.city.value = r.city ?? '';
  els.country.value = r.country ?? '';
  els.categoryId.value = r.category?.id ? String(r.category.id) : '';
  els.statusVal.value = r.status ?? 'prospect';
  els.tags.value = (r.tags || []).join(', ');
}

async function saveClient() {
  clearErrors();
  setSaving(true);
  const id = els.id.value.trim();
  const payload = {
    business_name: els.business.value.trim(),
    contact_name: els.contact.value.trim() || null,
    email: els.email.value.trim(),
    website_url: els.website.value.trim() || null,
    city: els.city.value.trim() || null,
    country: els.country.value.trim() || null,
    category_id: els.categoryId.value ? Number(els.categoryId.value) : null,
    status: els.statusVal.value,
    tags: els.tags.value.trim(),
  };

  const url = id ? `/app/ajax/clients/${encodeURIComponent(id)}` : '/app/ajax/clients';
  const method = id ? 'PATCH' : 'POST';
  const res = await window.App.fetchJson(url, { method, body: JSON.stringify(payload) });
  setSaving(false);

  if (!res?.ok) {
    if (res?.errors) applyErrors(res.errors);
    window.App.toast(res?.message || 'Save failed', 'danger');
    return;
  }

  window.App.toast(res?.message || 'Saved', 'success');
  closeModal('clientModal');
  await load();
}

async function deleteClient(id) {
  const r = cache.get(String(id));
  const ok = window.confirm(`Delete client "${r?.business_name ?? id}"?`);
  if (!ok) return;
  const res = await window.App.fetchJson(`/app/ajax/clients/${encodeURIComponent(id)}`, { method: 'DELETE' });
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

// Notes
function setNoteSaving(s) {
  els.noteSave.disabled = s;
  els.noteSave.classList.toggle('opacity-70', s);
  els.noteSave.textContent = s ? 'Saving…' : 'Add Note';
}

async function loadNotes(clientId) {
  els.noteErr.classList.add('hidden');
  els.notesList.innerHTML = `
    <div class="rounded-xl border border-border/60 bg-bg/40 p-3">
      <div class="h-4 w-40 bg-muted/60 rounded"></div>
      <div class="h-4 w-64 bg-muted/60 rounded mt-2"></div>
    </div>
  `;
  const res = await window.App.fetchJson(`/app/ajax/clients/${encodeURIComponent(clientId)}/notes`);
  if (!res?.ok) {
    els.notesList.innerHTML = `<div class="text-sm text-danger">Failed to load notes</div>`;
    return;
  }
  const notes = res.data.notes || [];
  if (!notes.length) {
    els.notesList.innerHTML = `<div class="text-sm text-muted-fg">No notes yet.</div>`;
    return;
  }
  els.notesList.innerHTML = notes.map((n) => {
    const who = n.user?.name ? escHtml(n.user.name) : 'System';
    const when = n.created_at ? new Date(n.created_at).toLocaleString() : '';
    return `
      <div class="rounded-xl border border-border/60 bg-bg/40 p-3">
        <div class="text-xs text-muted-fg">${who} • ${escHtml(when)}</div>
        <div class="text-sm mt-1 whitespace-pre-wrap">${escHtml(n.body)}</div>
      </div>
    `;
  }).join('');
}

async function addNote(clientId) {
  els.noteErr.classList.add('hidden');
  const body = els.noteBody.value.trim();
  if (!body) {
    els.noteErr.textContent = 'Note body is required.';
    els.noteErr.classList.remove('hidden');
    return;
  }
  setNoteSaving(true);
  const res = await window.App.fetchJson(`/app/ajax/clients/${encodeURIComponent(clientId)}/notes`, {
    method: 'POST',
    body: JSON.stringify({ body }),
  });
  setNoteSaving(false);
  if (!res?.ok) {
    els.noteErr.textContent = res?.message || 'Failed to add note';
    els.noteErr.classList.remove('hidden');
    return;
  }
  els.noteBody.value = '';
  window.App.toast(res?.message || 'Note added', 'success');
  await loadNotes(clientId);
  await load();
}

// Competitors
function compClearErrors() {
  document.querySelectorAll('[data-comp-err]').forEach((el) => {
    el.classList.add('hidden');
    el.textContent = '';
  });
  [els.compName, els.compWebsite, els.compSummary, els.compNotes].forEach((el) => {
    el?.classList.remove('border-danger/60');
  });
}

function compApplyErrors(errors) {
  compClearErrors();
  Object.entries(errors || {}).forEach(([field, msgs]) => {
    const el = document.querySelector(`[data-comp-err="${field}"]`);
    if (el) {
      el.textContent = (msgs || []).join(' ');
      el.classList.remove('hidden');
    }
    if (field === 'name') els.compName.classList.add('border-danger/60');
    if (field === 'website_url') els.compWebsite.classList.add('border-danger/60');
    if (field === 'summary') els.compSummary.classList.add('border-danger/60');
    if (field === 'notes') els.compNotes.classList.add('border-danger/60');
  });
}

function compResetForm() {
  compClearErrors();
  els.compId.value = '';
  els.compName.value = '';
  els.compWebsite.value = '';
  els.compSummary.value = '';
  els.compNotes.value = '';
}

function compFillForm(row) {
  compClearErrors();
  els.compId.value = String(row.id);
  els.compName.value = row.name ?? '';
  els.compWebsite.value = row.website_url ?? '';
  els.compSummary.value = row.summary ?? '';
  els.compNotes.value = row.notes ?? '';
}

function compSetSaving(s) {
  els.compSave.disabled = s;
  els.compSave.classList.toggle('opacity-70', s);
  els.compSave.textContent = s ? 'Saving…' : 'Save competitor';
}

function compRenderSkeleton() {
  els.compList.innerHTML = Array.from({ length: 3 }).map(() => `
    <div class="rounded-xl border border-border/60 bg-card/60 p-3">
      <div class="h-4 w-40 bg-muted/60 rounded"></div>
      <div class="h-4 w-64 bg-muted/60 rounded mt-2"></div>
      <div class="h-8 w-28 bg-muted/60 rounded-xl mt-3"></div>
    </div>
  `).join('');
}

function compRenderList(rows) {
  if (!rows.length) {
    els.compList.innerHTML = `<div class="text-sm text-muted-fg">No competitors yet. Add one below.</div>`;
    return;
  }

  els.compList.innerHTML = rows.map((r) => {
    const url = r.website_url ? `<a href="${escHtml(r.website_url)}" target="_blank" class="text-xs text-primary hover:underline">${escHtml(r.website_url)}</a>` : `<span class="text-xs text-muted-fg">—</span>`;
    const summary = r.summary ? `<div class="text-sm mt-2 whitespace-pre-wrap">${escHtml(r.summary)}</div>` : `<div class="text-sm mt-2 text-muted-fg">No summary</div>`;
    return `
      <div class="rounded-xl border border-border/60 bg-card/60 p-3">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="font-semibold truncate">${escHtml(r.name)}</div>
            <div class="mt-1">${url}</div>
          </div>
          <div class="flex items-center gap-2 shrink-0">
            <button type="button" data-comp-action="edit" data-comp-id="${escHtml(r.id)}"
              class="h-9 px-3 rounded-xl border border-border/60 bg-bg/40 hover:bg-muted/40 transition text-sm font-semibold">Edit</button>
            <button type="button" data-comp-action="delete" data-comp-id="${escHtml(r.id)}"
              class="h-9 px-3 rounded-xl border border-border/60 bg-bg/40 hover:bg-muted/40 transition text-sm font-semibold text-danger">Delete</button>
          </div>
        </div>
        ${summary}
      </div>
    `;
  }).join('');
}

async function compLoad(clientId) {
  compRenderSkeleton();
  const res = await window.App.fetchJson(`/app/ajax/clients/${encodeURIComponent(clientId)}/competitors`);
  if (!res?.ok) {
    els.compList.innerHTML = `<div class="text-sm text-danger">Failed to load competitors</div>`;
    return;
  }
  const rows = res.data.rows || [];
  // local competitor cache
  window.__compCache = new Map(rows.map((r) => [String(r.id), r]));
  compRenderList(rows);
}

async function compSave() {
  compClearErrors();
  const clientId = els.compClientId.value;
  if (!clientId) return;

  compSetSaving(true);
  const id = els.compId.value.trim();
  const payload = {
    name: els.compName.value.trim(),
    website_url: els.compWebsite.value.trim() || null,
    summary: els.compSummary.value.trim() || null,
    notes: els.compNotes.value.trim() || null,
  };

  const url = id
    ? `/app/ajax/competitors/${encodeURIComponent(id)}`
    : `/app/ajax/clients/${encodeURIComponent(clientId)}/competitors`;
  const method = id ? 'PATCH' : 'POST';
  const res = await window.App.fetchJson(url, { method, body: JSON.stringify(payload) });
  compSetSaving(false);

  if (!res?.ok) {
    if (res?.errors) compApplyErrors(res.errors);
    window.App.toast(res?.message || 'Save failed', 'danger');
    return;
  }

  window.App.toast(res?.message || 'Saved', 'success');
  compResetForm();
  await compLoad(clientId);
  await load(); // refresh counts
}

async function compDelete(id) {
  const ok = window.confirm('Delete this competitor?');
  if (!ok) return;
  const res = await window.App.fetchJson(`/app/ajax/competitors/${encodeURIComponent(id)}`, { method: 'DELETE' });
  if (!res?.ok) {
    window.App.toast(res?.message || 'Delete failed', 'danger');
    return;
  }
  window.App.toast(res?.message || 'Deleted', 'success');
  const clientId = els.compClientId.value;
  if (clientId) {
    await compLoad(clientId);
    await load();
  }
}

function bind() {
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

  els.category.addEventListener('change', () => {
    state.category_id = els.category.value;
    state.page = 1;
    load();
  });

  els.tag.addEventListener('input', debounce(() => {
    state.tag = els.tag.value.trim();
    state.page = 1;
    load();
  }, 300));

  els.refresh.addEventListener('click', () => load());
  els.prev.addEventListener('click', () => { if (state.page > 1) { state.page--; load(); } });
  els.next.addEventListener('click', () => { if (state.page < state.lastPage) { state.page++; load(); } });

  els.btnNew?.addEventListener('click', () => {
    resetForm();
    openModal('clientModal');
  });

  els.form.addEventListener('submit', (e) => {
    e.preventDefault();
    saveClient();
  });

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;
    const action = btn.getAttribute('data-action');
    const id = btn.getAttribute('data-id');
    if (!id) return;

    if (action === 'edit') {
      const r = cache.get(String(id));
      if (!r) return;
      fillForm(r);
      openModal('clientModal');
    }
    if (action === 'delete') {
      deleteClient(id);
    }
    if (action === 'notes') {
      const r = cache.get(String(id));
      els.notesClientId.value = String(id);
      els.notesHeader.textContent = r ? `${r.business_name} • ${r.email}` : '';
      openModal('clientNotesModal');
      loadNotes(id);
    }
    if (action === 'competitors') {
      const r = cache.get(String(id));
      els.compClientId.value = String(id);
      els.compHeader.textContent = r ? `${r.business_name} • ${r.email}` : '';
      compResetForm();
      openModal('clientCompetitorsModal');
      compLoad(id);
    }
  });

  els.noteForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const clientId = els.notesClientId.value;
    if (!clientId) return;
    addNote(clientId);
  });
  els.compReset?.addEventListener('click', () => compResetForm());
  els.compForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    compSave();
  });

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-comp-action]');
    if (!btn) return;
    const action = btn.getAttribute('data-comp-action');
    const id = btn.getAttribute('data-comp-id');
    if (!id) return;
    if (action === 'edit') {
      const row = window.__compCache?.get(String(id));
      if (row) compFillForm(row);
      return;
    }
    if (action === 'delete') {
      compDelete(id);
    }
  });
}

bind();
load();