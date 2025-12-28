<div class="glass-card-container">
<div class="glass-card">
<section class="home-section" id="featured-posts">
	<?php
	get_template_part(
		'parts/cta-title-link',
		null,
		[
			'url'   => '/articoli/?featured=1',
			'title' => 'Articoli in Evidenza',
			'desc'  => 'Una selezione curata di contenuti consigliati'
		]
	);

	$args = [
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 3,
		'meta_query'     => [
			[
				'key'     => 'is_featured',
				'value'   => '1',
				'compare' => '=',
			],
		],
		'meta_key'       => 'featured_order',
		'meta_type'      => 'NUMERIC',
		'orderby'        => [
			'meta_value_num' => 'ASC',
			'date'           => 'DESC',
		],
	];

	$featured_query = new WP_Query($args);

	if ($featured_query->have_posts()) :
		?>
		<div class="featured-grid">
			<?php while ($featured_query->have_posts()) : $featured_query->the_post(); ?>
				<a class="featured-card" href="<?php the_permalink(); ?>">
					<p class="featured-author"><?php the_author(); ?></p>
					<h3 class="featured-title"><?php the_title(); ?></h3>
				</a>
			<?php endwhile; ?>
		</div>
		<?php
		wp_reset_postdata();
	else :
		?>
		<p>Nessun contenuto in evidenza al momento.</p>
	<?php endif; ?>
</section>
</div>
</div>
