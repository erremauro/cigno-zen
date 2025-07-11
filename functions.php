<?php

/**
 * Setup del tema
 */
function cigno_zen_setup() {
	load_theme_textdomain('cigno-zen', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'cigno_zen_setup');


function cigno_zen_styles() {
  $css_file = get_stylesheet_directory() . '/style.css';
  $css_version = filemtime($css_file);


  error_log('css_path: ' . $css_file);
  error_log('filemtime: ' . filemtime($css_file));

  wp_enqueue_style(
    'cigno-zen-style',
    get_stylesheet_uri(),
    [],
    $css_version
  );
}
add_action('wp_enqueue_scripts', 'cigno_zen_styles');


/**
 * Caricamento di script e stili
 */
function cigno_zen_scripts() {
	//wp_enqueue_style('cigno-zen-style', get_stylesheet_uri());
	wp_enqueue_script('cigno-zen-script', get_template_directory_uri() . '/assets/js/script.js', array(), '1.0.0', true);
	carica_google_fonts();
}
add_action('wp_enqueue_scripts', 'cigno_zen_scripts');

function carica_google_fonts() {
	wp_enqueue_style('libre-baskerville', 'https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap', false);
}

/**
 * Funzionalità di Jetpack
 */
function jetpackme_remove_rp() {
	if (class_exists('Jetpack_RelatedPosts')) {
		$jprp = Jetpack_RelatedPosts::init();
		$callback = array($jprp, 'filter_add_target_to_dom');
		remove_filter('the_content', $callback, 40);
	}
}
add_filter('wp', 'jetpackme_remove_rp', 20);

function jetpackme_custom_related() {
	if (class_exists('Jetpack_RelatedPosts') && method_exists('Jetpack_RelatedPosts', 'init_raw')) {
		$related = Jetpack_RelatedPosts::init_raw()
			->set_query_name('edent-related-shortcode')
			->get_for_post_id(get_the_ID(), array('size' => 4));

		if ($related) {
			$output = "<h2 id='related-posts'>Altri Articoli:</h2>";
			$output .= "<ul class='related-posts'>";

			foreach ($related as $result) {
				$related_post_id = $result['id'];
				$related_post = get_post($related_post_id);
				$related_post_title = $related_post->post_title;
				$related_post_link = get_permalink($related_post_id);

				$output .= '<li class="related-post">';
				$output .= "<a href='{$related_post_link}'>{$related_post_title}</a>";
				$output .= "</li>";
			}
			$output .= "</ul>";
		}
		echo $output;
	}
}
add_shortcode('jprel', 'jetpackme_custom_related');

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

function display_author_info_conditionally() {
	$author_name = get_the_author();
	if ($author_name !== 'cigno') {
		echo '<div class="entry-meta">';
		the_author_posts_link();
		echo '</div>';
	}
}

function display_volumes_author( $term = null, $echo = true ) {
    if ( ! $term ) {
        $term = get_queried_object();
    }

    if ( ! $term || ! isset( $term->term_id ) ) {
        return '';
    }

    $author = get_field( 'author', $term->taxonomy . '_' . $term->term_id );

    // Se il campo restituisce un array, prendiamo il primo elemento
    if ( is_array( $author ) ) {
        $author = reset( $author );
    }

    if ( ! ( $author instanceof WP_User ) ) {
        return '';
    }

    $author_id   = $author->ID;
    $author_name = esc_html( $author->display_name );
    $author_url  = esc_url( get_author_posts_url( $author_id ) );

    $output  = '<div class="volumes-author">';
    $output .= ' <a href="' . $author_url . '">' . $author_name . '</a>';
    $output .= '</div>';

    if ( $echo ) {
        echo $output;
        return;
    }

    return $output;
}

function display_volumes_name() {
	$volumes_terms = get_the_terms(get_the_ID(), 'volumes');
	if ($volumes_terms && !is_wp_error($volumes_terms)) {
		$volumes_term = array_shift($volumes_terms);
		$volumes_link = get_term_link($volumes_term);
		echo '<p class="volumes-link"><a href="' . esc_url($volumes_link) . '">' . esc_html($volumes_term->name) . '</a></p>';
	}
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

function custom_post_pagination() {
    global $page, $numpages;

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

