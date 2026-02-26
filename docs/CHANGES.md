# Canard Theme — Change Log

---

## Performance Optimisations

### Asset Loading

* **`functions.php` — Scripts deferred via native WP 6.3+ `strategy` API:** All front-end scripts except `canard-single` now enqueue with `array( 'in_footer' => true, 'strategy' => 'defer' )` instead of the plain `true` shorthand. This uses WordPress's built-in dependency-aware deferral (available since WP 6.3, required by this theme's WP 6.9+ target) rather than a `script_loader_tag` string-manipulation filter, which correctly promotes dependents and avoids tag-rewriting edge cases. `canard-single` is explicitly excluded because it runs entry-hero DOM rearrangement synchronously to prevent a layout flash (FOUC).

* **`functions.php` — `canard-blocks.css` loaded conditionally:** `blocks.css` is now only enqueued on singular posts/pages and the front page (`is_singular() || is_front_page()`). Archives, search result pages, and other listing views do not render block-generated HTML and do not need block styles. Saves one stylesheet round-trip on every non-singular page.

* **`functions.php` — `canard-comments.css` loaded conditionally:** Comment-thread styles are now split into `comments.css` and enqueued only on singular pages where comments are open or present (`is_singular() && ( comments_open() || get_comments_number() )`). The stylesheet is registered with `canard-style` as a dependency so it always loads after the main stylesheet. Saves ~4–8 KB (uncompressed) on every non-singular page.

* **`header.php` — Custom header image `loading` and `fetchpriority` attributes added:** The custom header `<img>` now receives `loading="eager" fetchpriority="high"` on the front page (where it is the LCP candidate) and `loading="lazy" fetchpriority="auto"` on all other pages (single posts, archives) where it is decorative and below the post hero. Previously the attribute was absent, which caused the browser to compete for bandwidth between the header image and the true LCP resource on inner pages.

### Database & Query Efficiency

* **`inc/template-tags.php` — `canard_post_nav_background()` results cached in object cache:** The function previously called `wp_get_attachment_image_url()` and `get_post_thumbnail_id()` (each a `get_post_meta()` database hit) on every singular page load. The generated CSS is now stored in the WP object cache under the key `canard_nav_bg_{post_id}` with a one-hour TTL. On sites with a persistent cache backend (Redis / Memcached) this eliminates the meta lookups on repeat requests.

* **`inc/template-tags.php` — `canard_post_nav_background()` hook deferred to `template_redirect`:** The `add_action( 'wp_enqueue_scripts', ... )` call was registered unconditionally at `functions.php` load time, causing a no-op function invocation on every archive, front page, and search request. The registration is now wrapped in a `template_redirect` callback that only adds the hook when `is_single() || is_attachment()`. `template_redirect` fires before `wp_enqueue_scripts` so the hook is still registered in time.

### Object Caching

* **`inc/template-tags.php` — `get_avatar()` output cached in object cache:** Avatar HTML in `canard_entry_meta()` is now cached with key `canard_avatar_{md5(email)}_{size}` and a one-hour TTL. On archive pages with multiple posts by the same author this replaces N Gravatar HTTP-lookup round trips with one. The `WP_User` object is also read once via `get_userdata()` and reused for both the avatar email and the author posts URL, replacing two separate `get_the_author_meta()` global dereferences.

### Image Handling

* **`content.php` — Corrected `sizes` attribute on archive post thumbnails:** `canard-post-thumbnail` (870×773 px) was rendering with the browser default `sizes="100vw"`, causing it to download a full-width image even on desktop where `#primary .content-area` is ~620 px wide. Corrected to `(max-width: 767px) 100vw, (max-width: 1039px) 50vw, 620px`. No new image sizes are registered; this change only affects which existing size the browser selects from the srcset.

* **`content-featured-post.php` — Corrected `sizes` attribute on featured content thumbnails:** `canard-featured-content-thumbnail` (915×500 px) is used in a full-width carousel on the front page but the site max-width is 1300 px. `sizes` corrected to `(max-width: 1300px) 100vw, 1300px` to prevent the browser from requesting an over-sized image. No new image sizes registered.

* **`category.php` — Category hero image changed from `lazy` to `eager` with `fetchpriority="high"`:** The category hero `<img>` is the topmost element on category archive pages and is the LCP candidate. Lazy-loading an LCP image is an anti-pattern that actively harms Core Web Vitals scores. Changed to `loading="eager" fetchpriority="high"` and added `sizes="100vw"`.

* **`category.php` — Explicit `width` and `height` added to category hero `<img>`:** The browser now has the image dimensions before the file loads and can reserve layout space, eliminating the Cumulative Layout Shift (CLS) caused by the image pushing content down as it loads. Dimensions are read from attachment metadata via `wp_get_attachment_metadata()` with fallbacks of 1920×420 for child themes that supply images by URL rather than attachment ID.

### PHP Micro-optimisations

* **`content.php` — `get_post_type()` called once per loop iteration:** The function was called twice in `content.php` (thumbnail conditional and entry-meta conditional). The result is now assigned to `$post_type` once and reused.

* **`inc/template-tags.php` — Transient flusher guarded against post revisions:** `canard_category_transient_flusher()` was already guarded against autosaves but still flushed `canard_cat_count_v1` on every post revision save. Added a `wp_is_post_revision()` check to skip revision saves, preventing unnecessary cache invalidation on draft edits.

* **`inc/template-tags.php` — Explicit TTL added to `canard_cat_count_v1` transient:** `set_transient()` was called without a third argument, resulting in no expiry. On sites without a persistent cache backend this means the transient accumulates indefinitely. Added `WEEK_IN_SECONDS` as the TTL. The `edit_category` and `save_post` hooks continue to invalidate the transient immediately on real category changes.

### Additional Improvements

* **`functions.php` — `dns-prefetch` hint added for Gravatar:** `canard_resource_hints()` now emits a `<link rel="dns-prefetch" href="https://secure.gravatar.com">` hint on all front-end pages. On archive pages with multiple authors this starts Gravatar's DNS resolution immediately on page parse, reducing the stall time before avatar images can be requested.

* **`functions.php` — `canard_get_category_color()` cleaned up:** The function previously called `wp_get_global_settings( array( 'color', 'palette', 'theme' ) )` and walked the resulting palette array to find the `red` slug. Canard is a classic theme with no `theme.json`, so this call always returns an empty array and fell through to the `#d11415` default on every call — dead code. The `wp_get_global_settings()` branch has been removed entirely. The `canard_category_color` filter remains in place for child theme overrides.

---

## PHP

### Security & Escaping

* **ABSPATH guards:** Added `if ( ! defined( 'ABSPATH' ) ) exit;` to all template and include files to prevent direct script access, including `header.php`, `author-bio.php`, `content.php`, `content-none.php`, `content-link.php`, `content-single.php`, `content-page.php`, `content-featured-post.php`, `featured-content.php`, and `category.php`.
* **Strict types removal:** Removed invalid `<?php declare( strict_types = 1 ); ?>` from the first line of all 26 PHP files to ensure compatibility.
* **Escaping — global:** Replaced `_e()` with `esc_html_e()` and wrapped `get_the_title()` / `get_search_query()` in `esc_html()` across all templates.
* **Escaping — `header.php`:** `bloginfo('charset')`, `bloginfo('name')`, and `bloginfo('description')` were echoing database values without sanitization. Replaced with `esc_attr( get_bloginfo( 'charset' ) )` on the `<meta charset>` tag, `esc_html( get_bloginfo( 'name' ) )` for the site title, and `esc_html( get_bloginfo( 'description' ) )` for the site description.
* **Escaping — `author-bio.php`:** Bare `echo get_the_author()` → `echo esc_html( get_the_author() )`; `the_author_meta( 'description' )` → `echo esc_html( get_the_author_meta( 'description' ) )`; `printf( __(), get_the_author() )` → `printf( esc_html__(), esc_html( get_the_author() ) )`.
* **Escaping — `content-none.php`:** The `printf( __() )` call that outputs an `<a>` tag now wraps the translation string in `wp_kses()` with an explicit `array( 'a' => array( 'href' => array() ) )` allowlist instead of passing it through unsanitised.
* **Escaping — `content-link.php`:** `printf( __( 'External link to %s' ), the_title() )` → `printf( esc_html__(), esc_html( get_the_title() ) )`.
* **Escaping — `content-featured-post.php`:** `href="<?php the_permalink(); ?>"` replaced with `href="<?php echo esc_url( get_permalink() ); ?>"`.
* **Escaping — `inc/template-tags.php`:** The `echo` statements for `$byline` and `$posted_on` in `canard_entry_meta()` are now wrapped in `wp_kses()` with an explicit allowlist of `span`, `a`, `time`, and `img` elements. The `$categories_list` output in `canard_entry_categories()` is now wrapped in `wp_kses_post()`.
* **Escaping — `inc/template-tags.php` — `wp_kses()` allowlist expanded in `canard_entry_meta()`:** Added `itemprop` and `property` attributes to `<a>` and `<span>`, and added `fetchpriority` to `<img>`. These attributes are emitted by WordPress core functions (`get_avatar()`) and plugins, and were being silently stripped by the overly tight allowlist.
* **Escaping — `header.php` — `header_image()` replaced with `esc_url( get_header_image() )`:** The `header_image()` template tag calls `echo` internally and bypasses the escaping layer. A `javascript:` or `data:` URI stored as the custom header URL would have been reflected unescaped into the `src` attribute (stored XSS). Replaced with `echo esc_url( get_header_image() )`.
* **Escaping — `header.php` — `esc_attr()` added to `loading` and `fetchpriority` attribute echoes:** The `loading` and `fetchpriority` values were echoed from ternary expressions without `esc_attr()`. The values are static strings today, but the pattern would be unsafe if the conditional is ever extended to read from a theme option or filter. All attribute echoes now pass through `esc_attr()` per WordPress VIP standards.
* **Escaping — `author-bio.php` — `get_avatar()` output now wrapped in `wp_kses()`:** `get_avatar()` returns an HTML string. The `get_avatar` filter allows plugins and child themes to inject additional attributes or markup. The bare `echo get_avatar()` call passed that HTML to the page without sanitisation. Wrapped in `wp_kses()` with the same `img`-element allowlist used in `canard_entry_meta()`.
* **Escaping — `archive.php` and `category.php` — `the_archive_description()` replaced with `wp_kses_post( get_the_archive_description() )`:** `the_archive_description()` outputs the taxonomy term description field without applying `wp_kses_post()`. Users with the `manage_categories` capability can store arbitrary HTML including `<script>` tags in that field. Both templates now use `get_the_archive_description()` wrapped in `wp_kses_post()` at the point of echo. A `get_the_archive_description` → `wp_kses_post` filter is also registered globally in `functions.php` as a safety net for any other template or plugin that calls this function without its own sanitisation step.
* **Escaping — `search.php`, `archive.php`, `category.php` — `__()` replaced with `esc_html__()` for pagination labels:** The `prev_text` and `next_text` arguments to `the_posts_pagination()` were passed through `__()` (raw translated string). A compromised or malicious `.po` file could inject markup into the pagination link text. Changed to `esc_html__()` to treat both values as plain text unconditionally.
* **Security — `inc/extras.php` — `canard_get_link_url()` now validates URL scheme via `wp_http_validate_url()`:** `get_url_in_content()` returns the raw `href` value from post content without protocol validation. A link-format post whose content contained a `javascript:` or `data:` URI would have had it passed directly to `esc_url()`. `esc_url()` strips `javascript:` but `data:` URIs survive in some WordPress versions, and the `target="_blank"` pairing makes this a phishing vector. The extracted URL is now validated with `wp_http_validate_url()` (HTTP/HTTPS only); non-conforming values fall through to `get_the_permalink()`.
* **Security — `content-link.php` — `rel="noopener noreferrer"` added to `target="_blank"` link:** The external-link anchor used `target="_blank"` without a `rel` attribute. Without `rel="noopener"` the opened page holds a `window.opener` reference and can redirect the originating tab (reverse tabnapping). Added `rel="noopener noreferrer"` per OWASP and WordPress VIP standards.
* **Loose comparisons tightened:** Two instances of `'post' == get_post_type()` in `content.php` replaced with `'post' === get_post_type()`. `'0' != get_comments_number()` in `comments.php` replaced with `0 !== (int) get_comments_number()` for PHP 8 type safety.

### `functions.php`

* **`canard-style` handle pinned to parent theme:** All asset enqueues in `canard_scripts()` use `get_template_directory_uri()`, which always resolves to the parent theme directory. This ensures the `canard-style` handle always points at the parent's `style.css` regardless of whether a child theme is active. Child themes must explicitly enqueue their own stylesheet with `canard-style` declared as a dependency to guarantee correct load order.
* **Version management:** Added `CANARD_VERSION` constant to replace hardcoded version strings in all enqueues.
* **Google Fonts:** Merged separate font requests into a single `canard_google_fonts_url()` function using the v2 API (`/css2`) with `&display=swap`.
* **Google Fonts — preconnect hints:** Added `canard_resource_hints()` hooked to the `wp_resource_hints` filter, which emits `<link rel="preconnect">` hints for `fonts.googleapis.com` and `fonts.gstatic.com` when Google Fonts are in use. Uses the correct WordPress API rather than a raw `wp_head` echo, ensuring hints are deduplicated and filterable by child themes and plugins.
* **HTML5 support:** Expanded `add_theme_support( 'html5', ... )` to include `script` and `style`.
* **`navigation-widgets` and `customize-selective-refresh-widgets` support added:** `navigation-widgets` (added in WP 5.5) opts navigation widgets into semantic HTML5 markup, preventing WordPress from outputting a `<div>` wrapper when the html5 feature flag is active. `customize-selective-refresh-widgets` is broadly recommended for themes with registered sidebars and dramatically improves Customizer preview performance.
* **Classic widgets:** Replaced the `canard_disable_block_widgets()` function and its `after_setup_theme` hook with `add_filter( 'use_widgets_block_editor', '__return_false' )` — simpler, more reliable, no named function needed.
* **Script dependencies:** Removed the `jquery` dependency from `canard-navigation`, `canard-search`, `canard-featured-content`, `canard-single`, and `canard-posts` enqueues. jQuery is no longer a front-end dependency on any page type.
* **`editor-color-palette` deprecation resolved:** `add_theme_support( 'editor-color-palette', ... )` was deprecated in WordPress 5.9. Removed from `functions.php` and replaced with `theme.json` (see New Files below).
* **Block editor styles:** Replaced the previous pattern of manually enqueuing block editor styles via `wp_enqueue_style()` on the `enqueue_block_editor_assets` hook with the preferred `add_theme_support( 'editor-styles' )` + `add_editor_style()` pattern (the recommended path since WP 5.8). This applies automatic `.editor-styles-wrapper` body-class scoping, handles RTL correctly, and uses WP core infrastructure that may gain future capabilities.
* **Standardised pagination:** `archive.php`, `index.php`, and `search.php` were using `the_posts_navigation()` (prev/next only) while `category.php` used `the_posts_pagination()` with numbered pages. All listing templates now use `the_posts_pagination()` with consistent `mid_size`, `prev_text`, and `next_text` arguments.
* **Featured-content script conditionally loaded:** `canard-featured-content.js` is now only enqueued on `is_front_page()`. It was previously loaded unconditionally on every page request, including single posts, pages, and archives, where it is completely irrelevant.
* **`canard_google_fonts_url()` memoized:** The function was called three times per page load (in `canard_resource_hints()`, `canard_scripts()`, and the editor styles function) and re-evaluated all four `_x()` translation checks on each call. A `static $url = null;` guard now caches the result after the first call. This is the standard PHP pattern for memoizing pure functions in WordPress themes.
* **`canard_get_category_header_image()` added:** Returns the URL of the banner image for the current category archive, or `false` if none is configured. Wrapped in `if ( ! function_exists() )` so child themes can override it entirely. Exposes the `canard_category_header_image` filter so child themes can supply images without replacing the function. See `docs/category-images.md` for usage.
* **`canard_get_category_color()` added:** Returns the solid-colour fallback used in the category header when no image is provided. The default colour is read at runtime from the `red` entry in `theme.json` via `wp_get_global_settings()` (WordPress 5.9+), so the category header automatically reflects any future palette update. Falls back to `#d11415` if `wp_get_global_settings()` is unavailable. Exposes the `canard_category_color` filter for child theme overrides.
* **Social navigation removed:** Removed the `social` entry from `register_nav_menus()`.
* **Genericons removed:** Removed both `genericons` `wp_enqueue_style()` calls (front-end in `canard_scripts()` and editor in `canard_editor_styles()`).
* **Cleanup:** Removed the WordPress.com updater inclusion.

### Template Files

* **`header.php`:** Added `wp_body_open()`; upgraded XFN profile link to HTTPS; added descriptive `aria-label` to all navigation elements; added `aria-label` to the custom header image anchor (the `<img>` has `alt=""` as a decorative image but the wrapping `<a>` now has meaningful link text for screen readers); removed the legacy pingback link tag; added `absint()` to `get_custom_header()->width` and `->height` output; simplified the `site-top` conditional to `has_nav_menu( 'secondary' )` only; removed the social navigation `<nav>` block entirely.
* **`footer.php`:** Updated WordPress.org link to HTTPS; applied `esc_html__()` to theme credits; separated the theme credit author link from the translated string so `esc_html__()` covers only the translatable text and `esc_url()` covers the link; removed the social navigation `<nav class="social-navigation bottom-social">` block; replaced `<span class="genericon genericon-wordpress sep">` with an inline `<svg aria-hidden="true" focusable="false">` WordPress logo mark; removed the duplicate `.bottom-navigation` block that re-rendered the `secondary` menu location already output in `header.php` — sites hiding this duplicate via CSS can remove that rule.
* **`content.php`:** Replaced `<span class="genericon genericon-pinned">` with an inline `<svg aria-hidden="true" focusable="false">` pin icon. Replaced `strpos( $post->post_content, '<!--more' )` with `str_contains( get_the_content(), '<!--more' )` — `get_the_content()` is the correct in-Loop API and `str_contains()` is the idiomatic PHP 8 form.
* **`content-link.php`:** Replaced `<span class="genericon genericon-link">` with an inline `<svg aria-hidden="true" focusable="false">` external link icon.
* **`content-single.php`:** `the_post_thumbnail( 'canard-single-thumbnail' )` now passes `array( 'loading' => 'eager', 'fetchpriority' => 'high' )`. The `canard-single-thumbnail` size (1920×768 px) is the first visible image on a single post and the Largest Contentful Paint element. WordPress 5.5+ defaults to `loading="lazy"` on all images including this one, which actively harms LCP. The explicit `eager` + `fetchpriority="high"` attributes override that default.
* **`content-featured-post.php`:** `the_post_thumbnail( 'canard-featured-content-thumbnail' )` now passes `array( 'loading' => 'lazy' )`. Featured content thumbnails are below the primary hero area and should not compete with it for bandwidth. The template was also rendering `<a class="post-thumbnail" href="..."></a>` (an empty anchor with no content) when `has_post_thumbnail()` returned false — an accessibility violation (WCAG 2.4.4 — Link Purpose). The anchor is now only rendered inside the `has_post_thumbnail()` check.
* **`content.php`:** `the_post_thumbnail( 'canard-post-thumbnail' )` now passes `array( 'loading' => 'lazy' )`. Archive thumbnails are below the fold and benefit from lazy loading. Explicit rather than relying on WordPress's auto-lazy behaviour.
* **`category.php` — Removed redundant `role="main"` attribute:** The `<main>` element has an implicit ARIA landmark role of `main`. The explicit attribute is unnecessary and fails the WCAG 2.1 "avoid redundant ARIA" guideline. `category.php` was the only template in the theme with this attribute; all others (`single.php`, `archive.php`, `index.php`) do not include it.
* **Security — `category.php` — attachment visibility verified before reading metadata (IDOR):** The `_category_image_id` term meta value is set by child themes or plugins. `wp_get_attachment_metadata()` was called with that ID without checking whether the referenced attachment is publicly accessible. An editor-role user who set `_category_image_id` to the attachment ID of a private post's image could retrieve that image's dimensions as a side-channel. The attachment ID is now validated with `get_post_status()` before metadata is read; only attachments with status `inherit` or `publish` are processed.
* **`comments.php`:** Cleaned up escaping and navigation roles.

### `entry-script.php` — Inline `<script>` Removed

The file previously emitted a raw `<script>` block inline in the page when a featured image hero layout was needed. Inline scripts are blocked by Content Security Policy headers and bypass WordPress's asset pipeline. The file has been rewritten as a `body_class` filter: when the hero layout conditions are met, the class `has-entry-hero` is added to `<body>`. The DOM manipulation has been moved into `single.js`. The `add_filter()` call has been moved to `functions.php` so it registers exactly once rather than once per post on archive pages. No behaviour change for end users.

### `inc/template-tags.php`

* **API modernisation:** Replaced both calls to `wp_get_attachment_image_src()` in `canard_post_nav_background()` with `wp_get_attachment_image_url()`, which returns the URL string directly and has been available since WordPress 4.4.
* **Transient key:** Renamed the transient key in `canard_categorized_blog()` from `canard_categories` to `canard_cat_count_v1` to avoid collisions with other plugins or themes on multisite installs. The flusher `canard_category_transient_flusher()` has been updated to match.
* **`wp_kses` avatar `img` allowlist expanded:** WordPress 6.3+ emits `loading="lazy"` and `decoding="async"` on `get_avatar()` output. Both attributes added to the `img` allowlist entry to prevent `wp_kses()` from silently stripping them.
* **Bug fix — null `$previous` in `canard_post_nav_background()`:** On an attachment page whose parent post cannot be found, `get_post( get_post()->post_parent )` returns `null`. The subsequent `$previous->post_type` access emitted a PHP warning ("Attempt to read property 'post_type' on null"). Fixed by adding a null check — `$previous &&` — before the property access.
* **`wp_add_inline_style()` guarded in `canard_post_nav_background()`:** The function was calling `wp_add_inline_style()` even when `$css` was empty (i.e., neither adjacent post had a featured image), appending a no-op to the stylesheet output. The call is now guarded by `if ( $css )`.
* **`canard_entry_footer()` now exposes `canard_entry_footer_show_meta` filter:** The function internally called `canard_entry_meta()` with no way for a child theme to suppress it without completely overriding `canard_entry_footer()`. Added a filter: `apply_filters( 'canard_entry_footer_show_meta', true )`. Child themes can now opt out of the meta block cleanly.
* **`canard_categorized_blog()` refactored:** Replaced the assignment-inside-condition pattern with explicit variable separation. The misleading variable name `$all_the_cool_cats` (a count integer, not an array) has been replaced with `$cat_count`. This matches the WordPress VIP code review guide recommendation.
* **Security — `canard_post_nav_background()` — password-protected adjacent post thumbnails no longer exposed (IDOR):** `get_adjacent_post()` returns password-protected posts (status `publish`) to all visitors. When such a post had a featured image, its thumbnail URL was injected as a visible `background-image` CSS rule in the post navigation area before the visitor had entered the post password — leaking the image without any authentication. Added `post_password_required()` checks for both `$previous` and `$next` before reading or emitting their thumbnail URLs.
* **Security — `canard_entry_meta()` — avatar HTML re-validated after cache read:** The avatar HTML string is stored in and retrieved from the object cache. On sites with a shared persistent cache backend (Redis/Memcached), a poisoned cache entry could supply arbitrary HTML that would be concatenated directly into `$byline`. Cache hits are now passed through `wp_kses()` with the `img` allowlist before use, in addition to the existing `wp_kses()` call on the final composed string.
* **Security (multisite) — object cache keys prefixed with `get_current_blog_id()`:** The cache keys `canard_nav_bg_{post_id}` and `canard_avatar_{hash}_{size}` used a flat namespace. On a WordPress multisite network using a non-blog-specific persistent cache backend (the default for Redis/Memcached), post ID 42 on site 1 and post ID 42 on site 2 shared the same cache entry, potentially serving one site's CSS or avatar HTML to another. Both keys are now prefixed with `get_current_blog_id()`.
* **Bug fix — `canard_category_transient_flusher()` — correct post ID passed to `wp_is_post_revision()`:** The function previously called `get_the_ID()` to obtain the post ID for the revision check. `get_the_ID()` is a Loop function; in the `save_post` admin context the Loop is not running, so it returned `false` or a stale global value, making the revision guard unreliable. The function signature now accepts the `$post_id` argument that WordPress passes to `save_post` callbacks and uses that for the check.

### `inc/custom-header.php`

**`canard_header_style()` refactored to use `wp_add_inline_style()`:** The function was echoing a raw `<style>` tag via the `wp_head` callback. This bypasses WordPress asset management and is incompatible with Content Security Policy headers that use nonce-based `style-src` directives. Refactored to build the CSS string and pass it to `wp_add_inline_style( 'canard-style', $css )`. This matches the pattern already used in `canard_post_nav_background()`.

### `inc/customizer.php`

* **Checkbox sanitization:** Removed the custom `canard_sanitize_checkbox()` function and its `(bool)` cast. The cast is unreliable: the string `"false"` evaluates to `true` in PHP. The setting now uses `wp_validate_boolean()` as its `sanitize_callback`. An explicit `'default' => false` has been added to the setting registration, which was previously absent.
* **CSRF documentation comment added:** Added a developer documentation comment explaining that the Customizer API handles its own nonce verification, but any new AJAX endpoints or form submissions added to the theme must implement `wp_nonce_field()` / `check_ajax_referer()`. This establishes the expectation for future contributors and prevents CSRF vulnerabilities from being introduced inadvertently.

### `comments.php`

* **Security — comment form hardened via `comment_form_default_fields` filter:** Three changes applied inline before `comment_form()`: (1) The URL / website field is removed. It is an unauthenticated free-text field that is a primary spam vector and a stored-XSS surface if any downstream template echoes commenter URLs without `esc_url()`. Canard does not display commenter URLs in any template. (2) The email input type is changed from `type="text"` to `type="email"`, enabling native browser validation. (3) `autocomplete="email"` and `autocomplete="name"` hints are added to the email and author fields so browsers can pre-fill them correctly. WordPress core handles nonce generation and verification for the comment submission form internally; no additional `wp_nonce_field()` call is required.

### `inc/extras.php`

* Updated `@since` tags in `canard_excerpt_more()` and `canard_continue_reading()` from `1.0.3` / `1.0.4` to `2.0.0`.
* Removed `apply_filters( 'the_permalink', get_permalink() )` from `canard_get_link_url()`. The `the_permalink` filter hook was deprecated in WordPress 6.8. Replaced with `get_the_permalink()` directly.
* **Separated function definition from filter registration:** `canard_excerpt_more()` and `canard_continue_reading()` previously guarded the `add_filter()` call inside the `function_exists()` check, making it impossible for a child theme to define the function and still have the filter run on admin pages (e.g., for REST API excerpt generation). The function definition and filter registration are now separate, matching WordPress coding standards.
* **Security — `canard_get_link_url()` — URL scheme validated via `wp_http_validate_url()`:** See entry in Security & Escaping above.
* **Security — `canard_continue_reading()` — `$the_excerpt` sanitised with `wp_kses_post()` before concatenation:** This filter runs at priority 9, before other excerpt filters at higher priorities have finished. The incoming `$the_excerpt` value may contain markup injected by plugins hooked at priorities 1–8. The value is now passed through `wp_kses_post()` before the "Continue reading" link is appended.

### `inc/jetpack.php`

**`canard_jetpack_featured_image_display()` refactored:** Replaced dense ternary chains and nested `isset()` / `array_merge()` patterns with early-return guards and explicitly named variables (`$show_on_post`, `$show_on_page`). Uses the PHP 8 null-coalescing operator (`?? []`) for option reading. Functionally identical.

### HTML5 Semantics & Accessibility

* **Redundant `role` attributes removed:** Removed redundant `role="..."` attributes (e.g., `banner`, `navigation`, `main`, `complementary`) as they are implicit in modern HTML5 elements.

### PHP 8.x Modernisation & Type Safety

**Type hints added:** Parameter and return type hints added to all public/hookable functions:

* `canard_body_classes( array $classes ): array` in `inc/extras.php`
* `canard_excerpt_length( int $length ): int` in `inc/extras.php`
* `canard_continue_reading( string $the_excerpt ): string` in `inc/extras.php`
* `canard_categorized_blog(): bool` in `inc/template-tags.php`
* `canard_google_fonts_url(): string` in `functions.php`
* `canard_resource_hints( array $urls, string $relation_type ): array` in `functions.php`

---

## JavaScript

### jQuery Fully Removed

All five scripts that previously declared jQuery as a dependency have been rewritten in vanilla JavaScript. jQuery is no longer loaded as a front-end dependency on any page.

| File | What changed |
| --- | --- |
| **search.js** | jQuery IIFE and `.hover()` / `.focusin()` / `.focusout()` calls replaced with `addEventListener( 'mouseenter' / 'mouseleave' / 'focus' / 'blur' )`. |
| **featured-content.js** | Rewritten using `querySelectorAll`, `forEach`, `classList`, and `style.backgroundImage`. The `$(window).on('load')` wrapper replaced with `window.addEventListener('load')`. |
| **navigation.js** | Fully rewritten in vanilla JS. Event delegation via `document.addEventListener('click')` replaces jQuery's `.on('click', '.dropdown-toggle')`. `btn.className = 'dropdown-toggle'` replaced with `btn.classList.add('dropdown-toggle')`. |
| **single.js** | Rewritten in vanilla JS. `$('.author-info')`, `.prependTo()`, `.insertAfter()`, `$(window).width()`, and all Jetpack sharedaddy/table DOM operations replaced with `querySelector`, `insertBefore`, `Element.after()`, `window.innerWidth`, and `querySelectorAll().forEach()`. |
| **posts.js** | Rewritten in vanilla JS. `$('.site-main .hentry').each()`, `.hasClass()`, `.find()`, `.css()`, and `$(window).width()` replaced with `querySelectorAll`, `classList.contains`, `style` properties, and `window.innerWidth`. Fixed character encoding corruption (em dashes); renamed shadowed variables. **Bug fix — Jetpack infinite scroll event name corrected:** Replaced deprecated `'post-load'` listener (no longer fired as of Jetpack 9.2) with `'inf_scr_posts_loaded'`. **Bug fix — infinite scroll scoped to latest `.infinite-wrap`:** The `inf_scr_posts_loaded` handler previously passed `document` to `applyPostStyles`, re-walking and re-processing every post on the page on each new batch. The handler now selects only the most recently appended `.infinite-wrap` element and passes that as the scope, limiting work to newly injected posts only. **Bug fix — `applyBackground()` now called immediately; no longer gated on image `load`:** Previously both `applyBackground()` and `setHeight()` were tied to the img `'load'` event. For `loading="lazy"` images outside the initial viewport, that event never fires until the user scrolls — leaving a solid-black `.post-thumbnail` box visible indefinitely. `applyBackground()` is now called unconditionally at registration time using `thumbnail.getAttribute('src')` as a fallback, eliminating the persistent black-box state for all posts regardless of scroll position. When `load` subsequently fires (or for cache hits), `applyBackground()` is called again to upgrade to the higher-resolution `currentSrc` chosen by the browser from the `srcset`. **Bug fix — `setHeight()` now reads `paddingTop` from `getComputedStyle`, replacing a hardcoded constant:** `setHeight()` was computing `.post-thumbnail` height as `entry.offsetHeight - marginSize`, where `marginSize` was hardcoded as `window.innerWidth > 599 ? 60 : 30`. The parent stylesheet overrides the article's `padding-top` to `90px` at the 600px breakpoint, making the desktop value always 30px too small and causing the thumbnail to extend into the text region. The constant has been replaced with `parseInt( getComputedStyle( entry ).paddingTop, 10 )`, which reads the live computed value and remains correct if breakpoint values change. `setHeight()` continues to be deferred until the img `'load'` event (or a rAF for cache hits) to ensure layout has stabilised before `offsetHeight` is read. **Cache-hit fast path guard tightened:** The `thumbnail.complete && thumbnail.currentSrc` check used to detect already-decoded images has been changed to `thumbnail.complete && thumbnail.naturalWidth > 0`. `currentSrc` can be an empty string on cached images before the browser has committed to a srcset candidate; `naturalWidth > 0` is the reliable signal that the image is fully decoded. |

### Global Improvements

* **ES6 refactoring:** Replaced `var` with `const` and `let` throughout all scripts.
* **Strict equality:** Replaced `'undefined' === typeof x` checks with simple truthy/falsy `!x` logic.
* **`className` string manipulation:** Replaced all `.className.indexOf()`, `.className +=`, and `.className.replace()` patterns with `classList.contains()`, `classList.add()`, `classList.remove()`, and `classList.toggle()` throughout all scripts.
* **Event handlers:** Replaced all `button.onclick = function()` assignments with `button.addEventListener( 'click', function() )` for consistency and child-theme extensibility.

### File-Specific Changes

| File | Change |
| --- | --- |
| **utils.js** | **New file.** Shared `debounce` implementation exposed as `window.canardUtils.debounce`. Uses the standard `clearTimeout` / `setTimeout` pattern. Rest parameters (`...args`) replace `[].slice.call( arguments, 0 )`. |
| **single.js** | Absorbed the entry-hero DOM manipulation previously in the inline `<script>` in `entry-script.php`. |
| **customizer.js** | Removed jQuery IIFE; all `$()` selectors replaced with `document.querySelector()` / `document.querySelectorAll()`; `.text()` replaced with `.textContent`; `.addClass()` / `.removeClass()` replaced with `classList`. |
| **customizer.js** | **Security — hex colour validated before `style.color` assignment:** The `header_textcolor` binding previously assigned the `to` value directly to `el.style.color` after only checking for `'blank'`. An attacker who can manipulate the Customizer `postMessage` channel could inject arbitrary CSS values. The value is now validated against a strict hex-colour regex (`/^#[0-9a-fA-F]{3,8}$/`) before assignment; non-matching values are ignored and the colour is cleared. |
| **header.js** | Added null guard for `siteBranding` before accessing `clientHeight`. |
| **sidebar.js** | `button.onclick` replaced with `button.addEventListener( 'click', ... )`. |

### Accessibility & Correctness

* **`js/navigation.js` — Accessible names added to dropdown toggle buttons (WCAG 2.1 SC 4.1.2):** Toggle `<button>` elements were injected with `aria-expanded` but no accessible name, so screen readers announced just "button" with no context. Buttons now derive their label from the parent link text: `aria-label="Toggle [Menu Item] submenu"`. Falls back to `"Toggle submenu"` if no text is available.
* **`js/navigation.js` — Global touchstart handler closes open menus when tapping outside navigation:** Previously, a user on a touch device who opened a submenu then tapped post content would leave the submenu visually "open" (the `focus` class persisting). Added a `document.addEventListener( 'touchstart' )` handler that removes `focus` from all `.main-navigation` items when a tap lands outside `.main-navigation`.
* **`js/featured-content.js` and `js/posts.js` — Use `currentSrc` instead of `src` for background images:** Both scripts were reading `thumbnail.src` to set `background-image: url()`. This ignores the `srcset` attribute and may load a full-resolution image as the CSS background even when a smaller responsive variant has already been fetched. Changed to `thumbnail.currentSrc || thumbnail.src`, which uses the URL the browser already selected from the `srcset` (respecting device pixel ratio and viewport). In `posts.js` this is now applied at image-load time so `currentSrc` is guaranteed to be resolved before the assignment.
* **`js/single.js` — Added synchronous-execution comment to entry-hero block:** The entry-hero DOM rearrangement runs synchronously (without a `DOMContentLoaded` wrapper) to avoid a layout flash (FOUC) on pages with featured images. The reason for this was documented in `entry-script.php` (a PHP file), not in the JS file itself. Added a prominent comment block so JS developers do not inadvertently "fix" the missing wrapper and introduce a visible layout flash.

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
* **`style.css` — Jetpack infinite scroll now hides numbered pagination:** The existing `.infinite-scroll .posts-navigation { display: none; }` rule only matched the old prev/next navigation. Listing templates were standardised to use `the_posts_pagination()` (numbered pages), which outputs `<nav class="navigation pagination">` — a different class that the rule never matched, leaving a visible page-number bar at the bottom of the first page. The two selectors have been merged: `.infinite-scroll .posts-navigation, .infinite-scroll .navigation.pagination { display: none; }`.
* **`style.css` — `.post-thumbnail::before` overlay fixed (`format-image` / `format-gallery` archive posts):** The dark tint pseudo-element on `.post-thumbnail` inside format-image and format-gallery archive articles was missing `position: absolute`. Without it, `left`, `bottom`, and `z-index` are positioning properties that have no effect on `position: static` elements — the overlay was not being rendered as a tint at all. Fixed by adding `position: absolute; inset: 0; z-index: 1` and folding the separate `opacity: 0.3` into the background colour as `rgba(0, 0, 0, 0.3)`, which is both shorter and more explicit. `display: block`, `height: 100%`, and `width: 100%` are superseded by `inset: 0` and removed.
* **`style.css` — Entry text elements given explicit `z-index: 2` inside format-image / format-gallery archive posts:** `.entry-meta`, `.entry-summary`, and `.entry-title` inside format-image and format-gallery archive articles already had `position: relative` (required for stacking context participation), but no explicit `z-index`. Now that `.post-thumbnail::before` is correctly positioned and carries `z-index: 1`, those text elements require `z-index: 2` to render above the overlay. Without it, the overlay paints over the post title, summary, and metadata.

### Modernisation & Cleanup

* **Accessibility:** Replaced deprecated `clip: rect()` with `clip-path: inset(50%)` and `white-space: nowrap` for all `.screen-reader-text` declarations.
* **Legacy prefix removal:** Stripped all `-webkit-box`, `-ms-flexbox`, and `-webkit-transform` prefixes.
* **Normalisation:** Cleaned up the `style.css` normalise block; updated `abbr[title]` to use `underline dotted`.
* **Cleanup:** Removed all `speak: none` declarations and empty ruleset stubs in `blocks.css`.

---

## Files Removed / Added / Renamed

**Deleted:**
* `js/skip-link-focus-fix.js` — no longer required for modern browsers.
* `genericons/` — entire directory. No file in the theme references Genericons any longer. Safe to delete.

**Added:**
* `js/utils.js` — shared `debounce` utility.
* `category.php` — native category archive template. Displays a full-width hero banner at the top of category archive pages, followed by the standard post loop with pagination. Integrated with the same `entry-hero` layout structure used by single posts.
* `docs/CHANGES.md` — this file.
* `docs/category-images.md` — documents how child themes can supply per-category banner images and colours using the `canard_category_header_image` and `canard_category_color` filters, or by overriding the functions entirely.
