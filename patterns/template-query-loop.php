<?php
// Ottieni il numero della pagina corrente
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Ottieni l'ID dell'autore corrente (se presente)
$author_id = get_query_var('author');

// Ottieni lo slug della categoria corrente (se siamo in una categoria)
$category_slug = get_query_var('category_name');

// Argomenti per la query
$args = array(
    'post_type'      => 'post',
    'posts_per_page' => 5,
    'paged'          => $paged,
);

// Se c'è un autore, filtra per autore
if ($author_id) {
    $args['author'] = $author_id;
}

// Se c'è una categoria, filtra per categoria
if ($category_slug) {
    $args['category_name'] = $category_slug;
}

// Esegui la query
$the_query = new WP_Query($args);
?>

<header class="archive-header">
  <h1 class="archive-title">
    <?php
    if ( is_category() ) {
        // Categoria
        single_cat_title( 'Categoria: ' );
    } elseif ( is_home() || is_archive() ) {
        // Elenco generale articoli
        echo 'Tutti gli Articoli';
    }
    ?>
  </h1>
</header>

<?php if ($the_query->have_posts()) : ?>
	<?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="post-header">
				<?php
					if (! is_author() ):
						display_author_info_conditionally();
					endif
				?>
				<?php display_volumes_name() ?>
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
