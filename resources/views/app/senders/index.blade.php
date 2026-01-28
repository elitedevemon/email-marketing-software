<x-layouts.app-shell :title="'Senders — Email SaaS'">
  <div class="flex flex-col gap-4">
    <div class="flex items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-semibold tracking-tight">Senders</h1>
        <p class="mt-1 text-sm text-muted-fg">Multiple sender accounts with limits, windows, and encrypted
          credentials.</p>
      </div>
      <button
        class="h-10 rounded-xl bg-primary px-4 text-sm font-semibold text-primary-fg transition hover:opacity-95"
        id="btnNewSender" data-modal-open="senderModal" type="button">
        New Sender
      </button>
    </div>

    <div class="rounded-2xl border border-border/60 bg-card/60 p-4">
      <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <div class="flex-1">
          <input
            class="h-10 w-full rounded-xl border border-border/60 bg-bg/40 px-4 text-sm placeholder:text-muted-fg focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="senderSearch" type="text" placeholder="Search by name / from email / username…">
        </div>
        <div class="flex items-center gap-2">
          <select
            class="h-10 rounded-xl border border-border/60 bg-bg/40 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="senderStatus">
            <option value="all">All</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
          <button
            class="h-10 rounded-xl border border-border/60 bg-bg/40 px-4 text-sm font-semibold transition hover:bg-muted/40"
            id="senderRefresh" type="button">
            Refresh
          </button>
        </div>
      </div>

      <div class="mt-4 overflow-hidden rounded-2xl border border-border/60">
        <table class="w-full text-sm">
          <thead class="bg-muted/40">
            <tr class="text-left">
              <th class="px-4 py-3 font-semibold">Sender</th>
              <th class="px-4 py-3 font-semibold">Status</th>
              <th class="px-4 py-3 font-semibold">Daily</th>
              <th class="px-4 py-3 font-semibold">Window</th>
              <th class="px-4 py-3 font-semibold">SMTP</th>
              <th class="px-4 py-3 text-right font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border/60 bg-card" id="senderTbody">
            <!-- JS renders -->
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex items-center justify-between gap-3">
        <div class="text-sm text-muted-fg" id="senderMeta"></div>
        <div class="flex items-center gap-2">
          <button
            class="h-9 rounded-xl border border-border/60 bg-bg/40 px-3 text-sm font-semibold transition hover:bg-muted/40"
            id="senderPrev" type="button">
            Prev
          </button>
          <button
            class="h-9 rounded-xl border border-border/60 bg-bg/40 px-3 text-sm font-semibold transition hover:bg-muted/40"
            id="senderNext" type="button">
            Next
          </button>
        </div>
      </div>
    </div>
  </div>

  <x-ui.modal id="senderModal" title="Sender">
    <form class="space-y-4" id="senderForm">
      <input id="senderId" type="hidden" value="">

      <div class="grid gap-3 sm:grid-cols-2">
        <div>
          <label class="text-sm font-semibold">Label</label>
          <input
            class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-bg/40 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="senderName" type="text" placeholder="e.g. Gmail A">
          <div class="mt-1 hidden text-xs text-danger" data-err="name"></div>
        </div>
        <div class="flex items-end">
          <label class="inline-flex items-center gap-2">
            <input class="rounded border-border/60" id="senderActive" type="checkbox" checked>
            <span class="text-sm font-semibold">Active</span>
          </label>
        </div>
      </div>

      <div class="grid gap-3 sm:grid-cols-2">
        <div>
          <label class="text-sm font-semibold">From name</label>
          <input
            class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-bg/40 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="senderFromName" type="text">
          <div class="mt-1 hidden text-xs text-danger" data-err="from_name"></div>
        </div>
        <div>
          <label class="text-sm font-semibold">From email</label>
          <input
            class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-bg/40 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="senderFromEmail" type="email">
          <div class="mt-1 hidden text-xs text-danger" data-err="from_email"></div>
        </div>
      </div>

      <div class="rounded-2xl border border-border/60 bg-bg/40 p-3">
        <div class="text-sm font-semibold">Sending policy</div>
        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          <div>
            <label class="text-sm font-semibold">Daily limit</label>
            <input
              class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-card/60 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
              id="senderDailyLimit" type="number" value="50" min="1">
            <div class="mt-1 hidden text-xs text-danger" data-err="daily_limit"></div>
          </div>
          <div>
            <label class="text-sm font-semibold">Window start</label>
            <input
              class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-card/60 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
              id="senderWindowStart" type="time" value="09:00">
            <div class="mt-1 hidden text-xs text-danger" data-err="window_start"></div>
          </div>
          <div>
            <label class="text-sm font-semibold">Window end</label>
            <input
              class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-card/60 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
              id="senderWindowEnd" type="time" value="18:00">
            <div class="mt-1 hidden text-xs text-danger" data-err="window_end"></div>
          </div>
          <div>
            <label class="text-sm font-semibold">Timezone</label>
            <input
              class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-card/60 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
              id="senderTimezone" type="text" value="Asia/Dhaka">
            <div class="mt-1 hidden text-xs text-danger" data-err="timezone"></div>
          </div>
        </div>
        <div class="mt-3 grid gap-3 sm:grid-cols-2">
          <div>
            <label class="text-sm font-semibold">Jitter min (seconds)</label>
            <input
              class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-card/60 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
              id="senderJitterMin" type="number" value="30" min="0">
            <div class="mt-1 hidden text-xs text-danger" data-err="jitter_min_seconds"></div>
          </div>
          <div>
            <label class="text-sm font-semibold">Jitter max (seconds)</label>
            <input
              class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-card/60 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
              id="senderJitterMax" type="number" value="180" min="0">
            <div class="mt-1 hidden text-xs text-danger" data-err="jitter_max_seconds"></div>
          </div>
        </div>
      </div>

      <div class="rounded-2xl border border-border/60 bg-bg/40 p-3">
        <div class="text-sm font-semibold">SMTP (required)</div>
        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          <div>
            <label class="text-sm font-semibold">Host</label>
            <input
              class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-card/60 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
              id="smtpHost" type="text">
            <div class="mt-1 hidden text-xs text-danger" data-err="smtp_host"></div>
          </div>
          <div>
            <label class="text-sm font-semibold">Port</label>
            <input
              class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-card/60 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
              id="smtpPort" type="number" value="587" min="1">
            <div class="mt-1 hidden text-xs text-danger" data-err="smtp_port"></div>
          </div>
          <div>
            <label class="text-sm font-semibold">Encryption</label>
            <select
              class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-card/60 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
              id="smtpEnc">
              <option value="tls">tls</option>
              <option value="ssl">ssl</option>
              <option value="none">none</option>
            </select>
            <div class="mt-1 hidden text-xs text-danger" data-err="smtp_encryption"></div>
          </div>
          <div>
            <label class="text-sm font-semibold">Username</label>
            <input
              class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-card/60 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
              id="smtpUser" type="text">
            <div class="mt-1 hidden text-xs text-danger" data-err="smtp_username"></div>
          </div>
        </div>
        <div class="mt-3">
          <label class="text-sm font-semibold">Password</label>
          <input
            class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-card/60 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="smtpPass" type="password" autocomplete="new-password"
            placeholder="(required on create; leave blank to keep on edit)">
          <div class="mt-1 text-xs text-muted-fg">Stored encrypted. On edit, leave empty to keep existing.
          </div>
          <div class="mt-1 hidden text-xs text-danger" data-err="smtp_password"></div>
        </div>
      </div>

      <div class="rounded-2xl border border-border/60 bg-bg/40 p-3">
        <div class="text-sm font-semibold">IMAP (optional for reply/bounce later)</div>
        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          <div>
            <label class="text-sm font-semibold">Host</label>
            <input
              class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-card/60 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
              id="imapHost" type="text">
            <div class="mt-1 hidden text-xs text-danger" data-err="imap_host"></div>
          </div>
          <div>
            <label class="text-sm font-semibold">Port</label>
            <input
              class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-card/60 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
              id="imapPort" type="number" min="1" placeholder="993">
            <div class="mt-1 hidden text-xs text-danger" data-err="imap_port"></div>
          </div>
          <div>
            <label class="text-sm font-semibold">Encryption</label>
            <select
              class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-card/60 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
              id="imapEnc">
              <option value="">—</option>
              <option value="tls">tls</option>
              <option value="ssl">ssl</option>
              <option value="none">none</option>
            </select>
            <div class="mt-1 hidden text-xs text-danger" data-err="imap_encryption"></div>
          </div>
          <div>
            <label class="text-sm font-semibold">Username</label>
            <input
              class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-card/60 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
              id="imapUser" type="text">
            <div class="mt-1 hidden text-xs text-danger" data-err="imap_username"></div>
          </div>
        </div>
        <div class="mt-3">
          <label class="text-sm font-semibold">Password</label>
          <input
            class="mt-2 h-10 w-full rounded-xl border border-border/60 bg-card/60 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring/30"
            id="imapPass" type="password" autocomplete="new-password"
            placeholder="(required if IMAP host set; leave blank to keep on edit)">
          <div class="mt-1 text-xs text-muted-fg">Stored encrypted. On edit, leave empty to keep existing.
          </div>
          <div class="mt-1 hidden text-xs text-danger" data-err="imap_password"></div>
        </div>
      </div>

      <div class="flex items-center justify-between">
        <button
          class="h-10 rounded-xl border border-border/60 bg-bg/40 px-4 text-sm font-semibold transition hover:bg-muted/40"
          data-modal-close type="button">
          Cancel
        </button>
        <button
          class="h-10 rounded-xl bg-primary px-4 text-sm font-semibold text-primary-fg transition hover:opacity-95"
          id="senderSave" type="submit">
          Save
        </button>
      </div>
    </form>
  </x-ui.modal>

  @push('scripts')
    @vite(['resources/js/pages/senders.js'])
  @endpush
</x-layouts.app-shell>
