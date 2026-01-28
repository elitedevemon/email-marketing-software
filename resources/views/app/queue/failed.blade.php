<x-layouts.app-shell :title="'Failed Jobs — Email SaaS'">
  <div class="flex flex-col gap-4">
    <div class="flex items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-semibold tracking-tight">Failed Jobs</h1>
        <p class="mt-1 text-sm text-muted-fg">Inspect, retry, or forget failed queue jobs (admin actions).</p>
      </div>
    </div>

    <div class="rounded-2xl border border-border/60 bg-card/60 p-4">
      <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <div class="flex-1">
          <input
            class="h-10 w-full rounded-xl border border-border/60 bg-bg/40 px-4 text-sm placeholder:text-muted-fg focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="fjSearch" type="text" placeholder="Search by queue / connection / exception…">
        </div>
        <div class="flex items-center gap-2">
          <input
            class="text-smplaceholder:text-muted-fg h-10 w-56 rounded-xl border border-border/60 bg-bg/40 px-3 focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="fjQueue" type="text" placeholder="Queue filter (optional)">
          <button
            class="hover:bg-muted/40transition h-10 rounded-xl border border-border/60 bg-bg/40 px-4 text-sm font-semibold"
            id="fjRefresh" type="button">
            Refresh
          </button>
        </div>
      </div>

      <div class="mt-4 overflow-hidden rounded-2xl border border-border/60">
        <table class="w-full text-sm">
          <thead class="bg-muted/40">
            <tr class="text-left">
              <th class="px-4 py-3 font-semibold">When</th>
              <th class="px-4 py-3 font-semibold">Connection</th>
              <th class="px-4 py-3 font-semibold">Queue</th>
              <th class="px-4 py-3 font-semibold">Exception</th>
              <th class="px-4 py-3 text-right font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border/60 bg-card" id="fjTbody">
            <!-- JS renders -->
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex items-center justify-between gap-3">
        <div class="text-sm text-muted-fg" id="fjMeta"></div>
        <div class="flex items-center gap-2">
          <button
            class="h-9 rounded-xl border border-border/60 bg-bg/40 px-3 text-sm font-semibold transition hover:bg-muted/40"
            id="fjPrev" type="button">
            Prev
          </button>
          <button
            class="h-9 rounded-xl border border-border/60 bg-bg/40 px-3 text-sm font-semibold transition hover:bg-muted/40"
            id="fjNext" type="button">
            Next
          </button>
        </div>
      </div>
    </div>
  </div>

  <x-ui.modal id="fjModal" title="Failed job details">
    <div class="space-y-3">
      <div class="text-sm text-muted-fg" id="fjDetailMeta"></div>
      <div class="rounded-xl border border-border/60 bg-bg/40 p-3">
        <div class="text-sm font-semibold">Exception</div>
        <pre class="mt-2 max-h-[260px] overflow-auto whitespace-pre-wrap text-xs" id="fjException"></pre>
      </div>
      <div class="rounded-xl border border-border/60 bg-bg/40 p-3">
        <div class="text-sm font-semibold">Payload</div>
        <pre class="max-h-[260px mt-2 overflow-auto whitespace-pre-wrap text-xs" id="fjPayload"></pre>
      </div>
      <div class="flex items-center justify-end">
        <button
          class="hover:bg-muted/40transition h-10 rounded-xl border border-border/60 bg-bg/40 px-4 text-sm font-semibold"
          data-modal-close type="button">
          Close
        </button>
      </div>
    </div>
  </x-ui.modal>

  @push('scripts')
    @vite(['resources/js/pages/failed-jobs.js'])
  @endpush
</x-layouts.app-shell>
