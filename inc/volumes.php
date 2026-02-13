<?php

/**
 * Resolve the current volume id from context.
 *
 * @param int|null $volume_id Optional explicit volume ID.
 * @return int
 */
function cignozen_resolve_volume_id( $volume_id = null ) {
	$volume_id = absint( $volume_id );
	if ( $volume_id ) {
		return $volume_id;
	}

	$queried = get_queried_object();
	if ( $queried instanceof WP_Post && 'volume' === $queried->post_type ) {
		return (int) $queried->ID;
	}

	global $post;
	if ( $post instanceof WP_Post && 'volume' === $post->post_type ) {
		return (int) $post->ID;
	}

	return 0;
}

/**
 * Render volume author link for CPT `volume`.
 *
 * @param int|null $volume_id Optional explicit volume ID.
 * @param bool     $echo Whether to echo the HTML.
 * @return string|null
 */
function display_volume_author( $volume_id = null, $echo = true ) {
	$volume_id = cignozen_resolve_volume_id( $volume_id );
	if ( ! $volume_id ) {
		return '';
	}

	$author_id = (int) get_post_field( 'post_author', $volume_id );
	if ( ! $author_id ) {
		return '';
	}

	$author     = get_user_by( 'id', $author_id );
	$author_name = $author ? $author->display_name : '';
	$author_slug = $author ? $author->user_nicename : '';
	if ( '' === $author_name || '' === $author_slug ) {
		return '';
	}

	$author_url = home_url( '/autore/' . $author_slug );

	$output  = '<div class="volumes-author">';
	$output .= '<a href="' . esc_url( $author_url ) . '">' . esc_html( $author_name ) . '</a>';
	$output .= '</div>';

	if ( $echo ) {
		echo $output;
		return null;
	}

	return $output;
}

/**
 * Backward-compatible alias used by old templates.
 *
 * @deprecated Use display_volume_author().
 *
 * @param mixed $term Ignored for CPT `volume` support.
 * @param bool  $echo Whether to echo the HTML.
 * @return string|null
 */
function display_volumes_author( $term = null, $echo = true ) {
	unset( $term );
	return display_volume_author( null, $echo );
}

/**
 * Get the selected volume for a post.
 * Selection rule:
 * 1) If a primary volume exists, return it.
 * 2) Otherwise return the first by position.
 *
 * @param int|null $post_id Optional explicit post ID.
 * @return WP_Post|null
 */
function cignozen_get_post_volume( $post_id = null ) {
	global $wpdb;

	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	if ( ! $post_id ) {
		return null;
	}

	static $table_exists = null;
	static $has_primary  = null;

	$table_name = $wpdb->prefix . 'cz_volume_items';

	if ( null === $table_exists ) {
		$table_exists = ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) === $table_name );
	}

	if ( ! $table_exists ) {
		return null;
	}

	if ( null === $has_primary ) {
		$has_primary = (bool) $wpdb->get_var(
			$wpdb->prepare(
				"SHOW COLUMNS FROM {$table_name} LIKE %s",
				'is_primary'
			)
		);
	}

	$order_by = $has_primary ? 'is_primary DESC, position ASC, id ASC' : 'position ASC, id ASC';

	$volume_id = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT volume_id
			FROM {$table_name}
			WHERE post_id = %d
			ORDER BY {$order_by}
			LIMIT 1",
			$post_id
		)
	);

	if ( ! $volume_id ) {
		return null;
	}

	$volume_post = get_post( $volume_id );
	if ( $volume_post instanceof WP_Post && 'volume' === $volume_post->post_type ) {
		return $volume_post;
	}

	return null;
}

/**
 * Show selected volume link for single post pages.
 *
 * @param int|null $post_id Optional explicit post ID.
 * @param bool     $echo Whether to echo HTML.
 * @return string|null
 */
function display_volumes_name( $post_id = null, $echo = true ) {
	$volume_post = cignozen_get_post_volume( $post_id );
	if ( ! $volume_post ) {
		return '';
	}

	$output  = '<p class="volumes-link">';
	$output .= '<a href="' . esc_url( get_permalink( $volume_post->ID ) ) . '">' . esc_html( get_the_title( $volume_post->ID ) ) . '</a>';
	$output .= '</p>';

	if ( $echo ) {
		echo $output;
		return null;
	}

	return $output;
}
