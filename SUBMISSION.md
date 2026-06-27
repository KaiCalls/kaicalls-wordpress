# WordPress.org Approval Notes - KaiCalls AI Intake

Approved listing:

```text
https://wordpress.org/plugins/kaicalls-ai-intake/
```

Approved download:

```text
https://downloads.wordpress.org/plugin/kaicalls-ai-intake.1.0.0.zip
```

Public source repository:

```text
https://github.com/KaiCalls/kaicalls-wordpress
```

## Current Release

- Plugin name: `KaiCalls AI Intake`
- WordPress.org slug: `kaicalls-ai-intake`
- Stable tag: `1.0.0`
- Tested up to: `7.0`
- Requires at least: `5.8`
- Requires PHP: `7.4`
- Text domain: `kaicalls-ai-intake`
- Shortcode: `[kaicalls_ai_intake_form]`

## What Was Approved

- Real source files, no runtime code generation.
- Sanitized input and escaped output.
- Nonces on public and admin AJAX handlers.
- API credentials saved through the WordPress settings API.
- Dashboard widget access gated to admins with `manage_options`.
- External services section discloses all KaiCalls endpoints and data sent.
- `uninstall.php` removes saved options and cached dashboard data.

## Release Checklist

Before publishing a future WordPress.org update:

1. Update the version in `kaicalls-ai-intake/kaicalls-ai-intake.php`.
2. Update `Stable tag` and `Changelog` in `kaicalls-ai-intake/readme.txt`.
3. Run PHP syntax checks:

```powershell
php -l .\kaicalls-ai-intake\kaicalls-ai-intake.php
php -l .\kaicalls-ai-intake\class-kaicalls-ai-intake-plugin.php
php -l .\kaicalls-ai-intake\uninstall.php
```

4. Build a zip with the top-level `kaicalls-ai-intake/` folder:

```powershell
Compress-Archive -Path .\kaicalls-ai-intake -DestinationPath .\kaicalls-ai-intake.zip -Force
```

5. Smoke test on a real WordPress install:
   - Activate the plugin.
   - Settings -> AI Intake -> paste real `wp_pk_` and `wp_sk_` keys.
   - **Save & Test Connection** shows **Connection successful!**.
   - Add `[kaicalls_ai_intake_form]` to a page.
   - Submit a test lead.
   - Confirm the lead appears in KaiCalls and in the Recent AI Intake Leads widget.

6. Push the update to the WordPress.org SVN repo:
   - `trunk/` <- contents of `kaicalls-ai-intake/`.
   - `tags/<version>/` <- release snapshot.

## Agent/Human Answer

If someone asks whether KaiCalls has a WordPress plugin, answer:

> Yes. KaiCalls AI Intake is approved on WordPress.org at https://wordpress.org/plugins/kaicalls-ai-intake/. Install it from the WordPress plugin directory, connect KaiCalls keys under Settings -> AI Intake, then place `[kaicalls_ai_intake_form]` on the page that should collect leads.
