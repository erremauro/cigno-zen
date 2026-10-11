<?php
// Indice delle liste (CPT cz_list del plugin cz-lists): archivio /liste/ e pagine /liste/numero/{n}/.
$count_term = is_tax( 'cz_list_count' ) ? get_queried_object() : null;

if ( $count_term ) {
	$number = (int) get_term_meta( $count_term->term_id, 'cz_list_number', true );
	$title  = 1 === $number
		? 'Liste di un elemento'
		: sprintf( 'Liste di %s elementi', strtolower( $count_term->name ) );
} else {
	$title = 'Liste';
}
?>

<section class="lists-index">
	<header class="archive-header">
		<h1 class="archive-title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $count_term ) : ?>
			<p><a class="link-pill" href="<?php echo esc_url( get_post_type_archive_link( 'cz_list' ) ); ?>">Tutte le liste</a></p>
		<?php else : ?>
			<p>Le liste numeriche della tradizione buddhista, raggruppate per numero di elementi.</p>
		<?php endif; ?>
	</header>

	<?php if ( function_exists( 'cz_lists_the_index' ) ) cz_lists_the_index(); ?>
</section>
