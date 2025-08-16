<footer class="post-footer">
<?php if ( has_tag() ) : ?>
<div class="post-tags-list">
	<h3>Argomenti Correlati</h3>
	<p class="description">Esplora gli argomenti trattati in questo articolo: clicca su un’etichetta per leggere altri contenuti sullo stesso tema.</p>

	<?php
	$terms = get_the_terms( get_the_ID(), 'post_tag' );
	if ( $terms && ! is_wp_error( $terms ) ) :
		echo '<ul class="post-tags">';
		foreach ( $terms as $t ) {
			$link = get_term_link( $t );
			if ( is_wp_error( $link ) ) {
				continue;
			}
			printf(
				'<li><a href="%s" rel="tag">%s</a></li>',
				esc_url( $link ),
				esc_html( $t->name )
			);
		}
		echo '</ul>';
	endif;

	if ( function_exists( 'cz_print_article_jsonld_with_tags' ) ) {
		cz_print_article_jsonld_with_tags( get_post() );
	}
	?>
</div>

<?php endif ?>

<div class="related-articles">
	<?php
	// Jetpack related posts (if available)
	if ( class_exists( 'Jetpack_RelatedPosts' ) ) {
		echo do_shortcode( '[jprel]' );
	}
	?>
</div>
</footer>
