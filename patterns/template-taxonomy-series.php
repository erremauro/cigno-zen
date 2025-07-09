<?php
$term = get_queried_object(); // Ottiene il termine attuale della tassonomia
?>

<div class="wp-group">
	<?php display_series_author(); ?>
	<h1 class="series-title"><?php echo esc_html($term->name); ?></h1>
</div>

<?php
$args = array(
	'post_type' => array('post'), // Includi entrambi i tipi di post
	'tax_query' => array(
		array(
			'taxonomy' => 'series',
			'field'    => 'slug',
			'terms'    => $term->slug,
		),
	),
	'posts_per_page' => -1, // Mostra tutti i post
	'meta_key' => 'chapter', // Chiave del custom field da utilizzare per l'ordinamento
	'orderby' => 'meta_value_num', // Ordina per il valore numerico del custom field
	'order' => 'ASC', // Ordine crescente
);

$query = new WP_Query($args);
?>

<div class="series-chapters">
<?php
if ($query->have_posts()) :
	echo '<ul class="series-posts">';
	while ($query->have_posts()) : $query->the_post(); ?>
		<li>
			<h2 class="chapter-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		</li>
	<?php endwhile;
	echo '</ul>';

	wp_reset_postdata();
else :
	echo '<p>Nessun contenuto disponibile per questa serie.</p>';
endif;
?>
