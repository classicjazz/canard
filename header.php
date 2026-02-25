<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package Canard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="https://gmpg.org/xfn/11">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="hfeed site">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'canard' ); ?></a>

	<header id="masthead" class="site-header">
		<?php if ( has_nav_menu( 'secondary' ) ) : ?>
			<div class="site-top">
				<div class="site-top-inner">
					<nav class="secondary-navigation" aria-label="<?php esc_attr_e( 'Secondary Navigation', 'canard' ); ?>">
						<?php
							wp_nav_menu( array(
								'theme_location'  => 'secondary',
								'depth'           => 1,
							) );
						?>
					</nav><!-- .secondary-navigation -->
				</div><!-- .site-top-inner -->
			</div><!-- .site-top -->
		<?php endif; ?>

		<div class="site-branding">
			<?php canard_the_site_logo(); ?>
			<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a></h1>
			<h2 class="site-description"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></h2>
		</div><!-- .site-branding -->

		<?php if ( get_header_image() ) : ?>
			<div class="header-image">
				<div class="header-image-inner">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> &mdash; <?php esc_attr_e( 'Home', 'canard' ); ?>"><img src="<?php header_image(); ?>" width="<?php echo absint( get_custom_header()->width ); ?>" height="<?php echo absint( get_custom_header()->height ); ?>" alt=""></a>
				</div><!-- .header-image-inner -->
			</div><!-- .header-image -->
		<?php endif; ?>

		<div id="search-navigation" class="search-navigation">
			<div class="search-navigation-inner">
				<?php if ( has_nav_menu( 'primary' ) ) : ?>
					<nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Primary Navigation', 'canard' ); ?>">
						<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><span class="screen-reader-text"><?php esc_html_e( 'Primary Menu', 'canard' ); ?></span></button>
						<?php wp_nav_menu( array( 'theme_location'  => 'primary', 'menu_id' => 'primary-menu' ) ); ?>
					</nav><!-- #site-navigation -->
				<?php endif; ?>
				<div id="search-header" class="search-header">
					<button class="search-toggle" aria-controls="search-form" aria-expanded="false"><span class="screen-reader-text"><?php esc_html_e( 'Search', 'canard' ); ?></span></button>
					<?php get_search_form(); ?>
				</div><!-- #search-header -->
			</div><!-- .search-navigation-inner -->
		</div><!-- #search-navigation -->
	</header><!-- #masthead -->

	<div id="content" class="site-content">
