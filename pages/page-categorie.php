<section class="category-list">
	<section class="category-select">
		<h1>Esplora per Categoria</h1>

		<form method="get" action="<?php echo esc_url( get_permalink() ); ?>">
			<label for="cat">Seleziona Categoria:</label>
			<select name="cat" id="cat" onchange="this.form.submit()">
				<option value="">-- Tutte le categorie --</option>
				<?php
				$categories = get_categories([
					'hide_empty' => false,
					'orderby'    => 'name',
				]);

				$selected_cat   = isset($_GET['cat']) ? (int) $_GET['cat'] : 0;
				$selected_order = isset($_GET['order_by']) ? $_GET['order_by'] : 'author';

				foreach ( $categories as $category ) {
					printf(
						'<option value="%d"%s>%s</option>',
						$category->term_id,
						selected( $selected_cat, $category->term_id, false ),
						esc_html( $category->name )
					);
				}
				?>
			</select>

			<label for="order_by">Ordina per:</label>
			<select name="order_by" id="order_by" onchange="this.form.submit()">
				<option value="author" <?php selected($selected_order, 'author'); ?>>Autore</option>
				<option value="title" <?php selected($selected_order, 'title'); ?>>Titolo</option>
			</select>
		</form>
</section>

<section class="category-posts">
	<?php
	$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

	$args = [
		'post_type'      => 'post',
		'posts_per_page' => 10,
		'paged'          => $paged,
		'orderby'        => $selected_order,
		'order'          => 'ASC',
	];

	if ( $selected_cat ) {
		$args['cat'] = $selected_cat;
	}

	$query = new WP_Query( $args );

	if ( $query->have_posts() ) :
		if ( $selected_order === 'author' ) {
			// Raggruppa per autore
			$posts_by_author = [];

			while ( $query->have_posts() ) {
				$query->the_post();
				$author_id = get_the_author_meta('ID');
				$author_name = get_the_author_meta('display_name', $author_id);
				$posts_by_author[$author_name][] = [
					'title' => get_the_title(),
					'url'   => get_permalink(),
				];
			}

			echo '<ul class="category-post-list grouped-by-author">';
			foreach ( $posts_by_author as $author_name => $posts ) {
				$author_user = get_user_by('display_name', $author_name);
				$author_url = $author_user ? get_author_posts_url($author_user->ID) : '#';

				echo '<li class="category-author-group">';
				echo '<h3 class="category-author">' . esc_html($author_name) . '</h3>';
				echo '<ul class="category-author-posts">';
				foreach ( $posts as $post ) {
					echo '<li class="category-item"><h4 class="category-title"><a href="' . esc_url($post['url']) . '">' . esc_html($post['title']) . '</h4></a></li>';
				}
				echo '</ul>';
				echo get_template_part( 'parts/separator');
				echo '</li>';
			}
			echo '</ul>';
		} else {
			// Normale visualizzazione flat
			echo '<ul class="category-post-list">';
			while ( $query->have_posts() ) :
				$query->the_post();
				$author_id = get_the_author_meta('ID');
				$author_name = get_the_author_meta( 'display_name', $author_id );
				$author_url = get_author_posts_url( $author_id );
				?>
				<li class="category-item">
					<h6 class="category-author"><?php echo esc_html( $author_name ); ?></h6>
					<h4 class="category-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
					<?php get_template_part( 'parts/separator') ?>
				</li>
				<?php
			endwhile;
			echo '</ul>';
		}

		// Paginazione
		if ( $query->max_num_pages > 1 ) {
			echo '<div class="pagination">';
			echo paginate_links([
				'total'   => $query->max_num_pages,
				'current' => $paged,
				'format'  => '?paged=%#%' . ($selected_cat ? '&cat=' . $selected_cat : '') . ($selected_order ? '&order_by=' . $selected_order : ''),
				'add_args' => [
					'cat'      => $selected_cat,
					'order_by' => $selected_order,
				],
				'prev_text' => '«',
				'next_text' => '»',
			]);
			echo '</div>';
		}

		wp_reset_postdata();
	else :
		echo '<p>Nessun post trovato.</p>';
	endif;
	?>
</section>
