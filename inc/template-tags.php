<?php
/**
 * Custom template tags for this theme.
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'canard_entry_categories' ) ) :
/**
 * Outputs HTML with meta information for the post categories.
 */
function canard_entry_categories() {
	if ( 'post' === get_post_type() ) {
		/* translators: used between list items, there is a space after the comma */
		$categories_list = get_the_category_list( __( ', ', 'canard' ) );
		if ( $categories_list && canard_categorized_blog() ) {
			printf( '<div class="entry-meta"><span class="cat-links">%1$s</span></div>', wp_kses_post( $categories_list ) );
		}
	}
}
endif;

if ( ! function_exists( 'canard_entry_meta' ) ) :
/**
 * Outputs HTML with meta information for the author, post date/time, and comments.
 */
function canard_entry_meta() {
	/**
	 * Filters the author bio avatar size.
	 *
	 * @param int $size The avatar height and width size in pixels.
	 */
	$author_bio_avatar_size = apply_filters( 'canard_author_bio_avatar_size', 20 );

	$byline = sprintf( '<span class="author vcard">%1$s<a class="url fn n" href="%2$s">%3$s</a></span>',
		get_avatar( get_the_author_meta( 'user_email' ), $author_bio_avatar_size ),
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_html( get_the_author() )
	);

	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( 'c' ) ),
		esc_html( get_the_modified_date() )
	);

	$posted_on = sprintf( '<a href="%1$s" rel="bookmark">%2$s</a>', esc_url( get_permalink() ), $time_string );

	$allowed_meta_html = array(
		'span' => array( 'class' => array() ),
		'a'    => array( 'class' => array(), 'href' => array(), 'rel' => array() ),
		'time' => array( 'class' => array(), 'datetime' => array() ),
		'img'  => array(
			'src'     => array(),
			'class'   => array(),
			'alt'     => array(),
			'width'   => array(),
			'height'  => array(),
			'loading' => array(),
			'decoding' => array(),
		),
	);

	if ( is_single() && ( true === (bool) get_theme_mod( 'canard_author_bio' ) && get_the_author_meta( 'description' ) ) ) {
		echo wp_kses( '<span class="posted-on">' . $posted_on . '</span>', $allowed_meta_html );
	} else {
		echo wp_kses( '<span class="byline"> ' . $byline . '</span><span class="posted-on">' . $posted_on . '</span>', $allowed_meta_html );
	}

	if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
		echo '<span class="comments-link">';
		comments_popup_link( __( 'Leave a comment', 'canard' ), __( '1 Comment', 'canard' ), __( '% Comments', 'canard' ) );
		echo '</span>';
	}
}
endif;

if ( ! function_exists( 'canard_entry_footer' ) ) :
/**
 * Outputs HTML with meta information for the categories, tags, and comments.
 */
function canard_entry_footer() {
	canard_entry_meta();

	if ( 'post' === get_post_type() ) {
		/* translators: used between list items, there is a space after the comma */
		the_tags( '<span class="tags-links">', esc_html__( ', ', 'canard' ), '</span>' );
	}

	edit_post_link( __( 'Edit', 'canard' ), '<span class="edit-link">', '</span>' );
}
endif;

/**
 * Returns true if a blog has more than 1 category.
 *
 * @return bool
 */
function canard_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'canard_cat_count_v1' ) ) ) {
		$all_the_cool_cats = get_categories( array(
			'fields'     => 'ids',
			'hide_empty' => 1,
			// We only need to know if there is more than one category.
			'number'     => 2,
		) );

		$all_the_cool_cats = is_countable( $all_the_cool_cats ) ? count( $all_the_cool_cats ) : 0;

		set_transient( 'canard_cat_count_v1', $all_the_cool_cats );
	}

	return $all_the_cool_cats > 1;
}

/**
 * Flushes the transient used in canard_categorized_blog() when categories change.
 */
function canard_category_transient_flusher() {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	delete_transient( 'canard_cat_count_v1' );
}
add_action( 'edit_category', 'canard_category_transient_flusher' );
add_action( 'save_post',     'canard_category_transient_flusher' );

/**
 * Adds featured image as background image to post navigation elements.
 *
 * @see wp_add_inline_style()
 */
function canard_post_nav_background() {
	if ( ! is_single() ) {
		return;
	}

	if ( ! canard_jetpack_featured_image_display() ) {
		return;
	}

	$previous = ( is_attachment() ) ? get_post( get_post()->post_parent ) : get_adjacent_post( false, '', true );
	$next     = get_adjacent_post( false, '', false );
	$css      = '';

	if ( is_attachment() && 'attachment' === $previous->post_type ) {
		return;
	}

	if ( $previous && has_post_thumbnail( $previous->ID ) ) {
		$prev_url = wp_get_attachment_image_url( get_post_thumbnail_id( $previous->ID ), 'post-thumbnail' );
		$css .= '
			.post-navigation .nav-previous { background-image: url(' . esc_url( $prev_url ) . '); }
			.post-navigation .nav-previous .post-title, .post-navigation .nav-previous a:hover .post-title, .post-navigation .nav-previous .meta-nav { color: #fff; }
			.post-navigation .nav-previous a { background-color: rgba(0, 0, 0, 0.3); border: 0; text-shadow: 0 0 0.125em rgba(0, 0, 0, 0.3); }
			.post-navigation .nav-previous a:focus, .post-navigation .nav-previous a:hover { background-color: rgba(0, 0, 0, 0.6); }
			.post-navigation .nav-previous a:focus .post-title { color: #fff; }
		';
	}

	if ( $next && has_post_thumbnail( $next->ID ) ) {
		$next_url = wp_get_attachment_image_url( get_post_thumbnail_id( $next->ID ), 'post-thumbnail' );
		$css .= '
			.post-navigation .nav-next { background-image: url(' . esc_url( $next_url ) . '); }
			.post-navigation .nav-next .post-title, .post-navigation .nav-next a:hover .post-title, .post-navigation .nav-next .meta-nav { color: #fff; }
			.post-navigation .nav-next a { background-color: rgba(0, 0, 0, 0.3); border: 0; text-shadow: 0 0 0.125em rgba(0, 0, 0, 0.3); }
			.post-navigation .nav-next a:focus, .post-navigation .nav-next a:hover { background-color: rgba(0, 0, 0, 0.6); }
			.post-navigation .nav-next a:focus .post-title { color: #fff; }
		';
	}

	wp_add_inline_style( 'canard-style', $css );
}
add_action( 'wp_enqueue_scripts', 'canard_post_nav_background' );
