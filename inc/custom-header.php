<?php
/**
 * Implementation of the Custom Header feature.
 *
 * @link https://developer.wordpress.org/themes/functionality/custom-headers/
 * @package Canard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets up the WordPress core custom header feature.
 *
 * Registers custom-header theme support with default dimensions and a head
 * callback. The canard_custom_header_args filter is exposed so that child
 * themes can override dimensions, the default image, or the callback without
 * replacing this function entirely.
 *
 * @uses canard_header_style()
 * @return void
 */
function canard_custom_header_setup() {
	$args = apply_filters( 'canard_custom_header_args', [
		'default-image'      => '',
		'default-text-color' => '#d11415',
		'width'              => 1260,
		'height'             => 300,
		'flex-height'        => true,
		'flex-width'         => true,
		'wp-head-callback'   => 'canard_header_style',
	] );
	add_theme_support( 'custom-header', $args );
}
add_action( 'after_setup_theme', 'canard_custom_header_setup' );

if ( ! function_exists( 'canard_header_style' ) ) {
	/**
	 * Outputs inline CSS for the site title and description based on the active header text color.
	 *
	 * When a custom text color has been chosen in the Customizer this function emits
	 * the corresponding color rule via wp_add_inline_style(). When the header text
	 * has been hidden ("blank"), it emits an accessible visually-hidden rule so that
	 * screen readers can still announce the site title and description. If the color
	 * matches the theme default no CSS is output and the stylesheet defaults apply.
	 *
	 * Uses wp_add_inline_style() rather than echoing a raw <style> tag so that the
	 * output is compatible with Content Security Policy nonce-based style-src
	 * directives (WordPress 5.9.1+) and remains consistent with how
	 * canard_post_nav_background() registers inline styles.
	 *
	 * @see canard_custom_header_setup()
	 * @return void
	 */
	function canard_header_style() {
		$header_text_color  = get_header_textcolor();
		$default_text_color = get_theme_support( 'custom-header', 'default-text-color' );

		// The stylesheet already covers the default color; only emit inline CSS when the user has chosen an override.
		if ( $default_text_color === $header_text_color ) {
			return;
		}

		if ( 'blank' === $header_text_color ) {
			// 'blank' is the Customizer's sentinel value meaning "hide header text".
			// CSS visually hides the elements while keeping them accessible to screen readers.
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
			// sanitize_hex_color_no_hash() is the context-correct escaper here: the value is
			// interpolated into a CSS declaration, not an HTML attribute, so it must be
			// constrained to a bare hex triplet rather than passed through esc_attr().
			$css = '
				.site-title,
				.site-description {
					color: #' . sanitize_hex_color_no_hash( $header_text_color ) . ';
				}
			';
		}

		wp_add_inline_style( 'canard-style', $css );
	}
}
