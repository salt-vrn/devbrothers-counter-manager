=== DevBrothers Counter Manager ===
Contributors: lzolotarev
Tags: analytics, yandex-metrika, google-analytics, cookie, tracking
Requires at least: 5.8
Tested up to: 7.0
Stable tag: 1.0.4
Requires PHP: 7.4
Requires Plugins: devbrothers-admin-panel
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage Yandex.Metrika and Google Analytics counters with an optional cookie consent banner. No theme file edits.

== Description ==

**DevBrothers Counter Manager** lets you add and manage web analytics counters on your WordPress site without editing theme files. Paste the official counter code from Yandex.Metrika or Google Analytics, enable or disable each counter, and optionally show a cookie consent banner before scripts load.

= Key Features =

* **Yandex.Metrika** — paste and manage your Metrika counter code
* **Google Analytics** — paste and manage your gtag.js / GA code
* **Enable / disable** — turn counters on or off without deleting saved code
* **Insert into `<head>`** — active counter code is output in the document head
* **Cookie consent banner** — optional banner; analytics loads only after visitor accepts (when enabled)
* **Exclude paths** — skip the banner on selected URLs (e.g. dashboard pages)
* **DevBrothers Admin Panel** — settings inside the unified DevBrothers interface

= Cookie Consent (optional) =

When the cookie banner is enabled:

* Analytics scripts are **not** loaded until the visitor clicks Accept
* A consent value is stored in a **first-party cookie** on your domain (`dbcm_cookie_consent`)
* No consent data is sent to third parties — only stored locally in the visitor's browser
* You can link to your privacy / cookie policy page
* Light or dark banner theme

When the banner is disabled, counters behave as usual and load immediately.

= Requirements =

* WordPress 5.8+
* PHP 7.4+
* [DevBrothers Admin Panel](https://wordpress.org/plugins/devbrothers-admin-panel/) (required dependency)

== Installation ==

1. Install and activate **DevBrothers Admin Panel** from WordPress.org (or install it when prompted).
2. Install and activate **DevBrothers Counter Manager**.
3. Go to **DevBrothers → Counter Manager**.
4. Enable a counter, paste the full code from your analytics account, and save.
5. Optionally enable the cookie consent banner under the **Cookie banner** section.

== External services ==

This plugin integrates with third-party analytics services when you enable counters and paste their official tracking code.

= Yandex.Metrika =

When enabled, the plugin outputs the Yandex.Metrika counter code you provide. Data is sent to Yandex servers according to your Metrika configuration.

Typical data sent to Yandex.Metrika includes page views, browser/device information, and referrer data (see Yandex documentation for details).

* Service provider: Yandex LLC
* Terms: https://yandex.com/legal/metrica_termsofuse/
* Privacy: https://yandex.com/legal/privacy/

= Google Analytics =

When enabled, the plugin outputs the Google Analytics (gtag.js) code you provide. Data is sent to Google servers according to your Analytics configuration.

Typical data sent to Google Analytics includes page views, browser/device information, and referrer data (see Google documentation for details).

* Service provider: Google LLC
* Terms: https://www.google.com/analytics/terms/
* Privacy: https://policies.google.com/privacy

= Local cookie (consent banner) =

When the cookie banner is enabled, the plugin stores the visitor's choice (`accepted` or `declined`) in a first-party cookie on your site. This data is **not** transmitted to DevBrothers or any external service.

== Frequently Asked Questions ==

= Can I use Yandex.Metrika and Google Analytics at the same time? =

Yes. You can enable both counters simultaneously.

= Do I need to edit theme files? =

No. All settings are managed in **DevBrothers → Counter Manager**.

= What happens when I disable a counter? =

The code is saved in settings but not output on the front end until you enable it again.

= How does the cookie banner work? =

If enabled, analytics scripts load only after the visitor accepts. Until then, no Metrika or GA code is printed in `<head>`. The choice is remembered via a local cookie.

= Can I hide the banner on certain pages? =

Yes. Use **Exclude paths** in cookie banner settings (one path per line, e.g. `/dashboard/`).

= Is it safe to paste counter code in the admin? =

Only users with `manage_options` can save settings. The plugin blocks dangerous HTML tags in counter code while allowing standard `<script>` and `<noscript>` tags used by analytics vendors.

= Why is DevBrothers Admin Panel required? =

Counter Manager registers its settings page inside the DevBrothers admin UI. Install Admin Panel from WordPress.org first.

== Screenshots ==

1. Counter settings (Yandex.Metrika and Google Analytics)
2. Cookie consent banner settings
3. Cookie banner on the front end (example)

== Changelog ==

= 1.0.4 =
* Re-release with corrected SVN tag format
* No code changes from 1.0.3

= 1.0.3 =
* Code updates from author

= 1.0.2 =
* Cookie consent mode toggle: opt-in (default) or opt-out
* Opt-out loads counters until visitor declines; opt-in loads only after accept

= 1.0.1 =
* Fix Plugin URI and Google Analytics Terms URL for WordPress.org review
* Output counter scripts via wp_print_inline_script_tag (attributes escaped, JS intact)
* Escape noscript fallback markup via wp_kses
* Strengthen counter code validation on save

= 1.0.0 =
* Initial release
* Yandex.Metrika and Google Analytics support
* Optional cookie consent banner with path exclusions
* Integration with DevBrothers Admin Panel

== Upgrade Notice ==

= 1.0.2 =
Cookie consent mode: opt-in or opt-out.

= 1.0.1 =
Security and WordPress.org compliance update.

= 1.0.0 =
Initial release.
