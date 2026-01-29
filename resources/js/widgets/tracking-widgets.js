import Chart from 'chart.js/auto';

(() => {
  const $ = (s, el = document) => el.querySelector(s);
  const trendCanvas = $('#chartTrend');
  const reloadTrend = $('#btnReloadTrend');
  const reloadLinks = $('#btnReloadLinks');
  const linksRows = $('#topLinksRows');

  let chart = null;

  async function loadTrend() {
    if (!trendCanvas) return;
    const res = await window.App.fetchJson('/app/ajax/widgets/tracking/trend?days=30');
    if (!res.ok) { window.App.toast('Trend widget failed', 'error'); return; }
    const { labels, opens, clicks } = res.data;

    if (chart) chart.destroy();
    chart = new Chart(trendCanvas, {
      type: 'line',
      data: {
        labels,
        datasets: [
          { label: 'Opens', data: opens, tension: 0.25 },
          { label: 'Clicks', data: clicks, tension: 0.25 },
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { display: true } },
        scales: { x: { ticks: { maxTicksLimit: 8 } } }
      }
    });
  }

  function linksSkeleton() {
    if (!linksRows) return;
    linksRows.innerHTML = Array.from({ length: 6 }).map(() => `
      <tr>
        <td class="py-3 pr-3"><div class="h-4 w-72 bg-[var(--surface-2)] rounded"></div></td>
        <td class="py-3 text-right"><div class="h-4 w-10 bg-[var(--surface-2)] rounded ml-auto"></div></td>
      </tr>
    `).join('');
  }

  async function loadTopLinks() {
    if (!linksRows) return;
    linksSkeleton();
    const res = await window.App.fetchJson('/app/ajax/widgets/tracking/top-links?days=30');
    if (!res.ok) { window.App.toast('Top links widget failed', 'error'); return; }
    const items = res.data.items || [];
    if (!items.length) {
      linksRows.innerHTML = `<tr><td class="py-3 text-[var(--text-2)]" colspan="2">No clicks yet.</td></tr>`;
      return;
    }
    linksRows.innerHTML = items.map(it => `
      <tr class="hover:bg-[var(--surface-2)] cursor-pointer" data-uuid="${escapeHtml(it.outbound_uuid)}">
        <td class="py-3 pr-3 text-[var(--text-2)]">
          <div class="max-w-[520px] truncate" title="${escapeHtml(it.url || '')}">${escapeHtml(it.url || '')}</div>
        </td>
        <td class="py-3 text-right text-[var(--text-1)] font-semibold">${it.clicks ?? 0}</td>
      </tr>
    `).join('');
  }

  // Optional: clicking a row takes you to tracking events page filtered by uuid
  linksRows?.addEventListener('click', (e) => {
    const tr = e.target.closest('tr[data-uuid]');
    if (!tr) return;
    const uuid = tr.getAttribute('data-uuid');
    if (!uuid) return;
    window.location.href = `/app/tracking/events?search=${encodeURIComponent(uuid)}`;
  });

  reloadTrend?.addEventListener('click', loadTrend);
  reloadLinks?.addEventListener('click', loadTopLinks);

  loadTrend();
  loadTopLinks();

  function escapeHtml(s) {
    return String(s || '').replace(/[&<>"']/g, (c) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));
  }
})();