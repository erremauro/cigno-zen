<?php
/**
 * Template: page-registrazione.php
 * Registrazione con login immediato (cookie persistente) + honeypot + throttling + Display Name
 */

if ( ! defined('ABSPATH') ) { exit; }

// Inizializza errori e valori
$errors              = new WP_Error();
$username_value      = '';
$email_value         = '';
$display_name_value  = '';

/** Se l'utente è già loggato → home */
if ( is_user_logged_in() ) {
	wp_redirect( home_url() );
	exit;
}

/** POST handler */
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme_custom_register']) ) {

	// 1) Nonce
	if ( ! isset($_POST['_wpnonce']) || ! wp_verify_nonce($_POST['_wpnonce'], 'theme_custom_register') ) {
		$errors->add('nonce', 'Richiesta non valida. Ricarica la pagina e riprova.');
	} else {

		// 2) Throttling per IP (max 5 tentativi/10 minuti)
		$ip           = $_SERVER['REMOTE_ADDR'] ?? '';
		$rl_key       = 'reg_rl_' . md5($ip);
		$rl_tries     = (int) get_transient($rl_key);
		$rl_window    = 10 * MINUTE_IN_SECONDS;
		$rl_max_tries = 5;

		if ( $rl_tries >= $rl_max_tries ) {
			$errors->add('rate', 'Troppi tentativi ravvicinati. Attendi qualche minuto e riprova.');
		}

		// 3) Honeypot + tempo minimo
		$honeypot_filled = ! empty($_POST['website']);
		$ts              = isset($_POST['reg_ts']) ? (int) $_POST['reg_ts'] : 0;
		$too_fast        = ( $ts === 0 || ( time() - $ts ) < 3 );

		if ( $honeypot_filled || $too_fast ) {
			$errors->add('spam', 'Si è verificato un errore. Riprova.');
		}

		// 4) Dati e validazioni
		$username     = sanitize_user( wp_unslash( $_POST['username'] ?? '' ) );
		$email        = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$password     = $_POST['password'] ?? '';
		$display_name = sanitize_text_field( wp_unslash( $_POST['display_name'] ?? '' ) );

		// Ripopola campi in caso di errore
		$username_value     = $username;
		$email_value        = $email;
		$display_name_value = $display_name;

		if ( empty($username) ) {
			$errors->add('username', 'Lo username è obbligatorio.');
		} elseif ( username_exists($username) ) {
			$errors->add('username', 'Questo username è già registrato.');
		}

		if ( empty($email) || ! is_email($email) ) {
			$errors->add('email', 'Inserisci un indirizzo e-mail valido.');
		} elseif ( email_exists($email) ) {
			$errors->add('email', 'Questa e-mail è già registrata.');
		}

		if ( empty($password) ) {
			$errors->add('password', 'La password non può essere vuota.');
		} elseif ( strlen($password) < 8 ) {
			$errors->add('password_len', 'La password deve contenere almeno 8 caratteri.');
		}

		if ( strlen($display_name) > 60 ) {
			$errors->add('display_name_len', 'Il nome visualizzato è troppo lungo (max 60 caratteri).');
		}

		// 5) Se nessun errore → crea utente e login immediato (persistente)
		if ( ! $errors->has_errors() ) {
			set_transient( $rl_key, $rl_tries + 1, $rl_window );

			$user_id = wp_create_user( $username, $password, $email );

			if ( is_wp_error($user_id) ) {
				$errors->add( 'create_user', $user_id->get_error_message() );
			} else {
				// Display Name & Nickname
				$final_display_name = $display_name !== '' ? $display_name : $username;
				wp_update_user( array(
					'ID'           => $user_id,
					'display_name' => $final_display_name,
					'nickname'     => $final_display_name,
				) );

				// LOGIN IMMEDIATO con cookie persistente (equivale a "Ricordami" attivo)
				wp_set_current_user( $user_id );
				wp_set_auth_cookie( $user_id, true ); // <- true = persistente

				// Hook wp_login per compatibilità plugin
				$user = get_user_by( 'id', $user_id );
				do_action( 'wp_login', $user->user_login, $user );

				// Redirect finale (rispetta ?redirect_to)
				$redirect_to = isset($_REQUEST['redirect_to']) ? esc_url_raw($_REQUEST['redirect_to']) : home_url('/');
				wp_safe_redirect( wp_validate_redirect( $redirect_to, home_url('/') ) );
				exit;
			}
		} else {
			set_transient( $rl_key, min($rl_tries + 1, $rl_max_tries), $rl_window );
		}
	}
}

// Header (menu nascosto)
get_template_part( 'parts/header', null, array( 'show_menu' => false ) );
?>
<div id="primary" class="content-area">
  <main id="main" class="site-main">
	<h1 style="text-align:center; margin-bottom:1em;">Registrati</h1>

	<form method="post" class="registration-form" action="<?php echo esc_url( get_permalink() ); ?>">
	  <?php if ( $errors->has_errors() ) : ?>
		<div class="error-message">
		  <?php foreach ( $errors->get_error_messages() as $msg ) : ?>
			<p><?php echo esc_html( $msg ); ?></p>
		  <?php endforeach; ?>
		</div>
	  <?php endif; ?>

	  <div class="username-control">
		<label for="username">Username</label><br>
		<input class="form-control" type="text" name="username" id="username" required
			   value="<?php echo esc_attr( $username_value ); ?>">
	  </div>

	  <div class="display-name-control">
		<label for="display_name">Nome visualizzato (opzionale)</label><br>
		<input class="form-control" type="text" name="display_name" id="display_name"
			   value="<?php echo esc_attr( $display_name_value ); ?>">
	  </div>

	  <div class="email-control">
		<label for="email">Email</label><br>
		<input class="form-control" type="email" name="email" id="email" required
			   value="<?php echo esc_attr( $email_value ); ?>">
	  </div>

	  <div class="password-control">
		<label for="password">Password</label><br>
		<input class="form-control" type="password" name="password" id="password" required>
	  </div>

	  <!-- Honeypot (campo che deve restare vuoto) -->
	  <div class="hp-field" aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">
		<label for="website">Se hai un sito, scrivilo qui</label>
		<input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
	  </div>
	  <!-- Timestamp per tempo minimo -->
	  <input type="hidden" name="reg_ts" value="<?php echo time(); ?>">

	  <?php wp_nonce_field( 'theme_custom_register' ); ?>
	  <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $_GET['redirect_to'] ?? home_url('/') ); ?>">

	  <div class="submit-control">
		<input class="form-control" type="submit" name="theme_custom_register" value="Registrati">
	  </div>
	</form>
  </main>
</div>
<?php get_template_part('parts/footer'); ?>
