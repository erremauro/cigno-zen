<?php

// ── Titolo HTML ─────────────────────────────────────────────────────────────

/**
 * Restituisce il titolo del post: il campo ACF "titolo_html" se compilato,
 * altrimenti il titolo WordPress plain-text. L'HTML è limitato a tag inline
 * sicuri per una resa tipografica (corsivo, ruby, ecc.).
 */
function cz_get_html_title( int $post_id = 0 ): string {
	if ( ! $post_id ) {
		$post_id = get_the_ID() ?: 0;
	}

	$html = function_exists( 'get_field' ) ? (string) get_field( 'titolo_html', $post_id ) : '';
	$html = trim( $html );

	if ( $html !== '' ) {
		return wp_kses( $html, [
			'em'     => [],
			'i'      => [],
			'strong' => [],
			'b'      => [],
			'span'   => [ 'class' => true, 'lang' => true ],
			'ruby'   => [],
			'rt'     => [],
			'rp'     => [],
			'br'     => [],
			'sup'    => [],
			'sub'    => [],
		] );
	}

	return esc_html( get_the_title( $post_id ) );
}

function cz_the_html_title( int $post_id = 0 ): void {
	echo cz_get_html_title( $post_id );
}

// Registrazione campo ACF via codice
add_action( 'acf/init', function (): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( [
		'key'      => 'group_cz_titolo_html',
		'title'    => 'Titolo HTML',
		'fields'   => [
			[
				'key'           => 'field_cz_titolo_html',
				'label'         => 'Titolo HTML',
				'name'          => 'titolo_html',
				'type'          => 'text',
				'instructions'  => 'HTML inline per il titolo (es. <em>, <ruby>). Lascia vuoto per usare il titolo standard.',
				'required'      => 0,
				'placeholder'   => 'Es. Il <em>Dharma</em> della non-dualità',
			],
		],
		'location' => [
			[ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'post' ] ],
		],
		'position' => 'side',
		'style'    => 'default',
	] );
} );

function display_author_info_conditionally() {
	$author_name = get_the_author();
	if ($author_name !== 'cigno') {
		echo '<div class="entry-meta">';
		the_author_posts_link();
		echo '</div>';
	}
}

function has_the_subtitle() {
	$sottotitolo = get_field('sottotitolo');
	return !empty($sottotitolo);
}

function the_subtitle() {
	// Controlla se il campo ACF 'sottotitolo' ha un valore
	if (get_field('sottotitolo')) {
		// Ottieni il valore del campo 'sottotitolo'
		$sottotitolo = get_field('sottotitolo');
		// Visualizza il sottotitolo racchiuso in un tag <h3>
		echo esc_html($sottotitolo);
	}
}

/**
 * Mostra la data di pubblicazione e, se diversa, quella di modifica.
 * $style: 'icon' (icone, per Anteprima e single-post) o 'meta' (righe
 * etichettate "Data di pubblicazione:" / "Ultima Modifica:", per la vista Elenco).
 */
function cz_render_post_dates( int $post_id = 0, string $style = 'icon' ): void {
	if ( ! $post_id ) {
		$post_id = get_the_ID() ?: 0;
	}
	if ( ! $post_id ) {
		return;
	}

	$published_ts = get_post_time( 'U', false, $post_id );
	$modified_ts  = get_post_modified_time( 'U', false, $post_id );

	if ( ! $published_ts ) {
		return;
	}

	$show_modified = $modified_ts
		&& date_i18n( 'Y-m-d', $modified_ts ) !== date_i18n( 'Y-m-d', $published_ts );

	if ( 'meta' === $style ) {
		echo '<div class="post-meta-row post-meta-date-published" aria-label="' . esc_attr__( 'Data di pubblicazione', 'textdomain' ) . '">';
		get_template_part( 'parts/svg/clock' );
		echo '<time class="post-date-value" datetime="' . esc_attr( date_i18n( 'c', $published_ts ) ) . '">'
			. esc_html( date_i18n( 'j M Y', $published_ts ) ) . '</time>';
		echo '</div>';

		if ( $show_modified ) {
			echo '<div class="post-meta-row post-meta-date-modified" aria-label="' . esc_attr__( 'Ultima Modifica', 'textdomain' ) . '">';
			get_template_part( 'parts/svg/pencil' );
			echo '<time class="post-date-value" datetime="' . esc_attr( date_i18n( 'c', $modified_ts ) ) . '">'
				. esc_html( date_i18n( 'j M Y', $modified_ts ) ) . '</time>';
			echo '</div>';
		}
		return;
	}

	echo '<div class="post-dates">';

	echo '<span class="post-date post-date-published" title="' . esc_attr__( 'Data di pubblicazione', 'textdomain' ) . '">';
	get_template_part( 'parts/svg/clock' );
	echo '<time class="post-date-value" datetime="' . esc_attr( date_i18n( 'c', $published_ts ) ) . '">'
		. esc_html( date_i18n( 'j M Y', $published_ts ) ) . '</time>';
	echo '</span>';

	if ( $show_modified ) {
		echo '<span class="post-date post-date-modified" title="' . esc_attr__( 'Ultima Modifica', 'textdomain' ) . '">';
		get_template_part( 'parts/svg/pencil' );
		echo '<time class="post-date-value" datetime="' . esc_attr( date_i18n( 'c', $modified_ts ) ) . '">'
			. esc_html( date_i18n( 'j M Y', $modified_ts ) ) . '</time>';
		echo '</span>';
	}

	echo '</div>';
}

function custom_post_pagination() {
	global $multipage, $page, $numpages;

	if ( empty($multipage) ) return;

	// Inizia l'output del contenitore
	echo '<div class="post-pagination-control">';
	echo '<p class="pagination-control">';

	// Controlla se ci sono più pagine
	if ($numpages > 1) {
		// Mostra il link "Previous" se non siamo alla prima pagina
		if ($page > 1) {
			echo _wp_link_page($page - 1) . '« Indietro</a>';
		} else {
			echo '<a class="empty-link">« Indietro</a>'; // Elemento vuoto per "Previous"
		}

		// Mostra il link "Next" se non siamo all'ultima pagina
		if ($page < $numpages) {
			echo _wp_link_page($page + 1) . 'Avanti »</a>';
		} else {
			echo '<a class="empty-link">Avanti »</a>'; // Elemento vuoto per "Next"
		}
	} else {
		// Se c'è solo una pagina, mostra entrambi gli elementi vuoti
		echo '<a class="empty-link">« Indietro</a>';
		echo '<a class="empty-link">Avanti »</a>';
	}

	// Chiude l'output del contenitore
	echo '</p></div>';
}

// ── Titolo e description unici per le sotto-pagine paginate (<!--nextpage-->) ──
// Senza questo, tutte le sotto-pagine di un post lungo condividono lo stesso
// <title> e la stessa meta description (generati da Rank Math sul post base),
// segnale di contenuto duplicato che porta Google a scansionarle senza indicizzarle.
//
// Non possiamo usare le global $page/$numpages/$pages di WordPress: sono
// popolate da setup_postdata() dentro The Loop, che nei template di questo
// tema parte DOPO l'header (quindi dopo cignozen_get_title()). Ricalcoliamo
// quindi lo split per <!--nextpage--> direttamente dal post corrente, che è
// già risolvibile in head tramite get_post()/get_query_var('page').

function cz_get_pagination_state(): array {
	static $state = null;
	if ( $state !== null ) {
		return $state;
	}

	$post = is_singular() ? get_post() : null;

	if ( ! $post || strpos( $post->post_content, '<!--nextpage-->' ) === false ) {
		return $state = [ 'page' => 1, 'numpages' => 1, 'chunks' => [] ];
	}

	$chunks   = explode( '<!--nextpage-->', $post->post_content );
	$numpages = count( $chunks );
	$page     = max( 1, min( $numpages, (int) get_query_var( 'page' ) ?: 1 ) );

	return $state = [ 'page' => $page, 'numpages' => $numpages, 'chunks' => $chunks ];
}

/**
 * Estrae un estratto testuale dalla sotto-pagina correntemente mostrata.
 */
function cz_get_paginated_excerpt( int $length = 155 ): string {
	$state = cz_get_pagination_state();
	$chunk = $state['chunks'][ $state['page'] - 1 ] ?? '';

	if ( $chunk === '' ) {
		return '';
	}

	$chunk = strip_shortcodes( $chunk );
	$chunk = wp_strip_all_tags( $chunk );
	$chunk = trim( preg_replace( '/\s+/', ' ', $chunk ) );

	if ( $chunk === '' ) {
		return '';
	}

	if ( mb_strlen( $chunk ) > $length ) {
		$chunk = mb_substr( $chunk, 0, $length );
		$chunk = preg_replace( '/\s+\S*$/u', '', $chunk ) . '…';
	}

	return $chunk;
}

function cz_is_paginated_subpage(): bool {
	$state = cz_get_pagination_state();
	return $state['numpages'] > 1 && $state['page'] > 1;
}

function cz_append_page_suffix( string $title ): string {
	$state = cz_get_pagination_state();
	return trim( $title ) . sprintf( ' – Parte %d di %d', $state['page'], $state['numpages'] );
}

// Rank Math: il tag <title> visibile è generato da cignozen_get_title() (vedi
// inc/styles-and-scripts.php, che chiama cz_append_page_suffix() direttamente),
// ma Rank Math usa comunque il proprio titolo calcolato per og:title/twitter:title
// e per lo schema JSON-LD — lo teniamo allineato con lo stesso suffisso.
add_filter( 'rank_math/frontend/title', function ( $title ) {
	if ( cz_is_paginated_subpage() ) {
		$title = cz_append_page_suffix( (string) $title );
	}
	return $title;
} );

add_filter( 'rank_math/frontend/description', function ( $description ) {
	if ( cz_is_paginated_subpage() ) {
		$excerpt = cz_get_paginated_excerpt();
		if ( $excerpt !== '' ) {
			return $excerpt;
		}
	}
	return $description;
} );

// Reindirizza a un post casuale se presente ?random=1
add_action('template_redirect', function () {
    if ( isset($_GET['random']) ) {
        $rand = get_posts([
            'posts_per_page' => 1,
            'post_status'    => 'publish',
            'orderby'        => 'rand',
            'ignore_sticky_posts' => true,
        ]);
        if ( $rand ) {
            wp_safe_redirect( get_permalink($rand[0]), 302 );
            exit;
        }
        // Fallback: se non ci sono post pubblici
        wp_safe_redirect( home_url('/') );
        exit;
    }
});
