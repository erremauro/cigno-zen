<?php
$author_id = is_author() ?
    get_queried_object_id() :
    (int) get_query_var('author');
$category_id = is_category() ?
    get_queried_object_id() :
    0;
$tag_slug = is_tag() ?
    get_query_var('tag') :
    get_query_var('tag');

$offset_param   = isset($_GET['offset']) ? max(0, intval($_GET['offset'])) : 0;
$posts_per_page = (int) get_option('posts_per_page');
$paged          = max(1, get_query_var('paged') ?: get_query_var('page') ?: 1);
$query_offset   = $offset_param + ($paged - 1) * $posts_per_page;
$featured_only  = isset($_GET['featured']) && intval($_GET['featured']) === 1;
$hide_from_list_filter = [
    'relation' => 'OR',
    [
        'key'     => 'hide_from_list',
        'compare' => 'NOT EXISTS',
    ],
    [
        'key'     => 'hide_from_list',
        'value'   => '1',
        'compare' => '!=',
    ],
];

// Argomenti per la query
$args = [
    'post_type'           => 'post',
    'posts_per_page'      => $posts_per_page,
    'paged'               => $paged,
    'ignore_sticky_posts' => true,
    'offset'              => $query_offset,
];

// Filtri condizionali
if ( $featured_only ) {
    $args['meta_query'] = [
        'relation' => 'AND',
        [
            'key'     => 'is_featured',
            'value'   => '1',
            'compare' => '=',
        ],
        $hide_from_list_filter,
    ];
    $args['meta_key'] = 'featured_order';
    $args['meta_type'] = 'NUMERIC';
    $args['orderby'] = [
        'meta_value_num' => 'ASC',
        'date'           => 'DESC',
    ];
    $args['offset'] = 0; // lista curata, no pagine saltate da offset
}
if ( ! $featured_only ) {
    $args['meta_query'] = [ $hide_from_list_filter ];
}
if ( $author_id ) {
    $args['author'] = $author_id;
}
if ( $category_id ) {
    $args['cat'] = $category_id; // preferibile a category_name quando sei già in archivio categoria
} elseif ( $cat_slug = get_query_var('category_name') ) {
    $args['category_name'] = $cat_slug;
}
if ( $tag_slug ) {
    $args['tag'] = $tag_slug; // slug singolo; per più tag usa tag_slug__in
}

// ── Ordinamento ────────────────────────────────────────────────────────────
$sort_fields = [
    'date'     => __( 'Data di pubblicazione', 'textdomain' ),
    'modified' => __( 'Data di modifica', 'textdomain' ),
    'title'    => __( 'Titolo', 'textdomain' ),
    'author'   => __( 'Autore', 'textdomain' ),
    'volume'   => __( 'Volume', 'textdomain' ),
];
$sort_default_order = [
    'date'     => 'DESC',
    'modified' => 'DESC',
    'title'    => 'ASC',
    'author'   => 'ASC',
    'volume'   => 'ASC',
];

[ $orderby_param, $order_param ] = cz_resolve_sort_params( $sort_fields, $sort_default_order );

// La lista "in evidenza" mantiene il proprio ordinamento curato: niente riordino utente.
$cz_custom_sort = null;
if ( ! $featured_only ) {
    switch ( $orderby_param ) {
        case 'modified':
            $args['orderby'] = [ 'modified' => $order_param, 'ID' => $order_param ];
            break;
        case 'title':
            $args['orderby'] = [ 'title' => $order_param, 'ID' => $order_param ];
            break;
        case 'author':
        case 'volume':
            $cz_custom_sort      = $orderby_param;
            $args['orderby']     = 'cz_sort_value';
            $args['order']       = $order_param;
            $args['cz_sort_by']  = $cz_custom_sort;
            break;
        case 'date':
        default:
            $args['orderby'] = [ 'date' => $order_param, 'ID' => $order_param ];
            break;
    }
}

// Autore/Volume richiedono un JOIN ad-hoc: post_author non ha un nome
// leggibile e il volume "selezionato" vive in una tabella di relazione.
$cz_sort_join_filter    = null;
$cz_sort_orderby_filter = null;

if ( $cz_custom_sort ) {
    $cz_sort_join_filter = function ( $join, $query ) use ( $cz_custom_sort ) {
        global $wpdb;
        if ( 'cz_sort_value' !== $query->get( 'orderby' ) || $cz_custom_sort !== $query->get( 'cz_sort_by' ) ) {
            return $join;
        }

        if ( 'author' === $cz_custom_sort ) {
            $join .= " LEFT JOIN {$wpdb->users} AS cz_sort_author ON cz_sort_author.ID = {$wpdb->posts}.post_author ";
        } elseif ( 'volume' === $cz_custom_sort ) {
            $items_table = $wpdb->prefix . 'cz_volume_items';
            $has_primary = (bool) $wpdb->get_var(
                $wpdb->prepare( "SHOW COLUMNS FROM {$items_table} LIKE %s", 'is_primary' )
            );
            $priority = $has_primary
                ? 'is_primary DESC, position ASC, id ASC'
                : 'position ASC, id ASC';

            // Stesso criterio di "volume selezionato" usato da cignozen_get_post_volume().
            $join .= " LEFT JOIN {$items_table} AS cz_vi ON cz_vi.post_id = {$wpdb->posts}.ID
                AND cz_vi.id = (
                    SELECT cz_vi2.id FROM {$items_table} AS cz_vi2
                    WHERE cz_vi2.post_id = cz_vi.post_id
                    ORDER BY {$priority}
                    LIMIT 1
                )
                LEFT JOIN {$wpdb->posts} AS cz_volume_post ON cz_volume_post.ID = cz_vi.volume_id ";
        }

        return $join;
    };

    $cz_sort_orderby_filter = function ( $orderby, $query ) use ( $cz_custom_sort, $order_param ) {
        if ( 'cz_sort_value' !== $query->get( 'orderby' ) || $cz_custom_sort !== $query->get( 'cz_sort_by' ) ) {
            return $orderby;
        }

        global $wpdb;
        if ( 'author' === $cz_custom_sort ) {
            return "cz_sort_author.display_name {$order_param}, {$wpdb->posts}.ID {$order_param}";
        }
        return "cz_volume_post.post_title {$order_param}, {$wpdb->posts}.ID {$order_param}";
    };

    add_filter( 'posts_join', $cz_sort_join_filter, 10, 2 );
    add_filter( 'posts_orderby', $cz_sort_orderby_filter, 10, 2 );
}

$the_query = new WP_Query( $args );

if ( $cz_custom_sort ) {
    remove_filter( 'posts_join', $cz_sort_join_filter, 10 );
    remove_filter( 'posts_orderby', $cz_sort_orderby_filter, 10 );
}
?>

<header class="archive-header">
  <h1 class="archive-title">
    <?php
    if ( is_category() ) {
        // Categoria
        single_cat_title( 'Categoria: ' );
    } elseif ( $featured_only ) {
        echo 'Articoli in Evidenza';
    } elseif ( is_tag() ) {
        echo 'Articoli Correlati';
    } elseif ( is_home() || is_archive() ) {
        // Elenco generale articoli
        echo 'Tutti gli Articoli';
    }
    ?>
  </h1>

  <?php if ( ! $featured_only ) : ?>
    <?php
    $sort_hidden_params = [];
    if ( isset( $_GET['featured'] ) ) {
        $sort_hidden_params['featured'] = wp_unslash( $_GET['featured'] );
    }
    cz_render_sort_control( $sort_fields, $orderby_param, $order_param, $sort_hidden_params );
    ?>
  <?php endif; ?>
</header>

<?php if ($the_query->have_posts()) : ?>
    <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="post-header has-border-bottom">
                <?php
                    if (! is_author() ):
                        display_author_info_conditionally();
                    endif
                ?>
                <?php display_volumes_name( null, true, 'volumes-link--lined' ) ?>
                <h2 class="post-title">
                    <a href="<?php the_permalink(); ?>">
                        <?php cz_the_html_title(); ?>
                    </a>
                </h2>
                <h4 class="post-subtitle">
                    <a href="<?php the_permalink(); ?>">
                        <?php the_subtitle(); ?>
                    </a>
                <h4>
            </header>
            <div class="post-content">
                <?php the_excerpt(); ?>
                <div class="more-text">
                    <a class="link-pill" href="<?php echo esc_url( get_permalink( get_the_ID() ) ); ?>">
                        <?php esc_html_e( 'Continua', 'textdomain' ); ?>
                    </a>
                </div>
            </div>
        </article>
    <?php endwhile; ?>

    <!-- Paginazione -->
    <?php if ( $the_query->max_num_pages > 1 ) : ?>
    <div class="pagination">
        <?php
        echo paginate_links(array(
            'total'     => $the_query->max_num_pages,
            'current'   => $paged,
            'prev_text' => __('« Precedente'),
            'next_text' => __('Successivo »'),
        ));
        ?>
    </div>
    <?php endif; ?>

<?php else : ?>
    <p class="no-article-found"><em><?php _e('Nessun articolo trovato.'); ?></em></p>
<?php endif; ?>

<?php
// Resetta i dati della query globale
wp_reset_postdata();
?>
