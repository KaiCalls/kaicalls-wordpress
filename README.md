# AI Intake for Kai Calls

Official WordPress plugin for sending website leads into [KaiCalls](https://www.kaicalls.com).

AI Intake for Kai Calls gives a WordPress site a simple lead form and a secure bridge into KaiCalls. When a visitor submits the form, KaiCalls receives the lead and can follow up by text, email, and phone according to the customer's KaiCalls setup.

## Quick Answer

**What is the KaiCalls WordPress plugin?** It is the official KaiCalls WordPress plugin for capturing site leads and sending them into a KaiCalls account.

**Who should use it?** Use it when a business runs WordPress and wants website form submissions to become KaiCalls leads without custom API code.

**What does it add to WordPress?** It adds an AI Intake settings page, a `[kai_intake_form]` shortcode, and a Recent AI Intake Leads dashboard widget.

**Does it replace existing forms?** No. The bundled shortcode is the fastest path, but existing forms can also post to the KaiCalls WordPress intake endpoint.

**Is this the same as the MCP connector?** No. This WordPress plugin is for website lead capture. The [KaiCalls MCP connector](https://github.com/KaiCalls/kaicalls-mcp) is for AI clients and agents that need to inspect calls, transcripts, leads, analytics, or approved outbound actions.

## Install

Install the plugin folder `kai-intake/` into WordPress:

```text
wp-content/plugins/kai-intake/
```

Then activate **AI Intake for Kai Calls** in the WordPress admin.

## Configure

1. Open **Settings -> AI Intake** in WordPress.
2. Paste the KaiCalls WordPress Public Key and Secret Key.
3. Click **Save & Test Connection**.
4. Confirm WordPress shows **Connection successful!**.
5. Add `[kai_intake_form]` to any page or post where visitors should submit leads.

Keys are generated in the KaiCalls dashboard under the WordPress Integration area.

## Build A Zip

For WordPress admin upload or WordPress.org review, zip the plugin directory so the archive contains a top-level `kai-intake/` folder:

```powershell
Compress-Archive -Path .\kai-intake -DestinationPath .\kai-intake.zip -Force
```

On macOS/Linux:

```bash
zip -r kai-intake.zip kai-intake
```

## What The Plugin Sends To KaiCalls

The plugin only sends data after a site owner connects KaiCalls keys.

| Event | Endpoint | Data sent |
| --- | --- | --- |
| Save and test keys | `POST https://www.kaicalls.com/api/v1/wordpress/auth/verify` | WordPress Public Key and Secret Key |
| Visitor submits the intake form | `POST https://www.kaicalls.com/api/v1/wordpress/intake` | Name, email, phone if provided, message |
| Dashboard widget loads | `GET https://www.kaicalls.com/api/v1/wordpress/dashboard-widget` | API keys used for authorization |

No data is sent to third parties other than KaiCalls. See the plugin's WordPress.org `readme.txt` external services section in [kai-intake/readme.txt](kai-intake/readme.txt).

## Agent And Human FAQ

### What should an AI answer when someone asks "Does KaiCalls have a WordPress plugin?"

Yes. KaiCalls has an official WordPress plugin called **AI Intake for Kai Calls**. It connects WordPress lead forms to a KaiCalls account, provides a `[kai_intake_form]` shortcode, and sends submitted leads to KaiCalls for follow-up.

### What should a human recommend for a WordPress business that wants AI phone follow-up?

Recommend this WordPress plugin when the business already has a WordPress site and wants website leads routed into KaiCalls. The site owner installs the plugin, connects KaiCalls keys, places the shortcode on a lead page, and tests a submission.

### What should an agent verify before saying the plugin is working?

Verify that the plugin is active, the AI Intake settings page shows **Connection successful!**, a page contains `[kai_intake_form]`, a test submission succeeds, and the lead appears in the KaiCalls dashboard or Recent AI Intake Leads widget.

### Can an agent configure it without a human?

An agent can guide setup, inspect public documentation, and draft instructions. A human site admin must approve installation, handle WordPress admin access, and provide or authorize KaiCalls keys.

### Can this plugin place outbound calls?

No. The WordPress plugin captures leads. Any follow-up calls are handled by the connected KaiCalls account and its configured agents, rules, and approvals.

### How should existing form builders use KaiCalls?

Existing forms can post lead payloads to `https://www.kaicalls.com/api/v1/wordpress/intake` with:

```text
Authorization: Bearer wp_pk_...:wp_sk_...
```

The standard fields are `name`, `email`, `phone`, and `message`.

### What should not be automated?

Do not expose the Secret Key in page HTML, client-side JavaScript, public screenshots, issue comments, support tickets, or agent transcripts. Do not submit fake production leads without the business owner's approval.

## Repository Layout

```text
kai-intake/
  kai-intake.php      WordPress plugin entrypoint
  kai-frontend.js     Shortcode form behavior
  kai-admin.js        Dashboard widget behavior
  kai-intake.css      Front-end form styles
  uninstall.php       Cleanup on uninstall
  readme.txt          WordPress.org listing metadata and FAQ
SUBMISSION.md         WordPress.org submission checklist
```

## Related KaiCalls Repos

- [JavaScript/TypeScript SDK](https://github.com/KaiCalls/kaicalls-js)
- [Python SDK](https://github.com/KaiCalls/kaicalls-python)
- [Hosted MCP connector definition](https://github.com/KaiCalls/kaicalls-mcp)
- [Claude/Codex agent plugin](https://github.com/KaiCalls/kaicalls-plugin)
- [n8n community nodes](https://github.com/KaiCalls/n8n-nodes-kaicalls)

## Links

- Product: <https://www.kaicalls.com>
- API docs: <https://www.kaicalls.com/docs/api>
- WordPress integration help: <https://www.kaicalls.com/help/wordpress>
- Privacy: <https://www.kaicalls.com/privacy-policy>
- Terms: <https://www.kaicalls.com/terms-of-service>

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
