<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="post-header">
		<h2 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
	</header><!-- .entry-header -->

	<div class="post-content">
		<?php
        // Ottieni il contenuto del post
        $content = get_post_field('post_content', get_the_ID());
        $raw = strip_shortcodes($content);

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
            <?php
                $url = get_permalink( get_the_ID() );
                get_template_part(
                    'parts/cta-title-link',
                    null,
                    [
                        'url'   => $url,
                        'title' => 'Continua'
                    ]
                );
            ?>
        </div>
	</div>
</article>
