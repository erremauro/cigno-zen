<menu class="menu-bar">
	<p>Sfoglia per:</p>
		<?php
		$current_slug = get_post_field( 'post_name', get_post() );
		$is_home = is_home();
		$is_archive = is_archive();
		$links = [];

		if ( ! $is_home ) {
			$links[] = '<a href="' . esc_url( home_url( '/articoli' ) ) . '">Articoli</a>';
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

		?>

		<p><?php echo implode(', ', $links); ?></p>
		<?php get_template_part( 'parts/user-bar' ); ?>
</menu>
