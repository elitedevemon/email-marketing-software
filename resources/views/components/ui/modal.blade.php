@props([
    'id',
    'title' => '',
    'maxWidth' => '2xl', // sm|md|lg|xl|2xl|3xl|4xl
])

@php
  $maxWidthClass = match ($maxWidth) {
      'sm' => 'max-w-sm',
      'md' => 'max-w-md',
      'lg' => 'max-w-lg',
      'xl' => 'max-w-xl',
      '2xl' => 'max-w-2xl',
      '3xl' => 'max-w-3xl',
      '4xl' => 'max-w-4xl',
      default => 'max-w-2xl',
  };
@endphp

<div class="fixed inset-0 z-50 hidden" data-modal="{{ $id }}" aria-hidden="true">
  <!-- Backdrop -->
  <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]" data-modal-close></div>

  <!-- Dialog wrapper -->
  <div class="relative flex min-h-full items-end justify-center p-4 sm:items-center sm:p-6">
    <!-- Panel -->
    <div
      class="{{ $maxWidthClass }} flex max-h-[calc(100vh-2rem)] w-full flex-col rounded-2xl border border-border/60 bg-card shadow-xl sm:max-h-[calc(100vh-4rem)]"
      role="dialog" aria-modal="true">
      <!-- Header (sticky inside modal) -->
      <div class="flex shrink-0 items-center justify-between gap-3 border-b border-border/60 px-5 py-4">
        <div class="min-w-0">
          <div class="truncate font-semibold">{{ $title }}</div>
        </div>
        <button
          class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-border/60 bg-bg/40 text-sm font-semibold transition hover:bg-muted/40"
          data-modal-close type="button" aria-label="Close">
          ✕
        </button>
      </div>

      <!-- Body (scrollable) -->
      <div class="min-h-0 overflow-y-auto overscroll-contain px-5 py-4">
        {{ $slot }}
      </div>
    </div>
  </div>
</div>
