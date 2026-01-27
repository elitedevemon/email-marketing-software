<header
  class="sticky top-0 z-30 border-b border-border/60 bg-bg/70 backdrop-blur supports-[backdrop-filter]:bg-bg/50">
  <div class="flex h-16 items-center gap-3 px-6">
    <button
      class="grid h-10 w-10 place-items-center rounded-xl border border-border/60 bg-card/60 transition hover:bg-muted/40"
      data-sidebar-toggle type="button" aria-label="Toggle sidebar">
      <span class="text-sm font-semibold">≡</span>
    </button>

    <div class="min-w-0 flex-1">
      <div class="relative max-w-xl">
        <input
          class="h-10 w-full rounded-xl border border-border/60 bg-card/60 px-4 pr-10 text-sm placeholder:text-muted-fg focus:outline-none focus:ring-2 focus:ring-ring/30"
          type="text" placeholder="Search clients, templates, sequences…">
        <div class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-muted-fg">
          ⌘K
        </div>
      </div>
    </div>

    <button
      class="h-10 rounded-xl bg-primary px-4 text-sm font-semibold text-primary-fg transition hover:opacity-95"
      data-modal-open="quickActions" type="button">
      New
    </button>

    <button
      class="grid h-10 w-10 place-items-center rounded-xl border border-border/60 bg-card/60 transition hover:bg-muted/40"
      data-theme-toggle type="button" aria-label="Toggle theme">
      <span class="text-sm">☾</span>
    </button>

    <div class="flex h-10 items-center gap-2 rounded-xl border border-border/60 bg-card/60 px-3">
      <div class="grid h-7 w-7 place-items-center rounded-lg bg-muted/60 text-xs font-semibold">
        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
      </div>
      <div class="hidden sm:block">
        <div class="text-sm font-semibold leading-tight">{{ auth()->user()->name ?? 'User' }}</div>
        <div class="text-xs leading-tight text-muted-fg">{{ auth()->user()->email ?? '' }}</div>
      </div>
      <form class="ml-2" method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="text-xs text-muted-fg transition hover:text-fg" type="submit">Logout</button>
      </form>
    </div>
  </div>
</header>
