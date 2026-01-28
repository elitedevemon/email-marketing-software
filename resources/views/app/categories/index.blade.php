<x-layouts.app-shell :title="'Categories — Email SaaS'">
  <div class="flex flex-col gap-4">
    <div class="flex items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-semibold tracking-tight">Categories</h1>
        <p class="mt-1 text-sm text-muted-fg">Manage your own categories (no prebuilt).</p>
      </div>
      <button
        class="h-10 rounded-xl bg-primary px-4 text-sm font-semibold text-primary-fg transition hover:opacity-95"
        id="btnNewCategory" data-modal-open="categoryModal" type="button">
        New Category
      </button>
    </div>

    <div class="rounded-2xl border border-border/60 bg-card/60 p-4">
      <div class="flex flex-col gap-3 md:flex-row md:items-center">
        <div class="flex-1">
          <input
            class="h-10 w-full rounded-xl border border-border/60 bg-bg/40 px-4 text-sm placeholder:text-muted-fg focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="catSearch" type="text" placeholder="Search categories…">
        </div>
        <div class="flex items-center gap-2">
          <select
            class="h-10 rounded-xl border border-border/60 bg-bg/40 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="catStatus">
            <option value="all">All</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
          <button
            class="h-10 rounded-xl border border-border/60 bg-bg/40 px-4 text-sm font-semibold transition hover:bg-muted/40"
            id="catRefresh" type="button">
            Refresh
          </button>
        </div>
      </div>

      <div class="mt-4 overflow-hidden rounded-2xl border border-border/60">
        <table class="w-full text-sm">
          <thead class="bg-muted/40">
            <tr class="text-left">
              <th class="px-4 py-3 font-semibold">Name</th>
              <th class="px-4 py-3 font-semibold">Status</th>
              <th class="px-4 py-3 font-semibold">Sort</th>
              <th class="px-4 py-3 font-semibold">Created</th>
              <th class="px-4 py-3 text-right font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border/60 bg-card" id="catTbody">
            <!-- JS renders -->
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex items-center justify-between gap-3">
        <div class="text-sm text-muted-fg" id="catMeta"></div>
        <div class="flex items-center gap-2">
          <button
            class="h-9 rounded-xl border border-border/60 bg-bg/40 px-3 text-sm font-semibold transition hover:bg-muted/40"
            id="catPrev" type="button">
            Prev
          </button>
          <button
            class="h-9 rounded-xl border border-border/60 bg-bg/40 px-3 text-sm font-semibold transition hover:bg-muted/40"
            id="catNext" type="button">
            Next
          </button>
        </div>
      </div>
    </div>
  </div>

  <x-ui.modal id="categoryModal" title="Category">
    <form class="space-y-4" id="catForm">
      <input id="catId" type="hidden" value="">

      <div>
        <label class="text-sm font-semibold">Name</label>
        <input
          class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-bg/40 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
          id="catName" type="text">
        <div class="mt-1 hidden text-xs text-danger" data-err="name"></div>
      </div>

      <div class="grid gap-3 sm:grid-cols-2">
        <div>
          <label class="text-sm font-semibold">Color</label>
          <div class="mt-2 flex items-center gap-3">
            <input class="h-10 w-14 rounded-xl border border-border/60 bg-bg/40 p-1" id="catColorPicker"
              type="color" value="#3b82f6">
            <input
              class="h-10 flex-1 rounded-xl border border-border/60 bg-bg/40 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
              id="catColor" type="text" placeholder="#3b82f6">
          </div>
          <div class="mt-1 hidden text-xs text-danger" data-err="color"></div>
        </div>

        <div>
          <label class="text-sm font-semibold">Sort order</label>
          <input
            class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-bg/40 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="catSort" type="number" value="0" min="0">
          <div class="mt-1 hidden text-xs text-danger" data-err="sort_order"></div>
        </div>
      </div>

      <div class="flex items-center justify-between">
        <label class="inline-flex items-center gap-2">
          <input class="rounded border-border/60" id="catActive" type="checkbox" checked>
          <span class="text-sm font-semibold">Active</span>
        </label>

        <div class="flex items-center gap-2">
          <button
            class="h-10 rounded-xl border border-border/60 bg-bg/40 px-4 text-sm font-semibold transition hover:bg-muted/40"
            data-modal-close type="button">
            Cancel
          </button>
          <button
            class="h-10 rounded-xl bg-primary px-4 text-sm font-semibold text-primary-fg transition hover:opacity-95"
            id="catSave" type="submit">
            Save
          </button>
        </div>
      </div>
    </form>
  </x-ui.modal>

  @push('scripts')
    @vite(['resources/js/pages/categories.js'])
  @endpush
</x-layouts.app-shell>
