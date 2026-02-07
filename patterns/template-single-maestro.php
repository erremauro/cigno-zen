<?php

if (have_posts()) : while (have_posts()) : the_post();

/** ------------------------------------------------------------------------
 * Helpers
 * --------------------------------------------------------------------- */

/** Build a Google Maps URL (solo se lat/lng presenti). */
$build_map_url = function ($lat = null, $lng = null): ?string {
  $lat = is_numeric($lat) ? (float)$lat : null;
  $lng = is_numeric($lng) ? (float)$lng : null;
  if ($lat === null || $lng === null) return null;

  $lat_s = number_format($lat, 6, '.', '');
  $lng_s = number_format($lng, 6, '.', '');
  return sprintf('https://www.google.com/maps/search/?api=1&query=%s%%2C%s',
    rawurlencode($lat_s), rawurlencode($lng_s)
  );
};

/** Calcola anni tra nascita e morte. Ritorna int o null se non calcolabile. */
$calc_years = function (int $by = null, int $bm = null, int $bd = null, int $dy = null, int $dm = null, int $dd = null): ?int {
  if (!$by || !$dy) return null;
  if ($by && $bm && $bd && $dy && $dm && $dd) {
    try {
      $b = new DateTime(sprintf('%04d-%02d-%02d', $by, $bm, $bd));
      $d = new DateTime(sprintf('%04d-%02d-%02d', $dy, $dm, $dd));
      if ($d < $b) return null;
      return (int)$b->diff($d)->y;
    } catch (Exception $e) { /* degrade */ }
  }
  $age = $dy - $by;
  if ($bm && $dm) {
    if ($dm < $bm) $age -= 1;
    elseif ($dm === $bm && $bd && $dd && $dd < $bd) $age -= 1;
  }
  return ($age >= 0) ? $age : null;
};

/** Durata attività in anni interi (inclusiva se entrambi presenti). */
$calc_active_years = function (?int $start, ?int $end): ?int {
  if (!$start || !$end) return null;
  $dur = ($end - $start + 1);
  return ($dur > 0) ? $dur : null;
};

/** Estrae ID interi da vari formati (int|WP_Post|array). */
$normalize_ids = function ($input): array {
  $ids = [];
  foreach ((array)$input as $item) {
    if ($item instanceof WP_Post) { $ids[] = (int)$item->ID; }
    elseif (is_numeric($item))    { $ids[] = (int)$item; }
  }
  return array_values(array_unique(array_filter($ids)));
};

/** Link HTML per un singolo post ID. */
$link_for = function (int $id): ?string {
  $title = get_the_title($id);
  if (!$title) return null;
  return sprintf('<a href="%s">%s</a>', esc_url(get_permalink($id)), esc_html($title));
};

/** Ritorna gli ID dei maestri che hanno $current_id nel meta $meta_key (Post Object ACF). */
$get_reverse_rel_ids = function (int $current_id, string $meta_key): array {
  if (!$current_id) return [];
  $q = new WP_Query([
    'post_type'      => 'maestro',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'fields'         => 'ids',
    'meta_query'     => [[
      'key'     => $meta_key,
      'value'   => $current_id,
      'compare' => '='
    ]],
    'no_found_rows'  => true,
  ]);
  return $q->posts ?: [];
};

/** Linka una lista di ID come anchor comma-separated. */
$links_from_ids = function (array $ids): string {
  $out = [];
  foreach ($ids as $id) {
    $t = get_the_title($id);
    if ($t) $out[] = sprintf('<a href="%s">%s</a>', esc_url(get_permalink($id)), esc_html($t));
  }
  return implode(', ', $out);
};

/** Safe ACF getter con fallback a post_meta. */
$acf_get = function (string $key, $default = '') {
  if (function_exists('get_field')) {
    $val = get_field($key);
    if ($val !== null && $val !== '') return $val;
  }
  $val = get_post_meta(get_the_ID(), $key, true);
  return ($val !== null && $val !== '') ? $val : $default;
};

/** Lista termini linkati per una tassonomia. */
$render_term_list = function (int $post_id, string $tax): string {
  $terms = get_the_terms($post_id, $tax);
  if (!$terms || is_wp_error($terms)) return '';
  $links = [];
  foreach ($terms as $t) {
    if ($tax === 'generazione' && $t->parent) {
      $parent = get_term($t->parent, $tax);
      if (!is_wp_error($parent) && $parent) {
        $parent_url = get_term_link($parent);
        $child_url  = get_term_link($t);
        if (!is_wp_error($parent_url) && !is_wp_error($child_url)) {
          $links[] = sprintf(
            '<a href="%s">%s</a><br><a href="%s">%s</a>',
            esc_url($parent_url),
            esc_html($parent->name),
            esc_url($child_url),
            esc_html($t->name)
          );
          continue;
        }
      }
    }

    $url = get_term_link($t);
    if (!is_wp_error($url)) {
      $links[] = sprintf('<a href="%s">%s</a>', esc_url($url), esc_html($t->name));
    }
  }
  return implode(', ', $links);
};

/** Stampa una riga meta se valorizzata. */
$meta_row = function (string $label, $value, string $class = '') {
  if ($value === null || $value === '') return;
  printf(
    '<div class="meta-row %s"><span class="meta-label">%s</span> <span class="meta-data">%s</span></div>',
    esc_attr($class),
    esc_html($label),
    wp_kses($value, ['a' => ['href' => [], 'title' => [], 'target' => [], 'rel' => []], 'br' => []])
  );
};

/** Compone una data display da day/month/year + precision. */
$build_display_date = function (?int $y, ?int $m, ?int $d, ?string $prec): string {
  if (!$y) return '';
  $pad = fn($n) => str_pad((string)$n, 2, '0', STR_PAD_LEFT);
  switch ($prec) {
    case 'full':
      if ($m && $d) return "{$pad($d)}/{$pad($m)}/{$y}";
    case 'year-month':
      if ($m) return "{$pad($m)}/{$y}";
    case 'circa':
      return "c. {$y}";
    case 'year':
    default:
      return (string)$y;
  }
};

/** ISO per JSON-LD (Y, Y-m, Y-m-d). */
$build_iso_date = function (?int $y, ?int $m, ?int $d, ?string $prec): ?string {
  if (!$y) return null;
  $pad = fn($n) => str_pad((string)$n, 2, '0', STR_PAD_LEFT);
  if ($prec === 'full' && $m && $d) return "{$y}-{$pad($m)}-{$pad($d)}";
  if ($prec === 'year-month' && $m) return "{$y}-{$pad($m)}";
  return (string)$y;
};

/** ------------------------------------------------------------------------
 * Dati (metadati concordati)
 * --------------------------------------------------------------------- */

// Nomi
$name_latin   = (string) $acf_get('name_latin', get_the_title());
$name_romaji  = (string) $acf_get('name_romaji', '');
$name_hanzi   = (string) $acf_get('name_hanzi', '');
$honorific    = (string) $acf_get('honorific_name', '');

// Ritratto
$portrait_id      = (int) $acf_get('portrait', 0);
$portrait_size    = [320, 0]; // width-limited

// Date nascita/morte
$by = (int) $acf_get('birth_year', 0);
$bm = (int) $acf_get('birth_month', 0);
$bd = (int) $acf_get('birth_day', 0);
$bp = (string) $acf_get('birth_precision', 'year');

$dy = (int) $acf_get('death_year', 0);
$dm = (int) $acf_get('death_month', 0);
$dd = (int) $acf_get('death_day', 0);
$dp = (string) $acf_get('death_precision', 'year');

// Anni attività (floruit)
$fs = (int) $acf_get('floruit_start_year', 0);
$fe = (int) $acf_get('floruit_end_year', 0);

$birth_display = $build_display_date($by ?: null, $bm ?: null, $bd ?: null, $bp ?: null);
$death_display = $build_display_date($dy ?: null, $dm ?: null, $dd ?: null, $dp ?: null);

// Luoghi
$birth_place_name = (string) $acf_get('birth_place_name', '');
$birth_place_lat  = (string) $acf_get('birth_place_lat', '');
$birth_place_lng  = (string) $acf_get('birth_place_lng', '');

$death_place_name = (string) $acf_get('death_place_name', '');
$death_place_lat  = (string) $acf_get('death_place_lat', '');
$death_place_lng  = (string) $acf_get('death_place_lng', '');

// Tassonomie
$school_html      = $render_term_list(get_the_ID(), 'school');
$generation_html  = $render_term_list(get_the_ID(), 'generazione');

// Relazioni (IDs normalizzati)
$teachers_ids     = $normalize_ids($acf_get('teachers', []));
$primary_id       = $normalize_ids($acf_get('primary_teacher', 0))[0] ?? 0;
$heir_id          = $normalize_ids($acf_get('is_dharma_heir_of', 0))[0] ?? 0;

// Logica anti-ridondanza (un unico maestro copre primary+heir+teachers)
$single_master_id = 0;
if ($primary_id && !$heir_id) {
  if (count($teachers_ids) === 0 || (count($teachers_ids) === 1 && $teachers_ids[0] === $primary_id)) {
    $single_master_id = $primary_id;
  }
}

// Costruisci lista "Maestri" filtrando quelli già mostrati come primary/heir
$teachers_list = [];
if (!$single_master_id) {
  $exclude = [];
  if ($primary_id) $exclude[$primary_id] = true;
  if ($heir_id)    $exclude[$heir_id]    = true;
  foreach ($teachers_ids as $tid) {
    if (!isset($exclude[$tid])) {
      $html = $link_for($tid);
      if ($html) $teachers_list[] = $html;
    }
  }
}

// Successori (discendenti) da relazioni inverse
$current_id = get_the_ID();
$heirs_ids = $get_reverse_rel_ids($current_id, 'is_dharma_heir_of');   // eredi formali
$primary_students_ids = $get_reverse_rel_ids($current_id, 'primary_teacher'); // allievi principali
$primary_non_heir_ids = array_values(array_diff($primary_students_ids, $heirs_ids));

$heirs_html            = $links_from_ids($heirs_ids);
$primary_students_html = $links_from_ids($primary_non_heir_ids);

// Header: priorità dati — 1) nascita/morte, 2) floruit
$has_birth_death = ($birth_display !== '' || $death_display !== '');
$active_range    = ($fs ?: null) || ($fe ?: null);

// Sezione meta: c'è qualcosa?
$has_meta =
  ($birth_place_name !== '') ||
  ($death_place_name !== '') ||
  ($school_html !== '') ||
  ($generation_html !== '') ||
  $single_master_id ||
  count($teachers_list) > 0 ||
  ($primary_id && $primary_id !== $heir_id) ||
  ($heir_id && $heir_id !== $primary_id) ||
  ($heirs_html !== '' || $primary_students_html !== '');

?>
<article id="post-<?php the_ID(); ?>" <?php post_class('maestro-article'); ?> itemscope itemtype="https://schema.org/Person">

  <header class="master-info">
    <?php if ($name_romaji !== ''): ?>
      <div class="meta-alt-name">[ <?php echo esc_html($name_romaji); ?> ]</div>
    <?php endif; ?>

    <h1 class="master-title" itemprop="name"><?php echo esc_html($name_latin); ?></h1>

    <?php if ($name_hanzi !== ''): ?>
      <div class="meta-original-name"><?php echo esc_html($name_hanzi); ?></div>
    <?php endif; ?>

    <?php if ($honorific !== ''): ?>
      <div class="meta-honorific"><?php echo esc_html($honorific); ?></div>
    <?php endif; ?>

    <?php if ($portrait_id): ?>
      <figure class="maestro-portrait">
        <?php
          // NIENTE 'alt' passato => WP usa l'alt nativo dell'allegato
          echo wp_get_attachment_image(
            $portrait_id,
            $portrait_size,
            false,
            [
              'class'    => 'portrait-img',
              'itemprop' => 'image',
              'loading'  => 'lazy',
              'decoding' => 'async',
              'sizes'    => '(max-width: 480px) 50vw, 320px',
            ]
          );
        ?>
      </figure>
    <?php endif; ?>

    <?php
      $age_years = $has_birth_death
        ? $calc_years($by ?: null, $bm ?: null, $bd ?: null, $dy ?: null, $dm ?: null, $dd ?: null)
        : null;

      $birth_output = $birth_display;
      $death_output = $death_display;
      if ($birth_display && !$death_display) $death_output = __('?', 'cignozen');
      if (!$birth_display && $death_display) $birth_output = __('?', 'cignozen');

      $active_years = (!$has_birth_death && $active_range)
        ? $calc_active_years($fs ?: null, $fe ?: null)
        : null;
    ?>

    <?php if ($has_birth_death): ?>
      <div class="meta-master-dates">
        <span class="birth" itemprop="birthDate"><?php echo esc_html($birth_output); ?></span>
        <span class="sep" aria-hidden="true"> – </span>
        <span class="death" itemprop="deathDate"><?php echo esc_html($death_output); ?></span>
        <?php if ($age_years !== null): ?>
          <div class="age"> (<?php echo esc_html($age_years . ' ' . __('anni','cignozen')); ?>)</div>
        <?php endif; ?>
      </div>
    <?php elseif ($active_range): ?>
      <div class="meta-master-dates">
        <div class="active-label"><?php echo esc_html__('Periodo di attività', 'cignozen'); ?></div>
        <div class="active-range">
          <?php
            $start = $fs ? (string)$fs : '—';
            $end   = $fe ? (string)$fe : '—';
            echo esc_html($start . ' – ' . $end);
          ?>
        </div>
        <?php if ($active_years !== null): ?>
          <div class="active-years"> (<?php echo esc_html($active_years . ' ' . __('anni','cignozen')); ?>)</div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </header>

  <?php if ($has_meta): ?>
    <section class="master-meta collapsable-section" aria-labelledby="master-meta-title">
      <h2 id="master-meta-title" class="collapsable-toggle"><?php echo esc_html__('Informazioni', 'cignozen'); ?></h2>
      <div class="collapsable-content">
        <?php
          // Luogo di nascita (link solo se lat/lng presenti)
          $birth_map = $build_map_url($birth_place_lat, $birth_place_lng);
          if ($birth_place_name) {
            $bp_html = $birth_map
              ? sprintf('<a href="%s" target="_blank" rel="noopener nofollow">%s</a>',
                        esc_url($birth_map), esc_html($birth_place_name))
              : esc_html($birth_place_name);
            $meta_row(__('Luogo di nascita', 'cignozen'), $bp_html, 'meta-birth-place');
          }

          // Luogo del decesso (link solo se lat/lng presenti)
          $death_map = $build_map_url($death_place_lat, $death_place_lng);
          if ($death_place_name) {
            $dp_html = $death_map
              ? sprintf('<a href="%s" target="_blank" rel="noopener nofollow">%s</a>',
                        esc_url($death_map), esc_html($death_place_name))
              : esc_html($death_place_name);
            $meta_row(__('Luogo del decesso', 'cignozen'), $dp_html, 'meta-death-place');
          }

          if ($school_html !== '') {
            $meta_row(esc_html__('Scuola', 'cignozen'), $school_html, 'meta-school');
          }
          if ($generation_html !== '') {
            $meta_row(esc_html__('Generazione', 'cignozen'), $generation_html, 'meta-generation');
          }

          // --- Maestri (anti-ridondanza) ---
          if ($single_master_id) {
            if ($html = $link_for($single_master_id)) {
              $meta_row(__('Maestro','cignozen'), $html, 'meta-master-single');
            }
          } else {
            $total_teachers = count($teachers_ids);
            if ($total_teachers > 1 || count($teachers_list) > 0) {
              $meta_row(__('Maestri','cignozen'), implode(', ', $teachers_list), 'meta-teachers');
            }
            if ($primary_id && $primary_id !== $heir_id) {
              if ($html = $link_for($primary_id)) {
                $meta_row(__('Maestro','cignozen'), $html, 'meta-primary-teacher');
              }
            }
            if ($heir_id) {
              if ($html = $link_for($heir_id)) {
                $meta_row(__('Predecessore','cignozen'), $html, 'meta-dharma-heir-of');
              }
            }
          }

          // --- Successori (discendenti) ---
          if ($heirs_html !== '') {
            $meta_row(__('Successori','cignozen'), $heirs_html, 'meta-heirs');
          }
          if ($primary_students_html !== '') {
            $meta_row(__('Allievi Principali','cignozen'), $primary_students_html, 'meta-primary-students');
          }
        ?>
      </div>
    </section>
  <?php endif; ?>

  <section class="post-content">
    <?php
      the_content();

      wp_link_pages([
        'before' => '<div class="post-pagination"><h5>' . esc_html__('Pagine', 'cignozen') . '</h5><p class="page-links">',
        'next_or_number' => 'number',
        'after'  => '</p></div>',
      ]);
    ?>
  </section>

</article>

<?php
/** ------------------------------------------------------------------------
 * JSON-LD Person (SEO)
 * --------------------------------------------------------------------- */

$permalink = get_permalink();
$image_url = $portrait_id ? wp_get_attachment_image_url($portrait_id, 'full') : null;

$birthISO  = $build_iso_date($by ?: null, $bm ?: null, $bd ?: null, $bp ?: null);
$deathISO  = $build_iso_date($dy ?: null, $dm ?: null, $dd ?: null, $dp ?: null);

// Affiliazioni da tassonomia "school"
$build_affiliations = function (int $post_id): array {
  $terms = get_the_terms($post_id, 'school');
  if (!$terms || is_wp_error($terms)) return [];
  $orgs = [];
  foreach ($terms as $t) {
    $url = get_term_link($t);
    if (is_wp_error($url)) $url = '';
    $orgs[] = array_filter([
      '@type' => 'Organization',
      '@id'   => $url ? $url . '#org' : null,
      'url'   => $url ?: null,
      'name'  => wp_strip_all_tags($t->name),
      'description' => $t->description ? wp_strip_all_tags($t->description) : null,
    ], fn($v) => $v !== null && $v !== '');
  }
  return $orgs;
};

$to_person_nodes = function ($items) {
  if (empty($items)) return [];
  $items = is_array($items) ? $items : [$items];
  $out = [];
  foreach ($items as $p) {
    $id = $p instanceof WP_Post ? $p->ID : (is_numeric($p) ? (int)$p : 0);
    if ($id) {
      $out[] = [
        '@type' => 'Person',
        '@id'   => get_permalink($id),
        'name'  => get_the_title($id),
        'url'   => get_permalink($id),
      ];
    }
  }
  return $out;
};

$person = array_filter([
  '@context'        => 'https://schema.org',
  '@type'           => 'Person',
  '@id'             => $permalink . '#person',
  'url'             => $permalink,
  'name'            => wp_strip_all_tags($name_latin),
  'alternateName'   => $name_romaji ?: null,
  'additionalName'  => $name_hanzi ?: null,
  'honorificPrefix' => $honorific ?: null,
  'image'           => $image_url ?: null,
  'birthDate'       => $birthISO ?: null,
  'deathDate'       => $deathISO ?: null,
  'birthPlace'      => $birth_place_name ? array_filter([
                        '@type' => 'Place',
                        'name'  => wp_strip_all_tags($birth_place_name),
                        'geo'   => ($birth_place_lat !== '' && $birth_place_lng !== '') ? [
                          '@type' => 'GeoCoordinates',
                          'latitude'  => (float)$birth_place_lat,
                          'longitude' => (float)$birth_place_lng,
                        ] : null,
                      ]) : null,
  'deathPlace'      => $death_place_name ? array_filter([
                        '@type' => 'Place',
                        'name'  => wp_strip_all_tags($death_place_name),
                        'geo'   => ($death_place_lat !== '' && $death_place_lng !== '') ? [
                          '@type' => 'GeoCoordinates',
                          'latitude'  => (float)$death_place_lat,
                          'longitude' => (float)$death_place_lng,
                        ] : null,
                      ]) : null,
  'affiliation'     => $build_affiliations(get_the_ID()) ?: null,
  'knows'           => array_values(array_filter(array_merge(
                        $to_person_nodes($teachers_ids),
                        $to_person_nodes([$primary_id]),
                        $to_person_nodes([$heir_id])
                      ))) ?: null,
  'description'     => has_excerpt() ? wp_strip_all_tags(get_the_excerpt()) : null,
], fn($v) => $v !== null && $v !== '' && $v !== []);

echo '<script type="application/ld+json">' .
     wp_json_encode($person, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
     '</script>';

endwhile; endif;
