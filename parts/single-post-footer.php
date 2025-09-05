<footer class="post-footer">
<?php if ( has_tag() ) : ?>
<div class="post-tags-list">
    <h3>Argomenti Correlati</h3>
    <p class="description">Esplora gli argomenti trattati in questo articolo: clicca su un’etichetta per leggere altri contenuti sullo stesso tema.</p>

    <?php
    // Helper: safe ACF get for term meta (handles both "term_{id}" and "{tax}_{id}")
    $acf_term_get = function( string $field, WP_Term $term ) {
        if ( ! function_exists( 'get_field' ) ) return '';
        // Newer ACF accepts WP_Term or "term_{id}"
        $val = get_field( $field, $term );
        if ( $val === null || $val === '' ) {
            $val = get_field( $field, "{$term->taxonomy}_{$term->term_id}" );
        }
        if ( $val === null ) $val = '';
        return is_string($val) ? trim($val) : $val;
    };

    $terms = get_the_terms( get_the_ID(), 'post_tag' );
    $about_graph = []; // Will collect Things with alternateName for JSON-LD

    if ( $terms && ! is_wp_error( $terms ) ) :
        echo '<ul class="post-tags">';
        foreach ( $terms as $t ) {
            $link = get_term_link( $t );
            if ( is_wp_error( $link ) ) continue;

            // Display name: ACF 'show_as' fallback to term name
            $show_as = $acf_term_get( 'show_as', $t );
            $display_name = $show_as !== '' ? $show_as : $t->name;

            // Synonyms: ACF 'synonyms_csv' => array cleaned
            $syn_csv = $acf_term_get( 'synonyms_csv', $t );
            $synonyms = array_values( array_filter( array_map( function( $s ) {
                return trim( preg_replace( '/\s+/', ' ', $s ) );
            }, $syn_csv !== '' ? explode( ',', $syn_csv ) : [] ) ) );

            // Visible tag list uses display_name
            printf(
                '<li><a href="%s" rel="tag">%s</a></li>',
                esc_url( $link ),
                esc_html( $display_name )
            );

            // Build JSON-LD Thing for SEO with alternateName = synonyms
            $thing = [
                '@type'         => 'Thing',
                'name'          => $display_name,
            ];
            if ( ! empty( $synonyms ) ) {
                $thing['alternateName'] = $synonyms;
            }
            // Add canonical url of the tag if you like (helps disambiguate)
            $thing['sameAs'] = [ esc_url_raw( $link ) ];

            $about_graph[] = $thing;
        }
        echo '</ul>';
    endif;

    // Existing JSON-LD (kept as-is if available)
    if ( function_exists( 'cz_print_article_jsonld_with_tags' ) ) {
        cz_print_article_jsonld_with_tags( get_post() );
    }

    // Extra JSON-LD for synonyms (safe to include alongside your Article JSON-LD)
    if ( ! empty( $about_graph ) ) {
        $jsonld = [
            '@context' => 'https://schema.org',
            '@graph'   => $about_graph,
        ];
        echo '<script type="application/ld+json">' . wp_json_encode( $jsonld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>';
    }
    ?>
</div>
<?php endif; ?>

<div class="related-articles">
    <?php
    // Jetpack related posts (if available)
    if ( class_exists( 'Jetpack_RelatedPosts' ) ) {
        echo do_shortcode( '[jprel]' );
    }
    ?>
</div>
</footer>
