# Consolidated Theme Changes

## Summary of PHP Modifications

### Core PHP Standards (Global)

* **Strict Types Removal:** Removed invalid `<?php declare( strict_types = 1 ); ?>` from the first line of all **26** PHP files to ensure compatibility.
* **Security (ABSPATH):** Added `if ( ! defined( 'ABSPATH' ) ) exit;` guards to all template and include files to prevent direct script access.
* **Escaping:** Replaced `_e()` with `esc_html_e()` and wrapped `get_the_title()` / `get_search_query()` in `esc_html()` across all templates for XSS prevention.
* **HTML5 Semantics:** Removed redundant `role="..."` attributes (e.g., `banner`, `navigation`, `main`, `complementary`) as they are implicit in modern HTML5 elements.

### `functions.php`

* **Version Management:** Added `CANARD_VERSION` constant to replace hardcoded "2015" version strings in all enqueues.
* **Google Fonts:** Merged separate font requests into a single `canard_google_fonts_url()` function using the **v2 API (/css2)** with `&display=swap`.
* **HTML5 Support:** Expanded `add_theme_support( 'html5', ... )` to include `script` and `style`.
* **Classic Widgets:** Added `canard_disable_block_widgets()` to maintain the legacy widget interface.
* **Cleanup:** Removed the WordPress.com updater inclusion.

### Template Files (`header.php`, `footer.php`, `comments.php`)

* **header.php:** Added `wp_body_open()`; upgraded XFN profile to HTTPS; added descriptive `aria-label` to navigation elements; removed the legacy pingback link tag.
* **footer.php:** Updated WordPress.org link to HTTPS and moved it outside of translation strings; applied `esc_html__()` to theme credits.
* **comments.php:** Cleaned up escaping and navigation roles.

---

## JavaScript Modernization

### Global JS Improvements

* **jQuery Events:** Replaced all legacy `.load()` and `.resize()` calls with `.on( 'load', ... )` and `.on( 'resize', ... )`.
* **ES6 Refactoring:** Replaced `var` with `const` and `let` throughout all scripts; used `classList.add()` instead of string concatenation for class manipulation.
* **Strict Equality:** Replaced `'undefined' === typeof x` checks with simple truthy/falsy `!x` logic.

### File-Specific Changes

| File | Change Description |
| --- | --- |
| **utils.js** | **NEW FILE:** Contains shared `debounce` implementation exposed as `window.canardUtils.debounce`. |
| **navigation.js** | Integrated shared `debounce`; added comments for `find('div')` intent; strict ES6 refactor. |
| **customizer.js** | Replaced inline styles with `.addClass('screen-reader-text')`; updated `clip-path` logic. |
| **posts.js / single.js** | Fixed character encoding corruption (em dashes); renamed shadowed variables for clarity. |
| **header.js** | Added null guards for `siteBranding` before accessing `clientHeight`. |

---

## CSS & Styling (Fixes & Cleanup)

### Bug Fixes

* **#1 - #3 (editor-blocks.css):** Removed stray backtick; fixed typos (`.wp-block-latest-posts`); updated `.is-wide` to `.is-style-wide`.
* **#4 (rtl.css):** Fixed missing units on `right: 50px`.
* **#5 (style.css):** Replaced placeholder hacks with modern `::placeholder { color: #777; opacity: 1; }`.
* **#6 - #7 (Global):** Implemented keyboard-accessible focus: `:focus:not(:focus-visible) { outline: none; }`.
* **#14 (editor-blocks.css):** Replaced `wp-block-quote__citation` with `wp-block-quote cite` (pullquotes retain the `__citation` class).

### Modernization & Cleanup

* **Accessibility:** Replaced deprecated `clip: rect()` with `clip-path: inset(50%)` and `white-space: nowrap` for all `.screen-reader-text` declarations.
* **Legacy Prefix Removal:** Stripped all legacy `-webkit-box`, `-ms-flexbox`, and `-webkit-transform` prefixes (Issues #10, #11).
* **Normalization:** Cleaned up `style.css` normalize block; updated `abbr[title]` to use `underline dotted` (Issue #8).
* **Cleanup:** Removed all `speak: none` declarations (Issue #12) and empty ruleset stubs in `blocks.css` (Issue #16).

---

## Files Removed / Added

* **Deleted:** `skip-link-focus-fix.js` (no longer required for modern browsers).
* **Added:** `js/utils.js`.

---

## Total Change Metrics

| Category | Files Modified | Impact |
| --- | --- | --- |
| **PHP** | 29 | Security hardening, HTML5 compliance, strict escaping. |
| **JS** | 10 | ES6 modernization, shared utilities, jQuery API updates. |
| **CSS** | 4 | Prefix removal, accessibility fixes, Gutenberg block alignment. |
