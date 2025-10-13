<?php

// Genera UUID al salvataggio se assente
add_action('save_post_maestro', function($post_id){
  if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;
  if (!get_post_meta($post_id, 'cz_uuid', true)) {
    update_post_meta($post_id, 'cz_uuid', wp_generate_uuid4());
  }
}, 10, 1);

// Rende cz_uuid readonly in ACF (CSS veloce)
add_action('admin_head', function () {
  echo '<style>[name="acf[field_cz_uuid]"]{background:#f6f7f7!important;color:#555;pointer-events:none}</style>';
});

// Use Title if Name Latin is empty
add_action('save_post_maestro', function($post_id){
  if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;
  $latin = get_post_meta($post_id, 'name_latin', true);
  if (!$latin) update_post_meta($post_id, 'name_latin', get_the_title($post_id));
}, 11, 1);

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
