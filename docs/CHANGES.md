# Canard — Change Log

This document covers all changes from the upstream Automattic release (v1.0.21) to
this fork (v3.0.0). It is intended for child theme authors and site owners migrating
from the original theme. For installation and general usage, see `readme.txt`.

**Requirements:** WordPress 6.9+, PHP 8.0+. jQuery is no longer a front-end
dependency and is not loaded by this theme on any page type.

---

## Breaking Changes

These changes require action if you are running a child theme against the upstream
release.

- **`footer#colophon` selector change.** The footer container was a `<div>`; it is
  now a `<footer>` element. Child theme CSS rules using `div#colophon` must be
  updated to `footer#colophon`.

- **Category color fallback specificity reduced.** The `.category-color-fallback`
  `background-color` was previously set via an inline `style=""` attribute (highest
  possible specificity). It is now injected via `wp_add_inline_style()` scoped to
  `body.term-{id}`, giving it class-level specificity. Child theme rules that were
  being overridden by the inline style — and compensating with `!important` — will
  now win without it. Remove any `!important` declarations targeting this property.

- **`pre` border now uses `border-inline-start`.** The `pre` element previously used
  `border-left`. It now uses the logical property `border-inline-start`. Child theme
  rules targeting `border-left-color` on `pre` must be updated to
  `border-inline-start-color`.

- **`.site-content-inner` is now a flex container.** The two-column layout shell
  (`.site-main` + `.widget-area`) was previously float-based. It is now
  `display: flex`. The `display: flow-root` rule that Canard Child applied to
  `body:not(.single) .content-area article.hentry` was counteracting the old float
  context and is now dead code — it can be removed from the child theme.

- **Social navigation removed.** The `social` menu location no longer exists.
  `register_nav_menus()` no longer registers it, and all social `<nav>` template
  blocks have been removed from `header.php` and `footer.php`. If your child theme
  registers or renders the social menu location, those references can be removed.

- **Duplicate bottom navigation removed.** `footer.php` previously re-rendered the
  `secondary` menu location in a `.bottom-navigation` block that duplicated what
  `header.php` already outputs. This block has been removed. If your child theme was
  hiding it via CSS, that rule can be removed.

- **`canard_entry_categories()` no longer emits a `<div>` wrapper.** The function
  previously wrapped its output in a `<div>`; it now outputs a bare
  `<span class="cat-links">` so it sits inline alongside the byline and date spans
  inside `.entry-meta`. Child themes that target `div.entry-meta` or rely on the
  `<div>` wrapper must update their CSS and PHP accordingly. The meta output order on
  archive cards is now: author · date · comments · categories.

---

## New Features

### Category Archive Template (`category.php`)

A dedicated category archive template has been added. It displays a full-width hero
banner at the top — either a category image or a solid-color fallback — followed by
the standard post loop with numbered pagination. The hero uses the same `entry-hero`
layout structure as single posts.

Two new pluggable functions and filters allow child themes to supply per-category
images and colors without overriding the template entirely:

- `canard_get_category_header_image()` — returns the banner image URL for the current
  category, or `false`. Wrapped in `if ( ! function_exists() )`. Exposes the
  `canard_category_header_image` filter.
- `canard_get_category_color()` — returns the solid-color fallback (`#d11415` by
  default). Exposes the `canard_category_color` filter.

See `docs/category-images.md` for usage.

### Entry Meta Restructuring

A new `canard_entry_meta_date_format` filter has been added to `canard_entry_meta()`.
On archive and index pages, the date is now formatted using this filter (default:
`'M j, Y'`, e.g. "Apr 22, 2019") instead of the site's full date format, keeping the
meta line compact. Single post views retain full-format publish and modified dates
unchanged.

CSS separator rules have been updated to handle the new meta DOM order, including
suppression logic for single-author sites (where `.byline` is hidden) and sticky
posts (where `.posted-on` is hidden).

### Entry Hero Markup Promoted to PHP

The `entry-hero` CSS class and the `entry-header-wrapper`/`entry-header-inner` div
structure are now output directly by `content-single.php` rather than being
constructed at runtime by `single.js`. `single.js` now only handles the DOM
repositioning (moving the header outside `.site-content-inner`) — it no longer
constructs wrapper elements. This eliminates a class of FOUC risk where the hero
layout could visibly snap into place if the script ran after first paint.

### Child Theme Filter: `canard_entry_footer_show_meta`

`canard_entry_footer()` previously called `canard_entry_meta()` with no way to
suppress it without overriding the entire function. A filter has been added:

```php
add_filter( 'canard_entry_footer_show_meta', '__return_false' );
```

### Mutual Panel Dismissal

The mobile navigation and header search panels now dismiss each other. Opening the
nav panel while the search panel is open closes the search panel first, and vice
versa. State changes (ARIA attributes, CSS classes) are applied synchronously to
avoid a layout recalculation between the two transitions.

---

## Removed

- **jQuery.** All five scripts that previously declared jQuery as a dependency have
  been rewritten in vanilla JavaScript. jQuery is not loaded on any page type.
- **Genericons.** The `genericons/` directory and all font files have been removed.
  All icon glyphs are now rendered as inline SVG elements or SVG `background-image`
  data URIs. The directory is safe to delete from any existing installation.
- **`js/skip-link-focus-fix.js`.** No longer required for modern browsers.
- **Social navigation.** All template markup, menu registration, and ~130 lines of
  CSS including all 26 domain-specific icon rules have been removed.
- **WordPress.com updater.** The WordPress.com updater inclusion has been removed
  from `functions.php`.
- **`inc/jetpack-fonts.php`.** Consolidated into `inc/jetpack.php`. See the Jetpack
  section below.
- **`editor-color-palette` theme support declaration.** Deprecated since WordPress
  5.9 and removed. No `theme.json` replacement has been added; Canard is a classic
  theme and the block editor color palette is not a declared theme feature.

---

## Performance

### Script Loading

- **Deferred script enqueues.** All front-end scripts except `canard-single` now
  enqueue with WordPress's native `strategy: 'defer'` API (available since WP 6.3),
  replacing a manual `script_loader_tag` string-manipulation filter. `canard-single`
  is explicitly excluded because it runs entry-hero DOM rearrangement synchronously
  to prevent a layout flash.
- **`canard-featured-content.js` conditionally enqueued.** Previously loaded on every
  page; now only enqueued on `is_front_page()`.

### Conditional Stylesheet Loading

- **`canard-blocks.css`** is now only enqueued on singular posts/pages and the front
  page (`is_singular() || is_front_page()`). Archives and search result pages no
  longer load it.
- **`canard-comments.css`** is intended to be enqueued only on singular pages where
  comments are open or present. See Known Issues.

### Image Loading

- **Single post and category hero images** set `loading="eager" fetchpriority="high"`.
  Both are LCP candidates and require an explicit override of WordPress's default
  lazy-loading. Category hero images also carry explicit `width` and `height`
  attributes derived from attachment metadata (1920×420 fallbacks) to prevent CLS.
- **Custom header image** uses `loading="eager" fetchpriority="high"` on the front
  page and `loading="lazy"` on all other pages.
- **Archive and featured-content thumbnails** set `loading="lazy"` explicitly.
- **`sizes` attribute corrected** on archive thumbnails (`content.php`) and
  featured-content thumbnails (`content-featured-post.php`) to reflect actual rendered
  widths, preventing browsers from fetching oversized images.
- **`aspect-ratio: 16/9` removed from `.post-thumbnail img`** (`style.css`). The
  hardcoded rule was distorting near-square thumbnails. CLS prevention now relies on
  the `width`/`height` attributes WordPress outputs on every `<img>`, from which
  modern browsers derive the intrinsic ratio automatically.

### Object Caching

Post navigation background CSS, avatar HTML, and `canard_google_fonts_url()` output
are now stored in the WP object cache (one-hour, one-hour, and one-day TTLs
respectively). All cache keys are prefixed with `get_current_blog_id()` to prevent
cross-site collisions on multisite networks.

### DNS Prefetch

A `dns-prefetch` hint for `secure.gravatar.com` is now emitted on all front-end
pages, starting Gravatar DNS resolution early on archive pages with multiple authors.

---

## Accessibility and Internationalization

### rem Font Sizes

Heading `font-size` values in `style.css` have been converted from `px` to `rem`
tokens (`var(--text-4xl)` through `var(--text-base)` for `h1`–`h6`). Pixel
equivalents at default browser settings are unchanged. Headings now scale
proportionally when a user increases their browser root font size, which is a common
accessibility accommodation.

### CSS Logical Properties

Physical direction properties throughout `style.css` have been replaced with CSS
logical equivalents (`margin-left` → `margin-inline-start`, `border-left` →
`border-inline-start`, etc.). `rtl.css` has been rebuilt using logical properties,
eliminating approximately 90 lines of physical-direction overrides.

### ARIA and Semantics

- **Redundant `role` attributes removed** from all templates. HTML5 landmark elements
  (`<header>`, `<nav>`, `<main>`, `<aside>`) carry implicit ARIA roles; the explicit
  attributes are unnecessary and produce validator warnings.
- **`footer#colophon` now carries `role="contentinfo"`** explicitly, because it is a
  child of `#page` (a `<div>`) rather than a direct child of `<body>`. Without the
  explicit role, screen readers did not expose the footer as the page's `contentinfo`
  landmark.
- **`tabindex="-1"` added to `#content`** (`header.php`). The skip link target must
  be programmatically focusable per WCAG 2.4.3.
- **Dropdown toggle buttons now have accessible names** (`navigation.js`). Buttons
  derive their label from the parent link text: `aria-label="Toggle [Menu Item]
  submenu"`. Fixes WCAG 2.1 SC 4.1.2.
- **Comment navigation `<h2>` replaced with `<span>`** (`comments.php`). The visually
  hidden heading created a spurious heading level in the document outline.
- **Empty anchor removed when no post thumbnail exists** (`content-featured-post.php`).
  An empty `<a class="post-thumbnail">` was rendered when `has_post_thumbnail()`
  returned false — a WCAG 2.4.4 violation.
- **`prefers-reduced-motion` media query added** (`style.css`), setting near-zero
  durations on animations and transitions for users who have requested reduced motion
  (WCAG 2.1 SC 2.3.3).
- **`.screen-reader-text`** updated: deprecated `clip: rect()` replaced with
  `clip-path: inset(50%)` and `white-space: nowrap`.

### Pagination Standardized

All listing templates (`archive.php`, `index.php`, `search.php`, `category.php`) now
use `the_posts_pagination()` with consistent `mid_size`, `prev_text`, and `next_text`
arguments.

---

## Security Hardening

A full audit was conducted across all PHP templates, include files, and JavaScript
files. The following categories of issues were addressed:

- **Output escaping.** `_e()` replaced with `esc_html_e()`; titles, search queries,
  and author meta wrapped in `esc_html()`; `href` values in `esc_url()`; HTML-returning
  functions wrapped in `wp_kses()` with explicit allowlists. Archive descriptions use
  `wp_kses_post( get_the_archive_description() )` with a matching global filter in
  `functions.php` as a safety net.
- **`printf()` replaced with `echo wp_sprintf()`** (`search.php`), closing an edge
  case where a compromised `.po` file could inject C format directives into the
  template string.
- **ABSPATH guards** added to all PHP files that were missing them.
- **`declare(strict_types=1)` removed** from all 26 PHP files (invalid in WordPress
  theme context).
- **Inline `<style>` and `<script>` output replaced** with `wp_add_inline_style()` and
  a `body_class` filter approach respectively, for CSP compatibility.
- **IDOR: password-protected post thumbnails** are no longer exposed in post navigation.
  Adjacent posts are now checked with `post_password_required()` before thumbnail URLs
  are read.
- **IDOR: category image attachment visibility validated** before metadata is read;
  only attachments with status `inherit` or `publish` are processed.
- **URL scheme validation** in `canard_get_link_url()` via `wp_http_validate_url()`
  (HTTP/HTTPS only), replacing an `esc_url()`-only approach that did not reliably
  strip `data:` URIs.
- **`rel="noopener noreferrer"` added** to the `target="_blank"` link in
  `content-link.php`, preventing reverse tabnapping.
- **JavaScript CSS injection fixed** (`featured-content.js`, `posts.js`). Image URLs
  are now validated and encoded via a shared `safeCssUrl()` helper before being written
  into `background-image` strings. The helper rejects protocol-relative and HTTP URLs
  and percent-encodes `\`, `(`, and `)`.
- **`window.canardUtils` frozen** (`utils.js`) and consumers access debounce via a
  local `resolveDebounce()` helper to prevent attacker-writable global stubs.
- **CSS injection via unvalidated header text color** fixed in `inc/custom-header.php`.
  `esc_attr()` replaced with `sanitize_hex_color_no_hash()`, which rejects non-hex
  values that could break out of the CSS property context.
- **Translatable strings in `wp_link_pages()` arguments** now use `esc_html__()` to
  prevent markup injection from compromised translation files.

---

## JavaScript

### jQuery Removal

All five scripts that declared jQuery as a dependency have been rewritten in vanilla
JavaScript. Changes are consistent across all files:

- `var` → `const` / `let`
- `.className` string manipulation → `classList.contains()`, `.add()`, `.remove()`,
  `.toggle()`
- `button.onclick =` assignments → `addEventListener( 'click', ... )`
- `'undefined' === typeof x` guards → simple truthy/falsy checks

| File | Summary |
|---|---|
| `search.js` | jQuery IIFE and `.hover()` / `.focusin()` / `.focusout()` calls replaced with `addEventListener`. `querySelector` → `querySelectorAll` with `forEach` so all search forms on a page receive hover feedback. |
| `featured-content.js` | Rewritten using `querySelectorAll`, `forEach`, `classList`, and `style.backgroundImage`. `$(window).on('load')` → `window.addEventListener('load')`. All `var` declarations converted to `const`. |
| `navigation.js` | Fully rewritten. Event delegation via `document.addEventListener('click')` replaces jQuery's `.on('click', '.dropdown-toggle')`. `touchstart` listener registered with `{ passive: true }`, resolving mobile scroll jank. Mobile nav selector fixed from fragile `masthead.querySelector('div')` to `masthead.querySelector('#site-navigation')`. |
| `single.js` | Rewritten in vanilla JS. Also absorbs the entry-hero DOM rearrangement previously emitted as an inline `<script>` by `entry-script.php`. |
| `customizer.js` | All `$()` selectors replaced with `querySelector` / `querySelectorAll`; `.text()` → `.textContent`; jQuery class methods → `classList`. |

### New File: `js/utils.js`

A shared `debounce` utility is now exposed as `window.canardUtils.debounce`. The
object is frozen with `Object.freeze()`. All consumer scripts resolve debounce via
`resolveDebounce()` rather than touching the global directly; see Security Hardening.

### Bug Fixes

- **Persistent black thumbnail box fixed** (`posts.js`). `applyBackground()` is now
  called immediately using `thumbnail.getAttribute('src')` as a fallback, then
  upgraded to `currentSrc` when `load` fires. Previously it was gated on the `load`
  event, which never fires for lazy-loaded images outside the initial viewport.
- **Height normalization removed** (`posts.js`). The `setHeight()`/`normalizeHeights()`
  system has been removed. `.post-thumbnail` is now `position: absolute; top: 0;
  bottom: 0`, so CSS fills the full card height automatically.
- **Infinite scroll event fixed** (`posts.js`). The handler was listening for
  `'inf_scr_posts_loaded'` on `document`; the correct Jetpack event is `'is.post-load'`
  dispatched on `document.body`.
- **Debounced resize listeners hoisted to module scope** (`posts.js`, `navigation.js`,
  `single.js`). Previously a new function reference was created on each `load` event,
  causing listener accumulation with Jetpack Infinite Scroll's synthetic load events.
- **`checkSiteBranding()` deferred to `DOMContentLoaded`** (`header.js`). Reading
  `clientHeight` synchronously at parse time could return 0 before stylesheets applied,
  causing `no-site-branding` to be incorrectly added to `<body>`.

---

## CSS and Styling

### Design Tokens

A `:root` block is now the first rule in `style.css`, declaring CSS custom properties
for all design values: `--color-accent` (`#d11415`), `--color-text`, `--color-muted`,
`--color-border`, font-family tokens (`--font-body`, `--font-display`, `--font-ui`,
`--font-mono`), a rem-based type scale (`--text-xs` through `--text-4xl`), and
`--space-base`. All ~250–300 hardcoded hex values and pixel sizes throughout
`style.css` have been replaced with `var(--token)` references. Child themes can
override any value by redeclaring the token in their own `:root` block.

### Float → Flexbox

- **`.site-content-inner`** converted from float-based to `display: flex` at the
  960px+ breakpoint. Visual output is identical.
- **`.post-navigation` / `.posts-navigation`** converted from float-clearfix to
  `display: flex; flex-wrap: wrap`. `text-align: right` on `.nav-next` replaced with
  `text-align: end`.
- **`.clearfix` compatibility shim retained** for one version cycle for child themes
  using `class="clearfix"` inside converted layout shells. This shim will be removed
  in a future version.

### Genericons → SVG

All icon glyphs previously rendered via the Genericons font are now inline SVG
elements (in templates) or SVG `background-image` data URIs (in CSS pseudo-elements).

**CSS pseudo-element replacements (`style.css`):**

| Selector | Former glyph | Replacement |
|---|---|---|
| `blockquote:before` | `\f106` quotation mark | SVG quotation mark |
| `.search-form:before` | `\f400` magnifying glass | SVG magnifying glass |
| `.menu-toggle:before` | `\f419` hamburger | SVG hamburger |
| `.toggled .menu-toggle:before` | `\f406` close | SVG × |
| `.dropdown-toggle:before` | `\f431` chevron-down | SVG chevron-down |
| `.dropdown-toggle.toggled:before` | `\f432` chevron-up | SVG chevron-up |
| `.search-toggle:before` | `\f400` magnifying glass | SVG magnifying glass |
| `.toggled .search-toggle:before` | `\f406` close | SVG × |
| `.sidebar-toggle:before` | `\f476` ellipsis | SVG three-dot |
| `.toggled.sidebar-toggle:before` | `\f406` close | SVG × |
| `.posts-navigation .nav-next a:after` | `\f429` right arrow | SVG right chevron |
| `.posts-navigation .nav-previous a:before` | `\f430` left arrow | SVG left chevron |
| `.comment-navigation .nav-next a:after` | `\f429` right arrow | SVG right chevron |
| `.comment-navigation .nav-previous a:before` | `\f430` left arrow | SVG left chevron |
| `.main-navigation .menu-item-has-children > a:after` | `\f431` chevron-down | SVG chevron-down |

**Template icon replacements:**

| File | Former element | Replacement |
|---|---|---|
| `content.php` | `<span class="genericon genericon-pinned">` | Inline SVG pin icon |
| `content-link.php` | `<span class="genericon genericon-link">` | Inline SVG external-link icon |
| `footer.php` | `<span class="genericon genericon-wordpress sep">` | Inline SVG WordPress logo mark |

All replacement SVG elements carry `aria-hidden="true" focusable="false"`.

### Category Link Styles

- **`.entry-hero .cat-links`** now carries explicit typography, color, and text-shadow
  rules so category links render correctly against the hero image.
- **Archive card `.cat-links`** is forced to `display: block` with the generated CSS
  separator suppressed, so categories sit on their own line below the other meta items.

### Bug Fixes

- **`.post-thumbnail::before` overlay fixed** (`style.css`). The dark tint
  pseudo-element on format-image and format-gallery archive posts was missing
  `position: absolute`. Fixed with `position: absolute; inset: 0`.
- **Hamburger icon vertical misalignment on iPad Portrait (768–959px) fixed.**
  Replaced a hardcoded `margin-top: -30px` with `transform: translateY(-50%)`.
  `.search-navigation` given the same treatment.
- **Jetpack Infinite Scroll numbered pagination now hidden** when infinite scroll is
  active. The existing selector did not match `the_posts_pagination()` output.
- **Block editor styles restored** (`editor-blocks.css`). All 47 occurrences of the
  removed `.edit-post-visual-editor` class replaced with `.editor-styles-wrapper`.
- **`.alignwide` and `.alignfull` rules added** to both `blocks.css` and
  `editor-blocks.css`.
- **Legacy vendor prefixes removed.** All `-webkit-box`, `-ms-flexbox`, and
  `-webkit-transform` prefixes stripped.

---

## PHP

### WordPress API Modernization

- **Script deferral** uses the native WP 6.3+ `strategy: 'defer'` enqueue API.
- **Block editor styles** use `add_theme_support( 'editor-styles' )` +
  `add_editor_style()` — the recommended pattern since WP 5.8.
- **Classic widgets** use `add_filter( 'use_widgets_block_editor', '__return_false' )`
  in place of the removed `canard_disable_block_widgets()` function.
- **`apply_filters( 'the_permalink', ... )` removed** from `canard_get_link_url()`;
  replaced with `get_the_permalink()` (deprecated in WP 6.8).
- **Loose comparisons tightened** throughout: `==` → `===`; `strpos()` → `str_contains()`.
- **`add_theme_support`** expanded: `html5` now includes `script` and `style`;
  `align-wide`, `wp-block-styles`, `navigation-widgets`, and
  `customize-selective-refresh-widgets` added.
- **Google Fonts** merged into a single `/css2` request with `&display=swap`.
  Preconnect hints migrated from the soft-deprecated `wp_resource_hints` filter to
  `wp_preconnect_resources` / `wp_preconnect_url()`.
- **`CANARD_VERSION` constant added** to replace hardcoded version strings in
  all enqueues.

### PHP 8 Type Safety

Parameter and return type hints added to all public and hookable functions across all
PHP files. This includes `void`, `bool`, `array`, `array|false`, and object parameter
types (`WP_Customize_Manager`) where previously absent. Loose boolean casts
(`true === (bool) get_theme_mod(...)`) simplified to plain truthy checks throughout.

### Transients and Cache

- **Transient key renamed** from `canard_categories` to `canard_cat_count_v1` to
  avoid multisite collisions.
- **Explicit TTL added** (`WEEK_IN_SECONDS`). Previously called with no expiry
  argument, causing the transient to accumulate indefinitely on sites without a
  persistent cache backend.
- **Transient flusher guarded** against autosaves and revisions.

### Jetpack

- **`inc/jetpack-fonts.php` consolidated and deleted.** The file registered the
  `typekit_add_font_category_rules` filter without a `class_exists( 'TypekitTheme' )`
  guard, producing a fatal error on sites without Jetpack or with the Adobe Fonts
  module disabled. All rules moved into `inc/jetpack.php` under the existing guard.
- **Four typos in Typekit font rules fixed**: `font-wieght` spelling, a stray `{` in
  a font name, `'blod'` font-weight value, and missing leading `.` in `:not()`
  selectors — all of which silently disabled those rules in the upstream release.
- **`jetpack-content-options` declaration expanded.** `blog-display` and `author-bio`
  keys added to expose the corresponding toggles in Jetpack → Settings → Writing.
- **`array_search()` strict-mode argument added** in `canard_jetpack_portfolio_classes()`
  to prevent silent class removal via falsy key matches.
- **`canard_jetpack_portfolio_classes()`** now returns early when the post type is not
  `jetpack-portfolio`, avoiding an unconditional `get_post_format()` call on every
  page load.

### Bug Fixes

- **Pagination arrows rendered as literal entity names** (`archive.php`, `category.php`).
  `prev_text` and `next_text` arguments to `the_posts_pagination()` were wrapped with
  `esc_html__()`, which encodes `&` and caused `&larr;` / `&rarr;` to appear as literal
  text. Fixed by using `__()`, matching the pattern already used correctly in `index.php`.
- **`function_exists( 'canard_jetpack_featured_image_display' )` guard added** to
  `entry-script.php`. Previously calling this function when Jetpack was inactive would
  produce a fatal error.

---

## Code Quality and Housekeeping

This section covers changes with no behavioral impact: logic simplifications, style
unification, and comment hygiene across all PHP, JS, and template files.

### PHP

- **`function_exists` guard syntax unified** to brace form (`{ }`) across all files.
  The `: ... endif` form has been removed throughout. PHPDoc blocks are indented inside
  guards consistently.
- **`canard_google_fonts_url()`** wrapped in a `function_exists` guard, consistent
  with `canard_get_category_header_image()` and `canard_get_category_color()`.
- **`canard_widgets_init()`** shared sidebar markup extracted to `$sidebar_defaults`;
  `array_merge()` makes per-sidebar differences immediately visible.
- **`canard_scripts()`** `canard-single` enqueue rewritten to array-form args. The
  absence of `strategy: 'defer'` is now expressed as an inline comment adjacent to
  the enqueue call.
- **`canard_entry_meta()`** redundant `true === (bool)` double-cast simplified to a
  plain truthy check. Inaccurate comment referencing `$authordata` global corrected to
  reference `get_the_author_meta()`.
- **`canard_continue_reading()`** intermediate `$the_excerpt` variable removed;
  `sprintf()` result returned directly. Elvis operator (`?:`) used in
  `canard_get_link_url()` where the ternary repeated the condition.
- **`canard_jetpack_has_multiple_featured_posts()`** `if/return true; return false`
  pattern replaced with a direct boolean `return` expression.
- **`canard_custom_header_setup()`** args array extracted to a variable before being
  passed to `add_theme_support()`.
- **`canard_header_style()`** `get_theme_support()` call for the default text color
  assigned to a named variable to clarify intent.
- **`header.php`** `$site_title_tag` echoed via `esc_html()` per WordPress VIP
  standards, consistent with the allowlist validation already in place.
- **`sidebar.php`** redundant `true !== (bool)` casts simplified; `: ... endif` syntax
  inside the PHP block unified to brace syntax.
- **Inline comments** removed or replaced across `canard_setup()`, `canard_widgets_init()`,
  `canard_jetpack_setup()`, `inc/customizer.php`, `inc/extras.php`, `inc/template-tags.php`,
  `footer.php`, `sidebar.php`, `comments.php`, `author-bio.php`, and all top-level
  template files where comments described operations rather than rationale, duplicated
  PHPDoc, or preserved fix-history no longer relevant to contributors.

### JavaScript

- **`featured-content.js`** all `var` declarations converted to `const`.
- **`'use strict'`** removed from `posts.js` and `single.js` for consistency; the
  IIFE scope already provides equivalent isolation and no other file in the theme
  declares it.
- **Over-annotated anonymous callbacks** cleaned up across `navigation.js`, `posts.js`,
  `single.js`, `customizer.js`, and `featured-content.js`. JSDoc blocks on one-liner
  `.forEach()`, `.addEventListener()`, and `.bind()` callbacks have been removed.
- **`posts.js` `@fileoverview`** fix-history paragraph (describing what height
  normalization used to do) removed; current behavior is already documented.

---

## Files Added, Removed, and Renamed

**Added:**
- `js/utils.js` — shared `debounce` utility
- `category.php` — native category archive template with hero banner
- `docs/CHANGES.md` — this file
- `docs/category-images.md` — child theme guide for per-category banner images and
  colors

**Deleted:**
- `js/skip-link-focus-fix.js`
- `genericons/` — entire directory
- `inc/jetpack-fonts.php` — consolidated into `inc/jetpack.php`
