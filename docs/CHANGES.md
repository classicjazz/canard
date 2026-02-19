# Canard Theme — Change Log

## PHP

### Security & Escaping

* **Strict types removal:** Removed invalid `<?php declare( strict_types = 1 ); ?>` from the first line of all 26 PHP files to ensure compatibility.
* **ABSPATH guards:** Added `if ( ! defined( 'ABSPATH' ) ) exit;` to all template and include files to prevent direct script access, including `content.php`, `content-none.php`, `content-link.php`, `content-single.php`, `content-featured-post.php`, and `featured-content.php`.
* **Escaping — global:** Replaced `_e()` with `esc_html_e()` and wrapped `get_the_title()` / `get_search_query()` in `esc_html()` across all templates.
* **Escaping — `author-bio.php`:** Bare `echo get_the_author()` → `echo esc_html( get_the_author() )`; `the_author_meta( 'description' )` → `echo esc_html( get_the_author_meta( 'description' ) )`; `printf( __(), get_the_author() )` → `printf( esc_html__(), esc_html( get_the_author() ) )`.
* **Escaping — `content-none.php`:** The `printf( __() )` call that outputs an `<a>` tag now wraps the translation string in `wp_kses()` with an explicit `array( 'a' => array( 'href' => array() ) )` allowlist instead of passing it through unsanitised.
* **Escaping — `content-link.php`:** `printf( __( 'External link to %s' ), the_title() )` → `printf( esc_html__(), esc_html( get_the_title() ) )`.
* **Escaping — `content-featured-post.php`:** `href="<?php the_permalink(); ?>"` replaced with `href="<?php echo esc_url( get_permalink() ); ?>"`.
* **Escaping — `inc/template-tags.php`:** The `echo` statements for `$byline` and `$posted_on` in `canard_entry_meta()` are now wrapped in `wp_kses()` with an explicit allowlist of `span`, `a`, `time`, and `img` elements. The `$categories_list` output in `canard_entry_categories()` is now wrapped in `wp_kses_post()`.
* **HTML5 semantics:** Removed redundant `role="..."` attributes (e.g., `banner`, `navigation`, `main`, `complementary`) as they are implicit in modern HTML5 elements.
* **Loose comparisons tightened:** Two instances of `'post' == get_post_type()` in `content.php` replaced with `'post' === get_post_type()`. `'0' != get_comments_number()` in `comments.php` replaced with `0 !== (int) get_comments_number()` for PHP 8 type safety.

### `functions.php`

* **Bug fix — child stylesheet double-load:** `canard_scripts()` previously used `get_stylesheet_uri()` to enqueue the parent stylesheet as `canard-style`. In a child theme, `get_stylesheet_uri()` resolves to the child's `style.css`, not the parent's — so the `canard-style` handle was silently pointing at the wrong file. WordPress simultaneously auto-enqueued the child stylesheet as `canard-child-style-css`, resulting in the child stylesheet loading twice while the parent stylesheet was never loaded at all. Fixed by replacing `get_stylesheet_uri()` with `get_template_directory_uri() . '/style.css'`, which always resolves to the parent theme directory regardless of whether a child theme is active.

  **Child theme action required:** Child themes must explicitly enqueue their own stylesheet in their own `functions.php`:

  ```php
  add_action( 'wp_enqueue_scripts', 'my_child_theme_scripts' );
  function my_child_theme_scripts() {
      wp_enqueue_style(
          'canard-child-style',
          get_stylesheet_uri(),
          array( 'canard-style' ),
          wp_get_theme()->get( 'Version' )
      );
  }
  ```

  The `array( 'canard-style' )` dependency ensures the parent stylesheet always loads first.

* **Version management:** Added `CANARD_VERSION` constant to replace hardcoded version strings in all enqueues.
* **Google Fonts:** Merged separate font requests into a single `canard_google_fonts_url()` function using the v2 API (`/css2`) with `&display=swap`.
* **Google Fonts — preconnect hints:** Added `canard_resource_hints()` hooked to the `wp_resource_hints` filter, which emits `<link rel="preconnect">` hints for `fonts.googleapis.com` and `fonts.gstatic.com` when Google Fonts are in use. Uses the correct WordPress API rather than a raw `wp_head` echo, ensuring hints are deduplicated and filterable by child themes and plugins.
* **HTML5 support:** Expanded `add_theme_support( 'html5', ... )` to include `script` and `style`.
* **Classic widgets:** Replaced the `canard_disable_block_widgets()` function and its `after_setup_theme` hook with `add_filter( 'use_widgets_block_editor', '__return_false' )` — simpler, more reliable, no named function needed.
* **Script dependencies:** Removed the `jquery` dependency from `canard-navigation`, `canard-search`, `canard-featured-content`, `canard-single`, and `canard-posts` enqueues. jQuery is no longer a front-end dependency on any page type.
* **Social navigation removed:** Removed the `social` entry from `register_nav_menus()`.
* **Genericons removed:** Removed both `genericons` `wp_enqueue_style()` calls (front-end in `canard_scripts()` and editor in `canard_editor_styles()`).
* **`editor-color-palette` deprecation resolved:** `add_theme_support( 'editor-color-palette', ... )` was deprecated in WordPress 5.9. Removed from `functions.php` and replaced with `theme.json` (see New Files below).
* **`canard_get_category_header_image()` added:** Returns the URL of the banner image for the current category archive, or `false` if none is configured. Wrapped in `if ( ! function_exists() )` so child themes can override it entirely. Exposes the `canard_category_header_image` filter so child themes can supply images without replacing the function. See `docs/category-images.md` for usage.
* **`canard_get_category_color()` added:** Returns the solid-colour fallback used in the category header when no image is provided. The default colour is read at runtime from the `red` entry in `theme.json` via `wp_get_global_settings()` (WordPress 5.9+), so the category header automatically reflects any future palette update. Falls back to `#d11415` if `wp_get_global_settings()` is unavailable. Exposes the `canard_category_color` filter for child theme overrides.
* **Cleanup:** Removed the WordPress.com updater inclusion.

### Template Files

* **`header.php`:** Added `wp_body_open()`; upgraded XFN profile link to HTTPS; added descriptive `aria-label` to all navigation elements; added `aria-label` to the custom header image anchor (the `<img>` has `alt=""` as a decorative image but the wrapping `<a>` now has meaningful link text for screen readers); removed the legacy pingback link tag; added `absint()` to `get_custom_header()->width` and `->height` output; simplified the `site-top` conditional to `has_nav_menu( 'secondary' )` only; removed the social navigation `<nav>` block entirely.
* **`footer.php`:** Updated WordPress.org link to HTTPS; applied `esc_html__()` to theme credits; separated the theme credit author link from the translated string so `esc_html__()` covers only the translatable text and `esc_url()` covers the link; removed the social navigation `<nav class="social-navigation bottom-social">` block; replaced `<span class="genericon genericon-wordpress sep">` with an inline `<svg aria-hidden="true" focusable="false">` WordPress logo mark; removed the duplicate `.bottom-navigation` block that re-rendered the `secondary` menu location already output in `header.php` — sites hiding this duplicate via CSS can remove that rule.
* **`content.php`:** Replaced `<span class="genericon genericon-pinned">` with an inline `<svg aria-hidden="true" focusable="false">` pin icon. Replaced `strpos( $post->post_content, '<!--more' )` with `str_contains( get_the_content(), '<!--more' )` — `get_the_content()` is the correct in-Loop API and `str_contains()` is the idiomatic PHP 8 form.
* **`content-link.php`:** Replaced `<span class="genericon genericon-link">` with an inline `<svg aria-hidden="true" focusable="false">` external link icon.
* **`content-single.php`:** `the_post_thumbnail( 'canard-single-thumbnail' )` now passes `array( 'loading' => 'eager', 'fetchpriority' => 'high' )`. The `canard-single-thumbnail` size (1920×768 px) is the first visible image on a single post and the Largest Contentful Paint element. WordPress 5.5+ defaults to `loading="lazy"` on all images including this one, which actively harms LCP. The explicit `eager` + `fetchpriority="high"` attributes override that default.
* **`content-featured-post.php`:** `the_post_thumbnail( 'canard-featured-content-thumbnail' )` now passes `array( 'loading' => 'lazy' )`. Featured content thumbnails are below the primary hero area and should not compete with it for bandwidth.
* **`content.php`:** `the_post_thumbnail( 'canard-post-thumbnail' )` now passes `array( 'loading' => 'lazy' )`. Archive thumbnails are below the fold and benefit from lazy loading. Explicit rather than relying on WordPress's auto-lazy behaviour.
* **`comments.php`:** Cleaned up escaping and navigation roles.

### `entry-script.php` — Inline `<script>` Removed

The file previously emitted a raw `<script>` block inline in the page when a featured image hero layout was needed. Inline scripts are blocked by Content Security Policy headers and bypass WordPress's asset pipeline. The file has been rewritten as a `body_class` filter: when the hero layout conditions are met, the class `has-entry-hero` is added to `<body>`. The DOM manipulation has been moved into `single.js`. The `add_filter()` call has been moved to `functions.php` so it registers exactly once rather than once per post on archive pages. No behaviour change for end users.

### `inc/template-tags.php`

* **API modernisation:** Replaced both calls to `wp_get_attachment_image_src()` in `canard_post_nav_background()` with `wp_get_attachment_image_url()`, which returns the URL string directly and has been available since WordPress 4.4.
* **Transient key:** Renamed the transient key in `canard_categorized_blog()` from `canard_categories` to `canard_cat_count_v1` to avoid collisions with other plugins or themes on multisite installs. The flusher `canard_category_transient_flusher()` has been updated to match.
* **`wp_kses` avatar `img` allowlist expanded:** WordPress 6.3+ emits `loading="lazy"` and `decoding="async"` on `get_avatar()` output. Both attributes added to the `img` allowlist entry to prevent `wp_kses()` from silently stripping them.

### `inc/customizer.php`

Removed the custom `canard_sanitize_checkbox()` function and its `(bool)` cast. The cast is unreliable: the string `"false"` evaluates to `true` in PHP. The setting now uses `wp_validate_boolean()` as its `sanitize_callback`. An explicit `'default' => false` has been added to the setting registration, which was previously absent.

### `inc/extras.php`

* Updated `@since` tags in `canard_excerpt_more()` and `canard_continue_reading()` from `1.0.3` / `1.0.4` to `2.0.0`.
* Removed `apply_filters( 'the_permalink', get_permalink() )` from `canard_get_link_url()`. The `the_permalink` filter hook was deprecated in WordPress 6.8. Replaced with `get_the_permalink()` directly.

---

## JavaScript

### Global Improvements

* **ES6 refactoring:** Replaced `var` with `const` and `let` throughout all scripts.
* **Strict equality:** Replaced `'undefined' === typeof x` checks with simple truthy/falsy `!x` logic.
* **`className` string manipulation:** Replaced all `.className.indexOf()`, `.className +=`, and `.className.replace()` patterns with `classList.contains()`, `classList.add()`, `classList.remove()`, and `classList.toggle()` throughout all scripts.
* **Event handlers:** Replaced all `button.onclick = function()` assignments with `button.addEventListener( 'click', function() )` for consistency and child-theme extensibility.

### jQuery Fully Removed

All five scripts that previously declared jQuery as a dependency have been rewritten in vanilla JavaScript. jQuery is no longer loaded as a front-end dependency on any page.

| File | What changed |
| --- | --- |
| **search.js** | jQuery IIFE and `.hover()` / `.focusin()` / `.focusout()` calls replaced with `addEventListener( 'mouseenter' / 'mouseleave' / 'focus' / 'blur' )`. |
| **featured-content.js** | Rewritten using `querySelectorAll`, `forEach`, `classList`, and `style.backgroundImage`. The `$(window).on('load')` wrapper replaced with `window.addEventListener('load')`. |
| **navigation.js** | Fully rewritten in vanilla JS. Event delegation via `document.addEventListener('click')` replaces jQuery's `.on('click', '.dropdown-toggle')`. `btn.className = 'dropdown-toggle'` replaced with `btn.classList.add('dropdown-toggle')`. |
| **single.js** | Rewritten in vanilla JS. `$('.author-info')`, `.prependTo()`, `.insertAfter()`, `$(window).width()`, and all Jetpack sharedaddy/table DOM operations replaced with `querySelector`, `insertBefore`, `Element.after()`, `window.innerWidth`, and `querySelectorAll().forEach()`. |
| **posts.js** | Rewritten in vanilla JS. `$('.site-main .hentry').each()`, `.hasClass()`, `.find()`, `.css()`, and `$(window).width()` replaced with `querySelectorAll`, `classList.contains`, `style` properties, and `window.innerWidth`. Fixed character encoding corruption (em dashes); renamed shadowed variables. |

### File-Specific Changes

| File | Change |
| --- | --- |
| **utils.js** | **New file.** Shared `debounce` implementation exposed as `window.canardUtils.debounce`. Uses the standard `clearTimeout` / `setTimeout` pattern. Rest parameters (`...args`) replace `[].slice.call( arguments, 0 )`. |
| **single.js** | Absorbed the entry-hero DOM manipulation previously in the inline `<script>` in `entry-script.php`. |
| **customizer.js** | Removed jQuery IIFE; all `$()` selectors replaced with `document.querySelector()` / `document.querySelectorAll()`; `.text()` replaced with `.textContent`; `.addClass()` / `.removeClass()` replaced with `classList`. |
| **header.js** | Added null guard for `siteBranding` before accessing `clientHeight`. |
| **sidebar.js** | `button.onclick` replaced with `button.addEventListener( 'click', ... )`. |

---

## CSS & Styling

### Genericons Replaced with SVG

Genericons has been fully removed. All icon glyphs previously rendered via the Genericons icon font are now rendered using either inline SVG elements (for template icons) or SVG `background-image` data URIs (for CSS pseudo-element icons). The `genericons/` directory and its font files are no longer referenced and can be deleted.

**CSS pseudo-element icons replaced with SVG `background-image` data URIs (`style.css`):**

| Selector | Former glyph | Replacement |
| --- | --- | --- |
| `blockquote:before` | `\f106` quotation mark | SVG quotation mark, fill `#dddddd` |
| `.search-form:before` | `\f400` magnifying glass | SVG magnifying glass, fill `#d11415` |
| `.menu-toggle:before` | `\f419` hamburger | SVG hamburger, fill `#222222` |
| `.toggled .menu-toggle:before` | `\f406` close | SVG ×, fill `#ffffff` |
| `.dropdown-toggle:before` | `\f431` chevron-down | SVG chevron-down, fill `#d11415` |
| `.dropdown-toggle.toggled:before` | `\f432` chevron-up | SVG chevron-up, fill `#d11415` |
| `.search-toggle:before` | `\f400` magnifying glass | SVG magnifying glass, fill `#222222` |
| `.toggled .search-toggle:before` | `\f406` close | SVG ×, fill `#ffffff` |
| `.sidebar-toggle:before` | `\f476` ellipsis | SVG three-dot, fill `#222222` |
| `.toggled.sidebar-toggle:before` | `\f406` close | SVG ×, fill `#ffffff` |
| `.posts-navigation .nav-next a:after` | `\f429` right arrow | SVG right chevron |
| `.posts-navigation .nav-previous a:before` | `\f430` left arrow | SVG left chevron |
| `.comment-navigation .nav-next a:after` | `\f429` right arrow | SVG right chevron |
| `.comment-navigation .nav-previous a:before` | `\f430` left arrow | SVG left chevron |
| `.main-navigation .menu-item-has-children > a:after` | `\f431` chevron-down | SVG chevron-down, fill `#d11415` |

**Template `<span>` icons replaced with inline `<svg>` elements:**

| File | Former element | Replacement |
| --- | --- | --- |
| `content.php` | `<span class="genericon genericon-pinned">` | Inline `<svg>` pin icon, `aria-hidden="true" focusable="false"` |
| `content-link.php` | `<span class="genericon genericon-link">` | Inline `<svg>` external-link icon, `aria-hidden="true" focusable="false"` |
| `footer.php` | `<span class="genericon genericon-wordpress sep">` | Inline `<svg>` WordPress logo mark, `aria-hidden="true" focusable="false"`, `class="sep"` retained |

**CSS selector updates (`style.css`):**

* `.sticky-post .genericon` → `.sticky-post svg` (adjusted sizing to 16×16px with `vertical-align: middle`).
* `.post-link .genericon` → `.post-link svg` (padding-based centring replaces `line-height` centring; 60px circle container retained).
* `.post-link:active .genericon`, `.post-link:focus .genericon`, `.post-link:hover .genericon` → targeting `svg` instead.
* Transition list updated: `.post-link .genericon` → `.post-link svg`.
* `.site-info .sep` updated: `color`/`font-size`/`line-height` replaced with `fill`, `height`, `width`, and `vertical-align` appropriate for an `<svg>` element. The `sep:hover { transform: rotate(360deg); }` easter egg is preserved.

**`editor-blocks.css`:** Both Genericons blockquote `::before` rules replaced with equivalent SVG `background-image` data URI rules matching the front-end quotation mark style.

**Shared Genericons `font-family` declaration block removed:** The multi-selector block that set `font-family: Genericons` across all button and navigation pseudo-elements has been removed entirely.

### Social Navigation Removed

The social navigation menu location, all associated template markup, and all associated CSS have been removed.

**PHP changes:** `social` removed from `register_nav_menus()`; social `<nav>` blocks removed from `header.php` and `footer.php`; the `site-top` conditional in `header.php` simplified from `has_nav_menu( 'secondary' ) || has_nav_menu( 'social' )` to `has_nav_menu( 'secondary' )`.

**`style.css` changes:** Entire `.social-navigation` ruleset removed (~130 lines), including base layout, link styles, `:hover`/`:focus` states, and all 26 domain-specific `content: "\fXXX"` icon rules. `.site-social` and `.site-social-inner` rules removed (dead CSS). `.bottom-social > div` entries removed from all width/margin grouped selector lists. Related media query overrides removed.

**`rtl.css` changes:** `.social-navigation li` float-direction overrides removed. Media query `.social-navigation { float: left; ... }` override removed.

**`readme.txt` changes:** `Social menu.` bullet removed from the Description section. The "How do I add the Social Links to the header?" FAQ section removed in its entirety.

### Category Header

* **`style.css` — Category header styles added:** Two new rule blocks added under a `Category header` section comment. `.category-color-fallback` renders a solid-colour block at 260px / 360px / 420px tall across the three responsive breakpoints, matching the visual footprint of a hero image. `.entry-hero .post-thumbnail .category-header` applies `object-fit: cover` and matching responsive heights to the `<img>` element used when a category image is configured.

### Bug Fixes

* **`editor-blocks.css`:** Removed stray backtick; fixed typos in `.wp-block-latest-posts`; updated `.is-wide` to `.is-style-wide`; replaced `wp-block-quote__citation` with `wp-block-quote cite`.
* **`rtl.css`:** Fixed missing units on `right: 50px`.
* **`style.css`:** Replaced placeholder hacks with modern `::placeholder { color: #777; opacity: 1; }`; implemented keyboard-accessible focus via `:focus:not(:focus-visible) { outline: none; }`.
* **`style.css` — Version header:** Updated `Version:` from `1.1.0` to `2.0.0` to match the `CANARD_VERSION` constant.
* **`style.css` — License URI:** Upgraded from `http://` to `https://`.

### Modernisation & Cleanup

* **Accessibility:** Replaced deprecated `clip: rect()` with `clip-path: inset(50%)` and `white-space: nowrap` for all `.screen-reader-text` declarations.
* **Legacy prefix removal:** Stripped all `-webkit-box`, `-ms-flexbox`, and `-webkit-transform` prefixes.
* **Normalisation:** Cleaned up the `style.css` normalise block; updated `abbr[title]` to use `underline dotted`.
* **Cleanup:** Removed all `speak: none` declarations and empty ruleset stubs in `blocks.css`.

---

## Files Removed / Added / Renamed

**Deleted:**
* `js/skip-link-focus-fix.js` — no longer required for modern browsers.
* `inc/updater.old.php` — dead code, not included anywhere.
* `genericons/` — entire directory. No file in the theme references Genericons any longer. Safe to delete.

**Added:**
* `js/utils.js` — shared `debounce` utility.
* `category.php` — native category archive template. Displays a full-width hero banner at the top of category archive pages, followed by the standard post loop with pagination. Integrated with the same `entry-hero` layout structure used by single posts.
* `theme.json` — replaces the deprecated `add_theme_support( 'editor-color-palette' )` call. Declares the same six-colour palette (Black, Dark Gray, Medium Gray, Light Gray, White, Red) under `settings.color.palette`. Also declares `contentSize` and `wideSize` to match the theme's layout widths. Safe to use with classic (non-block) themes from WordPress 5.9 onwards.
* `docs/CHANGES.md` — this file.
* `docs/category-images.md` — documents how child themes can supply per-category banner images and colours using the `canard_category_header_image` and `canard_category_color` filters, or by overriding the functions entirely.
