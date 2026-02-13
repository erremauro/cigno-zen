<section class="volumes-list">
	<h1>Tutti i Volumi</h1>
	<?php
	$volumes_query = new WP_Query(
		array(
			'post_type'      => 'volume',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);

	if ( $volumes_query->have_posts() ) :
		echo '<ul class="volumes-items">';

		while ( $volumes_query->have_posts() ) :
			$volumes_query->the_post();
			?>
			<li class="volumes-item">
				<?php echo display_volume_author( get_the_ID(), false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<h4 class="volume-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
				<?php echo do_shortcode( '[separator]' ); ?>
			</li>
			<?php
		endwhile;

		echo '</ul>';
		wp_reset_postdata();
	else :
		echo '<p>Nessun volume trovato.</p>';
	endif;
	?>
</section>
