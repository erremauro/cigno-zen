<section class="home-section" id="volumes">
	<?php
		$taxonomy = 'volumes';

		$completed_ids = get_terms([
		  'taxonomy'   => $taxonomy,
		  'hide_empty' => false,         // conteggia anche se non associati a post
		  'fields'     => 'ids',
		  'meta_query' => [
		    [
		      'key'     => 'completato', // nome del campo ACF
		      'value'   => '1',          // ACF True/False salva '1' per true
		      'compare' => '='
		    ]
		  ],
		]);
		$completed_count = is_wp_error($completed_ids) ? 0 : count($completed_ids);

		$not_completed_ids = get_terms([
		  'taxonomy'   => $taxonomy,
		  'hide_empty' => false,
		  'fields'     => 'ids',
		  'meta_query' => [
		    [
		      'key'     => 'completato',
		      'value'   => '0',
		      'compare' => '='
		    ]
		  ],
		]);
		$not_completed_count = is_wp_error($not_completed_ids) ? 0 : count($not_completed_ids);

        $all_term_ids = get_terms([
          'taxonomy'   => $taxonomy,
          'hide_empty' => false,
          'fields'     => 'ids',
        ]);
        $unset_count = 0;
        if ( ! is_wp_error($all_term_ids) ) {
          // termini esplicitamente settati (1 o 0)
          $explicit_ids = array_unique(array_merge($completed_ids ?: [], $not_completed_ids ?: []));
          $unset_count  = count(array_diff($all_term_ids, $explicit_ids));
        }

        $not_completed_count = $not_completed_count + $unset_count;
        $total_count = $completed_count + $not_completed_count;


		$total_count = $completed_count + $not_completed_count;

		get_template_part(
			'parts/cta-title-link',
			null,
			[
				'url'	=> '/volumi',
				'title'	=> 'Sfoglia i Volumi',
				'desc'  => $completed_count . ' raccolte complete e ' . $not_completed_count . ' volumi in corso.'
			]
		);
	?>
	<div class="volumes-grid">
		<?php
		global $wpdb;

		// Query custom: per ogni termine della tassonomia "volumes"
		// trova la data dell'ultimo post pubblicato
		$results = $wpdb->get_results("
			SELECT t.term_id, t.name, t.slug, MAX(p.post_date) as last_post_date
			FROM {$wpdb->terms} t
			INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
			INNER JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
			INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID
			WHERE tt.taxonomy = 'volumes'
			  AND p.post_status = 'publish'
			  AND p.post_type = 'post'
			GROUP BY t.term_id
			ORDER BY last_post_date DESC
			LIMIT 10
		");

		if (!empty($results)) :
			// Enrich results con campo ACF "completato"
			foreach ($results as $term) {
				$completed = get_field('completato', 'volumes_' . $term->term_id);
				$term->completed = !empty($completed) ? 1 : 0;
			}

			// Ordina: prima completati (1), poi non completati (0), mantenendo last_post_date
			usort($results, function($a, $b) {
				if ($a->completed === $b->completed) {
					// Entrambi completati o entrambi incompleti → ordina per last_post_date
					return strtotime($b->last_post_date) <=> strtotime($a->last_post_date);
				}
				// Completati prima
				return $b->completed <=> $a->completed;
			});

			// Mostra solo i primi 2
			$results = array_slice($results, 0, 2);

			foreach ($results as $term) :
				$term_link = get_term_link((int) $term->term_id, 'volumes');
				if (is_wp_error($term_link)) {
					continue;
				}

				// Recupera l’autore (User Object)
				$author = get_field('author', 'volumes_' . $term->term_id);
				$author_name = ($author && is_object($author)) ? $author->display_name : '—';
				?>
				<a class="volume-card" href="<?php echo esc_url($term_link); ?>">
					<h2><?php echo esc_html($term->name); ?></h2>
					<?php if ($author_name !== '—'): ?>
						<p class="author"><?php echo esc_html($author_name); ?></p>
					<?php endif; ?>
				</a>
			<?php endforeach;
		else : ?>
			<p><em>Nessun volume disponibile.</em></p>
		<?php endif; ?>
	</div>
</section>
