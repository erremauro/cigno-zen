<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="post-header">
        <h2 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    </header><!-- .entry-header -->

    <div class="post-content">
        <?php
        // Usa la funzione di ElasticPress per evidenziare i risultati di ricerca
        if (function_exists('ep_highlight_excerpt')) {
            ep_highlight_excerpt();
        } else {
            the_excerpt();
        }
        ?>
        <p class="more-text"><a href="<?php the_permalink(); ?>" class="more-link">Continua ›</a></p>
    </div>
</article>
