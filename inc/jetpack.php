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
	add_theme_support( 'jetpack-content-options', array(
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
 * Returns true if there are multiple featured posts.
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
 * @return array|false
 */
function canard_get_featured_posts() {
	return apply_filters( 'canard_get_featured_posts', false );
}

/**
 * Removes Sharedaddy from the excerpt to avoid duplicate sharing buttons.
 */
function canard_remove_sharedaddy() {
	remove_filter( 'the_excerpt', 'sharing_display', 19 );
}
add_action( 'loop_start', 'canard_remove_sharedaddy' );

/**
 * Outputs the site logo using WordPress core custom logo functionality.
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
 * @return bool
 */
function canard_jetpack_featured_image_display() {
	if ( ! function_exists( 'jetpack_featured_images_remove_post_thumbnail' ) ) {
		return true;
	}

	$options         = get_theme_support( 'jetpack-content-options' );
	$featured_images = ( ! empty( $options[0]['featured-images'] ) ) ? $options[0]['featured-images'] : null;

	$settings = array(
		'post-default' => ( isset( $featured_images['post-default'] ) && false === $featured_images['post-default'] ) ? '' : 1,
		'page-default' => ( isset( $featured_images['page-default'] ) && false === $featured_images['page-default'] ) ? '' : 1,
	);

	$settings = array_merge( $settings, array(
		'post-option'  => get_option( 'jetpack_content_featured_images_post', $settings['post-default'] ),
		'page-option'  => get_option( 'jetpack_content_featured_images_page', $settings['page-default'] ),
	) );

	if ( ( ! $settings['post-option'] && is_single() )
		|| ( ! $settings['page-option'] && is_singular() && is_page() ) ) {
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
 * Applies Typekit font category rules when the Typekit/Adobe Fonts integration
 * is available via Jetpack.
 */
if ( class_exists( 'TypekitTheme' ) ) {
	add_filter( 'typekit_add_font_category_rules', function( $category_rules ) {

		TypekitTheme::add_font_category_rule( $category_rules, 'body-text',
			'b, strong',
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
			'body, button, input, select, textarea',
			array(
				array( 'property' => 'font-family', 'value' => '"PT Serif", serif' ),
				array( 'property' => 'font-size',   'value' => '16px' ),
			)
		);

		return $category_rules;
	} );
}
