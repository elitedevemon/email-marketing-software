# Deliverability & Compliance Guidelines

This app is a self-hosted email marketing + proposal automation tool. We do not use any
third-party marketing APIs. Deliverability is **not guaranteed**, but we implement best practices.

## Non-negotiables (ethics + compliance)
- Unsubscribe must exist and be honored globally (suppression list).
- No “spam bypass tricks”.
- Honest identity + footer + physical/business address placeholders.
- Consent-based sending is strongly recommended.

## DNS configuration (SPF / DKIM / DMARC)

### SPF
- Add/extend SPF to authorize your sending server(s).
- Example (conceptual):
  - `v=spf1 ip4:<YOUR_SERVER_IP> include:<YOUR_MX_OR_PROVIDER> -all`

### DKIM
- Enable DKIM signing for each sending domain.
- Publish the DKIM public key in DNS (selector-based).
- Rotate selectors periodically.

### DMARC
- Start with monitoring:
  - `p=none; rua=mailto:<report@domain>; adkim=s; aspf=s`
- Move gradually to enforcement when stable:
  - `p=quarantine` then `p=reject`

> We will include a UI checklist later (per sender/domain) to confirm DNS is configured.

## Sending behavior (planned implementation)
- Sender accounts store:
  - SMTP credentials (required)
  - IMAP credentials (optional for reply/bounce detection later)
  - Per-sender daily limit
  - Sending window + jitter
- Credentials are stored encrypted at rest (password fields).
- Per-sender daily limit (configurable)
- Sending windows (avoid 24/7 blasting)
- Random jitter to reduce bursty patterns
- Provider/domain throttling (gmail/yahoo/outlook) + backoff retries
- Warm-up plan support (optional config)

## List hygiene
- Hard bounces must be suppressed immediately.
- Complaints (if detectable) should be treated as suppression.
- Replies should mark client as “Engaged” and stop sequences.
- Avoid repeatedly emailing unopened recipients (stop rules) — configurable.

## Tracking limitations (honesty)
- Open tracking uses a pixel and is not 100% accurate:
  - image blocking, privacy proxies, and prefetching can cause false negatives/positives.
- Click tracking via redirect is generally more reliable but still limited by client behavior.
- Inbox placement depends on domain reputation, content, and recipient behavior.

## Content best practices (practical)
- Keep subject lines honest and specific.
- Avoid spammy phrasing and excessive punctuation.
- Use consistent from-name and from-address.
- Prefer plain text readability + light HTML.
- Include clear CTA, but avoid aggressive urgency patterns.

## Status (as of now)
Deliverability controls are **partially documented** here; the actual sending/rotation/throttle engine
will be implemented starting Step 5+.