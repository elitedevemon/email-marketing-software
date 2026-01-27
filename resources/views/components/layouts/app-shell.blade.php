<!doctype html>
<html class="h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Email SaaS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>

  <body class="h-full bg-bg text-fg antialiased">
    <x-ui.toast-container />

    <div class="flex min-h-screen">
      <x-app.sidebar />

      <div class="min-w-0 flex-1">
        <x-app.topbar />
        <main class="px-6 py-6">
          {{ $slot }}
        </main>
      </div>
    </div>

    <x-ui.modal id="quickActions" title="Quick actions">
      <div class="grid gap-3 sm:grid-cols-2">
        <button
          class="rounded-xl border border-border bg-card px-4 py-3 text-left transition hover:bg-muted/40"
          data-coming-soon="Add client" type="button">
          <div class="font-semibold">Add Client</div>
          <div class="text-sm text-muted-fg">Create a new client (modal-first)</div>
        </button>
        <button
          class="rounded-xl border border-border bg-card px-4 py-3 text-left transition hover:bg-muted/40"
          data-coming-soon="Add sender" type="button">
          <div class="font-semibold">Add Sender</div>
          <div class="text-sm text-muted-fg">Connect SMTP/IMAP credentials</div>
        </button>
        <button
          class="rounded-xl border border-border bg-card px-4 py-3 text-left transition hover:bg-muted/40"
          data-coming-soon="Create template" type="button">
          <div class="font-semibold">Create Template</div>
          <div class="text-sm text-muted-fg">Start from blocks & versioning</div>
        </button>
        <button
          class="rounded-xl border border-border bg-card px-4 py-3 text-left transition hover:bg-muted/40"
          data-coming-soon="View queue" type="button">
          <div class="font-semibold">Queue / Jobs</div>
          <div class="text-sm text-muted-fg">Backlog, retries, failures</div>
        </button>
      </div>
    </x-ui.modal>
  </body>

</html>
