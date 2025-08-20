<?php
/**
 * CZ Continue Reading — filtro: czcr_allowed_post_types
 *
 * Scopo: controlla QUALI post type possono comparire nel widget [readings].
 * Nota: questo filtro influisce SOLO sull’output del widget; non cambia il tracking
 * né i dati salvati. I post type non inclusi vengono semplicemente ignorati in lista.
 *
 * Esempi d’uso:
 * - Solo articoli del blog (default):         ['post']
 * - Articoli + Pagine statiche:               ['post', 'page']
 * - Includere custom post type:               ['post', 'guide', 'docs']  // usa gli slug registrati
 * - Tutti i public post type:                 array_values( get_post_types( ['public' => true] ) )
 * - Disabilitare tutto (debug/testing):       []  // il widget risulterà vuoto
 *
 * Correlato:
 * - Escludere ID specifici (es. pagina login) con il filtro 'czcr_excluded_post_ids':
 *
 *   add_filter('czcr_excluded_post_ids', function($ids){
 *       $ids[] = 123; // ID della pagina da escludere
 *       return $ids;
 *   });
 *
 * Suggerimento: assicurati che gli slug passati siano post type REALMENTE registrati,
 * altrimenti verranno ignorati in fase di output.
 */
add_filter( 'czcr_allowed_post_types', function( $types ) {
    return [ 'post' ]; // personalizza qui
});

add_filter( 'czcr_allowed_post_types', function( $types ) {
	return [ 'post' ];
});
