# ASSUMPTIONS

This file captures explicit defaults and implicit behaviors introduced during implementation,
so future steps remain consistent and production-safe.

## Product-level defaults
- Primary UI flow is **modal-first** and **AJAX-first** (no Livewire/Alpine/Inertia).
- Pagination default: **20 rows per page** for list endpoints (categories, clients).
- All AJAX endpoints return JSON with `{ ok: boolean, message?: string, data?: any, errors?: object }`.

## Data model assumptions (current)

### Clients
- `clients.status` is an enum-like string restricted to:
  - `prospect | engaged | paused | suppressed | archived`
- `clients.email` is unique and normalized to **lowercase** on create/update.
- `clients` are **soft-deleted** (we keep history and allow future restore workflow).

### Tags
- Tag input is a **comma-separated string** on the client modal.
- Tags are normalized to **lowercase**, trimmed, unique.
- Max tags per client enforced in controller: **20** (to avoid abuse/UX issues).
- Tags are stored globally in `tags` and connected via `client_tag` pivot.

### Categories
- Categories are user-managed (no pre-built auto-selection).
- `categories` are currently **hard-deleted** (can be changed later if we need auditability).

### Senders (SMTP/IMAP)
- Sender accounts are stored in `senders`.
- Sensitive fields are stored encrypted using Laravel encrypted casts:
  - `smtp_password`, `imap_password`
- Password edit behavior:
  - Create: SMTP password required
  - Edit: leaving password blank keeps the existing encrypted value
- IMAP is optional for now (reply/bounce detection later). If `imap_host` is set, IMAP credentials must exist.
- Sending policy baseline:
  - `daily_limit` integer
  - `window_start/window_end` time (timezone-aware via `timezone` column)
  - jitter range in seconds (`jitter_min_seconds`..`jitter_max_seconds`)
  
## Sequences v1
- Default sequence key: `DEFAULT_SEQUENCE_KEY=default_outreach`
- New clients auto-enroll only when `clients.status === 'prospect'`.
- Enrollment delay timing:
  - next email time is computed from **sent_at + next_step.delay_days**
- Idempotency:
  - one outbound per enrollment+step enforced via unique DB index.
- Backoff:
  - if no sender available, enrollment next_run_at is pushed by 5 minutes.
  - after queuing a send job, next_run_at is pushed by 10 minutes while the job runs.


### Competitors
- Competitors are stored per client (0..n), currently **hard-deleted**.
- `competitors.insights_json` baseline structure (manual capture for now):
  - `summary` (string)
  - `notes` (string)
  - `source` (string; default `manual`)
  - `captured_at` (ISO string)

## UI/UX assumptions
- Tables use a “premium feel” pattern:
  - search debounce (250–300ms)
  - skeleton loading rows
  - row actions via `details/summary` menu
  - inline validation messages below fields
- Toast system is available via `window.App.toast(message, type)`.
- Fetch wrapper is available via `window.App.fetchJson(url, {method, body})`.

## Security assumptions
- All `/app/*` routes are behind `auth`, `verified`, and `role:admin|operator`.
- We do not yet implement IP allowlists; will be considered for cron endpoint later.

## Not implemented yet (planned)
- Secure scheduler endpoint + logs
- Sender accounts (SMTP/IMAP) with encrypted secrets
- Suppression list + unsubscribe enforcement
- Queue dashboard + failed jobs UI
- Tracking (open/click/reply/bounce via IMAP) + automation engine
- IMAP inbound fetch (replies + bounces) + suppression from DSN parsing
- Inbox/Conversations UI + auto-stop on reply
- Dashboard widgets/reports + exports
- Admin control center (pause sending, cron health, queue backlog)

## Suppression + Unsubscribe + Tracking (Step 7)
- Global suppression is stored in `suppression_entries` (unique by email).
- Unsubscribe is a **signed URL** at `/u` with encrypted email token param `t`.
- Enforcement occurs at send-time (SendOutboundEmailJob) and tick-time (sequence:tick): suppressed emails must never be sent.
- Tracking v1:
  - Open pixel: `/t/o/{uuid}.gif` (soft-dedupe by uuid+type+ip+day)
  - Click redirect: `/t/c/{uuid}/{hash}`; original URL stored in `outbound_links`.
- Rendered content is stored in email_outbounds (`rendered_html`, `rendered_text`) after link rewrite + footer append.