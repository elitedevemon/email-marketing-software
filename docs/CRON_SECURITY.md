# Cron & Scheduler Security Guidelines

This app will run scheduled sending/automation jobs via Laravel 11 scheduler (`routes/console.php`)
but in production many users run it using an external cron website / uptime service.

This document defines the security requirements before we ship the cron endpoint.

## Threat model (what we prevent)
- Public discovery of the cron URL (mass triggering / DoS)
- Replay triggering (multiple runs back-to-back causing duplicate sends)
- Credential leakage through logs/referrers
- Unobserved failures (cron silently stops running)

## Minimum security controls

### 1) Secret token
- Use a long random token (32+ bytes).
- Accept token via **Authorization header** preferred:
  - `Authorization: Bearer <token>`
- Fallback support: query string `?token=` only if required by the cron provider.

**Never** log the token. If query token is used, avoid logging full URLs.

### 2) Rate limiting
- Protect the endpoint with strict limiter.
- Example policy:
  - `10 requests / 10 minutes` (burst control)
  - optional: `1 request / minute` hard throttle

### 3) Idempotency / double-run safety
Scheduler jobs must be safe if the endpoint is triggered twice.
We will enforce:
- a “scheduler lock” (cache/db lock) with TTL (e.g., 5–10 minutes)
- job-level idempotency (unique keys for “send due email” tasks)

### 4) Audit logs
Every cron run must record:
- timestamp
- outcome (ok/fail)
- duration
- counts (due emails processed / queued / skipped)
- caller IP / user agent (optional)
- correlation id (request id)

Store these in DB table (e.g., `cron_runs`) and show in Admin Control Center.

### 5) Optional IP allowlist
Some cron providers have stable IP ranges; if available we can allowlist.
This is optional because IPs can change, causing false blocks.

## Recommended external cron configuration
- Use HTTPS only.
- Use header-based bearer token if supported.
- Use a fixed schedule like every minute:
  - `* * * * *` equivalent
- Enable retry on failure with backoff (e.g., 3 retries).
- Alerting:
  - notify if endpoint returns non-200
  - notify if response body contains `ok:false`

## Operational health checks

We will add:
- “Last cron run at” indicator
- “Cron is late” warning if last run > X minutes (e.g., 5–10)
- “Queue backlog” indicator

## Status (as of now)
Cron endpoint is **not implemented yet**. This doc is the policy baseline.