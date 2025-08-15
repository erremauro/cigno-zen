<?php

function display_author_info_conditionally() {
	$author_name = get_the_author();
	if ($author_name !== 'cigno') {
		echo '<div class="entry-meta">';
		the_author_posts_link();
		echo '</div>';
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
