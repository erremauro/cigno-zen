<?php
// Start the WordPress Loop
while ( have_posts() ) : the_post();

    $resolve_attachment = static function ($field_name) {
        $file = function_exists('get_field') ? get_field($field_name, get_the_ID()) : '';
        $id = 0;
        $url = '';

        if (is_numeric($file)) {
            $id = (int) $file;
        } elseif (is_array($file)) {
            if (!empty($file['ID'])) {
                $id = (int) $file['ID'];
            } elseif (!empty($file['id'])) {
                $id = (int) $file['id'];
            } elseif (!empty($file['url'])) {
                $url = (string) $file['url'];
            }
        } elseif (is_string($file) && $file !== '') {
            $url = $file;
        }

        if ($id <= 0 && $url !== '') {
            $id = (int) attachment_url_to_postid($url);
        }
        if ($id > 0 && $url === '') {
            $url = (string) wp_get_attachment_url($id);
        }

        return [
            'id' => $id,
            'url' => $url,
        ];
    };

    $pdf = $resolve_attachment('article_pdf');
    $audio = $resolve_attachment('article_audio');
    $audio_player_shortcode = $audio['id'] > 0 ? sprintf('[player id="%d"]', $audio['id']) : '';
    $has_article_actions = ($pdf['url'] !== '' || $audio_player_shortcode !== '');

    ?>

    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <header class="post-header<?php echo $has_article_actions ? ' has-article-actions' : ''; ?>">
            <?php display_author_info_conditionally(); ?>
            <?php display_volumes_name(); ?>
            <h1 class="post-title"><?php the_title(); ?></h1>
            <h3 class="post-subtitle"><?php the_subtitle(); ?></h3>
            <?php if ($has_article_actions) : ?>
                <section class="article-actions" aria-label="<?php esc_attr_e('Azioni articolo', 'textdomain'); ?>">
                    <?php if ($pdf['url'] !== '') : ?>
                        <a class="link-pill" href="<?php echo esc_url($pdf['url']); ?>" download>
                            <?php esc_html_e('Scarica PDF', 'textdomain'); ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($audio_player_shortcode !== '') : ?>
                        <button
                            type="button"
                            class="link-pill js-reveal-audio-player"
                            data-label-collapsed="<?php echo esc_attr__('Ascolta Audio', 'textdomain'); ?>"
                            data-label-expanded="<?php echo esc_attr__('Nascondi Audio', 'textdomain'); ?>"
                            aria-expanded="false"
                            aria-controls="article-audio-player-<?php the_ID(); ?>">
                            <?php esc_html_e('Ascolta Audio', 'textdomain'); ?>
                        </button>
                    <?php endif; ?>
                </section>

                <?php if ($audio_player_shortcode !== '') : ?>
                    <div id="<?php echo esc_attr('article-audio-player-' . get_the_ID()); ?>" class="article-audio-player" hidden>
                        <?php echo do_shortcode($audio_player_shortcode); ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </header>

        <div class="post-content">
            <?php
            the_content();

            custom_post_pagination();

            wp_link_pages( array(
                'before'         => '<div class="post-pagination">' . __('<h5>Pagine</h5><p class="page-links">', 'textdomain'),
                'next_or_number' => 'number',
                'after'          => '</p></div>',
            ) );
            ?>
        </div>

        <?php get_template_part('parts/single-post-footer'); ?>
    </article>

<?php endwhile; ?>
