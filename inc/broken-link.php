<?php

/**
 * Broken-link highlighter — stile MediaWiki.
 *
 * Aggiunge la classe CSS `broken-link` ai link interni che puntano a risorse
 * inesistenti (post, pagine, tassonomie, autori). I risultati sono cachati a
 * due livelli (variabile statica per-request + transient WordPress) per ridurre
 * al minimo le query al database durante il rendering.
 */

// ── Inline CSS ─────────────────────────────────────────────────────────────

add_action( 'wp_enqueue_scripts', function () {
	wp_add_inline_style(
		'cigno-zen-style',
		'.broken-link, .broken-link:visited, .broken-link:hover, .broken-link:active { color: #ba0000; }'
	);
} );

// ── Filtro contenuto ────────────────────────────────────────────────────────

add_filter( 'the_content', 'cz_highlight_broken_links', 20 );

function cz_highlight_broken_links( string $content ): string {
	$home_host = (string) parse_url( home_url(), PHP_URL_HOST );

	return preg_replace_callback(
		'/<a\b[^>]+>/i',
		static function ( array $m ) use ( $home_host ): string {
			$tag = $m[0];

			// Estrai href (virgolette singole o doppie)
			if ( ! preg_match( '/\bhref=(["\'])([^"\']*)\1/i', $tag, $href_m ) ) {
				return $tag;
			}

			$href = $href_m[2];

			// Ignora link vuoti o solo ancorati (#...)
			if ( '' === $href || '#' === $href[0] ) {
				return $tag;
			}

			$href_host = (string) parse_url( $href, PHP_URL_HOST );

			// Ignora link esterni (host diverso e non-relativo)
			if ( $href_host && $href_host !== $home_host ) {
				return $tag;
			}

			// Rendi assoluto l'URL relativo
			$absolute = $href;
			if ( str_starts_with( $href, '//' ) ) {
				$absolute = 'https:' . $href;
			} elseif ( str_starts_with( $href, '/' ) ) {
				$absolute = home_url( $href );
			}

			if ( cz_url_is_valid( $absolute ) ) {
				return $tag;
			}

			// Aggiunge la classe broken-link senza toccare le classi esistenti
			if ( preg_match( '/\bclass=(["\'])([^"\']*)\1/i', $tag, $class_m ) ) {
				$new_class = trim( $class_m[2] . ' broken-link' );
				return str_replace(
					$class_m[0],
					'class=' . $class_m[1] . $new_class . $class_m[1],
					$tag
				);
			}

			// Nessuna classe presente: la inserisce prima della >
			return rtrim( $tag, '>' ) . ' class="broken-link">';
		},
		$content
	);
}

// ── Validazione URL ─────────────────────────────────────────────────────────

/**
 * Determina se un URL interno assoluto corrisponde a una risorsa esistente.
 * Cache a due livelli: variabile statica (per-request) e transient (12 ore).
 */
function cz_url_is_valid( string $url ): bool {
	static $cache = [];

	// Scarta frammento e query string: servono solo per la navigazione
	$url = (string) strtok( $url, '#' );
	$url = (string) strtok( $url, '?' );
	$url = untrailingslashit( $url );

	if ( '' === $url ) {
		return true;
	}

	// Home URL sempre valido
	if ( $url === untrailingslashit( home_url() ) ) {
		return true;
	}

	if ( array_key_exists( $url, $cache ) ) {
		return $cache[ $url ];
	}

	$t_key  = 'cz_bl_' . md5( $url );
	$stored = get_transient( $t_key );

	if ( false !== $stored ) {
		return $cache[ $url ] = ( '1' === $stored );
	}

	$is_valid      = cz_resolve_url( $url );
	$cache[ $url ] = $is_valid;
	set_transient( $t_key, $is_valid ? '1' : '0', 12 * HOUR_IN_SECONDS );

	return $is_valid;
}

/**
 * Risolve effettivamente la validità dell'URL tramite le API di WordPress.
 *
 * Ordine di controllo (dal più economico al più costoso):
 *  1. url_to_postid() — copre post, pagine e CPT
 *  2. Archivi tassonomia (category, tag)
 *  3. Archivi autore
 *  4. Archivi per data (anno/mese/giorno)
 */
function cz_resolve_url( string $url ): bool {
	// 1. Post / pagine / CPT
	if ( url_to_postid( $url ) > 0 ) {
		return true;
	}

	$path     = trim( (string) parse_url( $url, PHP_URL_PATH ), '/' );
	$segments = $path !== '' ? explode( '/', $path ) : [];

	if ( empty( $segments ) ) {
		return true; // radice del sito
	}

	$first = $segments[0];

	// 2. Archivi tassonomia: /base/slug oppure /slug se la base è vuota
	$cat_base = trim( get_option( 'category_base', '' ), '/' );
	$tag_base = trim( get_option( 'tag_base', '' ), '/' );

	// Tassonomie con base non vuota: l'URL è /{base}/{slug}
	$prefixed = [];
	if ( $cat_base !== '' ) {
		$prefixed[ $cat_base ] = 'category';
	}
	if ( $tag_base !== '' ) {
		$prefixed[ $tag_base ] = 'post_tag';
	}

	if ( isset( $prefixed[ $first ] ) && isset( $segments[1] ) ) {
		return (bool) get_term_by( 'slug', end( $segments ), $prefixed[ $first ] );
	}

	// Tassonomie con base vuota: l'URL è /{slug} — il primo segmento è lo slug stesso
	$baseless = [];
	if ( $cat_base === '' ) {
		$baseless[] = 'category';
	}
	if ( $tag_base === '' ) {
		$baseless[] = 'post_tag';
	}

	foreach ( $baseless as $taxonomy ) {
		if ( get_term_by( 'slug', $first, $taxonomy ) ) {
			return true;
		}
	}

	// 3. Archivio autore (base personalizzata "autore" impostata nel tema)
	global $wp_rewrite;
	$author_base = isset( $wp_rewrite->author_base ) ? trim( $wp_rewrite->author_base, '/' ) : 'author';

	if ( $first === $author_base && isset( $segments[1] ) ) {
		return (bool) get_user_by( 'login', $segments[1] );
	}

	// 4. Archivi per data: /YYYY/, /YYYY/MM/, /YYYY/MM/DD/
	if ( preg_match( '/^\d{4}$/', $first ) ) {
		return true;
	}

	return false;
}

// ── Invalidazione cache ─────────────────────────────────────────────────────

/**
 * Cancella il transient di un permalink quando il post cambia stato,
 * così un link a una pagina appena pubblicata smette di apparire come rotto.
 */
add_action(
	'transition_post_status',
	function ( string $new_status, string $old_status, WP_Post $post ): void {
		if ( $new_status === $old_status ) {
			return;
		}

		$permalink = get_permalink( $post->ID );
		if ( ! $permalink ) {
			return;
		}

		$url = untrailingslashit( (string) strtok( $permalink, '#' ) );
		$url = (string) strtok( $url, '?' );
		delete_transient( 'cz_bl_' . md5( $url ) );
	},
	10,
	3
);
