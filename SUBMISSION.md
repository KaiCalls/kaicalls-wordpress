# WordPress.org Submission Checklist - AI Intake for Kai Calls

Public source repository: `https://github.com/KaiCalls/kaicalls-wordpress`.
Source of truth for the plugin: `kai-intake/`.
Build the distributable zip by zipping the `kai-intake/` folder so the archive contains a top-level `kai-intake/` directory.

PowerShell:

```powershell
Compress-Archive -Path .\kai-intake -DestinationPath .\kai-intake.zip -Force
```

macOS/Linux:

```bash
zip -r kai-intake.zip kai-intake
```

## Code readiness (done in repo)

- [x] Real source files (no runtime code generation, no `file_put_contents`).
- [x] No `error_log` of secrets.
- [x] Nonces on both AJAX handlers (`check_ajax_referer`).
- [x] Input sanitized (`sanitize_text_field` / `sanitize_email` / `sanitize_textarea_field` + `wp_unslash`).
- [x] Output escaped (`esc_html__`, `esc_attr`) in settings + widget.
- [x] All strings use the `kai-intake` text domain; `load_plugin_textdomain` registered.
- [x] Styles enqueued via `kai-intake.css` (no inline `<style>` blocks).
- [x] `uninstall.php` removes options + cached transient.
- [x] **External services disclosed** in `readme.txt` (the #1 review blocker — the plugin
      sends lead data to kaicalls.com; the `== External services ==` section names every
      endpoint, the data sent, and links the TOS + Privacy Policy).
- [x] `readme.txt` headers valid: Stable tag matches version (1.0.0), `Tested up to: 6.8`,
      ≤5 tags, GPLv2 license.
- [x] Prefixing: `Kai_Intake_*`, `KAI_INTAKE_*`, `kai_intake_*` options/shortcode/actions.
- [x] Public README includes human and agent FAQ answers: what the plugin is, when to use it,
      what it sends, how to verify setup, and how it differs from the MCP connector.

## Pre-submission validation (recommended)

- [ ] Install the official **Plugin Check (PCP)** plugin on a local WP and run it against
      the built zip. Resolve any ERROR-level findings (warnings are usually fine).
- [ ] Smoke test on a real WordPress install:
      - Activate → Settings → AI Intake → paste real `wp_pk_` / `wp_sk_` keys → "Save & Test
        Connection" shows **Connection successful!**
      - Add `[kai_intake_form]` to a page → submit → lead appears in the KaiCalls dashboard.
      - Confirm the "Recent AI Intake Leads" dashboard widget shows the lead + weekly count.

## Manual WP.org steps (account-gated — Connor)

- [ ] Ensure a WordPress.org account exists with username **kaicalls** (matches the
      `Contributors:` line in `readme.txt`). Create it if needed.
- [ ] Submit the zip at https://wordpress.org/plugins/developers/add/ for manual review
      (typically 1–4 weeks; reviewers reply by email).
- [ ] On approval, push the plugin to the assigned SVN repo:
      - `trunk/` <- the contents of `kai-intake/`.
      - Tag a release: copy `trunk/` -> `tags/1.0.0/`.
- [ ] Add listing screenshots to SVN `assets/` (NOT inside the plugin zip), named to match
      the `== Screenshots ==` section in `readme.txt`:
      - `screenshot-1.png` — AI Intake settings page.
      - `screenshot-2.png` — the `[kai_intake_form]` lead form.
      - `screenshot-3.png` — the dashboard widget.
      (Screenshots are not required to pass review; they improve the listing.)
- [ ] Optional: add `assets/banner-772x250.png` and `assets/icon-256x256.png`.

## After listing

- Updates ship automatically through the WordPress.org update API once the plugin is
  listed — bump `Version`/`Stable tag` + add a `== Changelog ==` entry, then push a new
  SVN tag. No custom updater is needed (Slice 4's auto-update is satisfied by .org).

## Known optional follow-up (not a blocker)

- Slice 2 "Connect to KaiCalls" one-click auth: replace paste-two-keys with an authorize
  flow that mints + returns keys via `POST /api/v1/wordpress/keys/create`. The current
  paste-keys flow is fully usable; this is a UX upgrade, not a submission requirement.
