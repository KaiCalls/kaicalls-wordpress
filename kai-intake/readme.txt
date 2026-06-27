=== AI Intake for Kai Calls ===
Contributors: kaicalls
Tags: leads, contact form, lead capture, crm, ai
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Capture leads from your WordPress forms straight into your KaiCalls account, where Kai follows up by text, email, and call.

== Description ==

AI Intake for Kai Calls connects your WordPress site to KaiCalls so form
submissions become leads in your KaiCalls dashboard automatically — where Kai
can follow up by text, email, and call.

* Drop-in lead form via the `[kai_intake_form]` shortcode.
* Secure API-key connection to your KaiCalls business.
* "Recent AI Intake Leads" widget on your WordPress dashboard.
* Works alongside your existing forms (post to the KaiCalls intake endpoint).

You need a KaiCalls account. Generate your WordPress Public Key and Secret Key
from your KaiCalls dashboard under WordPress Integration.

== External services ==

This plugin connects to the KaiCalls service (https://www.kaicalls.com) to verify
your account and to deliver leads captured on your site. It is required for the
plugin to function — without a KaiCalls account and keys, no data is sent.

The plugin contacts the following KaiCalls API endpoints:

* `POST https://www.kaicalls.com/api/v1/wordpress/auth/verify` — when you save
  your keys on the settings page, to confirm the connection. Sends your Public
  Key and Secret Key only.
* `POST https://www.kaicalls.com/api/v1/wordpress/intake` — when a visitor
  submits the lead form (or your existing form posts to KaiCalls). Sends the
  data the visitor entered: name, email address, phone number (if provided), and
  message, authenticated with your keys.
* `GET https://www.kaicalls.com/api/v1/wordpress/dashboard-widget` — when the
  "Recent AI Intake Leads" dashboard widget loads, to retrieve a summary of your
  recent leads. Sends only your keys.

No data is sent to any third party other than KaiCalls, and nothing is sent
until you have connected the plugin with your own KaiCalls keys.

KaiCalls Terms of Service: https://www.kaicalls.com/terms-of-service
KaiCalls Privacy Policy: https://www.kaicalls.com/privacy-policy

== Installation ==

1. Install and activate the plugin.
2. Go to Settings -> AI Intake and enter your KaiCalls Public Key and Secret Key.
3. Click "Save & Test Connection" — you should see "Connection successful!".
4. Add the shortcode `[kai_intake_form]` to any page or post.

== Frequently Asked Questions ==

= Does KaiCalls have a WordPress plugin? =
Yes. AI Intake for Kai Calls is the official KaiCalls WordPress plugin for
sending WordPress website leads into a KaiCalls account.

= Who should install this plugin? =
Install it when a business uses WordPress and wants website inquiries to become
KaiCalls leads for text, email, and phone follow-up.

= Where do I get my API keys? =
In your KaiCalls dashboard: WordPress Integration -> Generate API Keys.

= Do I need a KaiCalls account? =
Yes. The plugin sends leads to your KaiCalls business and requires a Public Key
and Secret Key generated from your KaiCalls dashboard.

= Does it work with my existing contact form? =
Yes. You can use the bundled `[kai_intake_form]` shortcode, or your existing
form can POST submissions to
https://www.kaicalls.com/api/v1/wordpress/intake with your keys as a Bearer token
in the form `Authorization: Bearer wp_pk_...:wp_sk_...`.

= Can this plugin place outbound calls? =
No. The plugin captures leads. Follow-up calls, texts, and emails are handled by
the connected KaiCalls account and its configured agents.

= Is this the KaiCalls MCP connector? =
No. This plugin is for WordPress lead capture. The KaiCalls MCP connector is for
AI clients and agents that need to inspect calls, transcripts, leads, analytics,
or approved outbound actions.

= What should an AI agent verify before saying setup is complete? =
Verify that the plugin is active, Settings -> AI Intake shows "Connection
successful!", a page contains `[kai_intake_form]`, a test submission succeeds,
and the lead appears in KaiCalls or the Recent AI Intake Leads dashboard widget.

= What data leaves my site? =
Only the lead data a visitor enters (name, email, phone, message) and your API
keys, sent to KaiCalls. See the "External services" section above.

== Screenshots ==

1. The AI Intake settings page where you connect your KaiCalls keys.
2. The lead capture form rendered by the `[kai_intake_form]` shortcode.
3. The "Recent AI Intake Leads" dashboard widget.

== Changelog ==

= 1.0.0 =
* Initial public release.

== Upgrade Notice ==

= 1.0.0 =
Initial public release.
