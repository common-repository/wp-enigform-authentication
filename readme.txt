=== wp-enigform-authentication ===
Contributors: buanzo
Donate link: http://www.buanzo.com.ar/
Tags: security, authentication, openpgp, gnupg, pgp, enigform
Requires at least: 2.5.1
Tested up to: 2.7.1
Stable tag: 1.2.1

This plugin provides Enigform Secure Login support for Wordpress. Works in similar way to HTTP Authentication by dwc.

== Description ==

Enigform is a Firefox Add-On which uses OpenPGP to digitally sign outgoing HTTP requests and Securely login to remote web sites, as long
as the remote web server is Enigform-compliant. There is an Apache module called mod_openpgp which needs to be configured. I recommend you read the Enigform [Definitive Guide](http://wiki.buanzo.org/index.php?n=Main.Wp-enigform-authentication) at http://wiki.buanzo.org/index.php?n=Main.Wp-enigform-authentication THEN come back here... if you still need it :)

The Definitive Guide shows how to create a client and server OpenPGP Keypair using the GNU Privacy Guard (GnuPG) suite, how to install Enigform and mod_openpgp, and finally how to install this plugin and tweak your Wordpress template (only one change required).

This is an experimental, still in research technology. Your support is LOVED. :)

== Installation ==

First of all, read http://wiki.buanzo.org/index.php?n=Main.Wp-enigform-authentication COMPLETELY. The plugin installation itself is VERY easy, but it's dependencies might not be that easy. Having said that:

1. Upload `wp-enigform-authentication.php` to the `/wp-content/plugins/` directory on your blog's server.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. IMMEDIATELY configure the plugin. Authentication plugins are tricky. Read http://wiki.buanzo.org/index.php?n=Main.Wp-enigform-authentication for details.
4. Modify your template as instructed in http://wiki.buanzo.org/index.php?n=Main.Wp-enigform-authentication
5. Did I mention you HAVE to read http://wiki.buanzo.org/index.php?n=Main.Wp-enigform-authentication ?

== Frequently Asked Questions ==

= When I attempt to login, I get No HTTP_X_OPENPGP_SESSION_STATUS equals Valid header found in $_SERVER. =

This can mean one or many of the following:

* You haven't read the [Guide](http://wiki.buanzo.org/index.php?n=Main.Wp-enigform-authentication) http://wiki.buanzo.org/index.php?n=Main.Wp-enigform-authentication
* You used the wrong Long KeyID when configuring the plugin. Remove the plugin file, restart your browser, login normally to Administrative area. Start again. You can test the headers being sent by your Enigform-enabled Firefox using http://maotest.buanzo.org
* If in Enigform Preferences in your browser you have 'Verify Server Signatures' on, make sure you have imported your server's public key into your keyring.
* Running Firefox in Debug mode (enable this easily by installing the Extension Developer extension from http://addons.mozilla.org) might help.

= What if my problem is not listed here? =

First, read again http://wiki.buanzo.org/index.php?n=Main.Wp-enigform-authentication carefully. We've put lot of effort on that guide. As it's still a work in progress, you might come across a problem we didn't. If you verify the guide does not help you, visit the [Enigform Forums](http://foros.buanzo.com.ar/viewforum.php?f=35) and let me know about the problem:
http://foros.buanzo.com.ar/viewforum.php?f=35 (That section is in English, but if you need assistance in Spanish, post in spanish there, too).

Thanks for using Enigform!
Arturo 'Buanzo' Busleiman
http://www.buanzo.com.ar/pro/eng.html

