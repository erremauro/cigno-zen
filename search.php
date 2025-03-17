<?php get_template_part('parts/header'); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

    <?php if (have_posts()) : ?>

        <header class="page-header">
            <h1 class="search-page-title">
                <?php
                /* translators: %s: search query. */
                printf(esc_html__('Results for: %s', 'cigno-zen'), '<span>' . get_search_query() . '</span>');
                ?>
            </h1>
            <p><?php do_action( 'ep_suggestions' ); ?></p>
        </header>

        <?php
        // Start the Loop.
        while (have_posts()) :
            the_post();

            // Include the Post-Type-specific template for the content.
            get_template_part('parts/content', 'search');

        // End the loop.
        endwhile;

        // Previous/next page navigation.
        the_posts_navigation();

    // If no content, include the "No posts found" template.
    else :
        get_template_part('parts/content', 'none');

    endif;
    ?>

    </main><!-- #main -->
</div><!-- #primary -->

<?php get_template_part('parts/footer'); ?>
