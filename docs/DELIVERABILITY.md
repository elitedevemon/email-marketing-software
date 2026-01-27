# Deliverability & Compliance Guide (Ethical)

## Non-negotiables
- Always include unsubscribe link (global suppression).
- Respect suppression list everywhere (no sending).
- Use honest identity footer (company name/address/contact).

## SPF / DKIM / DMARC (minimum guidance)
1) SPF
- Add TXT record for your sending domain to authorize your SMTP IP/provider.
- Keep it under DNS lookup limits; avoid multiple nested includes.

2) DKIM
- Generate DKIM keys from your mail server/provider.
- Publish DKIM public key as TXT record.
- Ensure From domain aligns with DKIM signing domain if possible (alignment helps DMARC).

3) DMARC
- Start with monitoring:
  - p=none; rua=mailto:...
- Then gradually enforce:
  - p=quarantine -> p=reject (when confident)
- Ensure SPF or DKIM passes with alignment to pass DMARC.

## Sending hygiene
- Start slow (warm-up). Increase gradually.
- Use sending window + random jitter.
- Domain throttling (gmail/yahoo/outlook) and backoff on temp failures.
- Keep content relevant; avoid spammy patterns.
- Always send a plain-text alternative.

## Reality check
- Open tracking can be blocked (Apple MPP, image blocking).
- Inbox placement is never guaranteed; we focus on best practices only.
