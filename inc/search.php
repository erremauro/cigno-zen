<?php

/**
 * Funzionalità di ElasticPress
 */
add_filter(
	'ep_suggestion_html',
	function ($html, $terms, $query) {
		$valid_terms = array_filter($terms, function ($term) {
			return !empty(trim($term['text']));
		});

		if (!empty($valid_terms)) {
			$html = '<div class="ep-suggestions">';
			$html .= '<p class="ep-suggestion-item">' . esc_html__('Forse intendevi', 'elasticpress');
			foreach ($terms as $term) {
				$html .= ' <a href="' . esc_url(get_search_link($term['text'])) . '">' . esc_html($term['text']) . '</a>';
			}
			$html .= '?</p>';
			$html .= '</div>';
		}

		return $html;
	},
	10,
	3
);

/**
 * Funzioni di utilità
 */
function get_highlighted_paragraph($content, $search_term) {
	$content = preg_replace('/<sup>.*?<\/sup>/', '', $content);

	$dom = new DOMDocument();
	libxml_use_internal_errors(true);
	$loaded = $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));

	if (!$loaded) {
		libxml_clear_errors();
		return wp_trim_words($content, 55, '...');
	}

	$xpath = new DOMXPath($dom);
	$nodes = $xpath->query("//*[contains(translate(text(), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), '" . strtolower($search_term) . "')]");

	if ($nodes->length > 0) {
		$node = $nodes->item(0);
		$paragraph = $node;
		while ($paragraph && $paragraph->nodeName !== 'p') {
			$paragraph = $paragraph->parentNode;
		}

		if ($paragraph && $paragraph->nodeName === 'p') {
			$paragraph_content = $dom->saveHTML($paragraph);
			$highlighted_paragraph = preg_replace(
				'/(' . preg_quote($search_term, '/') . ')/i',
				'<mark class="highlight">$1</mark>',
				$paragraph_content
			);

			return wp_kses_post($highlighted_paragraph);
		}
	}

	libxml_clear_errors();
	return wp_trim_words($content, 55, '...');
}

/**
 * Suggerimenti ricerca live per top bar.
 */
function cignozen_ajax_search_suggestions() {
	$raw_query = isset( $_GET['q'] ) ? wp_unslash( $_GET['q'] ) : '';
	$query     = sanitize_text_field( $raw_query );
	$query     = trim( $query );

	if ( mb_strlen( $query ) < 2 ) {
		wp_send_json_success(
			array(
				'items' => array(),
			)
		);
	}

	$post_types = get_post_types(
		array(
			'public'              => true,
			'exclude_from_search' => false,
		)
	);

	unset( $post_types['attachment'] );

	$search_query = new WP_Query(
		array(
			'post_type'              => array_values( $post_types ),
			'post_status'            => 'publish',
			's'                      => $query,
			'posts_per_page'         => 8,
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		)
	);

	$items = array();
	$type_objects = get_post_types( array(), 'objects' );

	if ( $search_query->have_posts() ) {
		foreach ( $search_query->posts as $post ) {
			$post_type = get_post_type( $post );
			$label     = isset( $type_objects[ $post_type ] ) ? $type_objects[ $post_type ]->labels->singular_name : '';

			$items[] = array(
				'id'    => (int) $post->ID,
				'title' => cignozen_prepare_suggestion_title( get_the_title( $post ) ),
				'url'   => get_permalink( $post ),
				'type'  => $post_type,
				'label' => $label,
			);
		}
	}

	wp_reset_postdata();

	$dictionary_items = cignozen_get_dictionary_suggestions( $query, 8 );
	$merged           = cignozen_balance_suggestions( array( $dictionary_items, $items ), 8 );

	wp_send_json_success(
		array(
			'items' => $merged,
		)
	);
}
add_action( 'wp_ajax_cignozen_search_suggestions', 'cignozen_ajax_search_suggestions' );
add_action( 'wp_ajax_nopriv_cignozen_search_suggestions', 'cignozen_ajax_search_suggestions' );

/**
 * Converte eventuali entità HTML in caratteri reali e pulisce il testo.
 */
function cignozen_prepare_suggestion_title( $raw_title ) {
	$title = wp_strip_all_tags( (string) $raw_title );

	$decoded_once = html_entity_decode( $title, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$decoded_twice = html_entity_decode( $decoded_once, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

	return trim( $decoded_twice );
}

/**
 * Bilancia i suggerimenti alternando i risultati per label (Dizionario, Articolo, Maestro...).
 */
function cignozen_balance_suggestions( $lists, $limit = 8 ) {
	$queues      = array();
	$label_order = array();
	$seen_urls   = array();

	foreach ( (array) $lists as $list ) {
		foreach ( (array) $list as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$url = isset( $item['url'] ) ? trim( (string) $item['url'] ) : '';
			if ( '' === $url || isset( $seen_urls[ $url ] ) ) {
				continue;
			}
			$seen_urls[ $url ] = true;

			$label = isset( $item['label'] ) ? trim( (string) $item['label'] ) : '';
			if ( '' === $label ) {
				$label = 'Altro';
			}

			if ( ! isset( $queues[ $label ] ) ) {
				$queues[ $label ] = array();
				$label_order[]    = $label;
			}

			$queues[ $label ][] = $item;
		}
	}

	$merged = array();

	while ( count( $merged ) < $limit ) {
		$added_this_round = false;

		foreach ( $label_order as $label ) {
			if ( empty( $queues[ $label ] ) ) {
				continue;
			}

			$merged[] = array_shift( $queues[ $label ] );
			$added_this_round = true;

			if ( count( $merged ) >= $limit ) {
				break;
			}
		}

		if ( ! $added_this_round ) {
			break;
		}
	}

	return $merged;
}

/**
 * Normalizza una stringa in alfabeto latino base per match più tolleranti.
 */
function cignozen_normalize_latin( $value ) {
	$value = strtolower( (string) $value );
	$value = wp_strip_all_tags( $value );
	$value = strtr(
		$value,
		array(
			'ā' => 'a',
			'ă' => 'a',
			'à' => 'a',
			'á' => 'a',
			'â' => 'a',
			'ä' => 'a',
			'ã' => 'a',
			'å' => 'a',
			'ī' => 'i',
			'ì' => 'i',
			'í' => 'i',
			'î' => 'i',
			'ï' => 'i',
			'ū' => 'u',
			'ù' => 'u',
			'ú' => 'u',
			'û' => 'u',
			'ü' => 'u',
			'ṛ' => 'r',
			'ṝ' => 'r',
			'ḷ' => 'l',
			'ḹ' => 'l',
			'ṃ' => 'm',
			'ṁ' => 'm',
			'ṅ' => 'n',
			'ñ' => 'n',
			'ṇ' => 'n',
			'ṭ' => 't',
			'ḍ' => 'd',
			'ś' => 's',
			'ṣ' => 's',
			'ḥ' => 'h',
			'œ' => 'oe',
			'æ' => 'ae',
		)
	);
	$value = preg_replace( '/[^a-z0-9\s\-_]/', '', $value );
	$value = preg_replace( '/\s+/', ' ', (string) $value );
	return trim( (string) $value );
}

/**
 * Verifica match sia in forma originale che normalizzata.
 */
function cignozen_match_search_text( $needle, $haystack ) {
	$needle   = trim( (string) $needle );
	$haystack = trim( (string) $haystack );

	if ( '' === $needle || '' === $haystack ) {
		return false;
	}

	if ( false !== mb_stripos( $haystack, $needle ) ) {
		return true;
	}

	$normalized_needle   = cignozen_normalize_latin( $needle );
	$normalized_haystack = cignozen_normalize_latin( $haystack );

	if ( '' === $normalized_needle || '' === $normalized_haystack ) {
		return false;
	}

	return false !== strpos( $normalized_haystack, $normalized_needle );
}

/**
 * Restituisce un rank del match:
 * - 0: haystack inizia con needle (raw o normalizzato)
 * - 1: haystack contiene needle (raw o normalizzato)
 * - false: nessun match
 */
function cignozen_get_match_rank( $needle, $haystack ) {
	$needle   = trim( (string) $needle );
	$haystack = trim( (string) $haystack );

	if ( '' === $needle || '' === $haystack ) {
		return false;
	}

	if ( 0 === mb_stripos( $haystack, $needle ) ) {
		return 0;
	}
	if ( false !== mb_stripos( $haystack, $needle ) ) {
		return 1;
	}

	$normalized_needle   = cignozen_normalize_latin( $needle );
	$normalized_haystack = cignozen_normalize_latin( $haystack );

	if ( '' === $normalized_needle || '' === $normalized_haystack ) {
		return false;
	}

	$normalized_len = strlen( $normalized_needle );
	if ( 0 === strncmp( $normalized_haystack, $normalized_needle, $normalized_len ) ) {
		return 0;
	}
	if ( false !== strpos( $normalized_haystack, $normalized_needle ) ) {
		return 1;
	}

	return false;
}

/**
 * Suggerimenti dal dizionario (taxonomy post_tag) su nome, show_as, readings.
 */
function cignozen_get_dictionary_suggestions( $query, $limit = 8 ) {
	if ( '' === trim( (string) $query ) || ! taxonomy_exists( 'post_tag' ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'post_tag',
			'hide_empty' => false,
			'number'     => 0,
		)
	);

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return array();
	}

	$matches = array();

	foreach ( $terms as $term ) {
		if ( ! ( $term instanceof WP_Term ) ) {
			continue;
		}

		$show_as  = (string) get_term_meta( $term->term_id, 'show_as', true );
		$readings = (string) get_term_meta( $term->term_id, 'readings', true );

		$name_rank     = cignozen_get_match_rank( $query, $term->name );
		$show_as_rank  = cignozen_get_match_rank( $query, $show_as );
		$readings_rank = cignozen_get_match_rank( $query, $readings );

		if ( false === $name_rank && false === $show_as_rank && false === $readings_rank ) {
			continue;
		}

		$term_link = get_term_link( $term );
		if ( is_wp_error( $term_link ) ) {
			continue;
		}

		$scores = array();
		if ( false !== $name_rank ) {
			$scores[] = (int) $name_rank;
		}
		if ( false !== $show_as_rank ) {
			$scores[] = 2 + (int) $show_as_rank;
		}
		if ( false !== $readings_rank ) {
			$scores[] = 4 + (int) $readings_rank;
		}
		$score = ! empty( $scores ) ? min( $scores ) : 99;

		$matches[] = array(
			'score' => $score,
			'item'  => array(
				'id'    => (int) $term->term_id,
				'title' => cignozen_prepare_suggestion_title( $term->name ),
				'url'   => $term_link,
				'type'  => 'post_tag',
				'label' => 'Dizionario',
			),
		);
	}

	if ( empty( $matches ) ) {
		return array();
	}

	usort(
		$matches,
		function ( $a, $b ) {
			$score_a = isset( $a['score'] ) ? (int) $a['score'] : 99;
			$score_b = isset( $b['score'] ) ? (int) $b['score'] : 99;
			if ( $score_a === $score_b ) {
				$title_a = isset( $a['item']['title'] ) ? (string) $a['item']['title'] : '';
				$title_b = isset( $b['item']['title'] ) ? (string) $b['item']['title'] : '';
				return strnatcasecmp( $title_a, $title_b );
			}
			return $score_a - $score_b;
		}
	);

	$items = array();
	foreach ( $matches as $row ) {
		if ( isset( $row['item'] ) && is_array( $row['item'] ) ) {
			$items[] = $row['item'];
		}
		if ( count( $items ) >= $limit ) {
			break;
		}
	}

	return $items;
}
