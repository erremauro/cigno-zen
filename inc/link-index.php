<?php

/**
 * Link Index — indice inverso dei link interni per invalidazione selettiva della cache.
 *
 * Mantiene la tabella {prefix}cz_link_index che registra quali post linkano
 * quali URL interni. Quando un post viene pubblicato o eliminato, vengono
 * individuati tutti i post sorgente che lo linkano e la loro page cache W3TC
 * viene svuotata, in modo che il broken-link highlighter possa ri-valutarli.
 *
 * Dipendenze:
 *  - inc/broken-link.php (usa gli stessi transient cz_bl_ + md5)
 *  - W3 Total Cache (opzionale: se assente, si limita ad invalidare i transient)
 */

// ── Creazione / aggiornamento tabella ────────────────────────────────────────

function cz_link_index_create_table(): void {
    global $wpdb;

    $table   = $wpdb->prefix . 'cz_link_index';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table} (
        source_post_id BIGINT UNSIGNED NOT NULL,
        target_url_hash CHAR(32)       NOT NULL,
        PRIMARY KEY (source_post_id, target_url_hash),
        KEY idx_target_hash (target_url_hash)
    ) ENGINE=InnoDB {$charset};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );

    update_option( 'cz_link_index_db_version', '1', false );
}

// Crea la tabella all'attivazione del tema e la prima volta che manca.
add_action( 'after_switch_theme', 'cz_link_index_create_table' );

add_action( 'init', static function (): void {
    if ( get_option( 'cz_link_index_db_version' ) !== '1' ) {
        cz_link_index_create_table();
    }
} );

// ── Utilità ──────────────────────────────────────────────────────────────────

/**
 * Normalizza un URL esattamente come fa cz_url_is_valid() in broken-link.php:
 * rimuove frammento, query string e trailing slash.
 */
function cz_normalize_internal_url( string $url ): string {
    $pos = strpos( $url, '#' );
    if ( false !== $pos ) {
        $url = substr( $url, 0, $pos );
    }

    $pos = strpos( $url, '?' );
    if ( false !== $pos ) {
        $url = substr( $url, 0, $pos );
    }

    return untrailingslashit( $url );
}

/**
 * Estrae gli URL interni assoluti e normalizzati presenti nell'href dei link
 * del contenuto HTML passato.
 *
 * @return list<string>
 */
function cz_extract_internal_links( string $content ): array {
    $home_host = (string) parse_url( home_url(), PHP_URL_HOST );
    $links     = [];

    preg_match_all( '/<a\b[^>]+>/i', $content, $matches );

    foreach ( $matches[0] as $tag ) {
        if ( ! preg_match( '/\bhref=(["\'])([^"\']*)\1/i', $tag, $href_m ) ) {
            continue;
        }

        $href = $href_m[2];

        if ( '' === $href || '#' === $href[0] ) {
            continue;
        }

        $href_host = (string) parse_url( $href, PHP_URL_HOST );

        // Scarta link esterni
        if ( $href_host && $href_host !== $home_host ) {
            continue;
        }

        // Rendi assoluto
        if ( str_starts_with( $href, '//' ) ) {
            $href = 'https:' . $href;
        } elseif ( str_starts_with( $href, '/' ) ) {
            $href = home_url( $href );
        }

        $normalized = cz_normalize_internal_url( $href );
        if ( '' !== $normalized ) {
            $links[] = $normalized;
        }
    }

    return array_values( array_unique( $links ) );
}

// ── Aggiornamento indice al salvataggio del post ─────────────────────────────

add_action( 'save_post', static function ( int $post_id ): void {
    if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
        return;
    }

    $post = get_post( $post_id );
    if ( ! $post ) {
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'cz_link_index';

    // Sostituisce le righe precedenti del post sorgente.
    $wpdb->delete( $table, [ 'source_post_id' => $post_id ], [ '%d' ] );

    $links = cz_extract_internal_links( $post->post_content );
    if ( empty( $links ) ) {
        return;
    }

    $rows = [];
    foreach ( $links as $url ) {
        $rows[] = $wpdb->prepare( '(%d, %s)', $post_id, md5( $url ) );
    }

    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $wpdb->query(
        'INSERT IGNORE INTO ' . $table . ' (source_post_id, target_url_hash) VALUES '
        . implode( ', ', $rows )
    );
}, 10 );

// Pulizia dell'indice alla cancellazione definitiva del post.
add_action( 'delete_post', static function ( int $post_id ): void {
    global $wpdb;
    $wpdb->delete(
        $wpdb->prefix . 'cz_link_index',
        [ 'source_post_id' => $post_id ],
        [ '%d' ]
    );
} );

// ── Invalidazione cache al cambio di stato pubblicazione ─────────────────────

/**
 * Quando la pubblicazione di un post cambia (draft↔publish, publish→trash, ecc.),
 * trova tutti i post che linkano quell'URL e:
 *  1. Svuota il transient cz_bl_ del target (già fatto da broken-link.php, qui
 *     è ridondante ma difensivo per ordine di caricamento).
 *  2. Svuota la page cache W3TC di ogni post sorgente.
 */
add_action(
    'transition_post_status',
    static function ( string $new_status, string $old_status, WP_Post $post ): void {
        // Agisce solo quando lo stato "published" cambia in qualche direzione.
        if ( ! in_array( 'publish', [ $new_status, $old_status ], true ) ) {
            return;
        }

        if ( $new_status === $old_status ) {
            return;
        }

        $permalink = get_permalink( $post->ID );
        if ( ! $permalink ) {
            return;
        }

        $normalized = cz_normalize_internal_url( $permalink );
        $hash       = md5( $normalized );

        // Invalida il transient del target (ridondante ma difensivo).
        delete_transient( 'cz_bl_' . $hash );

        global $wpdb;

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $source_ids = $wpdb->get_col(
            $wpdb->prepare(
                'SELECT source_post_id FROM ' . $wpdb->prefix . 'cz_link_index WHERE target_url_hash = %s',
                $hash
            )
        );

        if ( empty( $source_ids ) ) {
            return;
        }

        $has_w3tc = function_exists( 'w3tc_flush_post' );

        foreach ( $source_ids as $source_id ) {
            $source_id = (int) $source_id;

            // Svuota page cache W3TC del post sorgente.
            if ( $has_w3tc ) {
                w3tc_flush_post( $source_id );
            }
        }
    },
    10,
    3
);

// ── WP-CLI: wp cignozen index-links ──────────────────────────────────────────

if ( defined( 'WP_CLI' ) && WP_CLI ) {

    /**
     * Ricostruisce l'indice cz_link_index su tutti i post pubblicati esistenti.
     */
    class CZ_Index_Links_Command {

        /**
         * Scansiona tutti i post pubblicati e (ri)costruisce l'indice dei link interni.
         *
         * Utile per popolare l'indice retroattivamente dopo l'attivazione del tema
         * o per forzare una riscansione completa.
         *
         * ## EXAMPLES
         *
         *     wp cignozen index-links
         *
         * @when after_wp_load
         */
        public function __invoke( array $args, array $assoc_args ): void {
            global $wpdb;
            $table = $wpdb->prefix . 'cz_link_index';

            $post_types        = array_values( get_post_types( [ 'public' => true ] ) );
            $type_placeholders = implode( ', ', array_fill( 0, count( $post_types ), '%s' ) );

            // Conteggio totale per la progress bar.
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $total = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->posts}
                     WHERE post_status = 'publish'
                       AND post_type IN ({$type_placeholders})",
                    ...$post_types
                )
            );

            if ( 0 === $total ) {
                WP_CLI::warning( 'Nessun post pubblicato trovato.' );
                return;
            }

            $progress = \WP_CLI\Utils\make_progress_bar(
                "Indicizzazione link su {$total} post",
                $total
            );

            $posts_processed = 0;
            $links_indexed   = 0;
            $batch_size      = 100;
            $offset          = 0;

            do {
                // Recupera solo ID e contenuto per ridurre la memoria.
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $posts = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT ID, post_content FROM {$wpdb->posts}
                         WHERE post_status = 'publish'
                           AND post_type IN ({$type_placeholders})
                         ORDER BY ID ASC
                         LIMIT %d OFFSET %d",
                        ...array_merge( $post_types, [ $batch_size, $offset ] )
                    )
                );

                foreach ( $posts as $post ) {
                    $post_id = (int) $post->ID;

                    $wpdb->delete( $table, [ 'source_post_id' => $post_id ], [ '%d' ] );

                    $links = cz_extract_internal_links( (string) $post->post_content );

                    if ( ! empty( $links ) ) {
                        $rows = [];
                        foreach ( $links as $url ) {
                            $rows[] = $wpdb->prepare( '(%d, %s)', $post_id, md5( $url ) );
                        }
                        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                        $wpdb->query(
                            'INSERT IGNORE INTO ' . $table . ' (source_post_id, target_url_hash) VALUES '
                            . implode( ', ', $rows )
                        );
                        $links_indexed += count( $links );
                    }

                    ++$posts_processed;
                    $progress->tick();
                }

                $offset += $batch_size;
                $this->free_memory();

            } while ( count( $posts ) === $batch_size );

            $progress->finish();

            WP_CLI::success(
                sprintf(
                    '%d post processati, %d link indicizzati.',
                    $posts_processed,
                    $links_indexed
                )
            );
        }

        /**
         * Libera la memoria accumulata da wpdb e dall'object cache tra un batch e l'altro.
         * Pattern standard consigliato dalla documentazione WP-CLI.
         */
        private function free_memory(): void {
            global $wpdb, $wp_object_cache;

            $wpdb->queries = [];

            if ( ! is_object( $wp_object_cache ) ) {
                return;
            }

            $wp_object_cache->group_ops      = [];
            $wp_object_cache->stats          = [];
            $wp_object_cache->memcache_debug = [];
            $wp_object_cache->cache          = [];

            if ( method_exists( $wp_object_cache, '__remoteset' ) ) {
                $wp_object_cache->__remoteset();
            }
        }
    }

    WP_CLI::add_command( 'cignozen index-links', 'CZ_Index_Links_Command' );
}
