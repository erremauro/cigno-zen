<!-- LATEST ARTICLES SECTION -->
<section class="home-section" id="latest-articles">
	<h2 class="section-title"><a class="no-color-link" href="/articoli" >Ultimi Articoli &rsaquo;</a></h2>
	<?php
		// Recupera l'ultimo articolo pubblicato
		$args = array(
		    'posts_per_page' => 1, // solo 1 articolo
		    'post_status'    => 'publish',
		);
		$latest_post = get_posts($args);

		if ($latest_post) :
	    	$post = $latest_post[0]; // prende il primo risultato
	    	setup_postdata($post);
    ?>
    	<a class="article-card" href="<?php echo get_permalink($post); ?>">
	    	<h2 class="article-title"><?php the_title(); ?></h2>
	    	<p class="author"><?php the_author(); ?></p>
		</a>
    <?php
	    wp_reset_postdata();
		endif;
	?>
</section>

<!-- AUTHORS SECTION -->
<section class="home-section" id="authors">
	<h2 class="section-title">
		<a class="no-color-link" href="/autori">Scopri gli Autori &rsaquo;</a>
	</h2>
	<ul>
		<?php
		global $wpdb;

		// Recupera autori ordinati per numero di post pubblicati
		$authors = $wpdb->get_results("
			SELECT u.ID, u.display_name, COUNT(p.ID) AS post_count
			FROM {$wpdb->users} u
			INNER JOIN {$wpdb->posts} p ON u.ID = p.post_author
			WHERE p.post_type = 'post'
			  AND p.post_status = 'publish'
			GROUP BY u.ID
			ORDER BY post_count DESC
			LIMIT 3
		");

		if (!empty($authors)) :
			foreach ($authors as $author) :
				$author_link = get_author_posts_url($author->ID);
				?>
				<li>
					<a href="<?php echo esc_url($author_link); ?>">
						<?php echo esc_html($author->display_name); ?>
					</a>
				</li>
				<?php
			endforeach;
		else :
			echo '<li><em>Nessun autore trovato.</em></li>';
		endif;
		?>
	</ul>
</section>

<!-- VOLUMES SECTION -->
<section class="home-section" id="volumes">
	<h2 class="section-title">
		<a class="no-color-link" href="/volumi">Sfoglia i Volumi &rsaquo;</a>
	</h2>
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
			LIMIT 2
		");

		if (!empty($results)) :
			foreach ($results as $term) :
				$term_link = get_term_link((int) $term->term_id, 'volumes');
				if (is_wp_error($term_link)) {
					continue;
				}

				// Recupera il campo ACF "author" (User Object)
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

<!-- CATEGORIES SECTION  -->
<section class="home-section" id="categories">
	<h2 class="section-title">
		<a class="no-color-link" href="/categorie">Esplora le Categorie &rsaquo;</a>
	</h2>

	<div class="categories-grid">
	  <a href="/categoria/approfondimento" class="category-card">Approfondimenti</a>
	  <a href="/categoria/sutra" class="category-card">Sutra</a>
	  <a href="/categoria/saggio" class="category-card">Saggi</a>
	  <a href="/categoria/poesia" class="category-card">Poesie</a>
	  <a href="/categoria/commentario" class="category-card">Commentari</a>
	  <a href="/categoria/estratto" class="category-card">Estratti</a>
	</div>
</section>
