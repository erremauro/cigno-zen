<?php
// Ottieni il numero della pagina corrente
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Ottieni l'ID dell'autore corrente
$author_id = get_query_var('author');

// Argomenti per la query
$args = array(
	'post_type'      => 'post', // Tipo di post da recuperare
	'posts_per_page' => 5,      // Numero di post per pagina
	'paged'          => $paged, // Numero della pagina corrente
	'author'         => $author_id, // Filtra per autore
);

// Esegui la query
$the_query = new WP_Query($args);
?>

<?php if ($the_query->have_posts()) : ?>
	<?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="post-header">
				<?php display_author_info_conditionally(); ?>
				<?php display_series_name() ?>
				<h2 class="post-title">
					<a href="<?php the_permalink(); ?>">
						<?php the_title(); ?>
					</a>
				</h2>
				<h4 class="post-subtitle">
					<a href="<?php the_permalink(); ?>">
						<?php the_subtitle(); ?>
					</a>
				<h4>
			</header>
			<div class="post-content">
				<?php the_excerpt(); ?>
				<p class="more-text"><a href="<?php the_permalink(); ?>" class="more-link">Continua ›</a></p>
			</div>
		</article>
	<?php endwhile; ?>

	<!-- Paginazione -->
	<?php if ( $the_query->max_num_pages > 1 ) : ?>
	<div class="pagination">
		<?php
		echo paginate_links(array(
			'total'     => $the_query->max_num_pages,
			'current'   => $paged,
			'prev_text' => __('« Precedente'),
			'next_text' => __('Successivo »'),
		));
		?>
	</div>
	<?php endif; ?>

<?php else : ?>
	<p><em><?php _e('Nessun articolo trovato.'); ?></em></p>
<?php endif; ?>

<?php
// Resetta i dati della query globale
wp_reset_postdata();
?>
