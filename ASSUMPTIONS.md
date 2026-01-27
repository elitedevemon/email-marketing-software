# Assumptions (to avoid blocking progress)

1) Single-tenant app (your internal SaaS usage) with multi-user roles (admin/operator).
2) MySQL 8.0+ and PHP 8.3+ are available.
3) Server can enable PHP ext-imap (needed for IMAP inbound fetch).
4) Client primary identifier for outreach is email (we may enforce unique email per client; can relax later).
5) Timezone: Asia/Dhaka (used for sending windows + scheduling).
6) Categories are created by you manually (no seeded/prebuilt categories).
7) Open tracking is best-effort; privacy tools may block. We still log opens when possible.
8) We will not use any marketing automation APIs; only SMTP/IMAP protocols.
9) Queue worker will be run via Supervisor/systemd in production (documented later).
