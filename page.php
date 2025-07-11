<?php get_template_part('parts/header'); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main">
		<?php
			$slug = get_post_field('post_name', get_post());
			$template_path = locate_template('pages/page-' . $slug . '.php');

			if ( $template_path ) {
				require $template_path;
			} else {
				while ( have_posts() ) : the_post();
					the_content();
				endwhile;
			}
		?>
	</main>
</div>

<?php get_template_part('parts/footer'); ?>
