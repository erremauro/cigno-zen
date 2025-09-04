<section class="home-section" id="latest-articles">
    <?php
    get_template_part(
        'parts/cta-title-link',
        null,
        [
            "url"   => "/articoli",
            "title" => "Ultimi Articoli"
        ]
    );

    // --- Ultimo articolo in evidenza ---
    $args = array(
        'posts_per_page' => 1,
        'post_status'    => 'publish',
    );
    $latest_post = get_posts($args);

    if ($latest_post) :
        $post = $latest_post[0];
        setup_postdata($post);
        ?>
        <a class="article-featured" href="<?php echo get_permalink($post); ?>">
            <div class="article-meta">
                <h2 class="article-title"><?php the_title(); ?></h2>
                <p class="article-author"><?php the_author(); ?></p>
            </div>
        </a>
        <?php
        wp_reset_postdata();
    endif;
    ?>
</section>
