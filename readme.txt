=== Canard ===

Contributors: automattic, Michael Connelly
Tags: red, white, light, two-columns, right-sidebar, responsive-layout, custom-header, custom-menu, featured-images, flexible-header, post-formats, rtl-language-support, sticky-post, theme-options, translation-ready, featured-content-with-pages

Requires at least: 6.9
Tested up to: 6.9
Stable tag: 2.5.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A flexible and versatile magazine theme.

== Description ==

This is a fork of the Wordpress theme, Canard. See docs/CHANGES.md for a comprehensive list of all changes from Automattic's version v1.0.21.

Canard is a flexible and versatile theme perfect for magazines, news sites, and blogs. It lets you highlight specific articles on the homepage and balances readability with a powerful use of photography — all in a layout that works on any device.

* Responsive layout.
* Jetpack.me compatibility for Infinite Scroll, Featured Content, Responsive Videos, Site Logo.
* The GPL v2.0 or later license. :) Use it to make something cool.

== Installation ==

1. In your admin panel, go to Appearance > Themes and click the Add New button.
2. Click Upload and Choose File, then select the theme's .zip file. Click Install Now.
3. Click Activate to use your new theme right away.

== Frequently Asked Questions ==

= I don't see the Featured Content menu in my customizer, where can I find it? =

To make the Featured Content menu appear in your customizer, you need to install the [Jetpack plugin](http://jetpack.me) because it has the required code needed to make [featured content](http://jetpack.me/support/featured-content/) work for the Canard theme.

Once Jetpack is active, the Featured Content menu will appear in your customizer. No special Jetpack module is needed and a WordPress.com connection is not required for the Featured Content feature to function. Featured Content will work on a localhost installation of WordPress if you add this line to `wp-config.php`:

`define( 'JETPACK_DEV_DEBUG', TRUE );`

= Where can I add widgets? =

Canard offers two widget areas, which can be configured in Appearance → Widgets:

* An optional sidebar widget area, which appears on the right.
* An optional footer widget area.

== Quick Specs (all measurements in pixels) ==

1. The main column width for posts is 540.
2. The main column width for pages is 870.
3. A widget is 270 wide.
4. Featured Images are 1920 wide by 768 high.
