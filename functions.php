<?php
/**
 * Canard functions and definitions
 *
 * @package Canard
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme version constant used for cache-busting enqueued assets.
 *
 * @var string CANARD_VERSION Semantic version string of the active theme build.
 */
define( 'CANARD_VERSION', '3.1.0' );

/**
 * Maximum pixel width of the main content column.
 *
 * Set once at theme load. canard_content_width() widens it to 869 on page
 * templates, which have no sidebar. Downstream consumers (e.g. oEmbed) read
 * this global to constrain embedded media dimensions.
 *
 * @var int $content_width
 */
$content_width ??= 720; /* pixels */

if ( ! function_exists( 'canard_content_width' ) ) {
	/**
	 * Widens $content_width for full-width page templates.
	 *
	 * The global defaults to 720 px at theme load. Page templates render
	 * without a sidebar, so 869 px more accurately reflects the actual
	 * container width and ensures media embeds are sized correctly.
	 * Hooked to template_redirect so the conditional template tags
	 * (is_page(), etc.) are reliable when this runs.
	 *
	 * @global int $content_width Maximum pixel width of the main content column.
	 * @return void
	 */
	function canard_content_width() {
		global $content_width;

		if ( is_page() ) {
			$content_width = 869;
		}
	}
}
add_action( 'template_redirect', 'canard_content_width' );

if ( ! function_exists( 'canard_setup' ) ) {
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Hooked to after_setup_theme, which runs before init. This is intentional:
	 * features such as post-thumbnails must be declared before init fires.
	 *
	 * @return void
	 */
	function canard_setup() {

		/*
		 * Make the theme available for translation.
		 * Translation files should be placed in the /languages/ directory.
		 * When building a child theme, replace 'canard' with the child theme's
		 * text domain in all template files.
		 */
		load_theme_textdomain( 'canard', get_template_directory() . '/languages' );

		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * Declaring this support signals that the theme does not hard-code a
		 * <title> tag, and WordPress will provide the correct title element.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for post thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );
		add_image_size( 'canard-post-thumbnail', 870, 773, true );
		add_image_size( 'canard-featured-content-thumbnail', 915, 500, true );
		add_image_size( 'canard-single-thumbnail', 1920, 768, true );

		add_theme_support( 'responsive-embeds' );

		/*
		 * Enable wide and full-width block alignment.
		 * Without this, wide/full-width blocks break silently in the editor.
		 */
		add_theme_support( 'align-wide' );

		/*
		 * Opt in to opinionated core block styles (borders, spacing defaults).
		 * Required for full WP 6.9 block compatibility.
		 */
		add_theme_support( 'wp-block-styles' );

		add_theme_support( 'custom-logo', [
			'width'       => 400,
			'height'      => 90,
			'flex-width'  => true,
			'flex-height' => true,
		] );

		register_nav_menus(
			[
				'primary'   => __( 'Primary Location', 'canard' ),
				'secondary' => __( 'Secondary Location', 'canard' ),
				'footer'    => __( 'Footer Location', 'canard' ),
			]
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5. Including 'script' and 'style' instructs
		 * WordPress to omit type attributes on those tags.
		 */
		add_theme_support(
			'html5',
			[
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'script',
				'style',
				'navigation-widgets',
			]
		);

		// Enables smoother widget previews in the Customizer.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/*
		 * Enable support for post formats.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/post-formats/
		 */
		add_theme_support(
			'post-formats',
			[
				'image',
				'link',
				'gallery',
			]
		);
	}
}
add_action( 'after_setup_theme', 'canard_setup' );

/**
 * Disables the block-based widgets editor to maintain the classic widget interface.
 */
add_filter( 'use_widgets_block_editor', '__return_false' );

if ( ! function_exists( 'canard_widgets_init' ) ) {
	/**
	 * Registers the primary sidebar (sidebar-1) and the footer widget area (sidebar-2).
	 *
	 * Both sidebars use <aside> as the widget wrapper and <h2> for widget titles,
	 * matching the semantic markup expected by the theme templates.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/sidebars/
	 * @return void
	 */
	function canard_widgets_init() {
		$sidebar_defaults = [
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		];

		register_sidebar( array_merge( $sidebar_defaults, [
			'name' => __( 'Sidebar', 'canard' ),
			'id'   => 'sidebar-1',
		] ) );

		register_sidebar( array_merge( $sidebar_defaults, [
			'name' => __( 'Footer', 'canard' ),
			'id'   => 'sidebar-2',
		] ) );
	}
}
add_action( 'widgets_init', 'canard_widgets_init' );

if ( ! function_exists( 'canard_google_fonts_url' ) ) {
	/**
	 * Builds a combined Google Fonts v2 URL for Lato, Inconsolata, PT Serif,
	 * and Playfair Display.
	 *
	 * Combines all enabled typefaces into a single stylesheet request to reduce
	 * HTTP round trips. Results are stored in the object cache for one day to
	 * avoid rebuilding the URL on every request.
	 *
	 * @return string Google Fonts stylesheet URL, or an empty string if all fonts are disabled.
	 */
	function canard_google_fonts_url(): string {
		$cache_key   = 'canard_google_fonts_url';
		$cache_group = 'canard_theme';
		$cached      = wp_cache_get( $cache_key, $cache_group );
		if ( false !== $cached ) {
			return (string) $cached;
		}

		/**
		 * Collected Google Fonts v2 family query strings.
		 *
		 * @var array<int, string> $families
		 */
		$families = [];

		/* Translators: If characters in your language are not supported by Lato, translate this to 'off'. */
		if ( 'off' !== _x( 'on', 'Lato font: on or off', 'canard' ) ) {
			$families[] = 'family=Lato:ital,wght@0,400;0,700;1,400;1,700';
		}

		/* Translators: If characters in your language are not supported by Inconsolata, translate this to 'off'. */
		if ( 'off' !== _x( 'on', 'Inconsolata font: on or off', 'canard' ) ) {
			$families[] = 'family=Inconsolata:wght@400;700';
		}

		/* Translators: If characters in your language are not supported by PT Serif, translate this to 'off'. */
		if ( 'off' !== _x( 'on', 'PT Serif font: on or off', 'canard' ) ) {
			$families[] = 'family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700';
		}

		/* Translators: If characters in your language are not supported by Playfair Display, translate this to 'off'. */
		if ( 'off' !== _x( 'on', 'Playfair Display font: on or off', 'canard' ) ) {
			$families[] = 'family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700';
		}

		if ( empty( $families ) ) {
			wp_cache_set( $cache_key, '', $cache_group, DAY_IN_SECONDS );
			return '';
		}

		$url = 'https://fonts.googleapis.com/css2?' . implode( '&', $families ) . '&display=swap';

		wp_cache_set( $cache_key, $url, $cache_group, DAY_IN_SECONDS );
		return $url;
	}
}

/**
 * Adds preconnect hints for Google Fonts and a DNS-prefetch hint for Gravatar.
 *
 * Uses wp_preconnect_resources (WP 6.7+) for preconnect hints and retains the
 * wp_resource_hints filter for dns-prefetch, which has no dedicated replacement
 * hook as of WP 6.9. The wp_resource_hints filter was soft-deprecated for
 * 'preconnect' only in WP 6.7; 'dns-prefetch' continues to rely on it.
 *
 * @param string[] $urls          Existing resource-hint URLs for 'dns-prefetch'.
 * @param string   $relation_type The hint relation type being processed.
 * @return string[] Filtered URLs with theme additions appended.
 */
function canard_resource_hints( array $urls, string $relation_type ): array {
	// Prefetch Gravatar DNS early so avatar requests on archive pages do not
	// stall on DNS resolution. This carries no additional privacy cost beyond
	// what get_avatar() already incurs.
	if ( 'dns-prefetch' === $relation_type && ! is_admin() ) {
		$urls[] = 'https://secure.gravatar.com';
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'canard_resource_hints', 10, 2 );

/**
 * Appends preconnect resource hints for Google Fonts origins.
 *
 * Hooked to wp_preconnect_resources (WP 6.7+), which passes the accumulated
 * hints array and the relation type string — the same signature as the
 * wp_resource_hints filter. Hints are only added when Google Fonts are active.
 *
 * fonts.googleapis.com serves the CSS stylesheet and requires a plain
 * preconnect. fonts.gstatic.com serves the font binaries, which are
 * cross-origin, so crossorigin="anonymous" (the $crossorigin = true flag)
 * is required for the browser to reuse the connection.
 *
 * @param array<int, string|array<string, string>> $urls          Accumulated preconnect URLs.
 * @param string                                   $relation_type The hint relation type being processed.
 * @return array<int, string|array<string, string>> Filtered URLs with Google Fonts origins appended.
 */
function canard_preconnect_hints( array $urls, string $relation_type ): array {
	if ( 'preconnect' !== $relation_type || ! canard_google_fonts_url() ) {
		return $urls;
	}

	$urls[] = [ 'href' => 'https://fonts.googleapis.com' ];
	$urls[] = [ 'href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous' ];

	return $urls;
}
add_filter( 'wp_preconnect_resources', 'canard_preconnect_hints', 10, 2 );

/**
 * Enqueues all front-end CSS and JavaScript assets for the theme.
 *
 * Conditional loading ensures scripts and styles are only sent to pages where
 * they are needed: featured-content only on the front page, sidebar.js only
 * when sidebar-1 is active, single.js only on singular views, and so on.
 * All scripts target WP 6.3+ deferred loading via the strategy API.
 *
 * @return void
 */
function canard_scripts() {

	// Block styles are only needed on singular posts/pages and the front page
	// (where the featured-content carousel may contain block markup).
	// Archives, search results, and other listing pages do not render block HTML.
	if ( is_singular() || is_front_page() ) {
		wp_enqueue_style( 'canard-blocks', get_template_directory_uri() . '/blocks.css', [], CANARD_VERSION );
	}

	// Single Google Fonts request for all typefaces used by the theme.
	$fonts_url = canard_google_fonts_url();
	if ( $fonts_url ) {
		wp_enqueue_style( 'canard-fonts', $fonts_url, [], null );
	}

	// Main stylesheet.
	wp_enqueue_style( 'canard-style', get_template_directory_uri() . '/style.css', [], CANARD_VERSION );

	// Comment styles are co-located in style.css to keep all layout concerns in a single stylesheet.
	// Shared utility functions (debounce). No dependencies — plain JS.
	// Uses the native WP 6.3+ strategy API (targeting WP 6.9+), which handles
	// dependency ordering correctly and avoids string manipulation on script tags.
	wp_enqueue_script(
		'canard-utils',
		get_template_directory_uri() . '/js/utils.js',
		[],
		CANARD_VERSION,
		[
			'in_footer' => true,
			'strategy'  => 'defer',
		]
	);

	wp_enqueue_script(
		'canard-navigation',
		get_template_directory_uri() . '/js/navigation.js',
		[ 'canard-utils' ],
		CANARD_VERSION,
		[
			'in_footer' => true,
			'strategy'  => 'defer',
		]
	);

	// Only enqueue the featured-content script on the front page where it is used.
	if ( is_front_page() ) {
		wp_enqueue_script(
			'canard-featured-content',
			get_template_directory_uri() . '/js/featured-content.js',
			[],
			CANARD_VERSION,
			[
				'in_footer' => true,
				'strategy'  => 'defer',
			]
		);
	}

	wp_enqueue_script(
		'canard-header',
		get_template_directory_uri() . '/js/header.js',
		[ 'canard-utils' ],
		CANARD_VERSION,
		[
			'in_footer' => true,
			'strategy'  => 'defer',
		]
	);

	wp_enqueue_script(
		'canard-search',
		get_template_directory_uri() . '/js/search.js',
		[],
		CANARD_VERSION,
		[
			'in_footer' => true,
			'strategy'  => 'defer',
		]
	);

	if ( is_singular() ) {
		// No defer — must run synchronously to prevent entry-hero layout flash before first paint.
		wp_enqueue_script(
			'canard-single',
			get_template_directory_uri() . '/js/single.js',
			[ 'canard-utils' ],
			CANARD_VERSION,
			[ 'in_footer' => true ]
		);
	}

	if ( is_active_sidebar( 'sidebar-1' ) ) {
		wp_enqueue_script(
			'canard-sidebar',
			get_template_directory_uri() . '/js/sidebar.js',
			[],
			CANARD_VERSION,
			[
				'in_footer' => true,
				'strategy'  => 'defer',
			]
		);
	}

	if ( is_home() || is_archive() || is_search() ) {
		wp_enqueue_script(
			'canard-posts',
			get_template_directory_uri() . '/js/posts.js',
			[ 'canard-utils' ],
			CANARD_VERSION,
			[
				'in_footer' => true,
				'strategy'  => 'defer',
			]
		);
	}

	if ( is_singular() && comments_open() && '1' === get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'canard_scripts' );

/**
 * Registers editor styles using the preferred add_editor_style() API.
 *
 * Runs at priority 11 so it fires after canard_setup() at priority 10,
 * ensuring add_theme_support( 'editor-styles' ) is declared after full
 * theme setup has completed.
 *
 * @return void
 */
function canard_editor_styles() {
	add_theme_support( 'editor-styles' );
	add_editor_style( 'blocks.css' );
	add_editor_style( 'editor-blocks.css' );

	$fonts_url = canard_google_fonts_url();
	if ( $fonts_url ) {
		add_editor_style( $fonts_url );
	}
}
add_action( 'after_setup_theme', 'canard_editor_styles', 11 );

/**
 * Loads the custom header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Loads custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Loads custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Loads Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Loads the Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';

/**
 * Loads the entry-hero body class filter exactly once.
 *
 * canard_entry_hero_body_class() is defined in entry-script.php, which is
 * also loaded via get_template_part() inside the Loop. Requiring it here
 * once at theme setup — purely for the function definition — and calling
 * add_filter() a single time prevents the callback from being registered
 * on every loop iteration on archive pages.
 */
require_once get_template_directory() . '/entry-script.php';
add_filter( 'body_class', 'canard_entry_hero_body_class' );

/**
 * WordPress outputs the taxonomy term description through the_archive_description()
 * without applying wp_kses_post(). Users with the manage_categories capability can
 * store arbitrary HTML (including <script> tags) in the description field. This
 * filter sanitizes all output from get_the_archive_description() to a safe HTML
 * subset before it is printed by archive.php and category.php.
 *
 * Note: archive.php and category.php also call wp_kses_post() directly at the
 * point of echo for defense in depth. This filter covers any other template or
 * plugin that calls the_archive_description() or get_the_archive_description()
 * without its own sanitization step.
 */
add_filter( 'get_the_archive_description', 'wp_kses_post' );

/**
 * canard_get_category_header_image() returns the URL of the banner image for
 * the current category archive, or false if none is configured.
 *
 * By default the function returns false so that category.php falls back to a
 * plain color block (see canard_get_category_color() below).
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
 *     return $url; // return the received value (false) to keep the color fallback
 *   } );
 *
 * Always return the received $url value (not a hard-coded false) for slugs with
 * no match, so the filter chain and color fallback continue to work correctly.
 *
 * See docs/category-images.md for full documentation.
 *
 * @return string|false Image URL, or false to trigger the color fallback.
 */
if ( ! function_exists( 'canard_get_category_header_image' ) ) {
	/**
	 * Returns the category header image URL for the current archive page.
	 *
	 * Applies the canard_category_header_image filter so child themes can
	 * supply a URL without replacing this function. Returns false by default,
	 * causing category.php to render the solid-color fallback instead.
	 *
	 * @return string|false Image URL string, or false when no image is configured.
	 */
	function canard_get_category_header_image() {
		/**
		 * Filters the category header image URL.
		 *
		 * Return a URL string to display an image banner, or false/empty to fall
		 * back to the solid color block defined by canard_get_category_color().
		 *
		 * @param string|false $url Image URL or false.
		 */
		return apply_filters( 'canard_category_header_image', false );
	}
}

/**
 * Returns the solid-color CSS fallback used in the category header when no image is available.
 * Defaults to the theme accent color (#d11415). Child themes can override
 * this function or use the canard_category_color filter:
 *
 *   add_filter( 'canard_category_color', function( $color ) {
 *     $map = array( 'travel' => '#1a6eb5', 'food' => '#e07b29' );
 *     $cat = get_queried_object();
 *     return $map[ $cat->slug ] ?? $color;
 *   } );
 *
 * @return string A valid CSS color value (hex, rgb, etc.).
 */
if ( ! function_exists( 'canard_get_category_color' ) ) {
	/**
	 * Returns the CSS color for the category header fallback block.
	 * Applies the canard_category_color filter so child themes can supply
	 * per-category colors without replacing the function. Defaults to the
	 * theme accent color #d11415.
	 *
	 * @return string A valid CSS color value (e.g. '#d11415').
	 */
	function canard_get_category_color() {
		/**
		 * Filters the category header fallback color.
		 * Defaults to the theme accent color (#d11415). Child themes can
		 * override this value without replacing the function.
		 *
		 * @param string $color CSS color value.
		 */
		return apply_filters( 'canard_category_color', '#d11415' );
	}
}
