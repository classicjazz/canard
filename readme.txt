=== Canard ===

Contributors: automattic, Michael Connelly
Tags: red, white, light, two-columns, right-sidebar, responsive-layout, custom-header, custom-menu, featured-images, flexible-header, post-formats, rtl-language-support, sticky-post, theme-options, translation-ready, featured-content-with-pages

Requires at least: 6.9
Tested up to: 6.9
Stable tag: 3.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A flexible and versatile magazine theme.

== Description ==

This is a community fork of the Automattic Canard theme (v1.0.21), modernized for
WordPress 6.9+ and maintained independently by Michael Connelly. It is not affiliated
with Automattic. For a full list of changes from the upstream version, see
docs/CHANGES.md.

Canard is a flexible and versatile theme for magazines, news sites, and blogs. It
highlights specific articles on the homepage and balances readability with strong use
of photography — all in a responsive layout that works on any device.

This theme is designed to be used with a child theme.

* Responsive layout.
* No jQuery dependency — all scripts are vanilla JavaScript.
* Jetpack compatibility for Infinite Scroll, Featured Content, Responsive Videos,
  and Site Logo.
* Category archive template with full-width hero banner.
* CSS custom properties for all design tokens, easily overridden in a child theme.
* RTL language support via CSS logical properties.
* Performance-optimized image loading: LCP-aware fetchpriority, conditional
  stylesheet enqueues, and object caching for avatars and navigation backgrounds.

== Installation ==

1. In your admin panel, go to Appearance > Themes and click the Add New button.
2. Click Upload and Choose File, then select the theme's .zip file. Click Install Now.
3. Click Activate to use your new theme right away.

== Frequently Asked Questions ==

= I don't see the Featured Content menu in my customizer, where can I find it? =

To make the Featured Content menu appear in your customizer, you need to install
the [Jetpack plugin](https://jetpack.com) because it has the required code needed
to make [featured content](https://jetpack.com/support/featured-content/) work
for the Canard theme.

Once Jetpack is active, the Featured Content menu will appear in your customizer.
No special Jetpack module is needed and a WordPress.com connection is not required
for the Featured Content feature to function. Featured Content will work on a
localhost installation of WordPress if you add this line to `wp-config.php`:

`define( 'JETPACK_DEV_DEBUG', true );`

= Where can I add widgets? =

Canard offers two widget areas, which can be configured in Appearance → Widgets:

* An optional sidebar widget area, which appears on the right.
* An optional footer widget area.

= How do I supply a banner image or color for a category archive page? =

See docs/category-images.md for instructions on using the
`canard_category_header_image` and `canard_category_color` filters, or overriding
`canard_get_category_header_image()` and `canard_get_category_color()` entirely in
a child theme.

== Quick Specs (all measurements in pixels) ==

Use these measurements when preparing images and custom layouts for this theme.

1. The main column width for posts is 540.
2. The main column width for pages is 870.
3. A widget is 270 wide.
4. Featured Images are 1920 wide by 768 high.
5. Category hero images are 1920 wide by 420 high (fallback aspect ratio; rendered
   height scales responsively at 260px, 360px, and 420px across breakpoints).
