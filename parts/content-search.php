<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="post-header">
		<h2 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
	</header><!-- .entry-header -->

	<div class="post-content">
		<?php
        // Ottieni il contenuto del post
        if ('maestro' === get_post_type() && function_exists('cz_get_maestro_search_content')) {
            // La biografia dei Maestri vive dentro [collapsable id="bio"]:
            // strip_shortcodes() sul post_content intero la cancellerebbe.
            $raw = cz_get_maestro_search_content(get_the_ID());
        } else {
            $content = get_post_field('post_content', get_the_ID());
            $raw = strip_shortcodes($content);
        }

        // Solo se davvero non c'è alcun testo (Maestro senza biografia) usa un
        // testo descrittivo di riserva costruito dai campi strutturati.
        if ('' === trim(wp_strip_all_tags($raw)) && 'maestro' === get_post_type() && function_exists('cz_get_maestro_search_fallback_text')) {
            $raw = '<p>' . esc_html(cz_get_maestro_search_fallback_text(get_the_ID())) . '</p>';
        }

        // Ottieni il termine di ricerca
        $search_term = get_search_query();

        $snippet = get_highlighted_paragraph( $raw, $search_term );

        // Se manca <p>, aggiungilo ora
        if (strpos($snippet, '<p') === false) {
            // false = non convertire le singole \n in <br>, solo paragrafi
            $snippet = wpautop($snippet, false);
        }

        // (opzionale) bilancia eventuali tag aperti
        $snippet = force_balance_tags($snippet);

        // (opzionale) consenti solo qualche tag “sicuro” nel risultato
        $snippet = wp_kses($snippet, [
            'p'      => ['class' => [], 'id' => []],
            'a'      => ['href' => [], 'title' => [], 'rel' => []],
            'strong' => [],
            'em'     => [],
            'mark'   => [],
            'br'     => [],
            'span'   => ['class' => []],
        ]);

        echo $snippet;
        ?>
		<div class="more-text">
            <a class="link-pill" href="<?php echo esc_url( get_permalink( get_the_ID() ) ); ?>">
                <?php esc_html_e( 'Continua', 'textdomain' ); ?>
            </a>
        </div>
	</div>
</article>
