<?php

add_filter( 'login_url', function( $login_url, $redirect, $force_reauth ) {
	// Metti il tuo URL personalizzato qui
	return home_url( '/login' );
}, 10, 3 );

add_filter( 'register_url', function( $register_url ) {
	// Metti il tuo URL personalizzato qui
	return home_url( '/registrazione' );
}, 10 );

// === Redirect post-login (i non-admin vanno in home) ===
add_filter('login_redirect', function ($redirect_to, $requested, $user) {
	if (is_wp_error($user) || !($user instanceof WP_User)) {
		return $redirect_to;
	}
	if (!user_can($user, 'manage_options')) {
		return home_url('/');
	}
	return $redirect_to;
}, 10, 3);

// === Impedisci ai non-admin l’accesso a /wp-admin (salvo AJAX) ===
add_action('admin_init', function () {
	if ( wp_doing_ajax() ) return;
	if ( ! current_user_can('manage_options') ) {
		wp_safe_redirect( home_url('/') );
		exit;
	}
});

// === Nascondi admin bar ai non-admin ===
add_filter('show_admin_bar', function ($show) {
	return current_user_can('manage_options') ? $show : false;
});

// === 404 per pagine autore di utenti che non possono scrivere ===
add_action('template_redirect', function () {
	if ( ! is_author() ) return;

	$author = get_queried_object();
	$uid = ($author instanceof WP_User) ? $author->ID : (int) get_query_var('author');

	if ( ! $uid || ! user_can($uid, 'edit_posts') ) {
		global $wp_query;
		$wp_query->set_404();
		status_header(404);
		nocache_headers();
		include get_404_template();
		exit;
	}
});
