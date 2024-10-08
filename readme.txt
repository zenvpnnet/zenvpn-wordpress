=== zenVPN ===
Contributors: poolby
Tags: security, admin, wp-admin, access, vpn, IP, protect, zero-trust, tunnel, tunnelling
Tested up to: 6.6.2
Stable tag: 1.0.0
Requires at least: 5.7
Requires PHP: 7.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html

Add an extra level of security to the `wp-admin` section of your WordPress by allowing access only via a secure dedicated tunnel provided by zenVPN. 

== Description ==

Add an extra level of security to the `wp-admin` section of your WordPress by allowing access only via a secure dedicated tunnel provided by [zenVPN](https://zenvpn.net/). Share the VPN tunnel with your team, and have everyone else get blocked.

== How it works ==

zenVPN guards your WordPress back-office by adding IP filters on who has access to it. 

Using our VPN service, you and your team visit your WordPress site through a secure tunnel by using a zenVPN app or browser extension. 

On WordPress, our plugin blocks access to anyone who does not come through that tunnel. 

You may use zenVPN for free just for your time, and using shared VPN locations. WordPress plugin will communicate with zenVPN via API to check your tunnel settings. Our cloud locations may be shared with other zenVPN users. For the highest level of security, we suggest buying a dedicated server with static IPs, just for you and your team.

zenVPN works via split tunnelling.  It means you can leave zenVPN on, and we will route only your WordPress site through a secure tunnel. You do not need to keep VPN connection established all the time for all your traffic. Other websites remain intact, going their regular route. 

== Installation ==

1. Create a zenVPN account [zenvpn.net](https://zenvpn.net).
2. Create a secure tunnel to your WordPress site [My site](https://app.zenvpn.net/my-site) for free. You may choose any VPN location, buy a dedicated server, or add more tunnels and invite your team when you upgrade to a paid plan.
3. Install this WordPress plugin and follow the login prompts to connect it to your account.
4. Download and install zenVPN client app for your OS or browser: [https://app.zenvpn.net/downloads](https://app.zenvpn.net/downloads)
5. Go to your site and check that access is granted.

If you have any issues, please reach out to [support@zenvpn.net](mailto:support@zenvpn.net?subject=wp-plugin) on our website for help.

== Frequently Asked Questions ==

= It is free? =

You can use zenVPN for free, for a single site (via *My site* tunnel). You will need to buy a monthly subscription to create more tunnels, for dedicated IP access, or to share it with your team. 

= Do I need a paid subscription for this plugin to work? =

Paid subscription is not required, but is recommended. You'd need a monthly subscription to invite your team, to use dedicated servers, or to use zenVPN as a regular VPN service.

= Do I need to keep zenVPN running on my computer? =

zenVPN supports split tunnelling, so you can leave it on, and we will route only your WordPress site through a secure tunnel. 

You do not need to route all your Internet traffic through a VPN connection. Your other sites remain intact, though you may use zenVPN as a regular VPN service as well.

= Does it work with WPMU? =
Yes, it does.

== Changelog ==

= 1.0.0 =

This is our initial release. The feedback is appreciated. Let's help each other to build a secure solution. Reach out to support on the website if you need any assistance or to report an issue.