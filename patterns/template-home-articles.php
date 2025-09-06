<section class="home-section" id="latest-articles">
    <?php
    get_template_part(
        'parts/cta-title-link',
        null,
        [
            "url"   => "/articoli",
            "title" => "Leggi gli Articoli"
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

    echo do_shortcode( '[separator]' );

    // --- Successivi 4 articoli in griglia 2x2 ---
    $args = array(
        'posts_per_page' => 3,
        'post_status'    => 'publish',
        'offset'         => 1, // salta il primo articolo già mostrato
    );
    $recent_posts = get_posts($args);

    if ($recent_posts) :
        echo '<div class="article-grid">';
        foreach ($recent_posts as $post) :
            setup_postdata($post);
            ?>
            <a class="article-card" href="<?php echo get_permalink($post); ?>">
                <p class="article-author"><?php the_author(); ?></p>
                <h3 class="article-title"><?php the_title(); ?></h3>
            </a>
            <?php
        endforeach;
        echo '</div>';
        wp_reset_postdata();
    endif;
    ?>
</section>
