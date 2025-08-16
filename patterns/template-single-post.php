<?php
// Start the WordPress Loop
while ( have_posts() ) : the_post();

	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<header class="post-header">
			<?php display_author_info_conditionally(); ?>
			<?php display_volumes_name(); ?>
			<h1 class="post-title"><?php the_title(); ?></h1>
			<h3 class="post-subtitle"><?php the_subtitle(); ?></h3>
		</header>

		<div class="post-content">
			<?php
			the_content();

			custom_post_pagination();

			wp_link_pages( array(
				'before'         => '<div class="post-pagination">' . __('<h5>Pagine</h5><p class="page-links">', 'textdomain'),
				'next_or_number' => 'number',
				'after'          => '</p></div>',
			) );
			?>
		</div>

		<?php get_template_part('patterns/template-single-post-footer'); ?>
	</article>

<?php endwhile; ?>
