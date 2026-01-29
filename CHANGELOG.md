# Changelog

## [Step 0] - Blueprint frozen
- Defined module map, DB schema outline, route map, UI component library
- Defined sending/tracking architecture (SMTP + IMAP + open/click tracking)
- Defined security + deliverability checklist
- Added PROJECT_MEMORY.md + ASSUMPTIONS.md + docs/*


## [Step 1] - Auth + SaaS UI foundation + RBAC
- Installed Breeze (Blade) auth scaffolding
- Added App Shell: collapsible sidebar + sticky topbar + quick actions modal
- Added theme tokens (CSS variables) + Tailwind mapped colors + dark mode (class)
- Added Vanilla JS UI utilities: toast, modal, fetch wrapper, shell init (sidebar/theme)
- Added Spatie roles (admin/operator) + Laravel 11 middleware aliases in bootstrap/app.php
- Auto-assign role on register (first user admin)

## [Hotfix] - Blade component path
- Fixed <x-layouts.app-shell> resolution by adding resources/views/components/layouts/app-shell.blade.php


## [Step 2] - Categories CRUD (AJAX + modal-first)
- Added categories table migration + Category model
- Added /app/categories page (premium table shell)
- Added AJAX endpoints: list/create/update/delete (JSON)
- Added categories.js: debounce search, skeleton loading, modal create/edit, inline validation, toasts
- Updated sidebar: Categories link enabled

## [Step 3] - Clients CRUD v1 + Tags + Notes
- Added clients table (soft delete) + tags + client_tag pivot + client_notes
- Added /app/clients page (premium table shell) with filters (status/category/tag) + pagination
- Added AJAX endpoints for clients CRUD
- Added Notes modal: list notes + add note (AJAX)
- Enabled sidebar Clients link

## [Step 4] - Competitors per client + insights baseline
- Added competitors table + Competitor model + Client->competitors relation
- Added AJAX endpoints: list/store/update/delete competitors
- Added Clients row action "Competitors" (modal-first CRUD)
- Added competitors_count to Clients list response and table column

## [Hotfix] - Documentation & assumptions hygiene
- Added ASSUMPTIONS.md (status enum, tag normalization, delete rules, insights_json baseline)
- Added docs/CRON_SECURITY.md (token, rate limit, idempotency, logs policy baseline)
- Added docs/DELIVERABILITY.md (SPF/DKIM/DMARC guidance + hygiene + tracking limitations)

## [Step 5] - Senders + Queue baseline
- Added senders table + Sender model (SMTP required, IMAP optional; password fields encrypted)
- Added /app/senders page + AJAX CRUD
- Added database queue tables: jobs, failed_jobs, job_batches
- Added Failed Jobs UI: /app/queue/failed with view + retry + forget (admin-only actions)
- Updated sidebar + quick actions to link Senders and Queue

## [Hotfix] - Scrollable modals
- Updated <x-ui.modal> to use max-height + scrollable body (no more zooming out).


## [Step 6] - Sequence engine v1 + Secure cron trigger
- Added sequences/steps/enrollments tables (minimal sequence system)
- Added email_outbounds (idempotent outbox per enrollment+step)
- Added sequence:tick command and scheduled it every minute via routes/console.php
- Added SendOutboundEmailJob (dynamic SMTP per sender)
- Added /cron/run endpoint with token auth + throttle + lock + cron_runs logging
- Auto-enroll new prospect clients into default_outreach sequence (seeded)

## [Step 7] - Unsubscribe + Suppression + Tracking v1
- Added suppression_entries + unsubscribe_events (global do-not-contact)
- Added signed unsubscribe endpoint (/u) and confirmation page
- Added tracking endpoints: open pixel (/t/o/{uuid}.gif) + click redirect (/t/c/{uuid}/{hash})
- Added outbound_links mapping + email_events table
- Enforced suppression at send-time (skip)

## [Step 8] - Outbound sending hardening (rotation/limits/window/throttle)
- Added sender_daily_counters table for per-sender daily limits
- Added domain_throttle_states table for provider/domain throttling + backoff
- Added retry-safe jitter (jitter_applied_at) to prevent bursts
- Implemented sender round-robin selection (last_selected_at) + quota/window checks