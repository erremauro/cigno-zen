<?php if ( ! is_user_logged_in() ) : ?>
	<div class="top-toolbar" role="toolbar" aria-hidden="true">
		<a class="top-toolbar-btn" href="/login">Accedi</a>
		<a class="top-toolbar-btn" href="/registrazione">Registrati</a>
	</div>
<?php else : ?>
	<div class="top-toolbar" role="toolbar" aria-hidden="true">
		<a class="top-toolbar-btn" href="/logout" title="Esci dal tuo profilo">Logout</a>
	</div>
<?php endif ?>
