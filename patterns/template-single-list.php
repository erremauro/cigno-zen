<?php
// Singola lista (CPT cz_list del plugin cz-lists): niente autore, date né tempo di lettura.
while ( have_posts() ) : the_post(); ?>

    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <header class="post-header has-border-bottom">
            <?php // Stesso stile del nome del volume sopra il titolo degli articoli. ?>
            <p class="volumes-link volumes-link--lined">
                <a href="<?php echo esc_url( get_post_type_archive_link( 'cz_list' ) ); ?>">Tutte le liste</a>
            </p>
            <h1 class="post-title"><?php cz_the_html_title(); ?></h1>
            <?php if ( function_exists( 'cz_lists_the_names' ) ) cz_lists_the_names(); ?>
        </header>

        <div class="post-content">
            <?php the_content(); ?>
        </div>

        <?php get_template_part('parts/single-post-footer'); ?>
    </article>

<?php endwhile; ?>
