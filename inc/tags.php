<?php
/**
 * Parse a CSV-like string of synonyms into a clean array
 */
function cz_parse_synonyms_csv( $raw ): array {
	if ( ! is_string( $raw ) || $raw === '' ) {
		return [];
	}
	// Split on commas (supports both ',' and '，'); also tolerates semicolons
	$items = preg_split( '/[,，;]+/u', $raw );
	$clean = [];

	foreach ( (array) $items as $item ) {
		$label = trim( wp_strip_all_tags( (string) $item ) );
		if ( $label !== '' ) {
			$clean[] = $label;
		}
	}
	// Deduplicate while preserving order
	return array_values( array_unique( $clean ) );
}

/**
 * Build JSON-LD for a tag term (DefinedTerm) using ACF fields
 * Requires ACF field on term: synonyms_csv (Text)
 * Optional ACF fields on term: short_definition (WYSIWYG) or description (WYSIWYG)
 */
function cz_build_defined_term_jsonld( WP_Term $term ): array {
	$term_link = get_term_link( $term );
	if ( is_wp_error( $term_link ) ) {
		$term_link = '';
	}

	$short_definition = get_field( 'short_definition', $term );
	$long_description = get_field( 'description', $term );
	$synonyms_csv     = get_field( 'synonyms_csv', $term );

	$alternate_names = cz_parse_synonyms_csv( $synonyms_csv );

	$json = [
		'@type'             => 'DefinedTerm',
		'@id'               => $term_link ? $term_link . '#term' : null,
		'name'              => $term->name,
		'url'               => $term_link ?: null,
		'description'       => wp_strip_all_tags( term_description( $term ) ),
		'inDefinedTermSet'  => home_url( '/tags#glossary' ),
	];

	// Prefer short_definition, then ACF description, else native term description
	if ( $short_definition ) {
		$json['description'] = wp_strip_all_tags( $short_definition );
	} elseif ( $long_description ) {
		$json['description'] = wp_kses_post( $long_description );
	}

	if ( ! empty( $alternate_names ) ) {
		$json['alternateName'] = $alternate_names;
	}

	// Remove null/empty
	return array_filter( $json, fn( $v ) => $v !== null && $v !== '' && $v !== [] );
}

/**
 * Print JSON-LD for current tag archive
 */
function cz_print_tag_jsonld_for_archive(): void {
	$term = get_queried_object();
	if ( ! ( $term instanceof WP_Term ) ) {
		return;
	}
	$data = cz_build_defined_term_jsonld( $term );
	if ( empty( $data ) ) {
		return;
	}
	$graph = [
		'@context' => 'https://schema.org',
	] + $data;

	echo '<script type="application/ld+json">' . wp_json_encode( $graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>';
}

/**
 * Print Article JSON-LD on single post, linking tags (and their synonyms via alternateName)
 */
function cz_print_article_jsonld_with_tags( WP_Post $post ): void {
	$terms = get_the_terms( $post, 'post_tag' );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return;
	}

	$defined_terms = [];
	foreach ( $terms as $t ) {
		if ( $t instanceof WP_Term ) {
			$node = cz_build_defined_term_jsonld( $t );
			if ( ! empty( $node ) ) {
				$defined_terms[] = $node;
			}
		}
	}

	if ( empty( $defined_terms ) ) {
		return;
	}

	$article = [
		'@type'            => 'Article',
		'@id'              => get_permalink( $post ) . '#article',
		'mainEntityOfPage' => get_permalink( $post ),
		'headline'         => get_the_title( $post ),
		'datePublished'    => get_the_date( 'c', $post ),
		'dateModified'     => get_the_modified_date( 'c', $post ),
		'author'           => [
			'@type' => 'Person',
			'name'  => get_the_author_meta( 'display_name', $post->post_author ),
		],
		'about'            => [],
		'mentions'         => [],
	];

	foreach ( $defined_terms as $dt ) {
		if ( isset( $dt['@id'] ) ) {
			$article['about'][]    = [ '@id' => $dt['@id'] ];
			$article['mentions'][] = [ '@id' => $dt['@id'] ];
		}
	}

	$graph = [
		'@context' => 'https://schema.org',
		'@graph'   => array_merge( [ $article ], $defined_terms ),
	];

	echo '<script type="application/ld+json">' . wp_json_encode( $graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>';
}
