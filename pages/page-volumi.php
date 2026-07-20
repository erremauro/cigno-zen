<section class="volumes-list">
	<?php
	$sort_fields = [
		'date'     => __( 'Data di pubblicazione', 'textdomain' ),
		'modified' => __( 'Data di modifica', 'textdomain' ),
		'title'    => __( 'Titolo', 'textdomain' ),
		'author'   => __( 'Autore', 'textdomain' ),
	];
	$sort_default_order = [
		'date'     => 'DESC',
		'modified' => 'DESC',
		'title'    => 'ASC',
		'author'   => 'ASC',
	];
	[ $orderby_param, $order_param ] = cz_resolve_sort_params( $sort_fields, $sort_default_order );

	$volumes_args = [
		'post_type'      => 'volume',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	];

	if ( 'author' === $orderby_param ) {
		$volumes_query = cz_query_sorted_by_author_name( $volumes_args, $order_param );
	} else {
		$orderby_map = [
			'date'     => 'date',
			'modified' => 'modified',
			'title'    => 'title',
		];
		$volumes_args['orderby'] = [ $orderby_map[ $orderby_param ] => $order_param, 'ID' => $order_param ];
		$volumes_query = new WP_Query( $volumes_args );
	}

	?>
	<header class="archive-header">
	  <h1 class="archive-title">Tutti i Volumi</h1>
	  <?php cz_render_sort_control( $sort_fields, $orderby_param, $order_param ); ?>
	</header>
	<?php

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
