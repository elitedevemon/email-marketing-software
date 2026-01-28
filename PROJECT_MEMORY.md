# Email Marketing + Client Proposal Automation (Laravel 11) — PROJECT MEMORY

## Current state recap
- Step 0 completed: Blueprint + architecture plan frozen.
- Step 1 completed: Breeze (Blade) auth + SaaS-grade App Shell + Vanilla JS UI utilities + RBAC foundation (Spatie).
- Hotfix applied: app-shell Blade component placed under resources/views/components/layouts to match <x-layouts.app-shell>.
- Step 2 completed: Categories CRUD (AJAX list/search/filter/pagination + modal create/edit + delete).
- Step 3 completed: Clients CRUD v1 (AJAX) + Tags (pivot) + Notes (AJAX modal).
- Step 4 completed: Competitors per client (AJAX CRUD via Clients page modal) + insights_json baseline.
- Hotfix docs hygiene: Added ASSUMPTIONS.md + docs/CRON_SECURITY.md + docs/DELIVERABILITY.md to capture baselines.
- Step 5 completed: Sender accounts CRUD (SMTP+optional IMAP, encrypted secrets, limits/windows) + database queue tables + Failed Jobs UI.
- Next: Proceed Step 6 (Sequence engine v1: schedules + due sends + idempotency + secure cron trigger).

## Non‑negotiable tech rules (must comply)
- Laravel 11 + Blade + Tailwind + Vanilla JS (fetch/XHR). No Vue/React/Livewire/Alpine/Inertia.
- DB: MySQL. Queue: database driver. Failed jobs UI will be built in-app.
- No 3rd‑party email marketing automation APIs/services.
- SMTP allowed (sending), IMAP allowed (reply/bounce detection).
- Scheduler follows Laravel 11 style (routes/console.php scheduling).
- Category is user-managed CRUD only; no prebuilt/auto-selected category.

## Product scope (high level)
- Clients + business details + location + category + tags + notes
- Competitors per client
- Automated sequences with scenario branching + stop rules
- Multi-sender rotation + daily limits + throttling + jitter + sending windows
- Tracking: open/click + IMAP reply/bounce
- Compliance: unsubscribe + global suppression enforced everywhere
- Inbox/conversations + quick replies + auto-stop on reply
- Widget-based dashboard + reports + exports
- Admin control center: pause sending, queue backlog, cron health

## Implementation plan (15 steps)
1) Scaffold + Breeze + SaaS layout + JS utilities + RBAC
2) Categories CRUD (AJAX + modal-first)
3) Clients CRUD v1 + Tags + Notes
4) Competitors CRUD + insights JSON
5) Sender accounts CRUD + encrypted secrets + daily limits + windows
6) Templates v1 + versioning + gallery + preview + test send
7) Sequences v1 + steps + scenario resolver + enrollments
8) Queue infra + dispatcher command + idempotency
9) Outbound sending v1 + rotation + throttling + jitter + logs
10) Tracking v1 (open pixel + click redirect)
11) Suppression + Unsubscribe (global enforcement)
12) IMAP inbound fetch (replies + bounces)
13) Inbox/Conversations UI
14) Dashboard widgets + charts + drilldowns
15) Admin center + exports + hardening (audit, backups)

## Module map
- Core: Auth, RBAC, Audit Logs
- CRM: Clients, Categories, Tags, Notes, Competitors
- Automation: Sequences, Steps, Enrollments, Stop Rules
- Sending: Sender Accounts, Outbound Messages, Domain throttles, Logs
- Tracking: Open/Click events, Link mapping
- Hygiene: Suppression + Unsubscribe
- Inbound: IMAP fetch, Replies, Bounces
- Inbox: Threads + quick replies
- Reporting: Dashboard widgets, Reports, Exports
- Ops/Admin: Queue/Failed jobs UI, Cron health, Pause controls, Settings

## DB schema outline (summary)
Planned tables:
- categories, tags, client_tag, clients, client_notes, competitors
- sender_accounts, sender_daily_counters
- templates, template_versions, template_layouts, template_blocks
- sequences, sequence_steps, sequence_enrollments
- outbound_messages, outbound_message_links, email_events, email_send_logs
- suppression_entries, unsubscribe_events
- inbound_messages, bounce_events
- app_settings, cron_runs, exports
- audit_logs
(Full outline lives in Step 0 chat response.)

## Route conventions
- App pages (Blade): /app/*
- AJAX JSON endpoints (CSRF protected): /app/ajax/*
- Public tracking: /t/o/{uuid}.gif and /t/c/{uuid}/{hash}
- Unsubscribe: signed URL (global suppression)
- Cron endpoint: POST /cron/schedule/run (token + rate limit + logs)

## JSON response shape (AJAX)
Success:
- { "ok": true, "message": "...", "data": {...} }
Validation error (422):
- { "ok": false, "message": "Validation failed", "errors": {field:[...]} }
Server error:
- { "ok": false, "message": "Something went wrong" }

## UI/UX baseline
- SaaS-grade: collapsible sidebar + sticky topbar + global search + quick actions
- Modal-first CRUD (create/edit in modal/drawer)
- AJAX-first: minimal reload; skeleton loaders + toasts + inline validation
- Light/Dark via CSS variables + Tailwind tokens
- Premium tables: search, filter chips, bulk actions, row action menus

## Scheduler/Cron baseline
- Scheduling configured in routes/console.php (Laravel 11 style).
- External cron hits secure endpoint -> calls schedule:run.
- Idempotency ensured at DB level via outbound_messages.uuid unique + atomic state transitions.

## Stop & Resume instructions
If you start a new chat:
1) Paste this PROJECT_MEMORY.md
2) Paste latest CHANGELOG.md
3) Say “Proceed Step X”
