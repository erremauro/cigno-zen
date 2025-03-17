<?php
// Ottieni il numero della pagina corrente
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Argomenti per la query
$args = array(
	'post_type' => 'post', // Tipo di post da recuperare
	'posts_per_page' => 5, // Numero di post per pagina
	'paged' => $paged, // Numero della pagina corrente
);

// Esegui la query
$the_query = new WP_Query($args);
?>

<?php if ($the_query->have_posts()) : ?>
	<?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="post-header">
				<h2 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<?php
				// Ottieni le categorie del post
				// $categories = get_the_category();
				// if (!empty($categories)) {
				// 	echo '<div class="post-categories">';
				// 	foreach ($categories as $category) {
				// 		echo '<a href="' . esc_url(get_category_link($category->term_id)) . '">' . esc_html($category->name) . '</a> ';
				// 	}
				// 	echo '</div>';
				// }
				?>
			</header>
			<div class="post-content">
				<?php the_excerpt(); ?>
				<p class="more-text"><a href="<?php the_permalink(); ?>" class="more-link">Continua ›</a></p>
			</div>
		</article>
	<?php endwhile; ?>

	<!-- Paginazione -->
	<div class="pagination">
		<?php
		echo paginate_links(array(
			'total' => $the_query->max_num_pages,
			'current' => $paged,
			'prev_text' => __('« Precedente'),
			'next_text' => __('Successivo »'),
		));
		?>
	</div>

<?php else : ?>
	<p><em><?php _e('Nessun articolo trovato.'); ?></em></p>
<?php endif; ?>

<?php
// Resetta i dati della query globale
wp_reset_postdata();
?>
