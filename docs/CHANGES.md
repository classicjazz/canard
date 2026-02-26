# Canard Theme — Change Log

**Version 2.5.0**

---

## Table of Contents

1. [Executive Summary: Theme Modernization Overview](#1-executive-summary-theme-modernization-overview)
2. [PHP & Backend Logic](#2-php--backend-logic)
   - [2.1 Security & Escaping](#21-security--escaping)
   - [2.2 Performance & Caching](#22-performance--caching)
   - [2.3 API Modernization](#23-api-modernization)
3. [JavaScript & Asset Management](#3-javascript--asset-management)
   - [3.1 jQuery Removal](#31-jquery-removal)
   - [3.2 ES6 & Global Improvements](#32-es6--global-improvements)
   - [3.3 File-Specific Changes](#33-file-specific-changes)
   - [3.4 Accessibility & Correctness](#34-accessibility--correctness)
4. [CSS & Design Systems](#4-css--design-systems)
   - [4.1 Genericons Replaced with SVG](#41-genericons-replaced-with-svg)
   - [4.2 Social Navigation Removed](#42-social-navigation-removed)
   - [4.3 Category Header System](#43-category-header-system)
   - [4.4 Bug Fixes & Modernization](#44-bug-fixes--modernization)
5. [Template & HTML Enhancements](#5-template--html-enhancements)
   - [5.1 Schema, Structured Data & ARIA](#51-schema-structured-data--aria)
   - [5.2 HTML5 Semantics](#52-html5-semantics)
6. [Removals & Cleanup](#6-removals--cleanup)
   - [6.1 Deleted Files](#61-deleted-files)
   - [6.2 Deprecated Functions Removed](#62-deprecated-functions-removed)
   - [6.3 Removed Third-Party Dependencies](#63-removed-third-party-dependencies)
   - [6.4 Added Files](#64-added-files)

---

## 1. Executive Summary: Theme Modernization Overview

Canard 2.5.0 is a significant architectural release. Three interconnected modernization initiatives drove the majority of the changes: a full transition to vanilla JavaScript, the retirement of the Genericons icon font in favor of inline and CSS SVG, and a targeted effort to improve Core Web Vitals scores and eliminate outstanding security surface area.

### JavaScript Modernization

All five front-end scripts — **`navigation.js`**, **`search.js`**, **`featured-content.js`**, **`single.js`**, and **`posts.js`** — have been fully rewritten in vanilla ES6 JavaScript. jQuery has been removed as a front-end dependency on every page type. The `jquery` declaration has been stripped from all five script enqueues in **`functions.php`**. A new shared utility module, **`js/utils.js`**, provides a `debounce` implementation used across scripts.

### Genericons Retirement and SVG Migration

Genericons, the icon font used throughout the theme for navigation controls, UI chrome, and social icons, has been removed entirely. The **`genericons/`** directory can be deleted. Template icons previously rendered via `<span class="genericon ...">` elements have been replaced with inline `<svg aria-hidden="true" focusable="false">` elements. CSS pseudo-element icons previously using font-character glyph codes (`\fXXX`) have been replaced with SVG `background-image` data URIs directly in **`style.css`** and **`editor-blocks.css`**.

### Core Web Vitals and Security Focus

Loading behavior has been corrected throughout the theme to align with Core Web Vitals best practices. LCP images — including the single-post hero thumbnail in **`content-single.php`**, the category hero in **`category.php`**, and the custom header image in **`header.php`** — now receive `loading="eager" fetchpriority="high"` to prevent browsers from deferring the page's most important image. Below-fold images receive explicit `loading="lazy"` attributes. `sizes` attributes have been corrected on archive and featured-content thumbnails to prevent browsers from downloading over-sized image variants.

Security improvements address five distinct vulnerability classes: stored XSS via unescaped output, IDOR via unvalidated attachment IDs, reverse tabnapping via missing `rel="noopener noreferrer"` on `target="_blank"` links, CSS injection via the Customizer postMessage channel, and cache poisoning in a multisite persistent-cache scenario. ABSPATH guards have been added to all 26 template and include files.

---

## 2. PHP & Backend Logic

### 2.1 Security & Escaping

#### ABSPATH Guards

Added `if ( ! defined( 'ABSPATH' ) ) exit;` to all template and include files to prevent direct HTTP access. Files covered: **`header.php`**, **`author-bio.php`**, **`content.php`**, **`content-none.php`**, **`content-link.php`**, **`content-single.php`**, **`content-page.php`**, **`content-featured-post.php`**, **`featured-content.php`**, and **`category.php`**.

#### Strict Types Removal

Removed the invalid `<?php declare( strict_types = 1 ); ?>` declaration from the first line of all 26 PHP files. This declaration has no effect on non-strict function calls made by WordPress core and was causing parse confusion in some environments.

#### Output Escaping — Global

- `_e()` replaced with `esc_html_e()` throughout all templates.
- `get_the_title()` and `get_search_query()` calls wrapped in `esc_html()` at every point of echo.

#### Output Escaping — File-Specific Changes

- **`header.php`**: `bloginfo('charset')` → `esc_attr( get_bloginfo( 'charset' ) )`; `bloginfo('name')` and `bloginfo('description')` → `esc_html( get_bloginfo(...) )`. The `header_image()` template tag (which calls `echo` internally) replaced with `echo esc_url( get_header_image() )` to prevent reflected XSS from a stored `javascript:` or `data:` URI in the custom header URL field.

- **`header.php`**: `loading` and `fetchpriority` attribute values, though currently static strings, now pass through `esc_attr()` per WordPress VIP coding standards so the pattern remains safe if conditionals are later extended to read from theme options or filters.

- **`author-bio.php`**: Bare `echo get_the_author()` → `echo esc_html( get_the_author() )`; `the_author_meta('description')` → `echo esc_html( get_the_author_meta('description') )`; `get_avatar()` output wrapped in `wp_kses()` using the `img` element allowlist, because the `get_avatar` filter allows downstream plugins to inject additional attributes.

- **`content-none.php`**: The `printf( __() )` call producing an `<a>` tag now wraps the translation string in `wp_kses()` with an explicit `array( 'a' => array( 'href' => array() ) )` allowlist.

- **`content-link.php`**: `printf( __( 'External link to %s' ), the_title() )` → `printf( esc_html__(), esc_html( get_the_title() ) )`.

- **`content-featured-post.php`**: `href="<?php the_permalink(); ?>"` → `href="<?php echo esc_url( get_permalink() ); ?>"`.

- **`inc/template-tags.php`**: `echo` statements for `$byline` and `$posted_on` in **`canard_entry_meta()`** wrapped in `wp_kses()` with an explicit allowlist of `span`, `a`, `time`, and `img` elements. The `$categories_list` output in **`canard_entry_categories()`** wrapped in `wp_kses_post()`.

- **`inc/template-tags.php`**: The `wp_kses()` allowlist in **`canard_entry_meta()`** expanded to include `itemprop` and `property` on `<a>` and `<span>` elements, and `fetchpriority` on `<img>` elements. These attributes are emitted by WordPress core (`get_avatar()`) and were being silently stripped.

- **`archive.php`** / **`category.php`**: `the_archive_description()` replaced with `wp_kses_post( get_the_archive_description() )`. Users with the `manage_categories` capability can store arbitrary HTML including `<script>` tags in the term description field. A `get_the_archive_description` → `wp_kses_post` filter is also registered globally in **`functions.php`** as a safety net.

- **`search.php`** / **`archive.php`** / **`category.php`**: Pagination labels passed to `the_posts_pagination()` via the `prev_text` and `next_text` arguments changed from `__()` to `esc_html__()` to prevent a compromised translation file from injecting markup.

#### Security — Logic-Level Fixes

- **`inc/extras.php` — `canard_get_link_url()`**: `get_url_in_content()` returns the raw `href` value from post content without protocol validation. A link-format post containing a `javascript:` or `data:` URI would have been passed to `esc_url()`; while `esc_url()` strips `javascript:` URIs, `data:` URIs survive in some WordPress versions, and the `target="_blank"` pairing creates a phishing vector. The extracted URL is now validated with **`wp_http_validate_url()`** (HTTP/HTTPS only); non-conforming values fall back to **`get_the_permalink()`**.

- **`content-link.php`**: Added `rel="noopener noreferrer"` to the `target="_blank"` external-link anchor. Without `rel="noopener"` the opened page holds a `window.opener` reference and can redirect the originating tab (reverse tabnapping). Per OWASP and WordPress VIP standards.

- **`inc/template-tags.php` — `canard_post_nav_background()`**: Password-protected adjacent-post thumbnails were being exposed as visible `background-image` CSS rules before the visitor had entered the post password — an IDOR that leaked the image without authentication. Added **`post_password_required()`** checks for both the `$previous` and `$next` adjacent posts before reading or emitting their thumbnail URLs.

- **`category.php` — attachment visibility check**: The `_category_image_id` term meta value is set by child themes or plugins. **`wp_get_attachment_metadata()`** was called with that ID without verifying accessibility, allowing an editor-role user to retrieve dimensions of a private post's image as a side-channel (IDOR). The attachment ID is now validated with **`get_post_status()`** before metadata is read; only attachments with status `inherit` or `publish` are processed.

- **`customizer.js` — hex colour validation**: The `header_textcolor` Customizer binding previously assigned the `to` value directly to `el.style.color` after only checking for `'blank'`. An attacker manipulating the postMessage channel could inject arbitrary CSS values. The value is now validated against a strict hex regex (`/^#[0-9a-fA-F]{3,8}$/`) before assignment.

- **`inc/template-tags.php` — `canard_entry_meta()`**: Avatar HTML retrieved from the object cache is now re-validated through `wp_kses()` before use, in addition to the existing validation on the final composed string. On sites with a shared Redis/Memcached backend, a poisoned cache entry could otherwise supply arbitrary HTML.

- **`inc/template-tags.php` — multisite cache key isolation**: Object cache keys `canard_nav_bg_{post_id}` and `canard_avatar_{hash}_{size}` used a flat namespace. On a multisite network with a non-blog-specific persistent cache backend, post ID 42 on site 1 and post ID 42 on site 2 shared the same cache entry. Both keys are now prefixed with **`get_current_blog_id()`**.

- **`comments.php` — comment form hardening** via the **`comment_form_default_fields`** filter: (1) The URL/website field is removed — it is an unauthenticated free-text field and a stored-XSS surface if any downstream template echoes commenter URLs without `esc_url()`. Canard does not display commenter URLs. (2) The email input type changed from `type="text"` to `type="email"`. (3) `autocomplete="email"` and `autocomplete="name"` hints added.

- **`inc/customizer.php` — checkbox sanitization**: Removed the custom **`canard_sanitize_checkbox()`** function and its `(bool)` cast — the cast is unreliable because the string `"false"` evaluates to `true` in PHP. The setting now uses **`wp_validate_boolean()`** as its `sanitize_callback`. An explicit `'default' => false` has been added to the setting registration, which was previously absent.

#### Loose Comparison Tightening

- Two instances of `'post' == get_post_type()` in **`content.php`** replaced with `'post' === get_post_type()`.
- `'0' != get_comments_number()` in **`comments.php`** replaced with `0 !== (int) get_comments_number()` for PHP 8 type safety.

---

### 2.2 Performance & Caching

#### Conditional Asset Loading

- **`functions.php` — `canard-blocks.css`**: Enqueued only on singular posts/pages and the front page (`is_singular() || is_front_page()`). Archives, search pages, and other listing views do not render block HTML. Saves one stylesheet round-trip on every non-singular page.

- **`functions.php` — `canard-comments.css`**: Split into a dedicated **`comments.css`** and enqueued only when `is_singular() && ( comments_open() || get_comments_number() )`. Registered with `canard-style` as a dependency to guarantee load order. Saves ~4–8 KB (uncompressed) on every non-singular page.

- **`functions.php` — `canard-featured-content.js`**: Now enqueued only on `is_front_page()`. Previously loaded unconditionally on every page request including single posts and archives where it is entirely irrelevant.

#### Script Deferral via WP 6.3+ Strategy API

**`functions.php`**: All front-end scripts except **`canard-single`** now enqueue with `array( 'in_footer' => true, 'strategy' => 'defer' )`. This uses WordPress's built-in dependency-aware deferral (available since WP 6.3, required by the theme's WP 6.9+ target) rather than a `script_loader_tag` string-manipulation filter, which correctly promotes dependents and avoids tag-rewriting edge cases. **`canard-single`** is explicitly excluded because it performs synchronous entry-hero DOM rearrangement to prevent a layout flash (FOUC).

#### Object Caching

- **`inc/template-tags.php` — `canard_post_nav_background()`**: Calls to **`wp_get_attachment_image_url()`** and **`get_post_thumbnail_id()`** (each a `get_post_meta()` database hit) on every singular page load are now replaced by a WP object cache lookup under the key `canard_nav_bg_{blog_id}_{post_id}` with a one-hour TTL. On sites with a persistent cache backend (Redis / Memcached) this eliminates the meta lookups on repeat requests.

- **`inc/template-tags.php` — `canard_entry_meta()`**: Avatar HTML is now cached with key `canard_avatar_{blog_id}_{md5(email)}_{size}` and a one-hour TTL. On archive pages with multiple posts by the same author this replaces N Gravatar HTTP-lookup round trips with one. The `WP_User` object is also read once via **`get_userdata()`** and reused for both the avatar email and the author posts URL.

#### Database & Query Efficiency

**`inc/template-tags.php` — `canard_post_nav_background()` hook deferred to `template_redirect`**: The `add_action( 'wp_enqueue_scripts', ... )` call was registered unconditionally at **`functions.php`** load time, causing a no-op invocation on every archive, front page, and search request. Registration is now wrapped in a `template_redirect` callback that only adds the hook when `is_single() || is_attachment()`. `template_redirect` fires before `wp_enqueue_scripts` so the hook is still registered in time.

#### PHP Micro-optimizations

- **`content.php` — `get_post_type()` called once**: The function was previously called twice per loop iteration (thumbnail conditional and entry-meta conditional). The result is now stored in `$post_type` and reused.

- **`inc/template-tags.php` — `canard_category_transient_flusher()`**: Guarded against post revisions via **`wp_is_post_revision()`** check, preventing unnecessary cache invalidation on draft edits. The function signature now correctly accepts the `$post_id` argument passed by WordPress's `save_post` hook instead of relying on **`get_the_ID()`** (a Loop function that returns unreliable values in the admin context).

- **`inc/template-tags.php` — `canard_cat_count_v1` transient TTL**: Added `WEEK_IN_SECONDS` as the third argument to `set_transient()`. Without an explicit TTL the transient accumulated indefinitely on sites without a persistent cache backend. The `edit_category` and `save_post` hooks continue to invalidate the transient immediately on real category changes.

- **`inc/template-tags.php`**: `wp_add_inline_style()` in **`canard_post_nav_background()`** is now guarded by `if ( $css )` to avoid appending a no-op style block when neither adjacent post has a featured image.

- **`functions.php` — `canard_google_fonts_url()` memoized**: The function was called three times per page load (in **`canard_resource_hints()`**, **`canard_scripts()`**, and the editor styles function), re-evaluating all four `_x()` translation checks on each call. A `static $url = null;` guard now caches the result after the first call.

#### Image Handling (Core Web Vitals)

- **`content-single.php`**: **`the_post_thumbnail( 'canard-single-thumbnail' )`** now passes `array( 'loading' => 'eager', 'fetchpriority' => 'high' )`. The `canard-single-thumbnail` size (1920×768 px) is the LCP element on single posts. WordPress 5.5+ defaults to `loading="lazy"` on all images, which actively harms LCP when applied to the page's primary image.

- **`category.php` — hero image**: Changed from `loading="lazy"` to `loading="eager" fetchpriority="high"`; added `sizes="100vw"`. The category hero is the topmost element on category archive pages and the LCP candidate. Lazy-loading an LCP image is an anti-pattern that actively harms Core Web Vitals scores.

- **`category.php` — `width`/`height` attributes**: Explicit `width` and `height` attributes added to the category hero `<img>`. Dimensions are read from attachment metadata via **`wp_get_attachment_metadata()`** with fallbacks of 1920×420 for child themes supplying images by URL. Eliminates the Cumulative Layout Shift (CLS) caused by the image pushing content down as it loads.

- **`header.php` — custom header image**: Receives `loading="eager" fetchpriority="high"` on the front page (LCP candidate) and `loading="lazy" fetchpriority="auto"` on all other pages (decorative, below post hero). Previously the attribute was absent, causing bandwidth competition on inner pages.

- **`content.php` — archive thumbnails**: `sizes` corrected from browser default `100vw` to `(max-width: 767px) 100vw, (max-width: 1039px) 50vw, 620px` for `canard-post-thumbnail` (870×773 px). Prevents the browser from downloading a full-width image on desktop where the content column is ~620 px wide. No new image sizes registered.

- **`content-featured-post.php` — featured thumbnails**: `sizes` corrected to `(max-width: 1300px) 100vw, 1300px` for `canard-featured-content-thumbnail` (915×500 px) used in the front-page carousel. Added explicit `loading="lazy"` since featured content thumbnails are below the primary hero area and should not compete for bandwidth. No new image sizes registered.

#### DNS Prefetch

**`functions.php` — `canard_resource_hints()`**: Now emits a `<link rel="dns-prefetch" href="https://secure.gravatar.com">` hint on all front-end pages. On archive pages with multiple authors this starts Gravatar DNS resolution immediately on page parse, reducing stall time before avatar images can be requested.

---

### 2.3 API Modernization

#### `functions.php`

- **`canard-style` handle pinned to parent theme**: All asset enqueues in **`canard_scripts()`** use **`get_template_directory_uri()`**, ensuring the `canard-style` handle always points to the parent theme's **`style.css`** regardless of child theme activation.

- **`CANARD_VERSION` constant**: Added to replace hardcoded version strings in all enqueues.

- **Google Fonts v2 API**: Merged separate font requests into a single **`canard_google_fonts_url()`** function using the `/css2` endpoint with `&display=swap`.

- **Google Fonts preconnect hints**: Added **`canard_resource_hints()`** hooked to the **`wp_resource_hints`** filter, emitting `<link rel="preconnect">` hints for `fonts.googleapis.com` and `fonts.gstatic.com` using the correct WordPress API rather than a raw `wp_head` echo, ensuring hints are deduplicated and filterable by child themes and plugins.

- **HTML5 support expanded**: **`add_theme_support( 'html5', ... )`** extended to include `script` and `style`.

- **`navigation-widgets` support**: Added (WP 5.5+) to opt navigation widgets into semantic HTML5 markup, preventing WordPress from outputting a `<div>` wrapper when the html5 feature flag is active. **`customize-selective-refresh-widgets`** added for improved Customizer preview performance.

- **Classic widgets**: Replaced **`canard_disable_block_widgets()`** function and its `after_setup_theme` hook with `add_filter( 'use_widgets_block_editor', '__return_false' )` — simpler, more reliable, no named function needed.

- **`editor-color-palette` deprecation resolved**: Removed deprecated `add_theme_support( 'editor-color-palette', ... )` (deprecated in WP 5.9) from **`functions.php`** and replaced with **`theme.json`**.

- **Block editor styles**: Replaced manual **`wp_enqueue_style()`** on the **`enqueue_block_editor_assets`** hook with `add_theme_support( 'editor-styles' )` + **`add_editor_style()`** — the recommended path since WP 5.8. This applies automatic `.editor-styles-wrapper` body-class scoping and handles RTL correctly.

- **Standardised pagination**: **`archive.php`**, **`index.php`**, and **`search.php`** were using **`the_posts_navigation()`** (prev/next only) while **`category.php`** used **`the_posts_pagination()`** with numbered pages. All listing templates now use **`the_posts_pagination()`** with consistent `mid_size`, `prev_text`, and `next_text` arguments.

- **`canard_get_category_header_image()` added**: Returns the banner image URL for the current category archive, or `false` if none is configured. Wrapped in `if ( ! function_exists() )` for child theme override. Exposes the **`canard_category_header_image`** filter. See **`docs/category-images.md`** for usage.

- **`canard_get_category_color()` cleaned up**: The **`wp_get_global_settings( array( 'color', 'palette', 'theme' ) )`** branch that attempted to read the `red` slug from the theme palette has been removed. Canard is a classic theme with no **`theme.json`**, so this call always returned an empty array, making the branch dead code that fell through to the `#d11415` default on every call. The **`canard_category_color`** filter remains in place for child theme overrides.

#### `inc/template-tags.php`

- **`wp_get_attachment_image_src()` → `wp_get_attachment_image_url()`**: Both calls in **`canard_post_nav_background()`** replaced with **`wp_get_attachment_image_url()`**, which returns the URL string directly and has been available since WordPress 4.4.

- **Transient key renamed**: `canard_categories` → `canard_cat_count_v1` in **`canard_categorized_blog()`** to avoid collisions with other plugins or themes on multisite installs. The flusher **`canard_category_transient_flusher()`** updated to match.

- **`wp_kses` avatar `img` allowlist updated**: WordPress 6.3+ emits `loading="lazy"` and `decoding="async"` on **`get_avatar()`** output. Both attributes added to the `img` allowlist entry to prevent **`wp_kses()`** from silently stripping them.

- **`canard_entry_footer()` — `canard_entry_footer_show_meta` filter**: Added `apply_filters( 'canard_entry_footer_show_meta', true )` so child themes can suppress the meta block without completely overriding the function.

- **`canard_categorized_blog()` refactored**: Assignment-inside-condition pattern replaced with explicit variable separation. The misleading variable name `$all_the_cool_cats` (a count integer, not an array) replaced with `$cat_count` per WordPress VIP code review guidelines.

- **Bug fix — null `$previous` in `canard_post_nav_background()`**: On an attachment page whose parent post cannot be found, `get_post( get_post()->post_parent )` returns `null`. The subsequent `$previous->post_type` access emitted a PHP warning ("Attempt to read property 'post_type' on null"). Fixed by adding a `$previous &&` null check before the property access.

#### `inc/custom-header.php`

**`canard_header_style()` refactored to use `wp_add_inline_style()`**: The function was echoing a raw `<style>` tag via the `wp_head` callback — incompatible with Content Security Policy headers using nonce-based `style-src` directives. Refactored to build the CSS string and pass it to `wp_add_inline_style( 'canard-style', $css )`, matching the pattern already used in **`canard_post_nav_background()`**.

#### `inc/customizer.php`

Added a developer documentation comment explaining that the Customizer API handles its own nonce verification, but any new AJAX endpoints or form submissions added to the theme must implement `wp_nonce_field()` / `check_ajax_referer()`. This establishes the expectation for future contributors and prevents CSRF vulnerabilities from being introduced inadvertently.

#### `inc/extras.php`

- **`the_permalink` filter deprecation**: Removed `apply_filters( 'the_permalink', get_permalink() )` from **`canard_get_link_url()`**. The **`the_permalink`** filter hook was deprecated in WordPress 6.8. Replaced with **`get_the_permalink()`** directly.

- **Function definition / filter registration separated**: **`canard_excerpt_more()`** and **`canard_continue_reading()`** previously guarded the `add_filter()` call inside the `function_exists()` check, making it impossible for a child theme to define the function and still have the filter run on admin pages (e.g., for REST API excerpt generation). Function definition and filter registration are now separate, matching WordPress coding standards.

- **`canard_continue_reading()` — `$the_excerpt` sanitized**: This filter runs at priority 9, before other excerpt filters at higher priorities have finished. The incoming `$the_excerpt` value is now passed through **`wp_kses_post()`** before the "Continue reading" link is appended.

- **`@since` tags updated**: **`canard_excerpt_more()`** and **`canard_continue_reading()`** updated from `1.0.3` / `1.0.4` to `2.5.0`.

#### `inc/jetpack.php`

**`canard_jetpack_featured_image_display()` refactored**: Replaced dense ternary chains and nested `isset()` / `array_merge()` patterns with early-return guards and explicitly named variables (`$show_on_post`, `$show_on_page`). Uses the PHP 8 null-coalescing operator (`?? []`) for option reading. Functionally identical.

#### `entry-script.php` — Inline Script Removed

The file previously emitted a raw `<script>` block inline in the page when a featured image hero layout was needed. Inline scripts are blocked by Content Security Policy headers and bypass WordPress's asset pipeline. The file has been rewritten as a `body_class` filter: when hero layout conditions are met, the class `has-entry-hero` is added to `<body>`. The DOM manipulation has been moved into **`single.js`**. The `add_filter()` call has been moved to **`functions.php`** so it registers exactly once rather than once per post on archive pages. No behavior change for end users.

#### PHP 8.x Type Hints

Parameter and return type hints added to all public/hookable functions:

- `canard_body_classes( array $classes ): array` — **`inc/extras.php`**
- `canard_excerpt_length( int $length ): int` — **`inc/extras.php`**
- `canard_continue_reading( string $the_excerpt ): string` — **`inc/extras.php`**
- `canard_categorized_blog(): bool` — **`inc/template-tags.php`**
- `canard_google_fonts_url(): string` — **`functions.php`**
- `canard_resource_hints( array $urls, string $relation_type ): array` — **`functions.php`**

#### Template Files — HTML & Semantic Updates

- **`header.php`**: Added **`wp_body_open()`**; upgraded XFN profile link to HTTPS; added descriptive `aria-label` to all navigation elements and the custom header image anchor; removed the legacy pingback link tag; added **`absint()`** to `get_custom_header()->width` and `->height` output; simplified the `site-top` conditional to `has_nav_menu( 'secondary' )` only.

- **`footer.php`**: Updated WordPress.org link to HTTPS; applied `esc_html__()` to theme credits; separated the theme credit author link from the translated string; removed the duplicate `.bottom-navigation` block that re-rendered the secondary menu already output in **`header.php`** — sites hiding this with CSS can remove that rule.

- **`content.php`**: Replaced `strpos( $post->post_content, '<!--more' )` with `str_contains( get_the_content(), '<!--more' )` — **`get_the_content()`** is the correct in-Loop API and `str_contains()` is the idiomatic PHP 8 form.

- **`category.php`**: Removed redundant `role="main"` attribute from the `<main>` element. The `<main>` element carries an implicit ARIA landmark role; the explicit attribute fails the WCAG 2.1 "avoid redundant ARIA" guideline.

- **`content-featured-post.php`**: Fixed an accessibility violation (WCAG 2.4.4 — Link Purpose) where `<a class="post-thumbnail" href="..."></a>` was rendered as an empty anchor with no accessible content when **`has_post_thumbnail()`** returned false. The anchor is now only rendered inside the **`has_post_thumbnail()`** check.

- **HTML5 role attributes removed**: Removed redundant `role` attributes (`banner`, `navigation`, `main`, `complementary`) from HTML5 sectioning elements where they are already implicit.

---

## 3. JavaScript & Asset Management

### 3.1 jQuery Removal

All five scripts that previously declared jQuery as a dependency have been fully rewritten in vanilla JavaScript. jQuery is no longer loaded as a front-end dependency on any page. The `jquery` dependency declaration has been removed from the **`canard-navigation`**, **`canard-search`**, **`canard-featured-content`**, **`canard-single`**, and **`canard-posts`** enqueues in **`functions.php`**.

| File | What Changed |
|---|---|
| **`search.js`** | jQuery IIFE and `.hover()` / `.focusin()` / `.focusout()` calls replaced with `addEventListener( 'mouseenter' / 'mouseleave' / 'focus' / 'blur' )`. |
| **`featured-content.js`** | Rewritten using `querySelectorAll`, `forEach`, `classList`, and `style.backgroundImage`. The `$(window).on('load')` wrapper replaced with `window.addEventListener('load')`. |
| **`navigation.js`** | Fully rewritten in vanilla JS. Event delegation via `document.addEventListener('click')` replaces jQuery's `.on('click', '.dropdown-toggle')`. `btn.className = 'dropdown-toggle'` replaced with `btn.classList.add('dropdown-toggle')`. |
| **`single.js`** | Rewritten in vanilla JS. `$('.author-info')`, `.prependTo()`, `.insertAfter()`, `$(window).width()`, and all Jetpack sharedaddy/table DOM operations replaced with `querySelector`, `insertBefore`, `Element.after()`, `window.innerWidth`, and `querySelectorAll().forEach()`. |
| **`posts.js`** | Rewritten in vanilla JS. `$('.site-main .hentry').each()`, `.hasClass()`, `.find()`, `.css()`, and `$(window).width()` replaced with `querySelectorAll`, `classList.contains`, `style` properties, and `window.innerWidth`. Fixed character encoding corruption (em dashes); renamed shadowed variables. |

---

### 3.2 ES6 & Global Improvements

- **`var` → `const` / `let`**: Replaced throughout all scripts.
- **Strict equality**: Replaced `'undefined' === typeof x` checks with simple truthy/falsy `!x` logic.
- **`className` string manipulation**: Replaced all `.className.indexOf()`, `.className +=`, and `.className.replace()` patterns with `classList.contains()`, `classList.add()`, `classList.remove()`, and `classList.toggle()` throughout all scripts.
- **Event handlers**: Replaced all `button.onclick = function()` assignments with `button.addEventListener( 'click', function() )` for consistency and child-theme extensibility.

---

### 3.3 File-Specific Changes

| File | Change |
|---|---|
| **`utils.js`** | **New file.** Shared `debounce` implementation exposed as `window.canardUtils.debounce`. Uses the standard `clearTimeout` / `setTimeout` pattern. Rest parameters (`...args`) replace `[].slice.call( arguments, 0 )`. |
| **`single.js`** | Absorbed the entry-hero DOM manipulation previously in the inline `<script>` in `entry-script.php`. |
| **`customizer.js`** | Removed jQuery IIFE; all `$()` selectors replaced with `document.querySelector()` / `document.querySelectorAll()`; `.text()` replaced with `.textContent`; `.addClass()` / `.removeClass()` replaced with `classList`. Hex colour validated before `el.style.color` assignment (see Security, Section 2.1). |
| **`header.js`** | Added null guard for `siteBranding` before accessing `clientHeight`. |
| **`sidebar.js`** | `button.onclick` replaced with `button.addEventListener( 'click', ... )`. |

---

### 3.4 Accessibility & Correctness

- **`navigation.js` — accessible names for dropdown toggles (WCAG 2.1 SC 4.1.2)**: Toggle `<button>` elements were injected with `aria-expanded` but no accessible name, so screen readers announced just "button" with no context. Buttons now derive their label from the parent link text: `aria-label="Toggle [Menu Item] submenu"`. Falls back to `"Toggle submenu"` if no text is available.

- **`navigation.js` — global `touchstart` handler**: A `document.addEventListener( 'touchstart' )` handler now removes the `focus` class from all `.main-navigation` items when a tap lands outside `.main-navigation`. Previously, a touch-device user who opened a submenu then tapped post content would leave the submenu visually open.

- **`featured-content.js` / `posts.js` — `currentSrc` for background images**: Both scripts were reading `thumbnail.src` to set `background-image: url()`, ignoring the `srcset` attribute and potentially loading a full-resolution image even when a smaller responsive variant had already been fetched. Changed to `thumbnail.currentSrc || thumbnail.src`.

- **`single.js` — synchronous-execution comment**: The entry-hero DOM rearrangement runs synchronously without a `DOMContentLoaded` wrapper to avoid a FOUC on pages with featured images. A prominent comment block now explains this so JS developers do not inadvertently "fix" the missing wrapper and introduce a visible layout flash.

---

## 4. CSS & Design Systems

### 4.1 Genericons Replaced with SVG

Genericons has been fully removed. All icon glyphs previously rendered via the Genericons icon font are now rendered using either inline SVG elements (for template icons) or SVG `background-image` data URIs (for CSS pseudo-element icons). The **`genericons/`** directory and its font files are no longer referenced and may be deleted.

#### CSS Pseudo-element Icons — `style.css`

| Selector | Former Glyph | Replacement |
|---|---|---|
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

#### Template `<span>` Icons Replaced with Inline `<svg>` Elements

| File | Former Element | Replacement |
|---|---|---|
| **`content.php`** | `<span class="genericon genericon-pinned">` | Inline `<svg>` pin icon, `aria-hidden="true" focusable="false"` |
| **`content-link.php`** | `<span class="genericon genericon-link">` | Inline `<svg>` external-link icon, `aria-hidden="true" focusable="false"` |
| **`footer.php`** | `<span class="genericon genericon-wordpress sep">` | Inline `<svg>` WordPress logo mark, `aria-hidden="true" focusable="false"`, `class="sep"` retained |

#### CSS Selector Updates — `style.css`

- `.sticky-post .genericon` → `.sticky-post svg` (sizing adjusted to 16×16 px with `vertical-align: middle`).
- `.post-link .genericon` → `.post-link svg` (padding-based centring replaces `line-height` centring; 60 px circle container retained).
- `.post-link:active .genericon`, `.post-link:focus .genericon`, `.post-link:hover .genericon` → targeting `svg` instead.
- Transition list updated: `.post-link .genericon` → `.post-link svg`.
- `.site-info .sep`: `color` / `font-size` / `line-height` replaced with `fill`, `height`, `width`, and `vertical-align` appropriate for an SVG element. The `sep:hover { transform: rotate(360deg); }` easter egg is preserved.
- **`editor-blocks.css`**: Both Genericons `blockquote ::before` rules replaced with equivalent SVG `background-image` data URI rules matching front-end style.
- Shared Genericons `font-family` declaration block spanning all button and navigation pseudo-elements removed entirely.

---

### 4.2 Social Navigation Removed

The social navigation menu location, all associated template markup, and all associated CSS have been removed.

- **PHP**: `social` removed from **`register_nav_menus()`**; social `<nav>` blocks removed from **`header.php`** and **`footer.php`**; the `site-top` conditional in **`header.php`** simplified from `has_nav_menu( 'secondary' ) || has_nav_menu( 'social' )` to `has_nav_menu( 'secondary' )`.

- **`style.css`**: Entire `.social-navigation` ruleset removed (~130 lines), including base layout, link styles, `:hover` / `:focus` states, and all 26 domain-specific `content: "\fXXX"` icon rules. `.site-social`, `.site-social-inner`, and `.bottom-social > div` entries removed from all width/margin grouped selector lists. Related media query overrides removed.

- **`rtl.css`**: `.social-navigation li` float-direction overrides and media query `.social-navigation { float: left; ... }` override removed.

- **`readme.txt`**: The "Social menu." bullet and the "How do I add the Social Links to the header?" FAQ section removed.

---

### 4.3 Category Header System

A new **`category.php`** template introduces a full-width hero banner at the top of category archive pages. The following CSS rules support both the image and color-fallback variants of this header, and are logically paired with the PHP functions **`canard_get_category_header_image()`** and **`canard_get_category_color()`** described in Section 2.3.

- **`style.css` — `.category-color-fallback`**: Renders a solid-color block at 260 px / 360 px / 420 px tall across the three responsive breakpoints, matching the visual footprint of a hero image for categories without a configured banner.

- **`style.css` — `.entry-hero .post-thumbnail .category-header`**: Applies `object-fit: cover` and matching responsive heights to the `<img>` element used when a category image is configured.

Both rules are added under a dedicated `/* Category header */` section comment for maintainability.

---

### 4.4 Bug Fixes & Modernization

- **`editor-blocks.css`**: Removed stray backtick; fixed typos in `.wp-block-latest-posts`; updated `.is-wide` to `.is-style-wide`; replaced `wp-block-quote__citation` with `wp-block-quote cite`.
- **`rtl.css`**: Fixed missing units on `right: 50px`.
- **`style.css` — placeholder**: Replaced vendor-prefixed placeholder hacks with modern `::placeholder { color: #777; opacity: 1; }`.
- **`style.css` — focus visibility**: Implemented keyboard-accessible focus via `:focus:not(:focus-visible) { outline: none; }` to suppress the default focus ring on mouse clicks while preserving it for keyboard navigation.
- **`style.css` — version header**: Updated `Version:` from `1.1.0` to `2.5.0` to match the `CANARD_VERSION` constant.
- **`style.css` — License URI**: Upgraded from `http://` to `https://`.

#### Accessibility

Replaced deprecated `clip: rect()` with `clip-path: inset(50%)` and `white-space: nowrap` for all `.screen-reader-text` declarations.

#### Legacy Cleanup

- Stripped all `-webkit-box`, `-ms-flexbox`, and `-webkit-transform` vendor prefixes.
- Cleaned up the **`style.css`** normalise block; updated `abbr[title]` to use `underline dotted`.
- Removed all `speak: none` declarations and empty ruleset stubs in **`blocks.css`**.

---

## 5. Template & HTML Enhancements

### 5.1 Schema, Structured Data & ARIA

- **`inc/template-tags.php` — `wp_kses()` allowlist expansion**: `itemprop` and `property` attributes added to the allowlist for `<a>` and `<span>` elements in **`canard_entry_meta()`** to prevent Schema.org microdata attributes from being silently stripped.

- **Redundant ARIA landmark roles removed**: Explicit `role` attributes (`banner`, `navigation`, `main`, `complementary`) removed from HTML5 sectioning elements where they are already implicit, complying with WCAG 2.1 "avoid redundant ARIA".

- **Navigation `aria-label` attributes**: Descriptive `aria-label` attributes added to all `<nav>` elements in **`header.php`** so screen readers can distinguish between primary, secondary, and other navigation landmarks.

- **Custom header image anchor**: The custom header `<img>` has `alt=""` as a decorative image, but the wrapping `<a>` now has meaningful link text via `aria-label` for screen readers.

- **`content-featured-post.php` — empty anchor fix**: The `<a class="post-thumbnail">` wrapper is now only rendered when **`has_post_thumbnail()`** returns true, eliminating a WCAG 2.4.4 Link Purpose violation.

- **`navigation.js` — button accessible names**: Dropdown toggle buttons now derive `aria-label="Toggle [Menu Item] submenu"` from their parent link text, satisfying WCAG 2.1 SC 4.1.2.

---

### 5.2 HTML5 Semantics

- **`wp_body_open()` added** to **`header.php`** immediately after `<body>`, enabling plugins and child themes to inject markup at the correct hook.

- **`str_contains()` in `content.php`**: Replaced `strpos( $post->post_content, '<!--more' )` with `str_contains( get_the_content(), '<!--more' )`. **`get_the_content()`** is the correct in-Loop API; `str_contains()` is the idiomatic PHP 8 form.

- **`category.php` — new template**: Introduces a semantic `<article>` wrapper for the category hero and integrates with the same `entry-hero` layout structure used by single posts for visual consistency.

- Legacy pingback link removed from **`header.php`**.

- XFN profile link upgraded to HTTPS in **`header.php`**.

---

## 6. Removals & Cleanup

### 6.1 Deleted Files

| File / Directory | Reason |
|---|---|
| **`js/skip-link-focus-fix.js`** | No longer required for modern browsers. |
| **`genericons/`** (entire directory) | No file in the theme references Genericons any longer. Safe to delete. |

---

### 6.2 Deprecated Functions Removed

- **`canard_disable_block_widgets()`**: Replaced with `add_filter( 'use_widgets_block_editor', '__return_false' )`.
- **`canard_sanitize_checkbox()`**: Replaced with **`wp_validate_boolean()`** as the sanitize callback.
- WordPress.com updater inclusion removed from **`functions.php`**.
- `apply_filters( 'the_permalink', get_permalink() )` removed from **`canard_get_link_url()`** (hook deprecated in WP 6.8).
- `add_theme_support( 'editor-color-palette', ... )` removed (deprecated in WP 5.9); replaced with **`theme.json`**.
- The **`wp_get_global_settings()`** branch in **`canard_get_category_color()`** removed (dead code — Canard is a classic theme with no `theme.json`).

---

### 6.3 Removed Third-Party Dependencies

- jQuery removed as a runtime front-end dependency from all five enqueued scripts.
- Genericons icon font removed from both the front-end enqueue in **`canard_scripts()`** and the editor enqueue in **`canard_editor_styles()`**.
- Social navigation menu location removed from **`register_nav_menus()`** and all associated template markup and CSS (~130 lines) removed.

---

### 6.4 Added Files

| File | Purpose |
|---|---|
| **`js/utils.js`** | Shared `debounce` utility exposed as `window.canardUtils.debounce`. |
| **`category.php`** | Native category archive template with full-width hero banner and standard post loop with pagination. Integrated with the `entry-hero` layout structure used by single posts. |
| **`theme.json`** | Replaces deprecated `add_theme_support( 'editor-color-palette', ... )`. |
| **`docs/CHANGES.md`** | This change log. |
| **`docs/category-images.md`** | Documents how child themes can supply per-category banner images and colors using the `canard_category_header_image` and `canard_category_color` filters, or by overriding the functions entirely. |
