<?php
// Inizia il loop di WordPress
while (have_posts()) : the_post();

	// Ottieni i termini della tassonomia "series"
	$series_terms = get_the_terms(get_the_ID(), 'series');
	if ($series_terms && !is_wp_error($series_terms)) {
		$series_term = array_shift($series_terms);
		$series_link = get_term_link($series_term);
		echo '<p class="series-link"><a href="' . esc_url($series_link) . '">' . esc_html($series_term->name) . '</a></p>';
	}
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<header class="post-header">
			<h1 class="post-title"><?php the_title(); ?></h1>
		</header>

		<div class="post-content">
			<?php the_content();

			// Aggiungi i link di paginazione per i post paginati
            wp_link_pages(array(
                'before' => '<div class="post-pagination">' . __('<h5>Pagine</h5><p class="page-links">', 'textdomain'),
                'next_or_number' => 'number',
                'next'  => '<p></div>',
                'nextpagelink' => __('Successivo »'),
				'previouspagelink' => __('« Precedente'),
            ));

			?>

		</div>

		<footer class="post-footer">
			<?php
				if ( class_exists( 'Jetpack_RelatedPosts' ) ) {
				    echo do_shortcode( '[jprel]' );
				}
			?>
		</footer>
	</article>
<?php endwhile; ?>
