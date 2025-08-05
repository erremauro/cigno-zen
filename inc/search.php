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
