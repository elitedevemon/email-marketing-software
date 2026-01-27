# Secure Scheduler Trigger (External Cron Website)

Goal: External cron service (cron-job.org / UptimeRobot / etc.) will hit ONE endpoint securely to run Laravel scheduler.

## Requirements
- Token authentication (long random token stored in env)
- Rate limiting (e.g., 2 requests/minute)
- Logs for every attempt (allowed/denied + IP + user agent + summary)
- POST only
- HTTPS only

## Recommended approach (we will implement in code in a later step)
1) Create endpoint:
   POST /cron/schedule/run

2) Token check:
   - Read token from env: CRON_TOKEN
   - Require header: X-CRON-TOKEN: <token>
   - If missing/invalid: 403 + log as denied

3) Rate limit:
   - Apply Laravel rate limiter middleware to this route
   - Example: 2/minute, with stronger limits per IP

4) Logging:
   - Insert a row into cron_runs table for:
     - ran_at, status (ok/error/denied), source_ip, user_agent, summary_json

5) Execution:
   - Internally call: Artisan::call('schedule:run')
   - Return JSON: { ok: true, output: "...", ran_at: "..." }

## Extra hardening options (optional)
- IP allowlist (if your cron provider publishes fixed IPs)
- Rotate token periodically
- Add alerting if cron not run within expected window
