<?php

// Genera UUID al salvataggio se assente
add_filter('acf/update_value/name=cz_uuid', function ($value, $post_id, $field) {
  $current = get_post_meta($post_id, 'cz_uuid', true);
  if ($current) {
    return $current; // già presente → non toccare
  }
  if (empty($value)) {
    $value = wp_generate_uuid4();
  }
  return $value;
}, 10, 3);

// Rende cz_uuid readonly in ACF (CSS veloce)
add_action('admin_head', function () {
  echo '<style>[name="acf[field_cz_uuid]"]{background:#f6f7f7!important;color:#555;pointer-events:none}</style>';
});

// Use Title if Name Latin is empty
add_filter('acf/update_value/name=name_latin', function($value, $post_id, $field) {

  $current = get_post_meta($post_id, 'name_latin', true);
  if ($current) {
    return $current;
  }
  if (empty($value)) {
    $value = get_the_title($post_id);
  }
  return $value;
}, 10, 3);

// Show Additional Columns
add_filter('manage_maestro_posts_columns', function($cols){
  $ins = [
    'col_romaji' => 'Romaji',
    'col_hanzi'  => 'Hanzi/Kanji',
    'col_school' => 'Scuola',
    'col_gen'    => 'Generazione',
  ];
  // inserisci dopo il titolo
  $new = [];
  foreach ($cols as $k=>$v) {
    $new[$k] = $v;
    if ($k === 'title') $new = array_merge($new, $ins);
  }
  return $new;
});

add_action('manage_maestro_posts_custom_column', function($col, $post_id){
  if ($col === 'col_romaji')  echo esc_html(get_post_meta($post_id, 'name_romaji', true));
  if ($col === 'col_hanzi')   echo esc_html(get_post_meta($post_id, 'name_hanzi', true));
  if ($col === 'col_school')  echo esc_html( get_the_term_list($post_id, 'school', '', ', ', '') ? strip_tags(get_the_term_list($post_id, 'school', '', ', ', '')) : '' );
  if ($col === 'col_gen')     echo esc_html( get_the_term_list($post_id, 'generazione', '', ', ', '') ? strip_tags(get_the_term_list($post_id, 'generazione', '', ', ', '')) : '' );
}, 10, 2);

add_filter('manage_edit-maestro_sortable_columns', function($cols){
  $cols['col_romaji'] = 'name_romaji';
  return $cols;
});

/**
 * Estrae il contenuto racchiuso dal primo shortcode $tag che matcha gli
 * attributi richiesti (es. [collapsable id="bio"]...[/collapsable]).
 * A differenza di strip_shortcodes(), che per gli shortcode "avvolgenti"
 * elimina anche il contenuto interno, qui recuperiamo proprio quel contenuto.
 */
function cz_extract_shortcode_inner_content( $content, $tag, $match_atts = [] ) {
  $content = (string) $content;
  if ( '' === $content || ! has_shortcode( $content, $tag ) ) {
    return '';
  }

  $pattern = get_shortcode_regex( [ $tag ] );
  if ( ! preg_match_all( '/' . $pattern . '/s', $content, $matches, PREG_SET_ORDER ) ) {
    return '';
  }

  foreach ( $matches as $match ) {
    if ( $match[2] !== $tag || empty( $match[5] ) ) {
      continue;
    }

    $atts = shortcode_parse_atts( $match[3] );
    $ok = true;
    foreach ( $match_atts as $key => $value ) {
      if ( ! is_array( $atts ) || ! isset( $atts[ $key ] ) || $atts[ $key ] !== $value ) {
        $ok = false;
        break;
      }
    }

    if ( $ok ) {
      return $match[5];
    }
  }

  return '';
}

/**
 * Testo del post_content da usare per l'excerpt di ricerca di un Maestro: la
 * biografia vera e propria vive dentro [collapsable id="bio"]...[/collapsable],
 * quindi la estraiamo per evitare che strip_shortcodes() cancelli tutto il
 * contenuto interno insieme al wrapper.
 */
function cz_get_maestro_search_content( $post_id ) {
  $content = get_post_field( 'post_content', $post_id );

  $bio = cz_extract_shortcode_inner_content( $content, 'collapsable', [ 'id' => 'bio' ] );

  return strip_shortcodes( '' !== $bio ? $bio : $content );
}

/**
 * Testo descrittivo di riserva per i risultati di ricerca dei Maestri privi
 * di un post_content proprio (la scheda si basa su campi ACF strutturati,
 * non su un corpo testuale), così da non mostrare un excerpt vuoto.
 */
function cz_get_maestro_search_fallback_text( $post_id ) {
  $get = function ( $key ) use ( $post_id ) {
    $val = function_exists( 'get_field' ) ? get_field( $key, $post_id ) : '';
    if ( $val === null || $val === '' ) {
      $val = get_post_meta( $post_id, $key, true );
    }
    return $val;
  };

  $parts = [];

  $honorific = (string) $get( 'honorific_name' );
  $romaji    = (string) $get( 'name_romaji' );
  $hanzi     = (string) $get( 'name_hanzi' );

  $name_line = trim( $honorific . ' ' . get_the_title( $post_id ) );
  $alt_names = array_filter( [ $romaji, $hanzi ] );
  if ( $alt_names ) {
    $name_line .= ' (' . implode( ' · ', $alt_names ) . ')';
  }
  $parts[] = $name_line;

  $school = wp_strip_all_tags( (string) get_the_term_list( $post_id, 'school', '', ', ', '' ) );
  if ( $school !== '' ) {
    $parts[] = __( 'Scuola', 'cignozen' ) . ': ' . $school;
  }

  $generazione = wp_strip_all_tags( (string) get_the_term_list( $post_id, 'generazione', '', ', ', '' ) );
  if ( $generazione !== '' ) {
    $parts[] = __( 'Generazione', 'cignozen' ) . ': ' . $generazione;
  }

  $by = (int) $get( 'birth_year' );
  $dy = (int) $get( 'death_year' );
  if ( $by || $dy ) {
    $parts[] = ( $by ?: '?' ) . ' – ' . ( $dy ?: '?' );
  }

  $birth_place = (string) $get( 'birth_place_name' );
  if ( $birth_place !== '' ) {
    $parts[] = __( 'Nato a', 'cignozen' ) . ' ' . $birth_place;
  }

  return implode( '. ', array_filter( $parts, fn( $p ) => trim( (string) $p ) !== '' ) );
}
