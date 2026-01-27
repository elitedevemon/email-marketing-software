@php
  $items = [
      ['label' => 'Dashboard', 'href' => route('app.dashboard')],
      ['label' => 'Clients', 'href' => '#', 'soon' => true],
      ['label' => 'Categories', 'href' => '#', 'soon' => true],
      ['label' => 'Senders', 'href' => '#', 'soon' => true],
      ['label' => 'Templates', 'href' => '#', 'soon' => true],
      ['label' => 'Sequences', 'href' => '#', 'soon' => true],
      ['label' => 'Inbox', 'href' => '#', 'soon' => true],
      ['label' => 'Reports', 'href' => '#', 'soon' => true],
      ['label' => 'Control Center', 'href' => '#', 'soon' => true],
  ];
@endphp

<aside
  class="w-72 shrink-0 border-r border-border/60 bg-card/60 backdrop-blur supports-[backdrop-filter]:bg-card/50"
  id="appSidebar">
  <div class="flex h-16 items-center gap-3 border-b border-border/60 px-4">
    <div class="grid h-9 w-9 place-items-center rounded-xl bg-primary font-bold text-primary-fg">
      E
    </div>
    <div class="min-w-0">
      <div class="sidebar-label font-semibold leading-tight">Email SaaS</div>
      <div class="sidebar-label text-xs text-muted-fg">Marketing + Proposals</div>
    </div>
  </div>

  <nav class="p-3">
    <div class="sidebar-label px-3 py-2 text-xs uppercase tracking-wide text-muted-fg">Workspace</div>

    <ul class="space-y-1">
      @foreach ($items as $item)
        @php
          $isActive = $item['href'] !== '#' && url()->current() === $item['href'];
          $base = 'flex items-center gap-3 rounded-xl px-3 py-2.5 transition';
          $active = 'bg-muted/60 text-fg';
          $idle = 'hover:bg-muted/40 text-fg/90';
        @endphp
        <li>
          <a class="{{ $base }} {{ $isActive ? $active : $idle }}" href="{{ $item['href'] }}"
            @if (!empty($item['soon'])) data-coming-soon="{{ $item['label'] }}" @endif>
            <span
              class="grid h-8 w-8 place-items-center rounded-lg border border-border/60 bg-bg/40 text-xs font-semibold">
              {{ strtoupper(substr($item['label'], 0, 1)) }}
            </span>
            <span class="sidebar-label truncate font-medium">{{ $item['label'] }}</span>
            @if (!empty($item['soon']))
              <span
                class="sidebar-label ml-auto rounded-full border border-border/60 px-2 py-0.5 text-[11px] text-muted-fg">Soon</span>
            @endif
          </a>
        </li>
      @endforeach
    </ul>
  </nav>

  <div class="mt-auto p-3">
    <div class="rounded-xl border border-border/60 bg-bg/40 p-3">
      <div class="sidebar-label text-sm font-semibold">Deliverability first</div>
      <div class="sidebar-label mt-1 text-xs text-muted-fg">
        No spam tricks. Suppression & unsubscribe enforced.
      </div>
    </div>
  </div>
</aside>
