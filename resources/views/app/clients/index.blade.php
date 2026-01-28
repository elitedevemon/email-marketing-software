<x-layouts.app-shell :title="'Clients — Email SaaS'">
  <div class="flex flex-col gap-4">
    <div class="flex items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-semibold tracking-tight">Clients</h1>
        <p class="mt-1 text-sm text-muted-fg">Single screen control: add/edit, tags, notes — AJAX-first.</p>
      </div>
      <button
        class="h-10 rounded-xl bg-primary px-4 text-sm font-semibold text-primary-fg transition hover:opacity-95"
        id="btnNewClient" data-modal-open="clientModal" type="button">
        New Client
      </button>
    </div>

    <div class="rounded-2xl border border-border/60 bg-card/60 p-4">
      <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <div class="flex-1">
          <input
            class="h-10 w-full rounded-xl border border-border/60 bg-bg/40 px-4 text-sm placeholder:text-muted-fg focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="clientSearch" type="text" placeholder="Search by business, email, city…">
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <select
            class="h-10 rounded-xl border border-border/60 bg-bg/40 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="clientStatus">
            <option value="all">All status</option>
            <option value="prospect">Prospect</option>
            <option value="engaged">Engaged</option>
            <option value="paused">Paused</option>
            <option value="suppressed">Suppressed</option>
            <option value="archived">Archived</option>
          </select>

          <select
            class="h-10 rounded-xl border border-border/60 bg-bg/40 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="clientCategory">
            <option value="">All categories</option>
            @foreach ($categories as $cat)
              <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
          </select>

          <input
            class="h-10 w-48 rounded-xl border border-border/60 bg-bg/40 px-3 text-sm placeholder:text-muted-fg focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="clientTag" type="text" placeholder="Tag filter (e.g. agency)">

          <button
            class="h-10 rounded-xl border border-border/60 bg-bg/40 px-4 text-sm font-semibold transition hover:bg-muted/40"
            id="clientRefresh" type="button">
            Refresh
          </button>
        </div>
      </div>

      <div class="mt-4 overflow-hidden rounded-2xl border border-border/60">
        <table class="w-full text-sm">
          <thead class="bg-muted/40">
            <tr class="text-left">
              <th class="px-4 py-3 font-semibold">Client</th>
              <th class="px-4 py-3 font-semibold">Category</th>
              <th class="px-4 py-3 font-semibold">Status</th>
              <th class="px-4 py-3 font-semibold">Tags</th>
              <th class="px-4 py-3 font-semibold">Notes</th>
              <th class="px-4 py-3 text-right font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border/60 bg-card" id="clientTbody">
            <!-- JS renders -->
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex items-center justify-between gap-3">
        <div class="text-sm text-muted-fg" id="clientMeta"></div>
        <div class="flex items-center gap-2">
          <button
            class="h-9 rounded-xl border border-border/60 bg-bg/40 px-3 text-sm font-semibold transition hover:bg-muted/40"
            id="clientPrev" type="button">
            Prev
          </button>
          <button
            class="h-9 rounded-xl border border-border/60 bg-bg/40 px-3 text-sm font-semibold transition hover:bg-muted/40"
            id="clientNext" type="button">
            Next
          </button>
        </div>
      </div>
    </div>
  </div>

  <x-ui.modal id="clientModal" title="Client">
    <form class="space-y-4" id="clientForm">
      <input id="clientId" type="hidden" value="">

      <div class="grid gap-3 sm:grid-cols-2">
        <div>
          <label class="text-sm font-semibold">Business name</label>
          <input
            class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-bg/40 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="clientBusiness" type="text">
          <div class="mt-1 hidden text-xs text-danger" data-err="business_name"></div>
        </div>
        <div>
          <label class="text-sm font-semibold">Contact name</label>
          <input
            class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-bg/40 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="clientContact" type="text">
          <div class="mt-1 hidden text-xs text-danger" data-err="contact_name"></div>
        </div>
      </div>

      <div class="grid gap-3 sm:grid-cols-2">
        <div>
          <label class="text-sm font-semibold">Email</label>
          <input
            class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-bg/40 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="clientEmail" type="email">
          <div class="mt-1 hidden text-xs text-danger" data-err="email"></div>
        </div>
        <div>
          <label class="text-sm font-semibold">Website URL</label>
          <input
            class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-bg/40 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="clientWebsite" type="url" placeholder="https://example.com">
          <div class="mt-1 hidden text-xs text-danger" data-err="website_url"></div>
        </div>
      </div>

      <div class="grid gap-3 sm:grid-cols-2">
        <div>
          <label class="text-sm font-semibold">City</label>
          <input
            class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-bg/40 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="clientCity" type="text">
          <div class="mt-1 hidden text-xs text-danger" data-err="city"></div>
        </div>
        <div>
          <label class="text-sm font-semibold">Country</label>
          <input
            class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-bg/40 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="clientCountry" type="text">
          <div class="mt-1 hidden text-xs text-danger" data-err="country"></div>
        </div>
      </div>

      <div class="grid gap-3 sm:grid-cols-2">
        <div>
          <label class="text-sm font-semibold">Category</label>
          <select
            class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-bg/40 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="clientCategoryId">
            <option value="">—</option>
            @foreach ($categories as $cat)
              <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
          </select>
          <div class="mt-1 hidden text-xs text-danger" data-err="category_id"></div>
        </div>
        <div>
          <label class="text-sm font-semibold">Status</label>
          <select
            class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-bg/40 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="clientStatusVal">
            <option value="prospect">Prospect</option>
            <option value="engaged">Engaged</option>
            <option value="paused">Paused</option>
            <option value="suppressed">Suppressed</option>
            <option value="archived">Archived</option>
          </select>
          <div class="mt-1 hidden text-xs text-danger" data-err="status"></div>
        </div>
      </div>

      <div>
        <label class="text-sm font-semibold">Tags</label>
        <input
          class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-bg/40 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
          id="clientTags" type="text" placeholder="comma separated: agency, dhaka, dentist">
        <div class="mt-1 hidden text-xs text-danger" data-err="tags"></div>
      </div>

      <div class="flex items-center justify-between">
        <button
          class="h-10 rounded-xl border border-border/60 bg-bg/40 px-4 text-sm font-semibold transition hover:bg-muted/40"
          data-modal-close type="button">
          Cancel
        </button>
        <button
          class="h-10 rounded-xl bg-primary px-4 text-sm font-semibold text-primary-fg transition hover:opacity-95"
          id="clientSave" type="submit">
          Save
        </button>
      </div>
    </form>
  </x-ui.modal>

  <x-ui.modal id="clientNotesModal" title="Client notes">
    <div class="space-y-4">
      <input id="notesClientId" type="hidden" value="">
      <div class="text-sm text-muted-fg" id="notesHeader"></div>

      <div class="max-h-[360px] space-y-3 overflow-auto pr-1" id="notesList">
        <!-- JS renders -->
      </div>

      <form class="space-y-2" id="noteForm">
        <textarea
          class="w-full rounded-xl border border-border/60 bg-bg/40 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
          id="noteBody" rows="3" placeholder="Add an internal note…"></textarea>
        <div class="hidden text-xs text-danger" id="noteErr"></div>
        <div class="flex items-center justify-between">
          <button
            class="h-10 rounded-xl border border-border/60 bg-bg/40 px-4 text-sm font-semibold transition hover:bg-muted/40"
            data-modal-close type="button">
            Close
          </button>
          <button
            class="h-10 rounded-xl bg-primary px-4 text-sm font-semibold text-primary-fg transition hover:opacity-95"
            id="noteSave" type="submit">
            Add Note
          </button>
        </div>
      </form>
    </div>
  </x-ui.modal>

  @push('scripts')
    @vite(['resources/js/pages/clients.js'])
  @endpush
</x-layouts.app-shell>
