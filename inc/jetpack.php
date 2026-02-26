<?php
/**
 * Jetpack Compatibility File
 *
 * @link https://jetpack.com/
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers Jetpack theme features.
 *
 * @return void
 */
function canard_jetpack_setup() {
	// Add theme support for Infinite Scroll.
	add_theme_support( 'infinite-scroll', array(
		'container'      => 'main',
		'footer'         => 'page',
		'footer_widgets' => array( 'sidebar-2' ),
	) );

	// Add theme support for Featured Content.
	add_theme_support( 'featured-content', array(
		'filter'      => 'canard_get_featured_posts',
		'description' => __( 'The featured content section displays on the front page above the header.', 'canard' ),
		'max_posts'   => 5,
		'post_types'  => array( 'post', 'page' ),
	) );

	// Add theme support for Responsive Videos.
	add_theme_support( 'jetpack-responsive-videos' );

	// Add theme support for Content Options.
	// 'blog-display' reflects Canard's default: the loop uses the_content() on
	// singular views and the_excerpt() on archives, so we declare 'content' as
	// the primary default and allow Jetpack to expose the toggle.
	// 'author-bio' is enabled; the template already calls jetpack_author_bio()
	// when available, so we omit 'author-bio-default' (it defaults to true).
	// 'featured-images' mirrors the three contexts the theme renders thumbnails:
	// archive list, single post, and single page. All three default to on.
	add_theme_support( 'jetpack-content-options', array(
		'blog-display'    => 'content',
		'author-bio'      => true,
		'post-details'    => array(
			'stylesheet' => 'canard-style',
			'date'       => '.posted-on, body:not(.group-blog) .entry-summary + .entry-meta > .comments-link:before',
			'categories' => '.cat-links',
			'tags'       => '.tags-links',
			'author'     => '.byline, .group-blog .entry-summary + .entry-meta > .posted-on:before',
			'comment'    => '.comments-link',
		),
		'featured-images' => array(
			'archive'    => true,
			'post'       => true,
			'page'       => true,
		),
	) );
}
add_action( 'after_setup_theme', 'canard_jetpack_setup' );

/**
 * Returns true when two or more featured posts are available.
 *
 * Used to decide whether carousel navigation controls should be shown.
 * A single featured post does not warrant previous/next controls.
 *
 * @return bool
 */
function canard_has_multiple_featured_posts() {
	$featured_posts = apply_filters( 'canard_get_featured_posts', array() );
	if ( is_array( $featured_posts ) && 1 < count( $featured_posts ) ) {
		return true;
	}
	return false;
}

/**
 * Returns the featured posts array via filter.
 *
 * Returns false when no featured posts are configured (Jetpack not active or
 * no posts are tagged). Callers should check for a non-empty array before iterating.
 *
 * @return array|false
 */
function canard_get_featured_posts() {
	return apply_filters( 'canard_get_featured_posts', false );
}

/**
 * Removes Sharedaddy from the excerpt to avoid duplicate sharing buttons.
 *
 * Sharedaddy appends sharing buttons to excerpt output at priority 19.
 * Removing it from the_excerpt prevents duplicate buttons on archive pages
 * where both the excerpt and the full content (via post_flair) are rendered.
 *
 * @return void
 */
function canard_remove_sharedaddy() {
	remove_filter( 'the_excerpt', 'sharing_display', 19 );
}
add_action( 'loop_start', 'canard_remove_sharedaddy' );

/**
 * Outputs the site logo using WordPress core custom logo functionality.
 *
 * @return void
 */
function canard_the_site_logo() {
	if ( function_exists( 'the_custom_logo' ) ) {
		the_custom_logo();
	}
}

/**
 * Determines whether the featured image should be displayed, respecting
 * Jetpack Content Options settings when Jetpack is active.
 *
 * Called before rendering any featured image to respect the admin toggle in
 * Jetpack -> Settings -> Writing -> Content Options.
 *
 * @return bool
 */
function canard_jetpack_featured_image_display() {
	if ( ! function_exists( 'jetpack_featured_images_remove_post_thumbnail' ) ) {
		return true;
	}

	$options = get_theme_support( 'jetpack-content-options' )[0] ?? array();
	$fi      = $options['featured-images'] ?? array();

	$show_on_post = (bool) get_option(
		'jetpack_content_featured_images_post',
		! isset( $fi['post-default'] ) || false !== $fi['post-default']
	);

	$show_on_page = (bool) get_option(
		'jetpack_content_featured_images_page',
		! isset( $fi['page-default'] ) || false !== $fi['page-default']
	);

	if ( is_single() && ! $show_on_post ) {
		return false;
	}

	if ( is_page() && ! $show_on_page ) {
		return false;
	}

	return true;
}

/**
 * Removes Post Format classes from Jetpack Portfolio items so they don't
 * interfere with portfolio-specific styling.
 *
 * @param array $classes Current post classes.
 * @return array Modified post classes.
 */
function canard_jetpack_portfolio_classes( $classes ) {
	$post_format = get_post_format();

	if ( $post_format && ! is_wp_error( $post_format ) ) {
		$class = 'format-' . sanitize_html_class( $post_format );
	} else {
		$class = 'format-standard';
	}

	$class_key = array_search( $class, $classes );

	if ( false !== $class_key && 'jetpack-portfolio' === get_post_type() ) {
		unset( $classes[ $class_key ] );
	}

	return $classes;
}
add_filter( 'post_class', 'canard_jetpack_portfolio_classes' );

/**
 * Applies Typekit / Adobe Fonts category rules for the Canard theme.
 *
 * Maps CSS selectors to the 'body-text' and 'headings' font categories so the
 * Customizer live preview can substitute Typekit fonts correctly. Selector font
 * sizes and weights mirror style.css.
 *
 * The filter is registered only when TypekitTheme is available (i.e. when the
 * Jetpack Adobe Fonts / Typekit module is active). This prevents a fatal error
 * on sites where Jetpack is not installed or the module is disabled.
 */
if ( class_exists( 'TypekitTheme' ) ) {
	add_filter( 'typekit_add_font_category_rules', function( $category_rules ) {

		// -----------------------------------------------------------------------
		// Base HTML elements
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'b,
		strong',
			array(
				array( 'property' => 'font-weight', 'value' => 'bold' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'dfn',
			array(
				array( 'property' => 'font-style', 'value' => 'italic' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'optgroup',
			array(
				array( 'property' => 'font-weight', 'value' => 'bold' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'body,
		button,
		input,
		select,
		textarea',
			array(
				array( 'property' => 'font-family', 'value' => '"PT Serif", serif' ),
				array( 'property' => 'font-size',   'value' => '16px' ),
			)
		);

		// -----------------------------------------------------------------------
		// Headings
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'h1,
		h2:not(.site-description):not(.author-title),
		h3,
		h4,
		h5,
		h6',
			array(
				array( 'property' => 'font-family', 'value' => '"Playfair Display", serif' ),
				array( 'property' => 'font-weight', 'value' => 'bold' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'h1',
			array(
				array( 'property' => 'font-size', 'value' => '49px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'h2:not(.site-description):not(.author-title)',
			array(
				array( 'property' => 'font-size', 'value' => '39px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'h3',
			array(
				array( 'property' => 'font-size', 'value' => '31px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'h4',
			array(
				array( 'property' => 'font-size', 'value' => '25px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'h5',
			array(
				array( 'property' => 'font-size', 'value' => '20px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'h6',
			array(
				array( 'property' => 'font-size', 'value' => '16px' ),
			)
		);

		// -----------------------------------------------------------------------
		// Inline / phrase elements
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'cite,
		dfn,
		em,
		i',
			array(
				array( 'property' => 'font-style', 'value' => 'italic' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'cite',
			array(
				array( 'property' => 'font-weight', 'value' => 'bold' ),
			)
		);

		// -----------------------------------------------------------------------
		// Blockquotes & definition lists
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'blockquote',
			array(
				array( 'property' => 'font-style', 'value' => 'italic' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'blockquote cite',
			array(
				array( 'property' => 'font-style', 'value' => 'normal' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'dt',
			array(
				array( 'property' => 'font-weight', 'value' => 'bold' ),
			)
		);

		// -----------------------------------------------------------------------
		// Links
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'a',
			array(
				array( 'property' => 'font-weight', 'value' => 'bold' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'a:visited',
			array(
				array( 'property' => 'font-weight', 'value' => 'normal' ),
			)
		);

		// -----------------------------------------------------------------------
		// Navigation
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.main-navigation',
			array(
				array( 'property' => 'font-size', 'value' => '16px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.secondary-navigation,
		.footer-navigation,
		.bottom-navigation',
			array(
				array( 'property' => 'font-size', 'value' => '13px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.comment-navigation a,
		.posts-navigation a',
			array(
				array( 'property' => 'font-size', 'value' => '16px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'.post-navigation .post-title',
			array(
				array( 'property' => 'font-family', 'value' => '"Playfair Display", serif' ),
				array( 'property' => 'font-size',   'value' => '25px' ),
				array( 'property' => 'font-weight', 'value' => 'bold' ),
			)
		);

		// -----------------------------------------------------------------------
		// Widgets
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.widget',
			array(
				array( 'property' => 'font-size', 'value' => '16px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'.widget-title,
		.widgettitle',
			array(
				array( 'property' => 'font-size', 'value' => '25px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.widget_recent_entries .post-date',
			array(
				array( 'property' => 'font-size', 'value' => '13px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.widget_rss .rss-date,
		.widget_rss cite',
			array(
				array( 'property' => 'font-size', 'value' => '13px' ),
			)
		);

		// -----------------------------------------------------------------------
		// Site identity
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'.site-title',
			array(
				array( 'property' => 'font-size', 'value' => '25px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'.site-description',
			array(
				array( 'property' => 'font-size', 'value' => '16px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.site-info',
			array(
				array( 'property' => 'font-size', 'value' => '13px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.site-info .sep',
			array(
				array( 'property' => 'font-size', 'value' => '16px' ),
			)
		);

		// -----------------------------------------------------------------------
		// Featured content
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'.featured-content .entry-title',
			array(
				array( 'property' => 'font-size', 'value' => '25px' ),
			)
		);

		// -----------------------------------------------------------------------
		// Post / entry elements
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.entry-summary',
			array(
				array( 'property' => 'font-size', 'value' => '16px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'.page-title',
			array(
				array( 'property' => 'font-size', 'value' => '39px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'.archive .hentry .entry-title,
		.blog .hentry .entry-title,
		.search .hentry .entry-title',
			array(
				array( 'property' => 'font-size', 'value' => '25px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'.page .entry-title,
		.single .entry-title',
			array(
				array( 'property' => 'font-size', 'value' => '39px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.entry-meta',
			array(
				array( 'property' => 'font-size', 'value' => '13px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.entry-footer',
			array(
				array( 'property' => 'font-size', 'value' => '13px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.page-links',
			array(
				array( 'property' => 'font-size', 'value' => '13px' ),
			)
		);

		// -----------------------------------------------------------------------
		// Author info
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.author-info .author-title',
			array(
				array( 'property' => 'font-size', 'value' => '13px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'.author-info .author-name',
			array(
				array( 'property' => 'font-size', 'value' => '25px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.author-info .author-bio',
			array(
				array( 'property' => 'font-size', 'value' => '16px' ),
			)
		);

		// -----------------------------------------------------------------------
		// Comments
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.comments-area',
			array(
				array( 'property' => 'font-size', 'value' => '16px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'.comment-reply-title,
		.comments-title,
		.no-comments',
			array(
				array( 'property' => 'font-size', 'value' => '25px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'.no-comments',
			array(
				array( 'property' => 'font-family', 'value' => '"Playfair Display", serif' ),
				array( 'property' => 'font-weight', 'value' => 'bold' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.comment-form,
		.comment-form code',
			array(
				array( 'property' => 'font-size', 'value' => '16px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.comment-content blockquote:before',
			array(
				array( 'property' => 'font-size', 'value' => '16px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'.comment-author',
			array(
				array( 'property' => 'font-size',   'value' => '16px' ),
				array( 'property' => 'font-family', 'value' => '"Playfair Display", serif' ),
				array( 'property' => 'font-weight', 'value' => 'bold' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.comment-author a,
		.comment-author b',
			array(
				array( 'property' => 'font-weight', 'value' => 'bold' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.comment-metadata .edit-link:before',
			array(
				array( 'property' => 'font-weight', 'value' => 'bold' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.comment-list .comment-reply-title small,
		.comment-metadata,
		.comment-reply-link',
			array(
				array( 'property' => 'font-size', 'value' => '13px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.required',
			array(
				array( 'property' => 'font-weight', 'value' => 'bold' ),
			)
		);

		// -----------------------------------------------------------------------
		// Media captions
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.wp-caption',
			array(
				array( 'property' => 'font-size', 'value' => '16px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.gallery-caption',
			array(
				array( 'property' => 'font-size', 'value' => '16px' ),
			)
		);

		// -----------------------------------------------------------------------
		// Jetpack: Infinite Scroll
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'#infinite-handle span button,
		#infinite-handle span button:active,
		#infinite-handle span button:focus,
		#infinite-handle span button:hover',
			array(
				array( 'property' => 'font-size', 'value' => '16px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'#infinite-footer',
			array(
				array( 'property' => 'font-size', 'value' => '13px' ),
			)
		);

		// -----------------------------------------------------------------------
		// Jetpack: Sharing / Rating
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.hentry div.sd-rating h3.sd-title,
		.hentry div.sharedaddy h3.sd-title',
			array(
				array( 'property' => 'font-size', 'value' => '13px' ),
			)
		);

		// -----------------------------------------------------------------------
		// Jetpack: Related Posts
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'.hentry div#jp-relatedposts h3.jp-relatedposts-headline',
			array(
				array( 'property' => 'font-family', 'value' => '"Playfair Display", serif' ),
				array( 'property' => 'font-size',   'value' => '25px' ),
				array( 'property' => 'font-weight', 'value' => 'bold' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.hentry div#jp-relatedposts div.jp-relatedposts-items p',
			array(
				array( 'property' => 'font-size', 'value' => '16px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.hentry div#jp-relatedposts div.jp-relatedposts-items .jp-relatedposts-post-context',
			array(
				array( 'property' => 'font-size', 'value' => '13px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.hentry div#jp-relatedposts div.jp-relatedposts-items .jp-relatedposts-post-title',
			array(
				array( 'property' => 'font-family', 'value' => '"PT Serif", serif' ),
				array( 'property' => 'font-weight', 'value' => 'bold' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.hentry div#jp-relatedposts div.jp-relatedposts-items .jp-relatedposts-post-title',
			array(
				array( 'property' => 'font-size', 'value' => '16px' ),
			)
		);

		// -----------------------------------------------------------------------
		// Jetpack: Display Posts Widget
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.widget_jetpack_display_posts_widget .jetpack-display-remote-posts h4',
			array(
				array( 'property' => 'font-size', 'value' => '20px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.widget_jetpack_display_posts_widget .jetpack-display-remote-posts p',
			array(
				array( 'property' => 'font-size', 'value' => '16px' ),
			)
		);

		// -----------------------------------------------------------------------
		// Jetpack / third-party: Goodreads Widget
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.widget_goodreads h2[class^="gr_custom_header"]',
			array(
				array( 'property' => 'font-size', 'value' => '20px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.widget_goodreads div[class^="gr_custom_title"]',
			array(
				array( 'property' => 'font-weight', 'value' => 'bold' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.widget_goodreads div[class^="gr_custom_author"]',
			array(
				array( 'property' => 'font-size', 'value' => '13px' ),
			)
		);

		// -----------------------------------------------------------------------
		// Jetpack: Gravatar / Profile Widget
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'.widget-grofile h4',
			array(
				array( 'property' => 'font-size', 'value' => '20px' ),
			)
		);

		// -----------------------------------------------------------------------
		// Jetpack: Reblog snapshot
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'body .hentry .wpcom-reblog-snapshot .reblogger-note-content blockquote',
			array(
				array( 'property' => 'font-style', 'value' => 'italic' ),
			)
		);

		// -----------------------------------------------------------------------
		// Third-party: About.me Widget
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'.aboutme_widget #am_name',
			array(
				array( 'property' => 'font-size', 'value' => '25px' ),
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'.aboutme_widget #am_headline',
			array(
				array( 'property' => 'font-size', 'value' => '20px' ),
			)
		);

		// -----------------------------------------------------------------------
		// Jetpack: Akismet Widget
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.widget_akismet_widget .a-stats',
			array(
				array( 'property' => 'font-size', 'value' => '14px' ),
			)
		);

		// -----------------------------------------------------------------------
		// Jetpack: Authors Widget
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'.widget_authors > ul > li > a',
			array(
				array( 'property' => 'font-family', 'value' => '"PT Serif", serif' ),
			)
		);

		// -----------------------------------------------------------------------
		// Responsive overrides (min-width: 768px)
		// -----------------------------------------------------------------------

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'body',
			array(
				array( 'property' => 'font-size', 'value' => '20px' ),
			),
			array(
				'screen and (min-width: 768px)',
			)
		);

		TypekitTheme::add_font_category_rule( $category_rules, 'headings',
			'.site-title',
			array(
				array( 'property' => 'font-size', 'value' => '49px' ),
			),
			array(
				'screen and (min-width: 768px)',
			)
		);

		return $category_rules;
	} );
}
