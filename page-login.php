<?php if ( is_user_logged_in() ) { wp_redirect(home_url()); } ?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<?php get_template_part('parts/head'); ?>

<body <?php body_class(); ?>>
	<header class="site-header">
		<!-- SITE LOGO -->
		<div class="site-branding">
			<a href="<?php echo esc_url(home_url('/')) ?>" rel="home" class="custom-logo-link">
				    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/cigno-zen.svg'); ?>" title="<?php echo bloginfo('description') ?>" alt="<?php echo bloginfo('name'); ?>" width="150" height="auto">
			</a>
		</div>
	</header>

<?php

if ($_POST && isset($_POST['cigno_zen_login'])) {
	$creds = array();
	$creds['user_login']    = sanitize_text_field($_POST['username']);
	$creds['user_password'] = $_POST['password'];
	$creds['remember']      = isset($_POST['remember']) ? true : false;

	$user = wp_signon($creds, false);

	if (is_wp_error($user)) {
		$ERROR_MESSAGE = $user->get_error_message();
	} else {
		wp_redirect(home_url('/dashboard')); // redirect dopo login
		exit;
	}
}
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main">
		<h1 style="text-align: center; margin-bottom: 1em;">Accedi</h1>
		<form method="post" class="login-form">
			<?php if ( $ERROR_MESSAGE ) : ?>
				<div class="error-message">
					<p><?php echo $ERROR_MESSAGE ?></p>
				</div>
			<?php endif ?>
			<div class="username-control">
				<label for="username">Username o Email</label><br>
				<input class="form-control" type="text" name="username" id="username" required>
			</div>
			<div class="password-control">
				<label for="password">Password</label><br>
				<input class="form-control" type="password" name="password" id="password" required>
			</div>
			<div class="remember-checkbox-control">
				<label class="checkbox-label">
  					<input type="checkbox" name="remember">
  					<span>Ricordami</span>
				</label>
			</div>
			<div class="submit-control">
				<input class="form-control" type="submit" name="cigno_zen_login" value="Accedi">
			</div>
			<div class="password-recovery-control">
				<a href="<?php echo wp_lostpassword_url(); ?>">Password dimenticata?</a>
			</div>
		</form>
	</main>
</div>

<?php get_template_part('parts/footer'); ?>
