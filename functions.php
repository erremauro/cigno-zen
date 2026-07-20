<?php

add_action('init', function () {
    global $wp_rewrite;
    $wp_rewrite->author_base = 'autore';
});

// Nasconde la admin bar nel frontend per tutti gli utenti
add_filter('show_admin_bar', '__return_false');

require_once get_template_directory() . '/inc/styles-and-scripts.php';
require_once get_template_directory() . '/inc/shortcodes.php';
require_once get_template_directory() . '/inc/jetpack.php';
require_once get_template_directory() . '/inc/authentication.php';
require_once get_template_directory() . '/inc/search.php';
require_once get_template_directory() . '/inc/posts.php';
require_once get_template_directory() . '/inc/volumes.php';
require_once get_template_directory() . '/inc/query-sort.php';
require_once get_template_directory() . '/inc/tags.php';
require_once get_template_directory() . '/inc/more-link.php';
require_once get_template_directory() . '/inc/cz-continue-reading.php';

require_once get_template_directory() . '/inc/masters.php';
require_once get_template_directory() . '/inc/broken-link.php';
require_once get_template_directory() . '/inc/link-index.php';

// Sticky nav: aggiunge la classe body di default; l'utente può disattivarla
// tramite la preferenza czup_sticky_nav = '0' in cz-user-preferences.
add_filter( 'body_class', function ( array $classes ): array {
	$user_id = get_current_user_id();
	if ( $user_id && get_user_meta( $user_id, 'czup_sticky_nav', true ) === '0' ) {
		return $classes;
	}
	$classes[] = 'czup-sticky-nav';
	return $classes;
} );
