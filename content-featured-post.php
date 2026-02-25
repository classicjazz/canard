<?php
/**
 * The template for displaying featured posts on the front page.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="post-thumbnail" href="<?php echo esc_url( get_permalink() ); ?>">
			<?php the_post_thumbnail( 'canard-featured-content-thumbnail', array( 'loading' => 'lazy' ) ); ?>
		</a>
	<?php endif; ?>

	<header class="entry-header">
		<?php
			canard_entry_categories();
			the_title( '<h1 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">','</a></h1>' );
		?>
	</header><!-- .entry-header -->

	<?php get_template_part( 'entry', 'script' ); ?>
</article><!-- #post-## -->
