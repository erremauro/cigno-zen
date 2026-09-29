<?php

// La tassonomia "school" è registrata da ACF (UI, salvata nel DB): qui forziamo
// la base degli URL a /scuola/<nome_scuola>/.
add_filter('register_taxonomy_args', function ($args, $taxonomy) {
  if ($taxonomy !== 'school') {
    return $args;
  }
  $args['public']  = true;
  $args['rewrite'] = ['slug' => 'scuola', 'with_front' => false];
  return $args;
}, 10, 2);

// Rigenera le rewrite rules una sola volta dopo il cambio di base.
add_action('init', function () {
  $version = 'scuola-1';
  if (get_option('cz_school_rewrite_version') !== $version) {
    flush_rewrite_rules(false);
    update_option('cz_school_rewrite_version', $version);
  }
}, 99);

// Redirect 301 dai vecchi URL /school/{slug}/ alla nuova base /scuola/{slug}/
add_action('template_redirect', function () {
  if (!is_404()) {
    return;
  }

  $path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
  if (strpos($path, 'school/') !== 0) {
    return;
  }

  $slug = strtok(substr($path, strlen('school/')), '/');
  if (!$slug || !term_exists($slug, 'school')) {
    return;
  }

  wp_safe_redirect(home_url('/scuola/' . $slug . '/'), 301);
  exit;
});

// Campo ACF WYSIWYG per la descrizione estesa della scuola (sostituisce la
// textarea standard nel rendering; term_description() resta come fallback).
add_action('acf/init', function () {
  if (!function_exists('acf_add_local_field_group')) {
    return;
  }

  acf_add_local_field_group([
    'key'      => 'group_cz_school',
    'title'    => 'Scuola',
    'fields'   => [
      [
        'key'          => 'field_cz_school_description',
        'label'        => 'Descrizione',
        'name'         => 'description',
        'type'         => 'wysiwyg',
        'instructions' => 'Descrizione mostrata nella pagina /scuola/. Se vuota, viene usata la descrizione standard del termine.',
        'tabs'         => 'all',
        'toolbar'      => 'full',
        'media_upload' => 1,
      ],
    ],
    'location' => [
      [['param' => 'taxonomy', 'operator' => '==', 'value' => 'school']],
    ],
    'style'    => 'default',
  ]);
});
