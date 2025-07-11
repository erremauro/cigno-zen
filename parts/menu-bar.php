<menu class="menu-bar">
	<p>Sfoglia:
		<?php
		$current_slug = get_post_field( 'post_name', get_post() );
		$is_home = is_front_page() || is_home();
		$links = [];

		if ( ! $is_home ) {
			$links[] = '<a href="' . esc_url( home_url( '/' ) ) . '">Tutto</a>';
		}

		$pages = [
			'volumi'    => 'Volumi',
			'autori'    => 'Autori',
			'categorie' => 'Categorie',
		];

		foreach ( $pages as $slug => $label ) {
			if ( $current_slug !== $slug ) {
				$links[] = '<a href="' . esc_url( home_url( '/' . $slug ) ) . '">' . esc_html( $label ) . '</a>';
			}
		}

		echo implode(', ', $links);
		?>
	</p>
</menu>
