const els = {
  tbody: document.getElementById('senderTbody'),
  meta: document.getElementById('senderMeta'),
  prev: document.getElementById('senderPrev'),
  next: document.getElementById('senderNext'),
  search: document.getElementById('senderSearch'),
  status: document.getElementById('senderStatus'),
  refresh: document.getElementById('senderRefresh'),
  btnNew: document.getElementById('btnNewSender'),

  form: document.getElementById('senderForm'),
  id: document.getElementById('senderId'),
  name: document.getElementById('senderName'),
  active: document.getElementById('senderActive'),
  fromName: document.getElementById('senderFromName'),
  fromEmail: document.getElementById('senderFromEmail'),
  daily: document.getElementById('senderDailyLimit'),
  wStart: document.getElementById('senderWindowStart'),
  wEnd: document.getElementById('senderWindowEnd'),
  tz: document.getElementById('senderTimezone'),
  jMin: document.getElementById('senderJitterMin'),
  jMax: document.getElementById('senderJitterMax'),

  smtpHost: document.getElementById('smtpHost'),
  smtpPort: document.getElementById('smtpPort'),
  smtpEnc: document.getElementById('smtpEnc'),
  smtpUser: document.getElementById('smtpUser'),
  smtpPass: document.getElementById('smtpPass'),

  imapHost: document.getElementById('imapHost'),
  imapPort: document.getElementById('imapPort'),
  imapEnc: document.getElementById('imapEnc'),
  imapUser: document.getElementById('imapUser'),
  imapPass: document.getElementById('imapPass'),

  save: document.getElementById('senderSave'),
};

if (!els.tbody) {
  // not on this page
  // eslint-disable-next-line no-unused-vars
  const noop = 0;
}

const state = { page: 1, lastPage: 1, q: '', status: 'all' };
const cache = new Map();

function escHtml(s) {
  return String(s ?? '').replace(/[&<>"']/g, (m) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
  }[m]));
}
function openModal(id) { document.querySelector(`[data-modal="${id}"]`)?.classList.remove('hidden'); }
function closeModal(id) { document.querySelector(`[data-modal="${id}"]`)?.classList.add('hidden'); }

function debounce(fn, ms) {
  let t = null;
  return (...args) => { if (t) clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
}

function clearErrors() {
  document.querySelectorAll('[data-err]').forEach((el) => { el.classList.add('hidden'); el.textContent = ''; });
  document.querySelectorAll('#senderForm input, #senderForm select, #senderForm textarea').forEach((el) => {
    el.classList.remove('border-danger/60');
  });
}
function applyErrors(errors) {
  clearErrors();
  Object.entries(errors || {}).forEach(([field, msgs]) => {
    const el = document.querySelector(`[data-err="${field}"]`);
    if (el) { el.textContent = (msgs || []).join(' '); el.classList.remove('hidden'); }
    const map = {
      name: els.name,
      from_name: els.fromName,
      from_email: els.fromEmail,
      daily_limit: els.daily,
      window_start: els.wStart,
      window_end: els.wEnd,
      timezone: els.tz,
      jitter_min_seconds: els.jMin,
      jitter_max_seconds: els.jMax,
      smtp_host: els.smtpHost,
      smtp_port: els.smtpPort,
      smtp_encryption: els.smtpEnc,
      smtp_username: els.smtpUser,
      smtp_password: els.smtpPass,
      imap_host: els.imapHost,
      imap_port: els.imapPort,
      imap_encryption: els.imapEnc,
      imap_username: els.imapUser,
      imap_password: els.imapPass,
    };
    if (map[field]) map[field].classList.add('border-danger/60');
  });
}
function setSaving(s) {
  els.save.disabled = s;
  els.save.classList.toggle('opacity-70', s);
  els.save.classList.toggle('cursor-not-allowed', s);
  els.save.textContent = s ? 'Saving…' : 'Save';
}

function badgeActive(isActive) {
  return isActive
    ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] border border-border/60 bg-muted/40">Active</span>`
    : `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] border border-border/60 bg-bg/40 text-muted-fg">Inactive</span>`;
}

function renderSkeleton() {
  els.tbody.innerHTML = Array.from({ length: 6 }).map(() => `
    <tr>
      <td class="px-4 py-4"><div class="h-4 w-56 bg-muted/60 rounded"></div></td>
      <td class="px-4 py-4"><div class="h-4 w-20 bg-muted/60 rounded"></div></td>
      <td class="px-4 py-4"><div class="h-4 w-24 bg-muted/60 rounded"></div></td>
      <td class="px-4 py-4"><div class="h-4 w-28 bg-muted/60 rounded"></div></td>
      <td class="px-4 py-4"><div class="h-4 w-28 bg-muted/60 rounded"></div></td>
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
        <td colspan="6" class="px-4 py-10 text-center">
          <div class="text-sm font-semibold">No senders found</div>
          <div class="text-sm text-muted-fg mt-1">Add a sender to start sending.</div>
        </td>
      </tr>
    `;
  } else {
    els.tbody.innerHTML = rows.map((r) => {
      const smtp = `${escHtml(r.smtp_host)}:${escHtml(r.smtp_port)} • ${escHtml(r.smtp_encryption)}`;
      const window = `${escHtml(r.window_start)}–${escHtml(r.window_end)} (${escHtml(r.timezone)})`;
      const daily = `${escHtml(r.sent_today)}/${escHtml(r.daily_limit)}`;

      return `
        <tr class="hover:bg-muted/20 transition">
          <td class="px-4 py-3">
            <div class="font-semibold">${escHtml(r.name)}</div>
            <div class="text-xs text-muted-fg mt-0.5">${escHtml(r.from_name)} • ${escHtml(r.from_email)}</div>
          </td>
          <td class="px-4 py-3">${badgeActive(r.is_active)}</td>
          <td class="px-4 py-3 text-muted-fg">${daily}</td>
          <td class="px-4 py-3 text-muted-fg">${window}</td>
          <td class="px-4 py-3 text-muted-fg">${smtp}</td>
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
  const qs = new URLSearchParams({ page: String(state.page), q: state.q, status: state.status });
  const res = await window.App.fetchJson(`/app/ajax/senders?${qs.toString()}`);
  if (!res?.ok) {
    window.App.toast(res?.message || 'Failed to load senders', 'danger');
    els.meta.textContent = 'Failed to load';
    return;
  }
  renderRows(res.data.rows || [], res.data.pagination || { page: 1, last_page: 1, total: 0 });
}

function resetForm() {
  clearErrors();
  els.id.value = '';
  els.name.value = '';
  els.active.checked = true;
  els.fromName.value = '';
  els.fromEmail.value = '';
  els.daily.value = '50';
  els.wStart.value = '09:00';
  els.wEnd.value = '18:00';
  els.tz.value = 'Asia/Dhaka';
  els.jMin.value = '30';
  els.jMax.value = '180';
  els.smtpHost.value = '';
  els.smtpPort.value = '587';
  els.smtpEnc.value = 'tls';
  els.smtpUser.value = '';
  els.smtpPass.value = '';
  els.imapHost.value = '';
  els.imapPort.value = '';
  els.imapEnc.value = '';
  els.imapUser.value = '';
  els.imapPass.value = '';
}

function fillForm(r) {
  clearErrors();
  els.id.value = String(r.id);
  els.name.value = r.name ?? '';
  els.active.checked = !!r.is_active;
  els.fromName.value = r.from_name ?? '';
  els.fromEmail.value = r.from_email ?? '';
  els.daily.value = String(r.daily_limit ?? 50);
  els.wStart.value = r.window_start ?? '09:00';
  els.wEnd.value = r.window_end ?? '18:00';
  els.tz.value = r.timezone ?? 'Asia/Dhaka';
  els.jMin.value = String(r.jitter_min_seconds ?? 30);
  els.jMax.value = String(r.jitter_max_seconds ?? 180);
  els.smtpHost.value = r.smtp_host ?? '';
  els.smtpPort.value = String(r.smtp_port ?? 587);
  els.smtpEnc.value = r.smtp_encryption ?? 'tls';
  els.smtpUser.value = r.smtp_username ?? '';
  els.smtpPass.value = ''; // keep blank
  els.imapHost.value = r.imap_host ?? '';
  els.imapPort.value = r.imap_port ? String(r.imap_port) : '';
  els.imapEnc.value = r.imap_encryption ?? '';
  els.imapUser.value = r.imap_username ?? '';
  els.imapPass.value = ''; // keep blank
}

async function save() {
  clearErrors();
  setSaving(true);
  const id = els.id.value.trim();

  const payload = {
    name: els.name.value.trim(),
    from_name: els.fromName.value.trim(),
    from_email: els.fromEmail.value.trim(),
    is_active: els.active.checked,

    daily_limit: Number(els.daily.value || 50),
    window_start: els.wStart.value,
    window_end: els.wEnd.value,
    timezone: els.tz.value.trim(),
    jitter_min_seconds: Number(els.jMin.value || 0),
    jitter_max_seconds: Number(els.jMax.value || 0),

    smtp_host: els.smtpHost.value.trim(),
    smtp_port: Number(els.smtpPort.value || 587),
    smtp_encryption: els.smtpEnc.value,
    smtp_username: els.smtpUser.value.trim(),
    smtp_password: els.smtpPass.value, // empty on edit = keep (server ignores empty)

    imap_host: els.imapHost.value.trim() || null,
    imap_port: els.imapPort.value ? Number(els.imapPort.value) : null,
    imap_encryption: els.imapEnc.value || null,
    imap_username: els.imapUser.value.trim() || null,
    imap_password: els.imapPass.value || null,
  };

  const url = id ? `/app/ajax/senders/${encodeURIComponent(id)}` : '/app/ajax/senders';
  const method = id ? 'PATCH' : 'POST';
  const res = await window.App.fetchJson(url, { method, body: JSON.stringify(payload) });
  setSaving(false);

  if (!res?.ok) {
    if (res?.errors) applyErrors(res.errors);
    window.App.toast(res?.message || 'Save failed', 'danger');
    return;
  }

  window.App.toast(res?.message || 'Saved', 'success');
  closeModal('senderModal');
  await load();
}

async function destroy(id) {
  const r = cache.get(String(id));
  const ok = window.confirm(`Delete sender "${r?.name ?? id}"?`);
  if (!ok) return;
  const res = await window.App.fetchJson(`/app/ajax/senders/${encodeURIComponent(id)}`, { method: 'DELETE' });
  if (!res?.ok) {
    window.App.toast(res?.message || 'Delete failed', 'danger');
    return;
  }
  window.App.toast(res?.message || 'Deleted', 'success');
  await load();
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

  els.refresh.addEventListener('click', () => load());
  els.prev.addEventListener('click', () => { if (state.page > 1) { state.page--; load(); } });
  els.next.addEventListener('click', () => { if (state.page < state.lastPage) { state.page++; load(); } });

  els.btnNew?.addEventListener('click', () => { resetForm(); openModal('senderModal'); });
  els.form.addEventListener('submit', (e) => { e.preventDefault(); save(); });

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
      openModal('senderModal');
    }
    if (action === 'delete') destroy(id);
  });
}

bind();
load();