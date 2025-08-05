<?php

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