<?php
// Start the WordPress Loop
while ( have_posts() ) : the_post();

    $pdf_file = function_exists('get_field') ? get_field('article_pdf', get_the_ID()) : '';
    $pdf_id = 0;
    $pdf_url = '';

    if (is_numeric($pdf_file)) {
        $pdf_id = (int) $pdf_file;
    } elseif (is_array($pdf_file)) {
        if (!empty($pdf_file['ID'])) {
            $pdf_id = (int) $pdf_file['ID'];
        } elseif (!empty($pdf_file['id'])) {
            $pdf_id = (int) $pdf_file['id'];
        } elseif (!empty($pdf_file['url'])) {
            $pdf_url = (string) $pdf_file['url'];
        }
    }

    if ($pdf_id > 0) {
        $pdf_url = (string) wp_get_attachment_url($pdf_id);
    }

    ?>

    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <header class="post-header<?php echo $pdf_url !== '' ? ' has-article-actions' : ''; ?>">
            <?php display_author_info_conditionally(); ?>
            <?php display_volumes_name(); ?>
            <h1 class="post-title"><?php the_title(); ?></h1>
            <h3 class="post-subtitle"><?php the_subtitle(); ?></h3>
            <?php if ($pdf_url !== '') : ?>
                <section class="article-actions" aria-label="<?php esc_attr_e('Azioni articolo', 'textdomain'); ?>">
                    <a class="link-pill" href="<?php echo esc_url($pdf_url); ?>" download>
                        <?php esc_html_e('Scarica PDF', 'textdomain'); ?>
                    </a>
                </section>
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
