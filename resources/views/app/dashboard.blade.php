<x-layouts.app-shell :title="'Dashboard — Email SaaS'">
  <div class="flex items-start justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold tracking-tight">Dashboard</h1>
      <p class="mt-1 text-sm text-muted-fg">
        Widget-based reports (AJAX) আসছে। এখন UI foundation + RBAC ready.
      </p>
    </div>
    <button
      class="h-10 rounded-xl border border-border/60 bg-card/60 px-4 text-sm font-semibold transition hover:bg-muted/40"
      data-coming-soon="Dashboard refresh" type="button">
      Refresh
    </button>
  </div>

  <div class="mt-6 grid gap-4 lg:grid-cols-4">
    @foreach ([['Clients', '—'], ['Active sequences', '—'], ['Opens (7d)', '—'], ['Replies (7d)', '—']] as $kpi)
      <div class="rounded-2xl border border-border/60 bg-card/60 p-4">
        <div class="text-sm text-muted-fg">{{ $kpi[0] }}</div>
        <div class="mt-2 text-2xl font-semibold">{{ $kpi[1] }}</div>
        <div class="mt-3 h-2 overflow-hidden rounded-full bg-muted/50">
          <div class="h-full w-1/3 rounded-full bg-primary/60"></div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
    <div class="rounded-xl border border-[var(--border)] bg-[var(--surface-1)] p-4">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-sm font-semibold text-[var(--text-1)]">Opens vs Clicks</div>
          <div class="text-xs text-[var(--text-2)]">Last 30 days</div>
        </div>
        <button
          class="rounded-lg border border-[var(--border)] px-3 py-1.5 text-sm hover:bg-[var(--surface-2)]"
          id="btnReloadTrend">Reload</button>
      </div>
      <div class="mt-3">
        <canvas id="chartTrend" height="120"></canvas>
      </div>
    </div>

    <div class="rounded-xl border border-[var(--border)] bg-[var(--surface-1)] p-4">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-sm font-semibold text-[var(--text-1)]">Top clicked links</div>
          <div class="text-xs text-[var(--text-2)]">Last 30 days</div>
        </div>
        <button
          class="rounded-lg border border-[var(--border)] px-3 py-1.5 text-sm hover:bg-[var(--surface-2)]"
          id="btnReloadLinks">Reload</button>
      </div>
      <div class="mt-3 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-[var(--text-2)]">
            <tr>
              <th class="py-2 pr-3 text-left font-medium">URL</th>
              <th class="py-2 text-right font-medium">Clicks</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[var(--border)]" id="topLinksRows"></tbody>
        </table>
      </div>
    </div>
  </div>

  @vite(['resources/js/widgets/tracking-widgets.js'])

  <div class="mt-6 grid gap-4 lg:grid-cols-3">
    <div class="rounded-2xl border border-border/60 bg-card/60 p-4 lg:col-span-2">
      <div class="flex items-center justify-between">
        <div class="font-semibold">Performance (placeholder)</div>
        <button class="text-sm text-muted-fg transition hover:text-fg" data-coming-soon="Drilldown modal"
          type="button">Drilldown</button>
      </div>
      <div
        class="mt-4 grid h-56 place-items-center rounded-xl border border-border/60 bg-bg/40 text-sm text-muted-fg">
        Chart widget will load via AJAX (Step 14)
      </div>
    </div>

    <div class="rounded-2xl border border-border/60 bg-card/60 p-4">
      <div class="font-semibold">System</div>
      <ul class="mt-4 space-y-3 text-sm">
        <li class="flex items-center justify-between">
          <span class="text-muted-fg">Queue</span>
          <span class="font-semibold">DB driver (later)</span>
        </li>
        <li class="flex items-center justify-between">
          <span class="text-muted-fg">Cron</span>
          <span class="font-semibold">Secure endpoint (later)</span>
        </li>
        <li class="flex items-center justify-between">
          <span class="text-muted-fg">Suppression</span>
          <span class="font-semibold">Planned</span>
        </li>
      </ul>
    </div>
  </div>
</x-layouts.app-shell>
