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

/**
 * Parse a "readings" string in the form:
 *   "Lingua: Lettura; Lingua: Lettura; ..."
 * into an array of [ 'name' => <Lingua>, 'value' => <Lettura> ].
 *
 * Robusto su spazi, punteggiatura multibyte e doppio spazio dopo il ';'.
 */
function cz_parse_readings_string( $raw ): array {
	if ( ! is_string( $raw ) || $raw === '' ) {
		return [];
	}

	// 1) Split in voci per ';' (supporta anche '；' fullwidth). Evita voci vuote.
	$parts = preg_split( '/[;；]+/u', $raw, -1, PREG_SPLIT_NO_EMPTY );
	$out   = [];

	foreach ( $parts as $part ) {
		$part = trim( $part );
		if ( $part === '' ) {
			continue;
		}

		// 2) Split solo sulla PRIMA ':' (supporta anche '：' fullwidth).
		//    Esempio: "Tibetano: གཉིས་མེད་ (gnyis med)"
		if ( preg_match( '/^(.+?)[：:]\s*(.+)$/u', $part, $m ) ) {
			$name  = trim( $m[1] );
			$value = trim( $m[2] );
			if ( $name !== '' && $value !== '' ) {
				$out[] = [ 'name' => $name, 'value' => $value ];
			}
		}
	}

	return $out;
}

/**
 * Render the "Pronunce" block with a clickable <h3> toggle and a hidden container.
 *
 * @param string $raw_readings      The raw readings string ("Lingua: Lettura; ...").
 * @param string $block_slug        Optional unique suffix for IDs (default 'term').
 * @param string $title             Optional title for the block (default 'Pronunce').
 */
function cz_render_readings_block( string $raw_readings, string $block_slug = 'term', string $title = 'Pronunce' ): void {
	$items = cz_parse_readings_string( $raw_readings );
	if ( empty( $items ) ) {
		return;
	}

	// Build unique IDs (you can pass a custom $block_slug per-term)
	$toggle_id    = 'readings-' . sanitize_html_class( $block_slug ) . '-toggle';
	$container_id = 'readings-' . sanitize_html_class( $block_slug ) . '-container';
	?>
	<div class="readings-block">
		<h3 id="<?php echo esc_attr( $toggle_id ); ?>"
			class="readings-title"
			role="button"
			tabindex="0"
			aria-controls="<?php echo esc_attr( $container_id ); ?>"
			aria-expanded="false">
			<?php echo esc_html( $title ); ?>
		</h3>

		<div id="<?php echo esc_attr( $container_id ); ?>" class="readings-container hidden">
			<?php foreach ( $items as $it ) : ?>
				<div class="reading-pill">
					<div class="reading-name"><?php echo esc_html( $it['name'] ); ?></div>
					<div class="reading-content"><?php echo esc_html( $it['value'] ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<script>
	// Lightweight inline toggle (usa solo per questo blocco).
	// Se preferisci, spostalo nel tuo script principale e aggancialo via delegation.
	(function(){
		var t = document.getElementById('<?php echo esc_js( $toggle_id ); ?>');
		var c = document.getElementById('<?php echo esc_js( $container_id ); ?>');
		if (!t || !c) return;
		function setState(expanded){
			t.setAttribute('aria-expanded', String(expanded));
			c.classList.toggle('hidden', !expanded);
		}
		function onToggle(e){ if (e) e.preventDefault(); setState(t.getAttribute('aria-expanded') !== 'true'); }
		t.addEventListener('click', onToggle);
		t.addEventListener('keydown', function(e){
			if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); onToggle(); }
		});
	})();
	</script>
	<?php
}
