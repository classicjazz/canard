# Exact Changes Made - Quick Reference

## Files Modified: 30 Total

### PHP Files: 26 files
**Change:** Removed invalid `<?php declare( strict_types = 1 ); ?>` from line 1

**Root Level (20 files):**
- 404.php
- archive.php
- author-bio.php
- comments.php
- content-featured-post.php
- content-link.php
- content-none.php
- content-page.php
- content-single.php
- content.php
- entry-script.php
- featured-content.php
- footer.php
- functions.php *(also has additional changes below)*
- header.php
- index.php
- page.php
- search.php
- sidebar-footer.php
- sidebar.php
- single.php

**inc/ Directory (7 files):**
- inc/custom-header.php
- inc/customizer.php
- inc/extras.php
- inc/jetpack-fonts.php
- inc/jetpack.php
- inc/template-tags.php
- inc/updater.php

---

## functions.php - 3 Specific Changes

### Change 1: Google Fonts - Lato/Inconsolata (Line ~208-212)
```php
// BEFORE:
$query_args = array(
    'family' => urlencode( implode( '|', $font_families ) ),
    'subset' => urlencode( 'latin,latin-ext' ),
);

// AFTER:
$query_args = array(
    'family'  => urlencode( implode( '|', $font_families ) ),
    'subset'  => urlencode( 'latin,latin-ext' ),
    'display' => 'swap',  // ← ADDED
);
```

### Change 2: Google Fonts - PT Serif/Playfair (Line ~247-251)
```php
// BEFORE:
$query_args = array(
    'family' => urlencode( implode( '|', $font_families ) ),
    'subset' => urlencode( 'cyrillic,latin,latin-ext' ),
);

// AFTER:
$query_args = array(
    'family'  => urlencode( implode( '|', $font_families ) ),
    'subset'  => urlencode( 'cyrillic,latin,latin-ext' ),
    'display' => 'swap',  // ← ADDED
);
```

### Change 3: Classic Widgets Support (After line 145)
```php
// ADDED:
/**
 * Disable block-based widgets editor to maintain classic widget interface.
 */
function canard_disable_block_widgets() {
    remove_theme_support( 'widgets-block-editor' );
}
add_action( 'after_setup_theme', 'canard_disable_block_widgets' );
```

---

## js/navigation.js - 3 Changes

### Change 1: Line 36
```javascript
// BEFORE:
$( window ).load( menuDropdownToggle ).resize( debounce( menuDropdownToggle, 500 ) );

// AFTER:
$( window ).on( 'load', menuDropdownToggle ).on( 'resize', debounce( menuDropdownToggle, 500 ) );
```

### Change 2: Line 38
```javascript
// BEFORE:
$( window ).load( function() {

// AFTER:
$( window ).on( 'load', function() {
```

### Change 3: Line 44
```javascript
// BEFORE:
$( '.dropdown-toggle' ).click( function( event ) {

// AFTER:
$( '.dropdown-toggle' ).on( 'click', function( event ) {
```

---

## style.css - 2 Changes

### Change 1: Line 2349
```css
/* BEFORE: */
z-index: -1

/* AFTER: */
z-index: -1;
```

### Change 2: Line 2802
```css
/* BEFORE: */
outline-color: rgba(255, 255, 255, 0.7)

/* AFTER: */
outline-color: rgba(255, 255, 255, 0.7);
```

---

## blocks.css - 1 Change

### Line 378
```css
/* BEFORE: */
.has-white-color:hover,
.has-white-color:focus,
,.has-white-color:active {

/* AFTER: */
.has-white-color:hover,
.has-white-color:focus,
.has-white-color:active {
```
*(Removed leading comma)*

---

readme.txt (cut irrelevant changelog)

---

## Files NOT Modified

All other files remain **100% original**:
- ✅ rtl.css (unchanged)
- ✅ editor-blocks.css (unchanged)
- ✅ genericons/genericons.css (unchanged)
- ✅ All other .js files (unchanged)
- ✅ All language files (unchanged)
- ✅ All images and fonts (unchanged)
- ✅ LICENSE (unchanged)
- ✅ screenshot.png (unchanged)

---

## Total Line Changes

| File | Lines Removed | Lines Added | Net Change |
|------|--------------|-------------|------------|
| **PHP files (26)** | 26 | 0 | -26 lines |
| **functions.php** | 2 lines | 11 lines | +9 lines |
| **navigation.js** | 3 lines | 3 lines | 0 lines |
| **style.css** | 2 lines | 2 lines | 0 lines |
| **blocks.css** | 1 line | 1 line | 0 lines |
| **TOTAL** | 34 lines | 17 lines | **-17 lines** |
