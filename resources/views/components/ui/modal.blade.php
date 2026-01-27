@props(['id', 'title' => null])

<div class="fixed inset-0 z-[90] hidden" data-modal="{{ $id }}">
  <div class="absolute inset-0 bg-black/40" data-modal-close></div>

  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl rounded-2xl border border-border bg-card text-card-fg shadow-xl">
      <div class="flex items-center gap-3 border-b border-border/60 px-5 py-4">
        <div class="min-w-0">
          @if ($title)
            <div class="font-semibold">{{ $title }}</div>
          @endif
        </div>
        <button class="ml-auto grid h-9 w-9 place-items-center rounded-xl transition hover:bg-muted/40"
          data-modal-close type="button" aria-label="Close">
          ✕
        </button>
      </div>

      <div class="p-5">
        {{ $slot }}
      </div>
    </div>
  </div>
</div>
