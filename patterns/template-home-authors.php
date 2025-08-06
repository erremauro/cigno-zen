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