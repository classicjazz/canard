<?php
/**
 * Canard Theme Customizer
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function canard_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	/* Theme Options */
	$wp_customize->add_section( 'canard_theme_options', array(
		'title'    => __( 'Theme Options', 'canard' ),
		'priority' => 130,
	) );

	/* Author Bio */
	$wp_customize->add_setting( 'canard_author_bio', array(
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
	) );
	$wp_customize->add_control( 'canard_author_bio', array(
		'label'             => __( 'Show author bio on single posts.', 'canard' ),
		'section'           => 'canard_theme_options',
		'priority'          => 10,
		'type'              => 'checkbox',
	) );
}
add_action( 'customize_register', 'canard_customize_register' );

/**
 * Enqueues the live-preview JS for the Theme Customizer.
 */
function canard_customize_preview_js() {
	wp_enqueue_script( 'canard-customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), CANARD_VERSION, true );
}
add_action( 'customize_preview_init', 'canard_customize_preview_js' );
