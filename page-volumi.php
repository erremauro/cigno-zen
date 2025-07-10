<?php get_template_part('parts/header'); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<section class="volumes-list">
			<h1>Tutti i Volumi</h1>
			<?php
			$terms = get_terms([
				'taxonomy'   => 'volumes',
				'hide_empty' => false,
			]);

			// Prepara array con autore e termine
			$volumes_with_authors = [];

			foreach ( $terms as $term ) {
				$author = get_field( 'author', 'volumes_' . $term->term_id );
				if ( is_array($author) ) {
					$author = reset($author);
				}
				$volumes_with_authors[] = [
					'term'   => $term,
					'author' => $author,
					'name'   => is_object($author) ? $author->display_name : '',
				];
			}

			// Ordina per nome autore
			usort($volumes_with_authors, function($a, $b) {
				return strcasecmp($a['name'], $b['name']);
			});

			if ( ! empty($volumes_with_authors) ) :
				echo '<ul class="volumes-items">';
				foreach ( $volumes_with_authors as $entry ) :
					$term = $entry['term'];
					$author = $entry['author'];
					$author_name = is_object($author) ? $author->display_name : '—';
					$term_link = get_term_link( $term );
					?>
					<li class="volumes-item">
						<h6 class="volumes-author"><?php echo esc_html( $author_name ); ?></h6>
						<h4 class="volume-title"><a href="<?php echo esc_url( $term_link ); ?>">
							<?php echo esc_html( $term->name ); ?>
						</a></h4>
					</li>
					<?php
				endforeach;
				echo '</ul>';
			else :
				echo '<p>Nessun volume trovato.</p>';
			endif;
			?>
		</section>
	</main>
</div>

<?php get_template_part('parts/footer'); ?>
