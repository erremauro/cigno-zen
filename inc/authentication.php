<?php
/**
 * === Email verification + admin lockout for non-admins ===
 * Drop in functions.php or (better) as an mu-plugin.
 */

/** ---------------------------
 * 1) Send verification email on registration
 * ---------------------------- */
add_action('user_register', function ($user_id) {
	// Skip for admins
	if ( user_can($user_id, 'manage_options') ) {
		update_user_meta($user_id, 'email_verified', '1');
		return;
	}

	// Create and store a fresh token
	$token = wp_generate_password(32, false, false);
	update_user_meta($user_id, '_email_verify_token', $token);
	delete_user_meta($user_id, 'email_verified'); // ensure not verified

	$user   = get_userdata($user_id);
	$email  = $user->user_email;
	$name   = $user->display_name ?: $user->user_login;

	// Build verification URL like: https://site/?verify_email=1&uid=XX&token=YY
	$verify_url = add_query_arg(array(
		'verify_email' => '1',
		'uid'          => $user_id,
		'token'        => $token,
	), site_url('/'));

	// Compose and send email
	$subject = sprintf('[%s] Conferma la tua email', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES));
	$message = "Ciao {$name},\n\n"
			 . "Per completare la registrazione, conferma la tua email cliccando il link seguente:\n\n"
			 . $verify_url . "\n\n"
			 . "Se non hai richiesto questo account, ignora questa email.\n";

	// Optional: set headers (will default to site admin email as From)
	$headers = array('Content-Type: text/plain; charset=UTF-8');

	wp_mail($email, $subject, $message, $headers);
}, 10, 1);


/** ---------------------------
 * 2) Handle verification link
 * ---------------------------- */
add_action('init', function () {
	if (!isset($_GET['verify_email'], $_GET['uid'], $_GET['token'])) {
		return;
	}

	$user_id = absint($_GET['uid']);
	$token   = sanitize_text_field(wp_unslash($_GET['token']));
	$saved   = get_user_meta($user_id, '_email_verify_token', true);

	if ($saved && hash_equals($saved, $token)) {
		// Mark as verified
		update_user_meta($user_id, 'email_verified', '1');
		delete_user_meta($user_id, '_email_verify_token');

		// Optional: small message + redirect to login
		wp_safe_redirect( add_query_arg('email_verified', '1', wp_login_url()) );
		exit;
	}

	// Invalid token -> send to login with error
	wp_safe_redirect( add_query_arg('email_verify_error', '1', wp_login_url()) );
	exit;
});


/** ---------------------------
 * 3) Block login if email not verified
 * ---------------------------- */
add_filter('authenticate', function ($user, $username, $password) {
	// If prior authentication failed or not a real user, respect it
	if (is_wp_error($user) || !($user instanceof WP_User)) {
		return $user;
	}

	// Allow admins regardless
	if ( user_can($user, 'manage_options') ) {
		return $user;
	}

	$verified = get_user_meta($user->ID, 'email_verified', true) === '1';

	if (!$verified) {
		// Human-friendly error (will show on your custom form too)
		return new WP_Error(
			'email_not_verified',
			__('Devi confermare la tua email prima di accedere. Controlla la posta (anche lo spam).', 'zen')
		);
	}

	return $user;
}, 30, 3);


/** ---------------------------
 * 4) Optional: allow resending verification email
 *    Shortcode: [resend_verification]
 * ---------------------------- */
add_shortcode('resend_verification', function () {
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['rv_email'])) {
		$email = sanitize_email(wp_unslash($_POST['rv_email']));
		if ($email && ($user = get_user_by('email', $email))) {
			// Rebuild token and resend
			$token = wp_generate_password(32, false, false);
			update_user_meta($user->ID, '_email_verify_token', $token);
			delete_user_meta($user->ID, 'email_verified');

			$verify_url = add_query_arg(array(
				'verify_email' => '1',
				'uid'          => $user->ID,
				'token'        => $token,
			), site_url('/'));

			$subject = sprintf('[%s] Conferma la tua email', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES));
			$message = "Ciao,\n\n"
					 . "Ecco il tuo nuovo link per confermare l'email:\n\n"
					 . $verify_url . "\n\n";

			wp_mail($email, $subject, $message, array('Content-Type: text/plain; charset=UTF-8'));

			return '<p>Email di verifica reinviata. Controlla la posta.</p>';
		}
		return '<p>Utente non trovato per quella email.</p>';
	}

	// Simple form
	$action = esc_url( add_query_arg(array()) );
	return '<form method="post" action="'.$action.'">
		<label>La tua email</label><br>
		<input type="email" name="rv_email" required>
		<button type="submit">Reinvia verifica</button>
	</form>';
});


/** ---------------------------
 * 5) Redirect after login (non-admins -> home)
 * ---------------------------- */
add_filter('login_redirect', function ($redirect_to, $requested, $user) {
	if (is_wp_error($user) || !($user instanceof WP_User)) {
		return $redirect_to;
	}
	if (!user_can($user, 'manage_options')) {
		return home_url('/');
	}
	return $redirect_to;
}, 10, 3);


/** ---------------------------
 * 6) Prevent non-admins from accessing /wp-admin
 * ---------------------------- */
add_action('admin_init', function () {
	if ( wp_doing_ajax() ) {
		return; // allow admin-ajax.php
	}
	if (!current_user_can('manage_options')) {
		wp_safe_redirect( home_url('/') );
		exit;
	}
});


/** ---------------------------
 * 7) Hide admin bar for non-admins
 * ---------------------------- */
add_filter('show_admin_bar', function ($show) {
	if ( current_user_can('manage_options') ) {
		return $show;
	}
	return false;
});
