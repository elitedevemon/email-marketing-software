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