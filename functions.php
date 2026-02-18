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
		add_theme_support( 'custom-logo', array(
			'width'       => 400,
			'height'      => 90,
			'flex-width'  => true,
			'flex-height' => true,
		) );

		// Register editor color palette.
		add_theme_support(
			'editor-color-palette',
			array(
				array(
					'name'  => esc_html__( 'Black', 'canard' ),
					'slug'  => 'black',
					'color' => '#222222',
				),
				array(
					'name'  => esc_html__( 'Dark Gray', 'canard' ),
					'slug'  => 'dark-gray',
					'color' => '#555555',
				),
				array(
					'name'  => esc_html__( 'Medium Gray', 'canard' ),
					'slug'  => 'medium-gray',
					'color' => '#777777',
				),
				array(
					'name'  => esc_html__( 'Light Gray', 'canard' ),
					'slug'  => 'light-gray',
					'color' => '#dddddd',
				),
				array(
					'name'  => esc_html__( 'White', 'canard' ),
					'slug'  => 'white',
					'color' => '#ffffff',
				),
				array(
					'name'  => esc_html__( 'Red', 'canard' ),
					'slug'  => 'red',
					'color' => '#d11415',
				),
			)
		);

		// Register navigation menu locations.
		register_nav_menus(
			array(
				'primary'   => __( 'Primary Location', 'canard' ),
				'secondary' => __( 'Secondary Location', 'canard' ),
				'footer'    => __( 'Footer Location', 'canard' ),
				'social'    => __( 'Social Location', 'canard' ),
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
function canard_disable_block_widgets() {
	remove_theme_support( 'widgets-block-editor' );
}
add_action( 'after_setup_theme', 'canard_disable_block_widgets' );

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
 * Enqueue scripts and styles.
 */
function canard_scripts() {

	// Gutenberg block styles.
	wp_enqueue_style( 'canard-blocks', get_template_directory_uri() . '/blocks.css', array(), CANARD_VERSION );

	wp_enqueue_style( 'genericons', get_template_directory_uri() . '/genericons/genericons.css', array(), '3.3' );

	// Single Google Fonts request for all typefaces used by the theme.
	$fonts_url = canard_google_fonts_url();
	if ( $fonts_url ) {
		wp_enqueue_style( 'canard-fonts', $fonts_url, array(), null );
	}

	wp_enqueue_style( 'canard-style', get_stylesheet_uri(), array(), CANARD_VERSION );

	// Shared utility functions (debounce). No dependencies — plain JS.
	wp_enqueue_script( 'canard-utils', get_template_directory_uri() . '/js/utils.js', array(), CANARD_VERSION, true );

	wp_enqueue_script( 'canard-navigation', get_template_directory_uri() . '/js/navigation.js', array( 'jquery', 'canard-utils' ), CANARD_VERSION, true );

	wp_enqueue_script( 'canard-featured-content', get_template_directory_uri() . '/js/featured-content.js', array( 'jquery' ), CANARD_VERSION, true );

	wp_enqueue_script( 'canard-header', get_template_directory_uri() . '/js/header.js', array(), CANARD_VERSION, true );

	wp_enqueue_script( 'canard-search', get_template_directory_uri() . '/js/search.js', array( 'jquery' ), CANARD_VERSION, true );

	if ( is_singular() ) {
		wp_enqueue_script( 'canard-single', get_template_directory_uri() . '/js/single.js', array( 'jquery', 'canard-utils' ), CANARD_VERSION, true );
	}

	if ( is_active_sidebar( 'sidebar-1' ) ) {
		wp_enqueue_script( 'canard-sidebar', get_template_directory_uri() . '/js/sidebar.js', array(), CANARD_VERSION, true );
	}

	if ( is_home() || is_archive() || is_search() ) {
		wp_enqueue_script( 'canard-posts', get_template_directory_uri() . '/js/posts.js', array( 'jquery', 'canard-utils' ), CANARD_VERSION, true );
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

	wp_enqueue_style( 'genericons', get_template_directory_uri() . '/genericons/genericons.css', array(), '3.3' );
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
