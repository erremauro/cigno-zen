<section class="home-section" id="authors">
	<?php
		get_template_part(
			'parts/cta-title-link',
			null,
			[
				"url"	=> "/autori",
				"title"	=> "Scopri gli Autori"
			]
		);
	?>

	<ul class="authors-grid">
		<?php
		global $wpdb;

		// Top authors ordered by published posts
		$authors = $wpdb->get_results("
			SELECT u.ID, u.display_name, COUNT(p.ID) AS post_count
			FROM {$wpdb->users} u
			INNER JOIN {$wpdb->posts} p ON u.ID = p.post_author
			WHERE p.post_type = 'post'
			  AND p.post_status = 'publish'
			GROUP BY u.ID
			ORDER BY post_count DESC
			LIMIT 4
		");

		if ( ! empty( $authors ) ) :
			foreach ( $authors as $author ) :
				$author_id   = (int) $author->ID;
				$author_name = get_the_author_meta( 'display_name', $author_id );
				$author_link = get_author_posts_url( $author_id );
				?>
				<li class="author-card">
					<a class="author-card-link" href="<?php echo esc_url( $author_link ); ?>" aria-label="<?php echo esc_attr( 'Vai agli articoli di ' . $author_name ); ?>">
						<div class="author-card-meta">
							<h3 class="author-card-name"><?php echo esc_html( $author_name ); ?></h3>
						</div>
					</a>
				</li>
				<?php
			endforeach;
		else :
			?>
			<li class="author-card author-card--empty"><em>Nessun autore trovato.</em></li>
			<?php
		endif;
		?>
	</ul>
</section>
