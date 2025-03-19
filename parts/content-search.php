<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="post-header">
		<h2 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
	</header><!-- .entry-header -->

	<div class="post-content">
		<?php
        // Ottieni il contenuto del post
        $content = get_the_content();

        // Ottieni il termine di ricerca
        $search_term = get_search_query();

        // Ottieni l'estratto evidenziato
        echo get_highlighted_paragraph($content, $search_term);
        ?>
		<p class="more-text"><a href="<?php the_permalink(); ?>" class="more-link">Continua ›</a></p>
	</div>
</article>
