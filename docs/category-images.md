# Category Header Images

Category archive pages in Canard display a full-width hero banner at the top of the page. Out of the box, the theme uses a solid colour block (the theme accent red, `#d11415`) as the banner. Child themes can replace this with custom images and/or per-category colours.

---

## How it works

`category.php` calls two functions defined in `functions.php`:

| Function | Returns | Used when |
|---|---|---|
| `canard_get_category_header_image()` | Image URL or `false` | First choice — shows an `<img>` tag |
| `canard_get_category_color()` | CSS colour string | Fallback — shows a solid colour block |

Both functions expose a WordPress filter so child themes can customise behaviour without touching parent theme files.

---

## Adding category images in a child theme

### Step 1 — Add your images

Place banner images in your child theme under:

```
your-child-theme/
└── images/
    └── categories/
        ├── category-travel.webp
        ├── category-food.webp
        └── ...
```

**Recommended image specs:**
- **Format:** WebP (preferred) or JPEG
- **Dimensions:** 1600 × 420 px minimum (displayed at up to 420 px tall, full viewport width)
- **File size:** Aim for under 150 KB — images are loaded on every page view for that category

---

### Step 2 — Register the images via filter

Add the following to your child theme's `functions.php`. The `canard_category_header_image` filter receives `false` by default; return a URL string to enable the image, or pass `false` through to keep the colour fallback.

```php
add_filter( 'canard_category_header_image', function( $url ) {
    $cat  = get_queried_object();
    $slug = ( $cat && isset( $cat->slug ) ) ? $cat->slug : '';

    // Map category slugs to image filenames in your child theme.
    $images = array(
        'travel'      => 'category-travel.webp',
        'food'        => 'category-food.webp',
        'technology'  => 'category-tech.webp',
        'photography' => 'category-photography.webp',
        // Add more slugs as needed.
    );

    if ( ! empty( $slug ) && array_key_exists( $slug, $images ) ) {
        return get_stylesheet_directory_uri() . '/images/categories/' . $images[ $slug ];
    }

    // Return $url (false) to fall back to the colour block.
    return $url;
} );
```

> **Tip:** Multiple slugs can point to the same image file — useful for grouping related categories under a shared visual style.

---

## Customising the fallback colour

When no image is configured for a category, `category.php` renders a solid colour block. The default is `#d11415` (the Canard accent red).

### Global override

Change the colour for all categories without images:

```php
add_filter( 'canard_category_color', function( $color ) {
    return '#1a6eb5'; // your preferred default
} );
```

### Per-category colours

Map individual slugs to distinct colours:

```php
add_filter( 'canard_category_color', function( $color ) {
    $cat  = get_queried_object();
    $slug = ( $cat && isset( $cat->slug ) ) ? $cat->slug : '';

    $palette = array(
        'travel'      => '#1a6eb5',
        'food'        => '#e07b29',
        'technology'  => '#2a9d8f',
        'photography' => '#6a4c93',
    );

    return $palette[ $slug ] ?? $color; // fall back to the theme default
} );
```

---

## Using both images and per-category colours together

You can combine both filters — images take priority, and the colour is only shown for categories with no image mapped:

```php
// images/categories/ → category images
add_filter( 'canard_category_header_image', function( $url ) {
    $slug   = get_queried_object()->slug ?? '';
    $images = array( 'travel' => 'category-travel.webp' );
    if ( isset( $images[ $slug ] ) ) {
        return get_stylesheet_directory_uri() . '/images/categories/' . $images[ $slug ];
    }
    return $url;
} );

// Per-category accent colours for everything else
add_filter( 'canard_category_color', function( $color ) {
    $slug    = get_queried_object()->slug ?? '';
    $palette = array( 'food' => '#e07b29', 'technology' => '#2a9d8f' );
    return $palette[ $slug ] ?? $color;
} );
```

---

## Overriding the function entirely (advanced)

If you need complete control over the image-resolution logic, you can replace the whole function in your child theme's `functions.php`. Because the parent theme wraps the function in `if ( ! function_exists() )`, declaring it first in your child theme takes precedence.

```php
// child theme functions.php — loaded before the parent theme
function canard_get_category_header_image() {
    // Your custom logic here.
    // Must return a URL string or false.
}
```

This approach bypasses the `canard_category_header_image` filter, so only use it when the filter-based approach is insufficient.
