<section class="home-section" id="volumes">
	<?php
	$archive_url      = get_post_type_archive_link( 'volume' );
	$completed_query  = new WP_Query(
		array(
			'post_type'      => 'volume',
			'post_status'    => 'publish',
			'posts_per_page' => 2,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'meta_query'     => array(
				array(
					'key'     => '_cz_volume_completed',
					'value'   => 1,
					'compare' => '=',
					'type'    => 'NUMERIC',
				),
			),
		)
	);

	$total_completed = (int) $completed_query->found_posts;

	get_template_part(
		'parts/cta-title-link',
		null,
		array(
			'url'   => $archive_url ? $archive_url : '/volumi',
			'title' => 'Sfoglia i Volumi',
			'desc'  => $total_completed . ' volumi completati disponibili.',
		)
	);
	?>

	<div class="volumes-grid">
		<?php if ( $completed_query->have_posts() ) : ?>
			<?php
			while ( $completed_query->have_posts() ) :
				$completed_query->the_post();
				$author_id   = (int) get_post_field( 'post_author', get_the_ID() );
				$author_name = $author_id ? get_the_author_meta( 'display_name', $author_id ) : '';
				?>
				<a class="volume-card" href="<?php the_permalink(); ?>">
					<h2><?php the_title(); ?></h2>
					<?php if ( $author_name ) : ?>
						<p class="author"><?php echo esc_html( $author_name ); ?></p>
					<?php endif; ?>
				</a>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		<?php else : ?>
			<p><em>Nessun volume completato disponibile.</em></p>
		<?php endif; ?>
	</div>
</section>
