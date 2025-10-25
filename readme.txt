=== Block Known Hostile Networks (waf4wordpress-myattackers) ===
Contributors: szepeviktor
Tags: security, firewall, waf, ip, network
Requires at least: 6.3
Tested up to: 6.3
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Blocks requests from known hostile networks using an in-database IP range list. Zero configuration.

== Description ==

**Block known hostile networks** at the WordPress level when you cannot use the host firewall.

Ideally, hostile networks should be blocked in the Linux firewall (see the ipset solution below). Use this plugin when you don't have access to the OS firewall (shared hosting, limited containers, etc.). On activation, it imports IP ranges from the plugin's `data/` directory into a database table. On every WordPress run, the client IP is checked against this table; matching requests are blocked before WordPress proceeds.

- **Zero-config.** Activate and it works.
- **Local only.** No external calls; lookups run from your database.
- **Deterministic.** Uses static IP ranges supplied with the plugin.
- **Simple.** No UI, no settings.

**Prefer the OS firewall when possible:**  
Linux ipset approach: https://github.com/szepeviktor/debian-server-tools/tree/master/security/myattackers-ipsets/ipset

= How it works =

1. **Activation import** – IP ranges from `data/` are loaded into a dedicated database table.
2. **Per-request check** – Each request's client IP is compared against the stored ranges as early as possible.
3. **Block decision** – If the IP falls into a hostile range, the request is denied.

For implementation details, see `BlockKnownHostileNetworks::isBlocked`.

= Data source =

The IP ranges live in the plugin’s `data/` directory as PHP arrays generated from ipset lists. If you maintain your own lists, see the conversion script below.

= Performance notes =

- Lookups run in a single in-DB range check per request.
- Because everything is local and static, there are no network round trips.
- Ensure your site resolves the correct **client IP** if you’re behind a proxy/load balancer (see FAQ).

= Privacy =

This plugin does not send any data to third parties. It only checks the request’s client IP against a local table.

== Installation ==

= From ZIP =

1. Download the latest release from GitHub: https://github.com/szepeviktor/waf4wordpress-myattackers/releases  
2. In **Plugins → Add New → Upload Plugin**, upload the ZIP and activate it.

= With Composer =

```
composer require szepeviktor/waf4wordpress-myattackers
```

Activate the plugin in WordPress after installation.

There is no configuration.

== Frequently Asked Questions ==

= Do I need to configure anything? =

No. There are no settings. Activation imports the ranges and blocking starts immediately.

= Where do the IP ranges come from? =

From the plugin’s `data/` directory. The lists originate from ipsets maintained outside WordPress. If you can, block at the OS firewall level:  
https://github.com/szepeviktor/debian-server-tools/tree/master/security/myattackers-ipsets/ipset

= How do I update the ranges? =

Install a newer plugin release (which includes updated `data/` files). If you modify `data/` manually, deactivate and reactivate the plugin to re-import.

= Does it work behind a reverse proxy or CDN? =

It depends on your server/proxy configuration. Ensure WordPress/PHP sees the real client IP (e.g., via proper server config for `REMOTE_ADDR` / trusted proxy headers). If the server only exposes the proxy’s IP, every request will appear to come from that IP.

= Will this block legitimate visitors? =

The plugin blocks IPs that fall within the listed ranges. Review your lists carefully. If you see false positives, adjust the source lists and re-import.

= Multisite? =

Not specifically tested. If you try it on Multisite, consider **Network Activate** to protect all sites.

== Screenshots ==

None. The plugin has no UI.

== Usage ==

- On plugin activation, IP ranges are imported from the `data/` directory into a database table.
- On every request, the client IP is checked against the stored ranges.
- See `BlockKnownHostileNetworks::isBlocked` for the block logic.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release. Imports hostile IP ranges locally and blocks matching requests. Prefer the OS firewall when available.
