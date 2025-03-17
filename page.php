<?php get_template_part('parts/header'); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main">
		<?php
		// Inizia il loop di WordPress
		while ( have_posts() ) : the_post();
			the_content();
		endwhile;
		?>
	</main>
</div>

<?php get_template_part('parts/footer'); ?>
