<?php

/**
 * Legge orderby/order da $_GET, validandoli contro un whitelist di campi.
 *
 * @param array $fields   Mappa 'chiave' => 'Etichetta' delle colonne ordinabili.
 * @param array $defaults Direzione di default per ciascuna chiave ('ASC'|'DESC').
 * @return array{0:string,1:string} [$orderby, $order]
 */
function cz_resolve_sort_params( array $fields, array $defaults ) {
	$default_key = array_key_first( $fields );

	$orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : $default_key;
	if ( ! array_key_exists( $orderby, $fields ) ) {
		$orderby = $default_key;
	}

	$order = isset( $_GET['order'] ) ? strtoupper( sanitize_key( wp_unslash( $_GET['order'] ) ) ) : ( $defaults[ $orderby ] ?? 'DESC' );
	if ( ! in_array( $order, [ 'ASC', 'DESC' ], true ) ) {
		$order = $defaults[ $orderby ] ?? 'DESC';
	}

	return [ $orderby, $order ];
}

/**
 * Esegue una WP_Query ordinata per nome autore (display_name) invece che per
 * post_author (ID numerico): post_author da solo non ha un ordine alfabetico
 * leggibile, quindi serve un JOIN ad-hoc su wp_users.
 *
 * @param array  $args  Argomenti WP_Query di base (senza 'orderby'/'order').
 * @param string $order 'ASC'|'DESC'
 * @return WP_Query
 */
function cz_query_sorted_by_author_name( array $args, $order ) {
	global $wpdb;

	$args['orderby'] = 'cz_author_name';
	$args['order']    = ( 'ASC' === $order ) ? 'ASC' : 'DESC';

	$join_filter = function ( $join, $query ) use ( $wpdb ) {
		if ( 'cz_author_name' !== $query->get( 'orderby' ) ) {
			return $join;
		}
		return $join . " LEFT JOIN {$wpdb->users} AS cz_sort_author ON cz_sort_author.ID = {$wpdb->posts}.post_author ";
	};

	$orderby_filter = function ( $orderby, $query ) use ( $order ) {
		if ( 'cz_author_name' !== $query->get( 'orderby' ) ) {
			return $orderby;
		}
		global $wpdb;
		$dir = ( 'ASC' === $order ) ? 'ASC' : 'DESC';
		return "cz_sort_author.display_name {$dir}, {$wpdb->posts}.ID {$dir}";
	};

	add_filter( 'posts_join', $join_filter, 10, 2 );
	add_filter( 'posts_orderby', $orderby_filter, 10, 2 );

	$query = new WP_Query( $args );

	remove_filter( 'posts_join', $join_filter, 10 );
	remove_filter( 'posts_orderby', $orderby_filter, 10 );

	return $query;
}

/**
 * Stampa il controllo di ordinamento (select + toggle direzione) condiviso
 * dalle liste del sito (articoli, volumi, ...). Il form usa GET e si
 * auto-invia sia al cambio di campo che al click del toggle direzione.
 *
 * @param array  $fields        Mappa 'chiave' => 'Etichetta'.
 * @param string $orderby       Chiave attualmente selezionata.
 * @param string $order         'ASC'|'DESC' attualmente attivo.
 * @param array  $hidden_params Coppie name => value da preservare come input hidden.
 */
function cz_render_sort_control( array $fields, $orderby, $order, array $hidden_params = [] ) {
	?>
	<div class="query-sort">
	  <form method="get">
	    <?php foreach ( $hidden_params as $name => $value ) : ?>
	      <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>">
	    <?php endforeach; ?>

	    <label for="cz-orderby" class="query-sort-label"><?php esc_html_e( 'Ordina per', 'textdomain' ); ?></label>

	    <div class="query-sort-controls">
	      <select id="cz-orderby" name="orderby" onchange="this.form.submit()">
	        <?php foreach ( $fields as $key => $label ) : ?>
	          <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $orderby, $key ); ?>><?php echo esc_html( $label ); ?></option>
	        <?php endforeach; ?>
	      </select>

	      <input type="hidden" name="order" id="cz-order-input" value="<?php echo esc_attr( strtolower( $order ) ); ?>">
	      <button
	        type="submit"
	        class="order-toggle<?php echo 'ASC' === $order ? ' is-asc' : ''; ?>"
	        data-next="<?php echo esc_attr( 'ASC' === $order ? 'desc' : 'asc' ); ?>"
	        onclick="document.getElementById('cz-order-input').value = this.dataset.next;"
	        aria-label="<?php echo 'ASC' === $order ? esc_attr__( 'Crescente — passa a decrescente', 'textdomain' ) : esc_attr__( 'Decrescente — passa a crescente', 'textdomain' ); ?>"
	      >
	        <svg class="order-toggle-icon" viewBox="0 0 12 12" width="16" height="16" aria-hidden="true"><polygon points="2,4 10,4 6,9" fill="currentColor"></polygon></svg>
	        <span class="order-toggle-label"><?php echo 'ASC' === $order ? esc_html__( 'Crescente', 'textdomain' ) : esc_html__( 'Decrescente', 'textdomain' ); ?></span>
	      </button>
	    </div>
	  </form>
	</div>
	<?php
}
