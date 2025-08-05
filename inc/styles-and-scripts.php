<?php

/**
 * Setup del tema
 */
function cigno_zen_setup() {
	load_theme_textdomain('cigno-zen', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'cigno_zen_setup');

function cignozen_add_favicon_meta() {
		echo '
		<link rel="icon" type="image/png" href="' . get_template_directory_uri() . '/assets/images/favicon/favicon-96x96.png" sizes="96x96" />
		<link rel="icon" type="image/svg+xml" href="' . get_template_directory_uri() . '/assets/images/favicon/favicon.svg" />
		<link rel="shortcut icon" href="' . get_template_directory_uri() . '/assets/images/favicon/favicon.ico" />
		<link rel="apple-touch-icon" sizes="180x180" href="' . get_template_directory_uri() . '/assets/images/favicon/apple-touch-icon.png" />
		<meta name="apple-mobile-web-app-title" content="Cigno Zen" />
		<link rel="manifest" href="' . get_template_directory_uri() . '/assets/images/favicon/site.webmanifest" />
		';
}
add_action('wp_head', 'cignozen_add_favicon_meta');


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
				$script_file_path = '/assets/js/script.js';
				$script_file = get_template_directory() . $script_file_path;
				$script_file_uri = get_template_directory_uri() . $script_file_path;
				$script_version = filemtime($script_file);
	wp_enqueue_script(
						'cigno-zen-script',
						$script_file_uri,
						[],
						$script_version,
						true
				);
	carica_google_fonts();
}
add_action('wp_enqueue_scripts', 'cigno_zen_scripts');

function carica_google_fonts() {
	wp_enqueue_style('libre-baskerville', 'https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap', false);
}

function cignozen_get_title() {
	// Caso: singolo articolo
	if ( is_single() && get_post_type() === 'post' ) {
		$author_name   = '';
		$volume_name   = '';
		$article_title = get_the_title();

		$volumes_terms = get_the_terms( get_the_ID(), 'volumes' );
		if ( $volumes_terms && ! is_wp_error( $volumes_terms ) ) {
			$volumes_term = array_shift( $volumes_terms );
			$volume_name  = $volumes_term->name ?? '';

			$author = get_field( 'author', $volumes_term->taxonomy . '_' . $volumes_term->term_id );
			if ( is_array( $author ) ) {
				$author = reset( $author );
			}
			if ( $author instanceof WP_User ) {
				$author_name = $author->display_name;
			}
		}

		$title = 'Cigno Zen';
		if ( $author_name ) {
			$title .= ' - ' . esc_html( $author_name );
		}
		if ( $volume_name ) {
			$title .= ' - ' . esc_html( $volume_name );
		}
		if ( $article_title ) {
			$title .= ': ' . esc_html( $article_title );
		}

		return $title;
	}

	// Caso: pagina autore
	if ( is_author() ) {
		$author = get_queried_object();
		if ( $author && $author instanceof WP_User ) {
			return 'Cigno Zen - Autore: ' . esc_html( $author->display_name );
		}
	}

	// Caso: pagina volumi
	if (is_page('volumi')) {
      return 'Cigno Zen - Tutti i Volumi';
  }

	// Default: nome del sito
	return get_bloginfo( 'name' );
}
