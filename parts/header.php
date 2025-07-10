<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<?php get_template_part('parts/head'); ?>

<body <?php body_class(); ?>>
	<header class="site-header">
		<!-- SITE LOGO -->
		<div class="site-branding">
			<a href="<?php echo esc_url(home_url('/')) ?>" rel="home" class="custom-logo-link">
				    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/cigno-zen.svg'); ?>" title="<?php echo bloginfo('description') ?>" alt="<?php echo bloginfo('name'); ?>" width="150" height="auto">
			</a>
		</div>
		<!-- SEARCH BAR -->
		<div id="search-bar" class="search-bar" style="<?php if (is_search()) echo 'display: block;'; else echo 'display: none;'; ?>">
			<?php get_template_part( 'parts/search-bar') ?>
			<!-- MENU -->
			<?php get_template_part( 'parts/menu-bar') ?>
		</div>
		<!-- SEARCH CHEVRON BUTTON -->
		<div class="site-search">
			<img id="search-button" class="<?php if (is_search()) echo 'rotated'; else echo ''; ?>" src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/chevron-down.svg'); ?>" title="<?php echo bloginfo('description') ?>" alt="<?php echo bloginfo('name'); ?>" width="32" height="auto" style="cursor: pointer">
		</div>
	</header>
