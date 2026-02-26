# Canard — Change Log

This document covers all changes from the upstream Automattic release (v1.0.21) to
this fork (v2.7.0). It is intended for child theme authors and site owners migrating
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

- **Single post hero** (`content-single.php`): `loading="eager" fetchpriority="high"`
  added. WordPress 5.5+ lazy-loads all images by default; the single post hero is the
  LCP element and requires an explicit override.
- **Category hero** (`category.php`): Changed from `loading="lazy"` to
  `loading="eager" fetchpriority="high"`. The category hero is the topmost element on
  archive pages and the LCP candidate. Explicit `width` and `height` attributes are
  now set from attachment metadata (with 1920×420 fallbacks) to prevent CLS.
- **Custom header image** (`header.php`): `loading="eager" fetchpriority="high"` on
  the front page; `loading="lazy" fetchpriority="auto"` on all other pages.
- **Archive and featured-content thumbnails** (`content.php`,
  `content-featured-post.php`): `loading="lazy"` set explicitly rather than relying
  on WordPress's auto-lazy behavior.
- **`sizes` attribute corrected on archive thumbnails** (`content.php`): Corrected to
  `(max-width: 767px) 100vw, (max-width: 1039px) 50vw, 620px`, preventing the
  browser from fetching a full-width image on desktop.
- **`sizes` attribute corrected on featured content thumbnails**
  (`content-featured-post.php`): Corrected to `(max-width: 1300px) 100vw, 1300px`.
- **`aspect-ratio: 16/9` removed from `.post-thumbnail img`** (`style.css`): The
  theme registers multiple image sizes with different natural ratios. The hardcoded
  rule was squashing near-square thumbnails. CLS prevention now relies on the
  `width`/`height` attributes WordPress outputs on every `<img>` element, from which
  modern browsers derive the intrinsic aspect ratio automatically.

### Object Caching

- **Post navigation background CSS** (`inc/template-tags.php`): The generated CSS
  is now stored in the WP object cache under `canard_nav_bg_{blog_id}_{post_id}` with
  a one-hour TTL, eliminating repeat meta lookups on sites with Redis or Memcached.
  The hook registration has also been deferred to `template_redirect` so it only fires
  on single post and attachment pages.
- **Avatar HTML** (`inc/template-tags.php`): `get_avatar()` output is cached under
  `canard_avatar_{blog_id}_{md5(email)}_{size}` with a one-hour TTL, replacing
  per-request Gravatar lookups on archive pages with multiple posts by the same author.
- **`canard_google_fonts_url()`** (`functions.php`): Memoized via the WP object cache
  with a `DAY_IN_SECONDS` TTL. The function was previously called three times per page
  load and re-evaluated all translation checks on each call.
- **All object cache keys are prefixed with `get_current_blog_id()`** to prevent
  cross-site cache collisions on multisite networks.

### DNS Prefetch

- A `dns-prefetch` hint for `secure.gravatar.com` is now emitted on all front-end
  pages via `canard_resource_hints()`, starting Gravatar DNS resolution early on
  archive pages with multiple authors.

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
`border-inline-start`, etc.). Affected properties include `.alignleft`/`.alignright`
margins, `.aligncenter` auto-margins, the `pre` border accent, and the
`.site-main`/`.widget-area` border and padding at the 960px breakpoint.

`rtl.css` has been rebuilt using logical properties, eliminating approximately 90
lines of physical-direction overrides. The `pre` RTL border override and the
`comment-navigation`/`posts-navigation` float-reversal overrides have been removed
entirely — they are no longer needed.

### ARIA and Semantics

- **Redundant `role` attributes removed** from all templates. HTML5 landmark elements
  (`<header>`, `<nav>`, `<main>`, `<aside>`) carry implicit ARIA roles; the explicit
  attributes are unnecessary and produce validator warnings.
- **`footer#colophon` now carries `role="contentinfo"`** explicitly, because it is a
  child of `#page` (a `<div>`) rather than a direct child of `<body>`. The implicit
  landmark role on `<footer>` only applies in the latter case. Without the explicit
  role, screen readers did not expose the footer as the page's `contentinfo` landmark.
- **`tabindex="-1"` added to `#content`** (`header.php`). The skip link target must
  be programmatically focusable (WCAG 2.4.3). In WebKit and older Blink, `<div>`
  elements are not keyboard-focusable without this attribute.
- **Dropdown toggle buttons now have accessible names** (`navigation.js`). Buttons
  derive their label from the parent link text: `aria-label="Toggle [Menu Item]
  submenu"`. Fixes WCAG 2.1 SC 4.1.2 — previously screen readers announced only
  "button" with no context.
- **Comment navigation `<h2>` replaced with `<span>`** (`comments.php`). The visually
  hidden heading inside `<nav aria-label="Comment Navigation">` created a spurious
  heading level in the document outline. The `<nav>`'s `aria-label` already provides
  full context.
- **Empty anchor removed when no post thumbnail exists** (`content-featured-post.php`).
  A `<a class="post-thumbnail">` with no content was rendered when
  `has_post_thumbnail()` returned false — a WCAG 2.4.4 violation. The anchor is now
  only rendered inside the `has_post_thumbnail()` check.
- **`prefers-reduced-motion` media query added** (`style.css`). An
  `@media (prefers-reduced-motion: reduce)` block sets near-zero durations on
  animations and transitions for users who have requested reduced motion (WCAG 2.1
  SC 2.3.3). Existing transition rules are left in place for users without the
  preference.
- **`.screen-reader-text`** updated: deprecated `clip: rect()` replaced with
  `clip-path: inset(50%)` and `white-space: nowrap`.

### Pagination Standardized

`archive.php`, `index.php`, and `search.php` were using `the_posts_navigation()`
(prev/next only) while `category.php` used `the_posts_pagination()` (numbered pages).
All listing templates now use `the_posts_pagination()` with consistent `mid_size`,
`prev_text`, and `next_text` arguments.

---

## Security Hardening

A full audit was conducted across all PHP templates, include files, and JavaScript
files. The following categories of issues were addressed:

- **Output escaping.** `_e()` replaced with `esc_html_e()`; `get_the_title()`,
  `get_search_query()`, `bloginfo()`, and author meta values wrapped in `esc_html()`;
  `href` values wrapped in `esc_url()`; HTML-returning functions (`get_avatar()`,
  `$categories_list`, entry meta) wrapped in `wp_kses()` with explicit allowlists.
  `the_archive_description()` replaced with `wp_kses_post( get_the_archive_description() )`
  in `archive.php` and `category.php`, with a global filter also registered in
  `functions.php` as a safety net.
- **ABSPATH guards.** `if ( ! defined( 'ABSPATH' ) ) exit;` added to all template and
  include files that were missing it. All PHP files in the theme now carry this guard.
- **`declare(strict_types=1)` removed.** Invalid in WordPress theme files; removed
  from all 26 PHP files.
- **Inline `<style>` output replaced with `wp_add_inline_style()`.** Both
  `canard_header_style()` in `inc/custom-header.php` and the category color injection
  in `category.php` previously echoed raw `<style>` tags. Both now use
  `wp_add_inline_style()`, which is compatible with Content Security Policy headers.
- **Inline `<script>` removed from `entry-script.php`.** The file previously emitted
  an inline script block. Inline scripts are blocked by CSP headers. The logic has
  been moved into `single.js`, triggered by a `has-entry-hero` body class set via a
  `body_class` filter in `functions.php`.
- **IDOR: password-protected post thumbnails no longer exposed** in post navigation
  (`inc/template-tags.php`). `get_adjacent_post()` returns password-protected posts
  to all visitors. Featured image URLs from those posts were being injected as visible
  CSS background rules before the password had been entered. Both adjacent posts are
  now checked with `post_password_required()` before their thumbnail URLs are read.
- **IDOR: category image attachment visibility validated** (`category.php`). The
  `_category_image_id` term meta value is now validated with `get_post_status()`
  before metadata is read; only attachments with status `inherit` or `publish` are
  processed.
- **URL scheme validation** (`inc/extras.php`). `canard_get_link_url()` now validates
  extracted URLs with `wp_http_validate_url()` (HTTP/HTTPS only) before use, replacing
  the previous `esc_url()`-only approach which did not reliably strip `data:` URIs.
- **`rel="noopener noreferrer"` added** to the `target="_blank"` external link in
  `content-link.php`, preventing reverse tabnapping.
- **JavaScript — CSS injection fixed** (`featured-content.js`, `posts.js`). Image
  `src` values were previously concatenated directly into `background-image: url()`
  strings. Both files now use a shared `safeCssUrl()` helper that validates the URL
  format and wraps the value in double-quoted, encoded form.
- **JavaScript — `window.canardUtils` frozen** (`utils.js`). The shared utilities
  object is now frozen with `Object.freeze()` to prevent third-party scripts from
  replacing `debounce` with a malicious implementation. Consumer scripts
  (`posts.js`, `navigation.js`, `single.js`) now open with a load-failure guard that
  installs a no-op passthrough if `canardUtils` is unavailable, preserving
  functionality under network errors or ad-blocker interference.
- **JavaScript — hex color validation consolidated** (`customizer.js`). The
  `header_textcolor` binding now validates against a single self-documenting regex
  (`/^#(?:[0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/`) before assignment,
  replacing a fragile two-part check that required parallel maintenance.

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
| `search.js` | jQuery IIFE and `.hover()` / `.focusin()` / `.focusout()` calls replaced with `addEventListener`. `querySelector` → `querySelectorAll` with `forEach` so all search forms on a page (header + widget area) receive hover feedback, not just the first. |
| `featured-content.js` | Rewritten using `querySelectorAll`, `forEach`, `classList`, and `style.backgroundImage`. `$(window).on('load')` → `window.addEventListener('load')`. |
| `navigation.js` | Fully rewritten. Event delegation via `document.addEventListener('click')` replaces jQuery's `.on('click', '.dropdown-toggle')`. Desktop cleanup now removes `.dropdown-toggle` buttons from both `.main-navigation` and `.widget_nav_menu` contexts. `document` `touchstart` listener now registered with `{ passive: true }`, resolving mobile scroll jank. Mobile nav selector fixed from fragile `masthead.querySelector('div')` to `masthead.querySelector('#site-navigation')`. |
| `single.js` | Rewritten in vanilla JS. Also absorbs the entry-hero DOM rearrangement previously emitted as an inline `<script>` by `entry-script.php`. `nextSibling` guard fixed to `nextElementSibling` to prevent redundant DOM reinsertion on every resize tick. |
| `customizer.js` | All `$()` selectors replaced with `querySelector` / `querySelectorAll`; `.text()` → `.textContent`; jQuery class methods → `classList`. |

### New File: `js/utils.js`

A shared `debounce` utility is now exposed as `window.canardUtils.debounce`. The
object is frozen with `Object.freeze()`. All consumer scripts include a load-failure
guard; see the Security section.

### Bug Fixes in `posts.js`

- **Persistent black thumbnail box fixed.** `applyBackground()` was previously gated
  on the image `load` event. For `loading="lazy"` images outside the initial viewport,
  that event never fires until the user scrolls, leaving a solid-black thumbnail box
  visible indefinitely. `applyBackground()` is now called immediately using
  `thumbnail.getAttribute('src')` as a fallback, then upgraded to `currentSrc` when
  `load` fires.
- **Card height computation fixed.** `setHeight()` was using a hardcoded `marginSize`
  constant (60px desktop / 30px mobile) that did not match the actual computed
  `padding-top` value, causing thumbnails to extend into the text region. Replaced
  with `parseInt( getComputedStyle( entry ).paddingTop, 10 )`.
- **Infinite scroll event fixed.** The handler was listening for
  `'inf_scr_posts_loaded'` on `document`. The correct Jetpack event is `'is.post-load'`
  dispatched on `document.body`. Both the event name and the target were wrong; the
  handler never fired and no infinite-scroll batch was ever processed.
- **Scoped `querySelectorAll` selector fixed.** The selector `'.site-main .hentry'`
  always returned zero results when called with a scoped `Element` (as in
  infinite-scroll batches) because the `.site-main` ancestor is outside the subtree.
  Changed to `'.hentry'`.
- **Uniform card height across each batch.** `setHeight()` previously computed each
  article's height independently, producing variable-height cards. The script now
  waits for all images in a batch to load, then measures every article in a single
  `requestAnimationFrame` and writes the batch maximum uniformly to all
  `.post-thumbnail` elements.
- **`currentSrc` vs `src` for background images** (`featured-content.js`, `posts.js`).
  Both scripts now use `thumbnail.currentSrc || thumbnail.src` so the background image
  matches the srcset-selected URL the browser already fetched, rather than the base
  `src`.

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
  960px+ breakpoint. `float: left` / `float: right` removed from `.site-main` and
  `.widget-area`. Visual output is identical.
- **`.post-navigation` / `.posts-navigation`** converted from float-clearfix to
  `display: flex; flex-wrap: wrap` with `flex: 0 0 50%` on each child.
  `text-align: right` on `.nav-next` replaced with `text-align: end`.
- **`.clearfix` compatibility shim retained** (`::after` pseudo-element clearfix) for
  one version cycle for child themes using `class="clearfix"` inside converted layout
  shells. This shim will be removed in a future version.

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

CSS selectors updated from `.genericon` to `svg` throughout `style.css` and
`editor-blocks.css`. The `post-link` hover easter egg is preserved.

### Bug Fixes

- **`.post-thumbnail::before` overlay fixed** (`style.css`). The dark tint
  pseudo-element on format-image and format-gallery archive posts was missing
  `position: absolute`, so it was not being rendered at all. Fixed with
  `position: absolute; inset: 0`. Entry text elements given explicit `z-index: 2`
  so they render above the overlay.
- **Hamburger icon vertical misalignment on iPad Portrait (768–959px) fixed**
  (`style.css`). The `.menu-toggle` used a hardcoded `margin-top: -30px` centering
  offset that did not recompute when `.site-branding` grew at the 768px font-size
  breakpoint. Fixed with `transform: translateY(-50%)` in a new
  `@media (min-width: 600px) and (max-width: 959px)` block. `.search-navigation`
  given the same treatment so both icons share the same vertical anchor. Button and
  pseudo-element dimensions normalized to 60×60px and 58×58px respectively across
  the 600px breakpoint.
- **Jetpack Infinite Scroll numbered pagination now hidden** (`style.css`). The
  existing `.infinite-scroll .posts-navigation { display: none; }` rule did not match
  `<nav class="navigation pagination">` (the output of `the_posts_pagination()`),
  leaving a visible page-number bar at the bottom of the first page. The two selectors
  have been merged.
- **Block editor styles restored** (`editor-blocks.css`). All 47 occurrences of the
  removed `.edit-post-visual-editor` class (deprecated in WP 5.8) replaced with
  `.editor-styles-wrapper`. Custom typography, link, blockquote, heading, and code
  styles were silently absent in the block editor since WP 6.x.
- **`.alignwide` and `.alignfull` rules added** to both `blocks.css` (frontend) and
  `editor-blocks.css` (editor preview).
- **`::placeholder` modernized** (`style.css`). Placeholder hacks replaced with
  `::placeholder { color: #777; opacity: 1; }`.
- **Focus ring modernized** (`style.css`). `:focus:not(:focus-visible) { outline:
  none; }` implemented for keyboard-accessible focus management.
- **Legacy vendor prefixes removed.** All `-webkit-box`, `-ms-flexbox`, and
  `-webkit-transform` prefixes stripped.
- **`speak: none` declarations removed** (`blocks.css`). Not a valid CSS property.

### Normalization and Cleanup

- `abbr[title]` updated to use `underline dotted`.
- Empty ruleset stubs removed from `blocks.css`.

---

## PHP

### WordPress API Modernization

- **Script deferral.** Uses the native WP 6.3+ `strategy: 'defer'` enqueue API
  rather than a `script_loader_tag` string-manipulation filter.
- **Block editor styles.** Replaced manual `wp_enqueue_style()` on
  `enqueue_block_editor_assets` with `add_theme_support( 'editor-styles' )` +
  `add_editor_style()` — the recommended pattern since WP 5.8. Provides automatic
  `.editor-styles-wrapper` scoping and correct RTL handling.
- **Classic widgets.** Replaced the `canard_disable_block_widgets()` function and its
  hook with `add_filter( 'use_widgets_block_editor', '__return_false' )`.
- **`header_image()` replaced with `esc_url( get_header_image() )`.** The template
  tag calls `echo` internally and bypasses the escaping layer.
- **`wp_get_attachment_image_src()` replaced with `wp_get_attachment_image_url()`**
  in `inc/template-tags.php`.
- **`apply_filters( 'the_permalink', ... )` removed** from `canard_get_link_url()`.
  The `the_permalink` filter was deprecated in WP 6.8; replaced with
  `get_the_permalink()`.
- **`strpos()` replaced with `str_contains()`** in `content.php` (PHP 8 idiomatic).
- **Loose comparisons tightened** throughout. `==` → `===`; `'0' != get_comments_number()`
  → `0 !== (int) get_comments_number()`.

### PHP 8 Type Safety

Parameter and return type hints added to all public/hookable functions, including:
`canard_body_classes()`, `canard_excerpt_length()`, `canard_continue_reading()`,
`canard_categorized_blog()`, `canard_google_fonts_url()`, and
`canard_resource_hints()`.

### Google Fonts

- **Merged into a single request** using the v2 API (`/css2`) with `&display=swap`.
- **Preconnect hints** added for `fonts.googleapis.com` and `fonts.gstatic.com` via
  the `wp_resource_hints` filter — the correct WordPress API rather than a raw
  `wp_head` echo.

### `functions.php`

- **`CANARD_VERSION` constant added** to replace hardcoded version strings in all
  enqueues.
- **`add_theme_support( 'html5' )` expanded** to include `script` and `style`.
- **`navigation-widgets` and `customize-selective-refresh-widgets` support added.**
  `navigation-widgets` prevents WordPress from outputting a `<div>` wrapper around
  navigation widgets when the html5 flag is active.
  `customize-selective-refresh-widgets` improves Customizer preview performance.
- **`add_theme_support( 'align-wide' )` added.** Without this declaration, the block
  editor silently ignores wide and full-width alignment controls.
- **`add_theme_support( 'wp-block-styles' )` added.** Required for full WP 6.9 block
  compatibility.
- **All asset enqueues use `get_template_directory_uri()`**, ensuring the
  `canard-style` handle always resolves to the parent theme regardless of child theme
  state.

### `inc/jetpack.php`

- **`inc/jetpack-fonts.php` consolidated and deleted.** The file registered the
  `typekit_add_font_category_rules` filter without a `class_exists( 'TypekitTheme' )`
  guard, producing a fatal error on sites without Jetpack or with the Adobe Fonts
  module disabled. All rules have been moved into `inc/jetpack.php` under the existing
  guard; the duplicate partial rule set in `inc/jetpack.php` has been removed; the
  file has been deleted.
- **Four typos in Typekit font rules fixed** during consolidation: `font-wieght`
  spelling, a stray `{` in a font name, `'blod'` font-weight value, and missing
  leading `.` in `:not()` class selectors — all of which caused those rules to have no
  effect in the upstream release.
- **`jetpack-content-options` declaration expanded.** `blog-display` and `author-bio`
  keys were absent; both have been added to expose the corresponding toggles in
  Jetpack → Settings → Writing → Content Options.

### Transients and Cache

- **Transient key renamed** from `canard_categories` to `canard_cat_count_v1` to
  avoid multisite collisions.
- **Explicit TTL added** to `canard_cat_count_v1` transient (`WEEK_IN_SECONDS`).
  Previously called with no expiry argument, causing the transient to accumulate
  indefinitely on sites without a persistent cache backend.
- **Transient flusher guarded against autosaves and revisions.** Unnecessary cache
  invalidation on draft edits is now skipped.

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

