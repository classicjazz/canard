# Consolidated Theme Changes

## PHP

### Security & Escaping

* **Strict Types Removal:** Removed invalid `<?php declare( strict_types = 1 ); ?>` from the first line of all **26** PHP files to ensure compatibility.
* **ABSPATH Guards:** Added `if ( ! defined( 'ABSPATH' ) ) exit;` to all template and include files to prevent direct script access. A second pass added guards and docblocks to `content.php`, `content-none.php`, and `content-link.php`, which were missed in the first pass. A third pass added the guard to `featured-content.php`.
* **Escaping — Global:** Replaced `_e()` with `esc_html_e()` and wrapped `get_the_title()` / `get_search_query()` in `esc_html()` across all templates.
* **Escaping — `author-bio.php`:** Bare `echo get_the_author()` → `echo esc_html( get_the_author() )`; `the_author_meta( 'description' )` (which echoes directly) → `echo esc_html( get_the_author_meta( 'description' ) )`; `printf( __(), get_the_author() )` → `printf( esc_html__(), esc_html( get_the_author() ) )`.
* **Escaping — `content-none.php`:** The `printf( __() )` call that outputs an `<a>` tag now wraps the translation string in `wp_kses()` with an explicit `array( 'a' => array( 'href' => array() ) )` allowlist instead of passing it through unsanitised.
* **Escaping — `content-link.php`:** `printf( __( 'External link to %s' ), the_title() )` → `printf( esc_html__(), esc_html( get_the_title() ) )`.
* **Escaping — `inc/template-tags.php`:** The final `echo` statements for `$byline` and `$posted_on` in `canard_entry_meta()` are now wrapped in `wp_kses()` with an explicit allowlist of `span`, `a`, `time`, and `img` elements. The `$categories_list` output in `canard_entry_categories()` is now wrapped in `wp_kses_post()`.
* **HTML5 Semantics:** Removed redundant `role="..."` attributes (e.g., `banner`, `navigation`, `main`, `complementary`) as they are implicit in modern HTML5 elements.

### `functions.php`

* **Version Management:** Added `CANARD_VERSION` constant to replace hardcoded "2015" version strings in all enqueues.
* **Google Fonts:** Merged separate font requests into a single `canard_google_fonts_url()` function using the **v2 API (/css2)** with `&display=swap`.
* **Google Fonts — Preconnect:** Added a `wp_head` action (priority 1) that emits `<link rel="preconnect">` hints for `fonts.googleapis.com` and `fonts.gstatic.com` when Google Fonts are in use, improving LCP.
* **HTML5 Support:** Expanded `add_theme_support( 'html5', ... )` to include `script` and `style`.
* **Classic Widgets:** Replaced the `canard_disable_block_widgets()` function + `after_setup_theme` hook with `add_filter( 'use_widgets_block_editor', '__return_false' )` — simpler, more reliable, no named function needed.
* **Script Dependencies:** Removed the `jquery` dependency from `canard-navigation`, `canard-search`, `canard-featured-content`, `canard-single`, and `canard-posts` enqueues. jQuery is no longer a front-end dependency of this theme on any page type.
* **Social Navigation Removed:** Removed the `social` entry from `register_nav_menus()`. The social nav location no longer exists.
* **Genericons Removed:** Removed both `genericons` `wp_enqueue_style()` calls (front-end in `canard_scripts()` and editor in `canard_editor_styles()`). Genericons is no longer loaded on any page.
* **Cleanup:** Removed the WordPress.com updater inclusion.

### Template Files

* **`header.php`:** Added `wp_body_open()`; upgraded XFN profile link to HTTPS; added descriptive `aria-label` to all navigation elements; removed the legacy pingback link tag; added `absint()` to `get_custom_header()->width` and `->height` output; simplified the `site-top` conditional from `has_nav_menu( 'secondary' ) || has_nav_menu( 'social' )` to `has_nav_menu( 'secondary' )` only; removed the social navigation `<nav>` block entirely.
* **`footer.php`:** Updated WordPress.org link to HTTPS and moved it outside of translation strings; applied `esc_html__()` to theme credits; removed the social navigation `<nav class="social-navigation bottom-social">` block entirely; replaced `<span class="genericon genericon-wordpress sep">` with an inline `<svg aria-hidden="true" focusable="false">` WordPress logo mark.
* **`content.php`:** Replaced `<span class="genericon genericon-pinned">` with an inline `<svg aria-hidden="true" focusable="false">` pin icon. Existing `<span class="screen-reader-text">` sibling retained.
* **`content-link.php`:** Replaced `<span class="genericon genericon-link">` with an inline `<svg aria-hidden="true" focusable="false">` external link icon. Existing `<span class="screen-reader-text">` sibling retained.
* **`comments.php`:** Cleaned up escaping and navigation roles.
* **`featured-content.php`:** Added missing `if ( ! defined( 'ABSPATH' ) ) exit;` guard.

### `content.php` — Direct `$post` Object Access

Replaced `strpos( $post->post_content, '<!--more' )` with `str_contains( get_the_content(), '<!--more' )`. Accessing `$post->post_content` directly bypasses WordPress filters and relies on the global `$post` being populated. `get_the_content()` is the correct in-Loop API. `str_contains()` replaces `strpos()` for a boolean existence check, as it is the idiomatic PHP 8 form.

### `entry-script.php` — Inline `<script>` Removed

The file previously emitted a raw `<script>` block inline in the page when a featured image hero layout was needed. Inline scripts are blocked by Content Security Policy headers and bypass WordPress's asset pipeline. The file has been rewritten as a `body_class` filter: when the hero layout conditions are met, the class `has-entry-hero` is added to the `<body>` element. The actual DOM manipulation has been moved into `single.js`, which reads this class on load. No behaviour change for end users.

### `inc/template-tags.php`

* **API Modernisation:** Replaced both calls to `wp_get_attachment_image_src()` in `canard_post_nav_background()` (which returns an array requiring `[0]` to extract the URL) with `wp_get_attachment_image_url()`, which returns the URL string directly and has been available since WordPress 4.4.
* **Transient Key:** Renamed the transient key in `canard_categorized_blog()` from `canard_categories` to `canard_cat_count_v1`. The original key is generic enough to collide with other plugins or themes on multisite installs. The flusher `canard_category_transient_flusher()` has been updated to match.

### `inc/customizer.php` — Checkbox Sanitisation

Removed the custom `canard_sanitize_checkbox()` function and its `(bool)` cast. The cast is unreliable: the string `"false"` evaluates to `true` in PHP. The setting now uses WordPress core's `wp_validate_boolean()` as its `sanitize_callback`, which handles string representations correctly. An explicit `'default' => false` has also been added to the setting registration, which was previously absent.

### `inc/extras.php` — Docblock Versions Updated

Updated `@since` tags in `canard_excerpt_more()` and `canard_continue_reading()` from `1.0.3` / `1.0.4` to `2.0.0` to reflect the current version.

---

## JavaScript

### Global Improvements

* **jQuery Events:** Replaced all legacy `.load()` and `.resize()` calls with `.on( 'load', ... )` and `.on( 'resize', ... )`.
* **ES6 Refactoring:** Replaced `var` with `const` and `let` throughout all scripts.
* **Strict Equality:** Replaced `'undefined' === typeof x` checks with simple truthy/falsy `!x` logic.
* **`className` String Manipulation:** Replaced all `.className.indexOf()`, `.className +=`, and `.className.replace()` patterns with `classList.contains()`, `classList.add()`, `classList.remove()`, and `classList.toggle()` throughout `navigation.js` and `sidebar.js`.

### jQuery Dependencies Fully Removed

All five scripts that previously declared jQuery as a dependency have been rewritten in vanilla JavaScript. jQuery is no longer loaded as a front-end dependency on any page.

| File | What changed |
| --- | --- |
| **search.js** | jQuery IIFE and `.hover()` / `.focusin()` / `.focusout()` calls replaced with `addEventListener( 'mouseenter' / 'mouseleave' / 'focus' / 'blur' )`. |
| **featured-content.js** | Rewritten using `querySelectorAll`, `forEach`, `classList`, and `style.backgroundImage`. The `$(window).on('load')` wrapper replaced with `window.addEventListener('load')`. |
| **navigation.js** | Fully rewritten in vanilla JS. The jQuery IIFE (dropdown toggle injection, touchstart handling, focus/blur management) and the existing vanilla IIFE (mobile menu button) are now a single consistent vanilla codebase. Event delegation via `document.addEventListener('click')` replaces jQuery's `.on('click', '.dropdown-toggle')`. |
| **single.js** | Rewritten in vanilla JS. `$('.author-info')`, `.prependTo()`, `.insertAfter()`, `$(window).width()`, and all Jetpack sharedaddy/table DOM operations replaced with `querySelector`, `insertBefore`, `Element.after()`, `window.innerWidth`, and `querySelectorAll().forEach()`. The `jquery` dependency has been removed from its enqueue. |
| **posts.js** | Rewritten in vanilla JS. `$('.site-main .hentry').each()`, `.hasClass()`, `.find()`, `.css()`, and `$(window).width()` replaced with `querySelectorAll`, `classList.contains`, `style` properties, and `window.innerWidth`. The `jquery` dependency has been removed from its enqueue. |

### File-Specific Changes

| File | Change Description |
| --- | --- |
| **utils.js** | **NEW FILE:** Contains the shared `debounce` implementation exposed as `window.canardUtils.debounce`. Simplified from a manual timestamp-based implementation to the standard `clearTimeout` / `setTimeout` pattern. Rest parameters (`...args`) replace the legacy `[].slice.call( arguments, 0 )` pattern. |
| **single.js** | Absorbed the entry-hero DOM manipulation previously in the inline `<script>` in `entry-script.php`. Fully rewritten in vanilla JS; jQuery dependency removed. |
| **posts.js** | Fixed character encoding corruption (em dashes); renamed shadowed variables. Fully rewritten in vanilla JS; jQuery dependency removed. |
| **customizer.js** | Removed jQuery IIFE; all `$()` selectors replaced with `document.querySelector()` / `document.querySelectorAll()`; `.text()` replaced with `.textContent`; `.addClass()` / `.removeClass()` replaced with `classList`. Runs only in the Customizer admin panel, not on the front end. |
| **navigation.js** | Mobile menu button toggle changed from `button.onclick = function()` to `button.addEventListener( 'click', function() )` for consistency and child-theme extensibility. |
| **sidebar.js** | `button.onclick` replaced with `button.addEventListener( 'click', ... )`. |
| **header.js** | Added null guard for `siteBranding` before accessing `clientHeight`. |

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
* `.site-info .sep` updated: `color`/`font-size`/`line-height` properties replaced with `fill`, `height`, `width`, and `vertical-align` appropriate for an `<svg>` element. The `sep:hover { transform: rotate(360deg); }` easter egg is preserved.

**`editor-blocks.css`:** Both Genericons blockquote `::before` rules (`.editor-block-list__block .wp-block-quote:before` and `.wp-block-freeform.block-library-rich-text__tinymce blockquote:before`) replaced with equivalent SVG `background-image` data URI rules matching the front-end quotation mark style.

**Shared Genericons `font-family` declaration block removed:** The multi-selector block (lines ~169–192 in the original) that set `font-family: Genericons` across all button and navigation pseudo-elements has been removed entirely. It had no remaining selectors after Genericons replacement and social navigation removal.

### Social Navigation Removed

The social navigation menu location, all associated template markup, and all associated CSS have been removed.

**PHP changes:** `social` removed from `register_nav_menus()`; social `<nav>` blocks removed from `header.php` (header position) and `footer.php` (`.bottom-social` position); the `site-top` conditional in `header.php` simplified from `has_nav_menu( 'secondary' ) || has_nav_menu( 'social' )` to `has_nav_menu( 'secondary' )`.

**`style.css` changes:**
* Entire `.social-navigation` ruleset removed (~130 lines), including base layout, link styles, `:hover`/`:focus` states, and all 26 domain-specific `content: "\fXXX"` icon rules.
* `.social-navigation > div:before` and `:after` removed from the clearfix grouped selector list.
* `.site-social` and `.site-social-inner` rules removed (dead CSS — no template ever rendered these classes).
* `.bottom-social > div` entries removed from all width/margin grouped selector lists.
* `.bottom-social { display: none; }` and `.social-navigation { float: right; ... }` media query overrides removed.

**`rtl.css` changes:**
* `.social-navigation li` float-direction overrides removed.
* Media query `.social-navigation { float: left; ... }` override removed.

**`readme.txt` changes:**
* `Social menu.` bullet removed from the Description section.
* "How do I add the Social Links to the header?" FAQ section removed in its entirety (including the list of 26 supported networks).

### Bug Fixes

* **`editor-blocks.css`:** Removed stray backtick; fixed typos in `.wp-block-latest-posts`; updated `.is-wide` to `.is-style-wide`; replaced `wp-block-quote__citation` with `wp-block-quote cite` (pullquotes retain the `__citation` class).
* **`rtl.css`:** Fixed missing units on `right: 50px`.
* **`style.css`:** Replaced placeholder hacks with modern `::placeholder { color: #777; opacity: 1; }`; implemented keyboard-accessible focus via `:focus:not(:focus-visible) { outline: none; }`.
* **`style.css` — Version header:** Updated `Version:` from `1.1.0` to `2.0.0` to match the `CANARD_VERSION` constant. WordPress reads this field to display the installed version in the admin dashboard.

### Modernisation & Cleanup

* **Accessibility:** Replaced deprecated `clip: rect()` with `clip-path: inset(50%)` and `white-space: nowrap` for all `.screen-reader-text` declarations.
* **Legacy Prefix Removal:** Stripped all `-webkit-box`, `-ms-flexbox`, and `-webkit-transform` prefixes.
* **Normalisation:** Cleaned up the `style.css` normalise block; updated `abbr[title]` to use `underline dotted`.
* **Cleanup:** Removed all `speak: none` declarations and empty ruleset stubs in `blocks.css`.

---

## Files Removed / Added / Renamed

* **Deleted:** `js/skip-link-focus-fix.js` — no longer required for modern browsers.
* **Deleted:** `inc/updater.old.php` — dead code, not included anywhere.
* **Deleted:** `genericons/` — entire directory. No file in the theme references Genericons any longer. Safe to delete.
* **Added:** `js/utils.js` — shared `debounce` utility.
* **Added:** `docs/CHANGES.md` — this file.

---

## Total Change Metrics

| Category | Files Modified | Impact |
| --- | --- | --- |
| **PHP** | 12 | Security hardening, HTML5 compliance, escaping, API modernisation, CSP compliance, checkbox sanitisation, social nav removal, Genericons removal, preconnect hints, widget filter simplification. |
| **JS** | 7 | Full jQuery removal (5 scripts converted), `classList` migration, `debounce` simplification, `addEventListener` consistency. |
| **CSS** | 4 | Genericons → SVG migration, social nav removal, prefix removal, accessibility fixes, Gutenberg block alignment, version sync. |
| **Docs** | 2 | CHANGES.md updated; GENERICONS.md retired (all items completed). |

---

## Round 3 Changes

### PHP

* **`content-single.php` — ABSPATH guard added:** The file previously opened with only a bare `@package` docblock and no `if ( ! defined( 'ABSPATH' ) ) exit;` guard. Guard and a proper file-level docblock added for consistency with all other template files.

* **`content-featured-post.php` — ABSPATH guard added:** Same issue as `content-single.php` — guard and proper docblock added.

* **`content-featured-post.php` — Unescaped `the_permalink()`:** `href="<?php the_permalink(); ?>"` replaced with `href="<?php echo esc_url( get_permalink() ); ?>"`. The rest of the file already used `esc_url( get_permalink() )` correctly; this one instance was missed in the previous round.

* **`inc/extras.php` — Deprecated `the_permalink` filter removed:** `canard_get_link_url()` previously called `apply_filters( 'the_permalink', get_permalink() )`. The `the_permalink` filter hook was deprecated in WordPress 6.8. The call is now replaced with `get_the_permalink()` directly, which is the correct current API and renders the filter unnecessary.

* **`content.php` — Loose `==` comparisons tightened to `===`:** Two instances of `'post' == get_post_type()` replaced with `'post' === get_post_type()`. The equivalent comparisons in `inc/template-tags.php` were already updated in the previous round; these two were missed.

* **`comments.php` — Type-safe comment count check:** `'0' != get_comments_number()` replaced with `0 !== (int) get_comments_number()` for PHP 8 type safety.

* **`entry-script.php` — Duplicate `add_filter()` registration eliminated:** The file previously called `add_filter( 'body_class', 'canard_entry_hero_body_class' )` at file scope. Because the file is loaded via `get_template_part()` inside the Loop, this caused the callback to be registered once per post on archive/index pages. The `add_filter()` call has been moved to `functions.php`, which runs once at theme setup. `entry-script.php` now only defines the function (wrapped in `function_exists()`) and is `require_once`'d from `functions.php` so it is loaded exactly once for the function definition. `get_template_part()` calls in templates continue to work because PHP silently skips a `function_exists()`-guarded re-declaration.

* **`functions.php` — `editor-color-palette` deprecation resolved:** `add_theme_support( 'editor-color-palette', ... )` was deprecated in WordPress 5.9. The setting has been removed from `functions.php` and replaced with a `theme.json` file at the theme root (see below). A note in the `custom-logo` block explains the change.

* **`inc/template-tags.php` — `wp_kses` avatar `img` allowlist expanded:** WordPress 6.3+ emits `loading="lazy"` and `decoding="async"` attributes on `get_avatar()` output. Without `loading` and `decoding` in the allowlist, `wp_kses()` silently stripped them, degrading performance. Both attributes added to the `img` entry.

### JavaScript

* **`js/search.js` — Incomplete `classList` migration completed:** The second IIFE (header search toggle) was not migrated in the previous round despite being listed as done in CHANGES.md. It used `button.onclick`, `container.className.indexOf()`, `document.body.className` string manipulation, and `container.className +=`. All replaced with `button.addEventListener('click', ...)`, `container.classList.contains()`, `document.body.classList.toggle()`, and `container.classList.toggle()`.

* **`js/navigation.js` — `btn.className` assignment replaced:** `btn.className = 'dropdown-toggle'` on a newly created element replaced with `btn.classList.add('dropdown-toggle')` for consistency with the rest of the migrated codebase.

### CSS / Accessibility

* **`header.php` — Header image link made accessible:** The custom header image `<img>` had `alt=""` (correct for a decorative image), but the surrounding `<a>` linked to the homepage with no accessible text. An `aria-label` combining the site name and "Home" has been added to the anchor, giving screen reader users meaningful link text.

* **`style.css` — License URI upgraded to HTTPS:** The stylesheet header contained `http://www.gnu.org/licenses/gpl-2.0.html`. Updated to `https://`.

* **`footer.php` — Theme credit link separated from translated string:** The `printf()` for the theme credit previously passed a raw `<a>` HTML string as a `%s` argument inside a translated string, bypassing `esc_html__()`. Restructured so `esc_html__()` covers only the translatable text and the author link is output separately with `esc_url()`.

### New Files

* **`theme.json`:** Replaces the deprecated `add_theme_support( 'editor-color-palette' )` call. Declares the same six-colour palette (Black, Dark Gray, Medium Gray, Light Gray, White, Red) under `settings.color.palette`. Also declares `contentSize` and `wideSize` to match the theme's layout widths. The file uses schema version 2 and is safe to use with classic (non-block) themes from WordPress 5.9 onwards; it does not require block templates or a `templates/` directory.
