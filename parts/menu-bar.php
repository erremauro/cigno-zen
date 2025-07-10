<menu class="menu-bar">
	<p>Sfoglia:
		<?php
		$current_slug = get_post_field( 'post_name', get_post() );
		$is_home = is_front_page() || is_home();
		$links = [];

		// Aggiungi "Tutto" solo se NON sei in home
		if ( ! $is_home ) {
			$links[] = '<a href="' . esc_url( home_url( '/' ) ) . '">Tutto</a>';
		}

		// Gestione ordine in base alla pagina attuale
		if ( $current_slug === 'i-volumi' ) {
			$links[] = '<a href="' . esc_url( home_url( '/autori' ) ) . '">Autori</a>';
		} elseif ( $current_slug === 'gli-autori' ) {
			$links[] = '<a href="' . esc_url( home_url( '/volumi' ) ) . '">Volumi</a>';
		} else {
			$links[] = '<a href="' . esc_url( home_url( '/volumi' ) ) . '">Volumi</a>';
			$links[] = '<a href="' . esc_url( home_url( '/autori' ) ) . '">Autori</a>';
		}

		// Unisci i link con virgola
		echo implode(', ', $links);
		?>
	</p>
</menu>
