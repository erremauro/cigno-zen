<section class="home-section" id="latest-articles">
    <?php
    $count_posts = wp_count_posts('post');
    $total_posts = $count_posts->publish;
    get_template_part(
        'parts/cta-title-link',
        null,
        [
            "url"   => "/articoli",
            "title" => "Leggi gli Articoli",
            "desc"  => "Immergiti tra gli oltre " . $total_posts . " articoli pubblicati"
        ]
    );

    // --- Ultimo articolo in evidenza ---
    $args = [
        'posts_per_page' => 1,
        'post_status'    => 'publish',
    ];
    $latest_post = get_posts($args);

    if ($latest_post) :
        $post = $latest_post[0];
        setup_postdata($post);
        ?>
        <a class="article-featured" href="<?php echo get_permalink($post); ?>">
            <div class="article-meta">
                <p class="article-author"><?php the_author(); ?></p>
                <h2 class="article-title"><?php the_title(); ?></h2>
            </div>
        </a>
        <?php
        wp_reset_postdata();
    endif;

    echo do_shortcode('[separator]');

    // --- Successivi 3 articoli ---
    $args = [
        'posts_per_page' => 3,
        'post_status'    => 'publish',
        'offset'         => 1,
    ];
    $recent_posts = get_posts($args);
    $count_posts  = is_array($recent_posts) ? count($recent_posts) : 0;

    if ($recent_posts) :
        ?>
        <div class="article-grid cz-carousel" data-count="<?php echo esc_attr($count_posts); ?>">
            <div class="cz-carousel-track">
                <?php foreach ($recent_posts as $post) : setup_postdata($post); ?>
                    <a class="article-card" href="<?php echo get_permalink($post); ?>">
                        <p class="article-author"><?php the_author(); ?></p>
                        <h3 class="article-title"><?php the_title(); ?></h3>
                    </a>
                <?php endforeach; wp_reset_postdata(); ?>
            </div>

            <?php if ($count_posts > 1): ?>
                <div class="cz-carousel-dots" role="tablist" aria-label="Scorri articoli">
                    <?php for ($i = 0; $i < $count_posts; $i++): ?>
                        <button
                            type="button"
                            class="cz-carousel-dot<?php echo $i === 0 ? ' is-active' : ''; ?>"
                            data-index="<?php echo esc_attr($i); ?>"
                            role="tab"
                            aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
                            aria-label="Mostra articolo <?php echo esc_attr($i + 1); ?>"
                        ><?php echo esc_html($i + 1); ?></button>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    endif;
    ?>
    <div class="more-articles" style="text-align: center; margin: 2em;">
        <a class="link-pill" href="/articoli/?offset=4">Continua&hellip;</a>
    </div>
</section>
