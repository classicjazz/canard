<?php
/**
 * Template part for displaying featured posts on the front page.
 *
 * Renders a single featured post as a linked thumbnail followed by the post
 * categories and title. Intended to be included via get_template_part() from
 * the front-page template. The thumbnail uses eager loading and a high fetch
 * priority because it is typically the Largest Contentful Paint element.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 * @package Canard
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="post-thumbnail" href="<?php echo esc_url( (string) get_permalink() ); ?>">
			<?php
			the_post_thumbnail(
				'canard-featured-content-thumbnail',
				[
					'loading'       => 'eager',
					'fetchpriority' => 'high',
					'sizes'         => '(max-width: 1300px) 100vw, 1300px',
				]
			);
			?>
		</a>
	<?php endif; ?>

	<header class="entry-header">
		<?php
			canard_entry_categories();
			the_title( '<h1 class="entry-title"><a href="' . esc_url( (string) get_permalink() ) . '" rel="bookmark">', '</a></h1>' );
		?>
	</header><!-- .entry-header -->

	<?php get_template_part( 'entry', 'script' ); ?>
</article><!-- #post-## -->
