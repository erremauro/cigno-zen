<?php
$paged = max( 1, (int) get_query_var('paged') );

// Context correnti (preferisci ID quando possibile)
$author_id     = is_author()   ? get_queried_object_id() : (int) get_query_var('author');
$category_id   = is_category() ? get_queried_object_id() : 0;                 // uso ID
$tag_slug      = is_tag()      ? get_query_var('tag')     : get_query_var('tag');

// Argomenti per la query
$args = [
    'post_type'           => 'post',
    'posts_per_page'      => 5,
    'paged'               => $paged,
    'ignore_sticky_posts' => true,
];

// Filtri condizionali
if ( $author_id ) {
    $args['author'] = $author_id;
}
if ( $category_id ) {
    $args['cat'] = $category_id; // preferibile a category_name quando sei già in archivio categoria
} elseif ( $cat_slug = get_query_var('category_name') ) {
    $args['category_name'] = $cat_slug;
}
if ( $tag_slug ) {
    $args['tag'] = $tag_slug; // slug singolo; per più tag usa tag_slug__in
}

$the_query = new WP_Query( $args );
?>

<header class="archive-header">
  <h1 class="archive-title">
    <?php
    if ( is_category() ) {
        // Categoria
        single_cat_title( 'Categoria: ' );
    } elseif ( is_tag() ) {
    	echo 'Articoli Correlati';
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
				<div class="more-text">
					<?php
						$url = get_permalink( get_the_ID() );
						get_template_part(
							'parts/cta-title-link',
							null,
							[
								'url'	=> $url,
								'title'	=> 'Continua'
							]
						);
					?>
				</div>
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
	<p class="no-article-found"><em><?php _e('Nessun articolo trovato.'); ?></em></p>
<?php endif; ?>

<?php
// Resetta i dati della query globale
wp_reset_postdata();
?>
