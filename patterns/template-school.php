<?php
$term = get_queried_object();
if ( ! ( $term instanceof WP_Term ) ) {
	return;
}

// Descrizione dal campo ACF WYSIWYG; in mancanza, la descrizione standard del termine.
// Entrambe passano da the_content, così gli shortcode (es. [maestro]) vengono eseguiti.
$acf_description = function_exists( 'get_field' ) ? (string) get_field( 'description', $term, false ) : '';
$raw_description = trim( $acf_description ) !== '' ? $acf_description : (string) $term->description;
$description     = trim( $raw_description ) !== '' ? apply_filters( 'the_content', $raw_description ) : '';

// Tutti i maestri della scuola (sottoscuole incluse), in ordine alfabetico.
$masters = get_posts([
	'post_type'      => 'maestro',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'orderby'        => 'title',
	'order'          => 'ASC',
	'no_found_rows'  => true,
	'tax_query'      => [[
		'taxonomy' => $term->taxonomy,
		'field'    => 'term_id',
		'terms'    => $term->term_id,
	]],
]);
?>

<div class="tags-header-container">
	<h3 class="tag-header-title">Scuola</h3>

	<header class="tag-header school-header">
		<h1 class="tag-title"><?php echo esc_html( $term->name ); ?></h1>

		<?php if ( $description ) : ?>
			<div class="tag-definition">
				<?php echo wp_kses_post( $description ); ?>
			</div>
		<?php endif; ?>
	</header>
</div>

<section class="school-masters">
	<h2>Maestri</h2>

	<?php if ( $masters ) : ?>
		<ul class="school-masters-list">
			<?php foreach ( $masters as $master ) :
				$romaji      = (string) get_post_meta( $master->ID, 'name_romaji', true );
				$traditional = (string) get_post_meta( $master->ID, 'traditional_name', true );
				if ( $traditional === '' ) {
					$traditional = (string) get_post_meta( $master->ID, 'name_hanzi', true );
				}
				$by = (int) get_post_meta( $master->ID, 'birth_year', true );
				$dy = (int) get_post_meta( $master->ID, 'death_year', true );
				?>
				<li class="school-master-item">
					<?php if ( $romaji !== '' ) : ?>
						<span class="school-master-romaji">[ <?php echo esc_html( $romaji ); ?> ]</span>
					<?php endif; ?>
					<a class="school-master-name" href="<?php echo esc_url( get_permalink( $master ) ); ?>"><?php echo esc_html( get_the_title( $master ) ); ?></a>
					<?php if ( $traditional !== '' ) : ?>
						<span class="school-master-traditional" lang="zh-Hant"><?php echo esc_html( $traditional ); ?></span>
					<?php endif; ?>
					<?php if ( $by || $dy ) : ?>
						<span class="school-master-years"><?php echo esc_html( ( $by ?: '?' ) . ' – ' . ( $dy ?: '?' ) ); ?></span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<p>Nessun maestro trovato per questa scuola.</p>
	<?php endif; ?>
</section>
