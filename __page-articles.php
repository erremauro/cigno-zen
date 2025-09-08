<?php
/**
 * Template Name: Articles
 * Description: Lists site posts with dates, author (with ACF portrait), subtitle, categories, tags. Paginated (10 per page).
 */

get_template_part( 'parts/header' );

// Determine current page for pagination
$paged = max( 1, get_query_var('paged') ? get_query_var('paged') : get_query_var('page') );

// Custom query for posts
$query = new WP_Query( [
    'post_type'      => 'post',
    'posts_per_page' => 10,
    'paged'          => $paged,
    'post_status'    => 'publish',
] );
?>
<style>
/* ===== Minimal layout for the list ===== */
.articles-wrap {
    max-width: 1100px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.article-card {
    display: grid;
    grid-template-columns: 64px 1fr;
    gap: 1rem;
    padding: 1.25rem;
    border: 1px solid rgba(60,60,60,1);
    background-color: rgba(30,30,30,1);
    border-radius: 16px;
    margin-bottom: 1rem;
}

.article-card h2 {
    margin: 0 0 .25rem 0;
    font-size: 1.4rem;
    line-height: 1.2;
}

.article-subtitle {
    margin: 0 0 .5rem 0;
    color: #c0c0c3;
    font-style: italic;
    font-size: 1rem;
}

.article-meta,
.article-tax {
    font-size: .95rem;
    color: #b0b0b3;
}

.article-meta .dot {
    padding: 0 .4rem;
    opacity: .6;
}

.article-author {
    display: flex;
    align-items: center;
    gap: .5rem;
}

.article-author .author-portrait {
    width: 64px;
    height: 64px;
    object-fit: cover;
    border: 1px solid rgba(60,60,60,1);
}

.article-tax .label {
    text-transform: uppercase;
    font-size: .8rem;
    letter-spacing: .06em;
    opacity: .8;
    margin-right: .5rem;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: .4rem;
    margin: 2rem 0;
    flex-wrap: wrap;
}

.pagination a,
.pagination span {
    border: 1px solid rgba(60,60,60,1);
    background-color: rgba(30,30,30,1);
    color: #98989B;
    padding: .4rem .7rem;
    border-radius: 999px;
    font-size: .95rem;
}

.pagination .current {
    background: rgba(60,60,60,1);
    border-color: rgba(90,90,90,1);
    color: #fff;
}
</style>

<main class="articles-wrap">
    <?php if ( $query->have_posts() ) : ?>
        <?php while ( $query->have_posts() ) : $query->the_post(); ?>
            <?php
            // -- Gather data for each post --

            // Dates
            $published_date = get_the_date();
            $modified_date  = get_the_modified_date();

            // Author and ACF portrait (stored on user profile: 'user_{ID}')
            $author_id      = get_post_field( 'post_author', get_the_ID() );
            $author_name    = get_the_author_meta( 'display_name', $author_id );
            $acf_portrait   = function_exists('get_field') ? get_field( 'author_portrait', 'user_' . $author_id ) : null;

            // Fallback to WP avatar if ACF field is empty
            if ( empty( $acf_portrait ) ) {
                $acf_portrait = get_avatar_url( $author_id, [ 'size' => 128 ] );
            }

            // Subtitle (ACF field on post)
            $subtitle = function_exists('get_field') ? get_field( 'sottotitolo', get_the_ID() ) : '';

            // Categories and Tags HTML
            $cats_html = get_the_category_list( ' · ' );
            $tags_html = get_the_tag_list( '', ' · ' );
            ?>
            <article <?php post_class('article-card'); ?>>
                <div class="article-author">
                    <?php if ( $acf_portrait ) : ?>
                        <img class="author-portrait" src="<?php echo esc_url( $acf_portrait ); ?>" alt="<?php echo esc_attr( $author_name ); ?>" width="64" height="64" />
                    <?php endif; ?>
                </div>
                <div>
                    <header>
                        <h2>
                            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h2>
                        <?php if ( ! empty( $subtitle ) ) : ?>
                            <p class="article-subtitle"><?php echo esc_html( $subtitle ); ?></p>
                        <?php endif; ?>
                        <div class="article-meta">
                            <span><strong><?php echo esc_html( $author_name ); ?></strong></span>
                            <span class="dot">•</span>
                            <span><?php echo esc_html__( 'Published:', 'your-textdomain' ); ?> <?php echo esc_html( $published_date ); ?></span>
                            <span class="dot">/</span>
                            <span><?php echo esc_html__( 'Updated:', 'your-textdomain' ); ?> <?php echo esc_html( $modified_date ); ?></span>
                        </div>
                    </header>

                    <div class="article-tax" style="margin-top:.5rem;">
                        <?php if ( $cats_html ) : ?>
                            <div class="article-cats">
                                <span class="label"><?php echo esc_html__( 'Categories', 'your-textdomain' ); ?>:</span>
                                <span><?php echo wp_kses_post( $cats_html ); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ( $tags_html ) : ?>
                            <div class="article-tags" style="margin-top:.25rem;">
                                <span class="label"><?php echo esc_html__( 'Tags', 'your-textdomain' ); ?>:</span>
                                <span><?php echo wp_kses_post( $tags_html ); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>

        <nav class="pagination" aria-label="<?php esc_attr_e('Pagination', 'your-textdomain'); ?>">
            <?php
            echo paginate_links( [
                'total'     => $query->max_num_pages,
                'current'   => $paged,
                'type'      => 'list',
                'prev_text' => '«',
                'next_text' => '»',
            ] );
            ?>
        </nav>

    <?php else : ?>
        <p><?php esc_html_e( 'No posts found.', 'your-textdomain' ); ?></p>
    <?php endif; wp_reset_postdata(); ?>
</main>

<?php get_template_part( 'parts/footer' ); ?>
