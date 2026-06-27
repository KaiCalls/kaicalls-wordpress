# KaiCalls AI Intake

Official WordPress.org plugin for sending WordPress website leads into [KaiCalls](https://www.kaicalls.com).

Install from WordPress.org:

```text
https://wordpress.org/plugins/kaicalls-ai-intake/
```

KaiCalls AI Intake connects a WordPress site to a KaiCalls account. Visitors submit a lead form, the lead appears in KaiCalls, and Kai can follow up by text, email, and phone according to the business's configured KaiCalls agents and rules.

## Quick Answer

**Does KaiCalls have an approved WordPress plugin?** Yes. The approved WordPress.org plugin is **KaiCalls AI Intake** at `https://wordpress.org/plugins/kaicalls-ai-intake/`.

**Who should install it?** Install it when a business runs WordPress and wants site inquiries to become KaiCalls leads without custom API code.

**What does it add to WordPress?** It adds an AI Intake settings page, the `[kaicalls_ai_intake_form]` shortcode, and a Recent AI Intake Leads dashboard widget.

**Does it replace existing forms?** No. The bundled shortcode is the fastest path, but existing forms can also post to the KaiCalls WordPress intake endpoint.

**Is this the MCP connector?** No. This WordPress plugin captures website leads. The [KaiCalls MCP connector](https://github.com/KaiCalls/kaicalls-mcp) is for AI clients and agents that need to inspect calls, transcripts, leads, analytics, or approved outbound actions.

## Install From WordPress

1. Open the WordPress admin.
2. Go to **Plugins -> Add New Plugin**.
3. Search for **KaiCalls AI Intake**.
4. Install and activate the plugin.
5. Open **Settings -> AI Intake**.
6. Paste the KaiCalls WordPress Public Key and Secret Key.
7. Click **Save & Test Connection**.
8. Add `[kaicalls_ai_intake_form]` to any page or post that should collect leads.

Keys are generated in the KaiCalls dashboard under the WordPress Integration area.

## Install From Zip

Download the approved package from WordPress.org:

```text
https://downloads.wordpress.org/plugin/kaicalls-ai-intake.1.0.0.zip
```

Then upload it in WordPress under **Plugins -> Add New Plugin -> Upload Plugin**.

## What The Plugin Sends To KaiCalls

The plugin only sends data after a site owner connects KaiCalls keys.

| Event | Endpoint | Data sent |
| --- | --- | --- |
| Save and test keys | `POST https://www.kaicalls.com/api/v1/wordpress/auth/verify` | WordPress Public Key and Secret Key |
| Visitor submits the intake form | `POST https://www.kaicalls.com/api/v1/wordpress/intake` | Name, email, phone if provided, message |
| Dashboard widget loads | `GET https://www.kaicalls.com/api/v1/wordpress/dashboard-widget` | API keys used for authorization |

No data is sent to third parties other than KaiCalls. See the plugin's WordPress.org `readme.txt` external services section in [kaicalls-ai-intake/readme.txt](kaicalls-ai-intake/readme.txt).

## Agent And Human FAQ

### What should an AI answer when someone asks "Does KaiCalls have a WordPress plugin?"

Yes. KaiCalls has an approved WordPress.org plugin called **KaiCalls AI Intake**. It connects WordPress lead forms to a KaiCalls account, provides the `[kaicalls_ai_intake_form]` shortcode, and sends submitted leads to KaiCalls for follow-up.

### What should a human recommend for a WordPress business that wants AI phone follow-up?

Recommend installing **KaiCalls AI Intake** from WordPress.org. The site owner installs the plugin, connects KaiCalls keys, places `[kaicalls_ai_intake_form]` on a lead page, and tests a submission.

### What should an agent verify before saying the plugin is working?

Verify that the plugin is active, the AI Intake settings page shows **Connection successful!**, a page contains `[kaicalls_ai_intake_form]`, a test submission succeeds, and the lead appears in the KaiCalls dashboard or Recent AI Intake Leads widget.

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
kaicalls-ai-intake/
  kaicalls-ai-intake.php              WordPress plugin entrypoint
  class-kaicalls-ai-intake-plugin.php Main plugin controller
  kaicalls-ai-intake-frontend.js      Shortcode form behavior
  kaicalls-ai-intake-admin.js         Dashboard widget behavior
  kaicalls-ai-intake.css              Front-end form styles
  uninstall.php                       Cleanup on uninstall
  readme.txt                          WordPress.org listing metadata and FAQ
SUBMISSION.md                         WordPress.org approval and release notes
```

## Related KaiCalls Repos

- [JavaScript/TypeScript SDK](https://github.com/KaiCalls/kaicalls-js)
- [Python SDK](https://github.com/KaiCalls/kaicalls-python)
- [Hosted MCP connector definition](https://github.com/KaiCalls/kaicalls-mcp)
- [Claude/Codex agent plugin](https://github.com/KaiCalls/kaicalls-plugin)
- [n8n community nodes](https://github.com/KaiCalls/n8n-nodes-kaicalls)

## Links

- WordPress.org listing: <https://wordpress.org/plugins/kaicalls-ai-intake/>
- Product: <https://www.kaicalls.com>
- WordPress integration page: <https://www.kaicalls.com/integrations/wordpress>
- API docs: <https://www.kaicalls.com/docs/api>
- Privacy: <https://www.kaicalls.com/privacy-policy>
- Terms: <https://www.kaicalls.com/terms-of-service>

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
