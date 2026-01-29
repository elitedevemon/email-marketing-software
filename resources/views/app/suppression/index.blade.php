@php($title = 'Suppression')
<x-layouts.app-shell :title="$title">
  <div class="p-6">
    <div class="flex items-center justify-between gap-3">
      <div>
        <h1 class="text-xl font-semibold text-[var(--text-1)]">Suppression</h1>
        <p class="mt-1 text-sm text-[var(--text-2)]">
          Global “do not contact” list. Unsubscribe & bounces will land here.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <button class="rounded-lg bg-[var(--primary)] px-3 py-2 text-sm text-white hover:opacity-90"
          id="btnAddSuppression">
          Add
        </button>
      </div>
    </div>

    <div class="mt-4 flex items-center gap-2">
      <input
        class="w-full rounded-lg border border-[var(--border)] bg-[var(--surface-2)] px-3 py-2 text-sm md:w-96"
        id="search" placeholder="Search email, reason, source…" />
    </div>

    <div class="mt-4 overflow-hidden rounded-xl border border-[var(--border)] bg-[var(--surface-1)]">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-[var(--surface-2)] text-[var(--text-2)]">
            <tr>
              <th class="px-4 py-3 text-left font-medium">Email</th>
              <th class="px-4 py-3 text-left font-medium">Reason</th>
              <th class="px-4 py-3 text-left font-medium">Source</th>
              <th class="px-4 py-3 text-left font-medium">Created</th>
              <th class="px-4 py-3 text-right font-medium">Actions</th>
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

  <x-ui.modal id="modalSuppression" title="Add to suppression">
    <form class="space-y-3" id="formSuppression">
      <div>
        <label class="mb-1 block text-sm text-[var(--text-2)]">Email</label>
        <input class="w-full rounded-lg border border-[var(--border)] bg-[var(--surface-2)] px-3 py-2 text-sm"
          name="email" type="email" placeholder="client@example.com" required />
        <p class="mt-1 hidden text-xs text-red-500" data-err="email"></p>
      </div>
      <div>
        <label class="mb-1 block text-sm text-[var(--text-2)]">Reason</label>
        <input class="w-full rounded-lg border border-[var(--border)] bg-[var(--surface-2)] px-3 py-2 text-sm"
          name="reason" type="text" placeholder="Unsubscribed / Bounce / Manual…" />
        <p class="mt-1 hidden text-xs text-red-500" data-err="reason"></p>
      </div>
      <div class="flex items-center justify-end gap-2 pt-2">
        <button class="rounded-lg border border-[var(--border)] px-3 py-2 text-sm hover:bg-[var(--surface-2)]"
          data-modal-close type="button">
          Cancel
        </button>
        <button class="rounded-lg bg-[var(--primary)] px-3 py-2 text-sm text-white hover:opacity-90"
          type="submit">
          Save
        </button>
      </div>
    </form>
  </x-ui.modal>

  @vite(['resources/js/pages/suppression.js'])
</x-layouts.app-shell>
