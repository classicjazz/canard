<?php
/**
 * Implementation of the Custom Header feature.
 *
 * @link https://developer.wordpress.org/themes/functionality/custom-headers/
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets up the WordPress core custom header feature.
 *
 * @uses canard_header_style()
 */
function canard_custom_header_setup() {
	add_theme_support( 'custom-header', apply_filters( 'canard_custom_header_args', array(
		'default-image'          => '',
		'default-text-color'     => '#d11415',
		'width'                  => 1260,
		'height'                 => 300,
		'flex-height'            => true,
		'flex-width'             => true,
		'wp-head-callback'       => 'canard_header_style',
	) ) );
}
add_action( 'after_setup_theme', 'canard_custom_header_setup' );

if ( ! function_exists( 'canard_header_style' ) ) :
/**
 * Outputs CSS for the site title and description when a custom header text
 * color is set, or hides them when the header text is disabled.
 *
 * Uses wp_add_inline_style() rather than echoing a raw <style> tag so that
 * the output is compatible with Content Security Policy nonce-based style-src
 * directives (WordPress 5.9.1+) and is consistent with how
 * canard_post_nav_background() registers inline styles.
 *
 * @see canard_custom_header_setup()
 */
function canard_header_style() {
	$header_text_color = get_header_textcolor();

	// If no custom text color is set, bail — default styles apply.
	if ( get_theme_support( 'custom-header', 'default-text-color' ) === $header_text_color ) {
		return;
	}

	if ( 'blank' === $header_text_color ) {
		$css = '
			.site-title,
			.site-description {
				position: absolute;
				clip-path: inset(50%);
				white-space: nowrap;
				overflow: hidden;
				height: 1px;
				width: 1px;
			}
		';
	} else {
		$css = '
			.site-title,
			.site-description {
				color: #' . esc_attr( $header_text_color ) . ';
			}
		';
	}

	wp_add_inline_style( 'canard-style', $css );
}
endif;
