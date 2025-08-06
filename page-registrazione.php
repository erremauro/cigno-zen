<?php

// Fa un redirect se l'utente è già loggato
if ( is_user_logged_in() ) { wp_redirect(home_url()); }

// Elabora i dati di post dopo il reponse di registrazione
if ($_POST && isset($_POST['theme_custom_register'])) {
	$username = sanitize_user($_POST['username']);
	$email    = sanitize_email($_POST['email']);
	$password = $_POST['password'];

	$errors = new WP_Error();

	if (username_exists($username)) {
		$errors->add('username', 'Questo username è già registrato.');
	}
	if (email_exists($email)) {
		$errors->add('email', 'Questa email è già registrata.');
	}
	if (empty($password)) {
		$errors->add('password', 'La password non può essere vuota.');
	}
}

// Carica l'header nascondendo il menu
get_template_part('parts/header', null, array( 'show_menu' => false ));

?>
<div id="primary" class="content-area">
	<main id="main" class="site-main">
		<h1 style="text-align: center; margin-bottom: 1em;">Iscriviti</h1>
		<form method="post" class="registration-form">
			<?php
				if ($_POST && isset($_POST['theme_custom_register'])) {
					if (empty($errors->errors) == 1) {
						$user_id = wp_create_user($username, $password, $email);
						if (!is_wp_error($user_id)) {
							echo '<div class="success-message">Registrazione completata! <a href="'.home_url('/login').'">Accedi ora</a></div>';
						} else {
							echo '<div class="error-message">Errore: '. $user_id->get_error_message().'</div>';
						}
					} else {
						echo '<div class="error-message">';
						foreach ($errors->get_error_messages() as $error) {
							echo '<p>'.$error.'</p>';
						}
						echo '</div>';
					}
				}
			?>
			<div class="username-control">
				<label for="username">Username</label><br>
				<input class="form-control" type="text" name="username" id="username" required>
			</div>
			<div class="email-control">
				<label for="email">Email</label><br>
				<input class="form-control" type="email" name="email" id="email" required>
			</div>
			<div class="password-control">
				<label for="password">Password</label><br>
				<input class="form-control" type="password" name="password" id="password" required>
			</div>
			<div class="submit-control">
				<input class="form-control" type="submit" name="theme_custom_register" value="Registrati">
			</div>
		</form>
	</main>
</div>

<?php get_template_part('parts/footer'); ?>
