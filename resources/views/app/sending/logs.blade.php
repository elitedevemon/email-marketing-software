@php($title = 'Send Logs')
<x-layouts.app-shell :title="$title">
  <div class="p-6">
    <div class="flex items-center justify-between gap-3">
      <div>
        <h1 class="text-xl font-semibold text-[var(--text-1)]">Send Logs</h1>
        <p class="mt-1 text-sm text-[var(--text-2)]">
          SMTP attempts + outcomes. Use this to debug deliverability and retries.
        </p>
      </div>
    </div>

    <div class="mt-4 flex flex-col gap-2 md:flex-row md:items-center">
      <input
        class="w-full rounded-lg border border-[var(--border)] bg-[var(--surface-2)] px-3 py-2 text-sm md:w-96"
        id="search" placeholder="Search to_email, subject, uuid, error…" />
      <select
        class="w-full rounded-lg border border-[var(--border)] bg-[var(--surface-2)] px-3 py-2 text-sm md:w-52"
        id="status">
        <option value="">All statuses</option>
        <option value="success">Success</option>
        <option value="failed">Failed</option>
        <option value="skipped">Skipped</option>
        <option value="retrying">Retrying</option>
      </select>
    </div>

    <div class="mt-4 overflow-hidden rounded-xl border border-[var(--border)] bg-[var(--surface-1)]">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-[var(--surface-2)] text-[var(--text-2)]">
            <tr>
              <th class="px-4 py-3 text-left font-medium">Time</th>
              <th class="px-4 py-3 text-left font-medium">To</th>
              <th class="px-4 py-3 text-left font-medium">Subject</th>
              <th class="px-4 py-3 text-left font-medium">Status</th>
              <th class="px-4 py-3 text-left font-medium">Attempt</th>
              <th class="px-4 py-3 text-left font-medium">Duration</th>
              <th class="px-4 py-3 text-left font-medium">Error</th>
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

  @vite(['resources/js/pages/send-logs.js'])
</x-layouts.app-shell>
