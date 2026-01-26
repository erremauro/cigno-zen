<?php

add_filter( 'login_url', function( $login_url, $redirect, $force_reauth ) {
	// Metti il tuo URL personalizzato qui
	return home_url( '/login' );
}, 10, 3 );

add_filter( 'register_url', function( $register_url ) {
	// Metti il tuo URL personalizzato qui
	return home_url( '/registrazione' );
}, 10 );

add_filter( 'czcr_login_url', function( $url ) {
    return home_url( '/login' );
}, 999 );

add_filter( 'czcr_register_url', function( $url ) {
    return home_url( '/registrazione' );
}, 999 );

add_filter( 'czcr_home_url', function( $url ) {
    return home_url( '/' );
}, 999 );

if ( ! defined( 'CZ_REMEMBER_COOKIE' ) ) {
	define( 'CZ_REMEMBER_COOKIE', 'cz_remember_me' );
}

add_filter( 'auth_cookie_expiration', function ( $expiration, $user_id, $remember ) {
	if ( $remember ) {
		return 14 * DAY_IN_SECONDS;
	}

	return $expiration;
}, 10, 3 );

add_action( 'set_auth_cookie', function ( $auth_cookie, $expire, $expiration ) {
	if ( headers_sent() ) {
		return;
	}

	$paths  = array_unique( array( COOKIEPATH, SITECOOKIEPATH ) );
	$secure = is_ssl();

	foreach ( $paths as $path ) {
		if ( $expire > 0 ) {
			setcookie( CZ_REMEMBER_COOKIE, '1', $expiration, $path, COOKIE_DOMAIN, $secure, true );
		} else {
			setcookie( CZ_REMEMBER_COOKIE, '', time() - DAY_IN_SECONDS, $path, COOKIE_DOMAIN, $secure, true );
		}
	}
}, 10, 3 );

add_action( 'init', function () {
	if ( ! is_user_logged_in() ) {
		return;
	}

	if ( empty( $_COOKIE[ CZ_REMEMBER_COOKIE ] ) ) {
		return;
	}

	$user_id = get_current_user_id();
	$token   = wp_get_session_token();

	if ( $user_id && $token ) {
		wp_set_auth_cookie( $user_id, true, is_ssl(), $token );
	}
}, 1 );

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
