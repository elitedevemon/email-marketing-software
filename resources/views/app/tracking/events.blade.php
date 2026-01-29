@php($title = 'Tracking Events')
<x-layouts.app-shell :title="$title">
  <div class="p-6">
    <div class="flex items-center justify-between gap-3">
      <div>
        <h1 class="text-xl font-semibold text-[var(--text-1)]">Tracking Events</h1>
        <p class="mt-1 text-sm text-[var(--text-2)]">
          Pixel opens and redirect clicks. Opens are not 100% accurate (privacy proxies, image blocking).
        </p>
      </div>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-2 md:grid-cols-5">
      <input
        class="w-full rounded-lg border border-[var(--border)] bg-[var(--surface-2)] px-3 py-2 text-sm md:col-span-2"
        id="search" placeholder="Search outbound uuid / IP…" />
      <select class="w-full rounded-lg border border-[var(--border)] bg-[var(--surface-2)] px-3 py-2 text-sm"
        id="type">
        <option value="">All types</option>
        <option value="open">Open</option>
        <option value="click">Click</option>
      </select>
      <input class="w-full rounded-lg border border-[var(--border)] bg-[var(--surface-2)] px-3 py-2 text-sm"
        id="from" type="date" />
      <input class="w-full rounded-lg border border-[var(--border)] bg-[var(--surface-2)] px-3 py-2 text-sm"
        id="to" type="date" />
    </div>

    <div class="mt-4 overflow-hidden rounded-xl border border-[var(--border)] bg-[var(--surface-1)]">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-[var(--surface-2)] text-[var(--text-2)]">
            <tr>
              <th class="px-4 py-3 text-left font-medium">Time</th>
              <th class="px-4 py-3 text-left font-medium">Type</th>
              <th class="px-4 py-3 text-left font-medium">Outbound</th>
              <th class="px-4 py-3 text-left font-medium">IP</th>
              <th class="px-4 py-3 text-left font-medium">UA</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[var(--border)]" id="rows"></tbody>
        </table>
      </div>
      <div class="flex items-center justify-between px-4 py-3 text-sm text-[var(--text-2)]">
        <div id="meta"></div>
        <div class="flex items-center gap-2">
          <button class="rounded-lg border border-[var(--border)] px-3 py-1.5 hover:bg-[var(--surface-2)]"
            id="prev">
            Prev
          </button>
          <button class="rounded-lg border border-[var(--border)] px-3 py-1.5 hover:bg-[var(--surface-2)]"
            id="next">
            Next
          </button>
        </div>
      </div>
    </div>
  </div>

  <x-ui.modal id="modalOutboundTracking" title="Outbound tracking">
    <div class="space-y-4">
      <div class="grid grid-cols-2 gap-3">
        <div class="rounded-lg border border-[var(--border)] bg-[var(--surface-2)] p-3">
          <div class="text-xs text-[var(--text-2)]">Opens</div>
          <div class="text-lg font-semibold text-[var(--text-1)]" id="mOpens">—</div>
        </div>
        <div class="rounded-lg border border-[var(--border)] bg-[var(--surface-2)] p-3">
          <div class="text-xs text-[var(--text-2)]">Clicks</div>
          <div class="text-lg font-semibold text-[var(--text-1)]" id="mClicks">—</div>
        </div>
      </div>

      <div class="overflow-hidden rounded-xl border border-[var(--border)]">
        <table class="min-w-full text-sm">
          <thead class="bg-[var(--surface-2)] text-[var(--text-2)]">
            <tr>
              <th class="px-4 py-2 text-left font-medium">URL</th>
              <th class="px-4 py-2 text-right font-medium">Clicks</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[var(--border)]" id="mLinks"></tbody>
        </table>
      </div>
    </div>
  </x-ui.modal>

  @vite(['resources/js/pages/tracking-events.js'])
</x-layouts.app-shell>
