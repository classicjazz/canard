<?php
/**
 * Canard functions and definitions
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme version constant used for cache-busting enqueued assets.
 */
define( 'CANARD_VERSION', '2.0.0' );

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 720; /* pixels */
}

if ( ! function_exists( 'canard_content_width' ) ) {
	function canard_content_width() {
		global $content_width;

		if ( is_page() ) {
			$content_width = 869;
		}
	}
}
add_action( 'template_redirect', 'canard_content_width' );

if ( ! function_exists( 'canard_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function canard_setup() {

		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on Canard, use a find and replace
		 * to change 'canard' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'canard', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );
		add_image_size( 'canard-post-thumbnail', 870, 773, true );
		add_image_size( 'canard-featured-content-thumbnail', 915, 500, true );
		add_image_size( 'canard-single-thumbnail', 1920, 768, true );

		// Add support for responsive embeds.
		add_theme_support( 'responsive-embeds' );

		// Add support for custom logo.
		// The editor colour palette is defined in theme.json rather than via
		// add_theme_support( 'editor-color-palette' ), which was deprecated
		// in WordPress 5.9.
		add_theme_support( 'custom-logo', array(
			'width'       => 400,
			'height'      => 90,
			'flex-width'  => true,
			'flex-height' => true,
		) );

		// Register navigation menu locations.
		register_nav_menus(
			array(
				'primary'   => __( 'Primary Location', 'canard' ),
				'secondary' => __( 'Secondary Location', 'canard' ),
				'footer'    => __( 'Footer Location', 'canard' ),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5. Including 'script' and 'style' tells WordPress to
		 * omit type attributes on script and style tags.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'script',
				'style',
			)
		);

		/*
		 * Enable support for Post Formats.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/post-formats/
		 */
		add_theme_support(
			'post-formats',
			array(
				'image',
				'link',
				'gallery',
			)
		);
	}
endif;
add_action( 'after_setup_theme', 'canard_setup' );

/**
 * Disable block-based widgets editor to maintain classic widget interface.
 */
add_filter( 'use_widgets_block_editor', '__return_false' );

/**
 * Register widget areas.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/
 */
function canard_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Sidebar', 'canard' ),
			'id'            => 'sidebar-1',
			'description'   => '',
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( 'Footer', 'canard' ),
			'id'            => 'sidebar-2',
			'description'   => '',
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'canard_widgets_init' );

/**
 * Build a combined Google Fonts v2 URL for Lato, Inconsolata, PT Serif,
 * and Playfair Display. Using a single request reduces HTTP round trips.
 *
 * @return string Google Fonts stylesheet URL, or empty string if all fonts are disabled.
 */
function canard_google_fonts_url() {
	$families = array();

	/* translators: If characters in your language are not supported by Lato, translate this to 'off'. */
	if ( 'off' !== _x( 'on', 'Lato font: on or off', 'canard' ) ) {
		$families[] = 'family=Lato:ital,wght@0,400;0,700;1,400;1,700';
	}

	/* translators: If characters in your language are not supported by Inconsolata, translate this to 'off'. */
	if ( 'off' !== _x( 'on', 'Inconsolata font: on or off', 'canard' ) ) {
		$families[] = 'family=Inconsolata:wght@400;700';
	}

	/* translators: If characters in your language are not supported by PT Serif, translate this to 'off'. */
	if ( 'off' !== _x( 'on', 'PT Serif font: on or off', 'canard' ) ) {
		$families[] = 'family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700';
	}

	/* translators: If characters in your language are not supported by Playfair Display, translate this to 'off'. */
	if ( 'off' !== _x( 'on', 'Playfair Display font: on or off', 'canard' ) ) {
		$families[] = 'family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700';
	}

	if ( empty( $families ) ) {
		return '';
	}

	return 'https://fonts.googleapis.com/css2?' . implode( '&', $families ) . '&display=swap';
}

/**
 * Outputs preconnect resource hints for Google Fonts to improve LCP.
 * Only emitted when Google Fonts are actually in use.
 */
add_action( 'wp_head', function() {
	if ( canard_google_fonts_url() ) {
		echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
		echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	}
}, 1 );

/**
 * Enqueue scripts and styles.
 */
function canard_scripts() {

	// Gutenberg block styles.
	wp_enqueue_style( 'canard-blocks', get_template_directory_uri() . '/blocks.css', array(), CANARD_VERSION );

	// Single Google Fonts request for all typefaces used by the theme.
	$fonts_url = canard_google_fonts_url();
	if ( $fonts_url ) {
		wp_enqueue_style( 'canard-fonts', $fonts_url, array(), null );
	}

	wp_enqueue_style( 'canard-style', get_stylesheet_uri(), array(), CANARD_VERSION );

	// Shared utility functions (debounce). No dependencies — plain JS.
	wp_enqueue_script( 'canard-utils', get_template_directory_uri() . '/js/utils.js', array(), CANARD_VERSION, true );

	wp_enqueue_script( 'canard-navigation', get_template_directory_uri() . '/js/navigation.js', array( 'canard-utils' ), CANARD_VERSION, true );

	wp_enqueue_script( 'canard-featured-content', get_template_directory_uri() . '/js/featured-content.js', array(), CANARD_VERSION, true );

	wp_enqueue_script( 'canard-header', get_template_directory_uri() . '/js/header.js', array(), CANARD_VERSION, true );

	wp_enqueue_script( 'canard-search', get_template_directory_uri() . '/js/search.js', array(), CANARD_VERSION, true );

	if ( is_singular() ) {
		wp_enqueue_script( 'canard-single', get_template_directory_uri() . '/js/single.js', array( 'canard-utils' ), CANARD_VERSION, true );
	}

	if ( is_active_sidebar( 'sidebar-1' ) ) {
		wp_enqueue_script( 'canard-sidebar', get_template_directory_uri() . '/js/sidebar.js', array(), CANARD_VERSION, true );
	}

	if ( is_home() || is_archive() || is_search() ) {
		wp_enqueue_script( 'canard-posts', get_template_directory_uri() . '/js/posts.js', array( 'canard-utils' ), CANARD_VERSION, true );
	}

	// canard-skip-link-focus-fix removed — the WebKit/Opera/IE hashchange focus
	// fix it provided has been unnecessary since ~2016. IE is unsupported since
	// WP 5.8 and Opera as a distinct engine has not existed since 2013.
	// Native browser hash navigation handles skip-link focus correctly today.

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'canard_scripts' );

/**
 * Enqueue styles for the block editor.
 */
function canard_editor_styles() {
	wp_enqueue_style( 'canard-block-style', get_template_directory_uri() . '/blocks.css', array(), CANARD_VERSION );
	wp_enqueue_style( 'canard-editor-block-style', get_template_directory_uri() . '/editor-blocks.css', array(), CANARD_VERSION );

	$fonts_url = canard_google_fonts_url();
	if ( $fonts_url ) {
		wp_enqueue_style( 'canard-fonts', $fonts_url, array(), null );
	}
}
add_action( 'enqueue_block_editor_assets', 'canard_editor_styles' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';

/**
 * Register the entry-hero body class filter.
 *
 * canard_entry_hero_body_class() is defined in entry-script.php, which is
 * loaded via get_template_part() inside the Loop. To avoid registering the
 * callback on every loop iteration (which would add it multiple times on
 * archive pages), we load the file here once at theme setup — purely for the
 * function definition — and call add_filter() a single time.
 */
require_once get_template_directory() . '/entry-script.php';
add_filter( 'body_class', 'canard_entry_hero_body_class' );

/**
 * -------------------------------------------------------------------------
 * Category Header Image
 * -------------------------------------------------------------------------
 *
 * canard_get_category_header_image() returns the URL of the banner image for
 * the current category archive, or false if none is configured.
 *
 * By default the function returns false so that category.php falls back to a
 * plain colour block (see canard_get_category_color() below).
 *
 * CHILD THEME OVERRIDE — use the canard_category_header_image filter:
 *
 *   add_filter( 'canard_category_header_image', function( $url ) {
 *     $cat   = get_queried_object();
 *     $slug  = $cat ? $cat->slug : '';
 *     $map   = array( 'travel' => 'travel.webp', ... );
 *     if ( isset( $map[ $slug ] ) ) {
 *       return get_stylesheet_directory_uri() . '/images/categories/' . $map[ $slug ];
 *     }
 *     return $url; // return the received value (false) to keep the colour fallback
 *   } );
 *
 * Always return the received $url value (not a hardcoded false) for slugs with
 * no match, so the filter chain and colour fallback continue to work correctly.
 *
 * See docs/category-images.md for full documentation.
 *
 * @return string|false Image URL, or false to trigger the colour fallback.
 */
if ( ! function_exists( 'canard_get_category_header_image' ) ) {
	function canard_get_category_header_image() {
		/**
		 * Filters the category header image URL.
		 *
		 * Return a URL string to show an image banner, or false/empty to fall
		 * back to the solid colour block defined by canard_get_category_color().
		 *
		 * @param string|false $url Image URL or false.
		 */
		return apply_filters( 'canard_category_header_image', false );
	}
}

/**
 * Returns the solid-colour fallback used in the category header when no image
 * is available.
 *
 * Defaults to the theme accent colour (#d11415). Child themes can override
 * this function or add a filter:
 *
 *   add_filter( 'canard_category_color', function( $color ) {
 *     $map = array( 'travel' => '#1a6eb5', 'food' => '#e07b29' );
 *     $cat = get_queried_object();
 *     return $map[ $cat->slug ] ?? $color;
 *   } );
 *
 * @return string A valid CSS colour value (hex, rgb, etc.).
 */
if ( ! function_exists( 'canard_get_category_color' ) ) {
	function canard_get_category_color() {
		// Read the accent colour from theme.json so the category header stays
		// in sync if the palette is ever updated (WordPress 5.9+).
		$default = '#d11415';
		if ( function_exists( 'wp_get_global_settings' ) ) {
			$palette = wp_get_global_settings( array( 'color', 'palette', 'theme' ) );
			if ( is_array( $palette ) ) {
				foreach ( $palette as $entry ) {
					if ( isset( $entry['slug'], $entry['color'] ) && 'red' === $entry['slug'] ) {
						$default = $entry['color'];
						break;
					}
				}
			}
		}

		/**
		 * Filters the category header fallback colour.
		 *
		 * Defaults to the 'red' colour defined in theme.json (currently #d11415).
		 * Falls back to the hardcoded hex if wp_get_global_settings() is unavailable.
		 *
		 * @param string $color CSS colour value.
		 */
		return apply_filters( 'canard_category_color', $default );
	}
}
