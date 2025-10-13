<?php
/**
 * Single Maestro — Cigno Zen
 * - Campi ACF Free: name_latin, name_hanzi, name_romaji, honorific_name,
 *   portrait(+alt/credit), date strutturate, luoghi (name+lat/lng),
 *   tassonomie: school, generazione, relazioni: teachers, primary_teacher, is_dharma_heir_of.
 * - Resiliente: funziona anche senza ACF (fallback post_meta per text/number).
 */

if (have_posts()) : while (have_posts()) : the_post();

/** ------------------------------------------------------------------------
 * Helpers
 * --------------------------------------------------------------------- */

/** Build a map URL (solo se lat/lng presenti). Supporta: google | osm | ohm (default). */
$build_map_url = function (?string $name, $lat = null, $lng = null, string $provider = 'ohm', int $zoom = 10): ?string {
  // Normalizza coordinate
  $lat = is_numeric($lat) ? (float)$lat : null;
  $lng = is_numeric($lng) ? (float)$lng : null;

  if ($lat !== null && $lng !== null) {
    $lat_s = number_format($lat, 6, '.', '');
    $lng_s = number_format($lng, 6, '.', '');

    if ($provider === 'osm') {
      return sprintf('https://www.openstreetmap.org/?mlat=%s&mlon=%s#map=%d/%s/%s',
        rawurlencode($lat_s), rawurlencode($lng_s), $zoom, rawurlencode($lat_s), rawurlencode($lng_s));
    }
    if ($provider === 'ohm') {
      // OpenHistoricalMap (viewer storico)
      return sprintf('https://www.openhistoricalmap.org/#map=%d/%s/%s',
        $zoom, rawurlencode($lat_s), rawurlencode($lng_s));
    }
    // google (fallback)
    return sprintf('https://www.google.com/maps/search/?api=1&query=%s%%2C%s',
      rawurlencode($lat_s), rawurlencode($lng_s));
  }
  // niente coord → nessun link
  return null;
};

/** Costruisce query OHM ?date=...&daterange=... a partire dagli anni. */
$ohm_time_query = function (?int $year_focus, ?int $year_start, ?int $year_end): string {
  $qs = [];
  if ($year_focus) {
    $qs['date'] = sprintf('%04d-01-01', $year_focus);
  }
  if ($year_start || $year_end) {
    $start = $year_start ? sprintf('%04d-01-01', $year_start) : '0001-01-01';
    $end   = $year_end   ? sprintf('%04d-12-31', $year_end)   : '2100-12-31';
    $qs['daterange'] = $start . ',' . $end;
  }
  return $qs ? ('?' . http_build_query($qs)) : '';
};

/** Inserisce la query PRIMA di "#map=" nell'URL di OpenHistoricalMap. */
$ohm_with_time = function (?string $ohm_url, string $query): ?string {
  if (!$ohm_url || $query === '') return $ohm_url;
  // Inserisce i parametri prima di #map=
  $pos = strpos($ohm_url, '#map=');
  if ($pos === false) return $ohm_url;
  // Se l'URL ha già una query (poco probabile), la sostituiamo in blocco
  $base = substr($ohm_url, 0, $pos);
  $hash = substr($ohm_url, $pos);
  // Evita doppio "?" se già presente
  if (strpos($base, '?') !== false) {
    // rimpiazza qualsiasi cosa dopo il ? con la nuova query
    $base = preg_replace('/\?.*$/', '', $base);
  }
  return $base . $query . $hash;
};

/** Calcola anni tra nascita e morte. Ritorna int o null se non calcolabile. */
$calc_years = function (int $by = null, int $bm = null, int $bd = null, int $dy = null, int $dm = null, int $dd = null): ?int {
  if (!$by || !$dy) return null;

  // Se abbiamo date complete, usa DateTime per un'età precisa
  if ($by && $bm && $bd && $dy && $dm && $dd) {
    try {
      $b = new DateTime(sprintf('%04d-%02d-%02d', $by, $bm, $bd));
      $d = new DateTime(sprintf('%04d-%02d-%02d', $dy, $dm, $dd));
      if ($d < $b) return null;
      return (int)$b->diff($d)->y;
    } catch (Exception $e) { /* degrada sotto */ }
  }

  // Altrimenti differenza anni (con correzione grossolana se mese/giorno disponibili)
  $age = $dy - $by;
  if ($bm && $dm) {
    if ($dm < $bm)        $age -= 1;
    elseif ($dm === $bm && $bd && $dd && $dd < $bd) $age -= 1;
  }
  return ($age >= 0) ? $age : null;
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
    wp_kses($value, ['a' => ['href' => [], 'title' => [], 'target' => [], 'rel' => []]])
  );
};

/** Compone una data display da year/month/day + precision. */
$build_display_date = function (?int $y, ?int $m, ?int $d, ?string $prec): string {
  if (!$y) return '';
  $pad = fn($n) => str_pad((string)$n, 2, '0', STR_PAD_LEFT);
  switch ($prec) {
    case 'full':
      if ($m && $d) return "{$y}-{$pad($m)}-{$pad($d)}";
      // degrade gracefully
    case 'year-month':
      if ($m) return "{$y}-{$pad($m)}";
      // degrade
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
$portrait_alt     = (string) $acf_get('portrait_alt', '');
$portrait_credit  = (string) $acf_get('portrait_credit', '');
$portrait_size    = [320, 0]; // width-limited

// Date
$by = (int) $acf_get('birth_year', 0);
$bm = (int) $acf_get('birth_month', 0);
$bd = (int) $acf_get('birth_day', 0);
$bp = (string) $acf_get('birth_precision', 'year');

$dy = (int) $acf_get('death_year', 0);
$dm = (int) $acf_get('death_month', 0);
$dd = (int) $acf_get('death_day', 0);
$dp = (string) $acf_get('death_precision', 'year');

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

// Logica anti-ridondanza
$single_master_id = 0;
if ($primary_id && $heir_id && $primary_id === $heir_id) {
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

// Header: c'è almeno una data?
$has_dates = ($birth_display !== '' || $death_display !== '');

// Determina se c'è almeno una riga “maestri” da mostrare
$has_master_rows = $single_master_id
  || count($teachers_list) > 0
  || ($primary_id && $primary_id !== $heir_id)
  || ($heir_id && $heir_id !== $primary_id);

// Sezione meta: c'è qualcosa?
$has_meta =
  ($birth_place_name !== '') ||
  ($death_place_name !== '') ||
  ($school_html !== '') ||
  ($generation_html !== '') ||
  $has_master_rows;

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
          echo wp_get_attachment_image(
            $portrait_id,
            $portrait_size,
            false,
            [
              'class'    => 'portrait-img',
              'itemprop' => 'image',
              'alt'      => esc_attr($portrait_alt ?: ($name_latin . ' – Ritratto')),
              'loading'  => 'lazy',
              'decoding' => 'async',
              'sizes'    => '(max-width: 480px) 50vw, 320px',
            ]
          );
          if ($portrait_credit) {
            echo '<figcaption class="portrait-credit">'. esc_html($portrait_credit) .'</figcaption>';
          }
        ?>
      </figure>
    <?php endif; ?>

    <?php
      $age_years = $calc_years($by ?: null, $bm ?: null, $bd ?: null, $dy ?: null, $dm ?: null, $dd ?: null);
    ?>
    <?php if ($birth_display || $death_display): ?>
      <div class="meta-master-dates">
        <?php if ($birth_display): ?>
          <span class="birth" itemprop="birthDate"><?php echo esc_html($birth_display); ?></span>
        <?php endif; ?>
        <?php if ($death_display): ?>
          <span class="sep" aria-hidden="true"> – </span>
          <span class="death" itemprop="deathDate"><?php echo esc_html($death_display); ?></span>
        <?php endif; ?>
        <?php if ($age_years !== null): ?>
          <span class="age"> (<?php echo esc_html($age_years . ' ' . __('anni','cignozen')); ?>)</span>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </header>

  <?php if ($has_meta): ?>
    <section class="master-meta collapsable-section" aria-labelledby="master-meta-title">
      <h2 id="master-meta-title" class="collapsable-toggle"><?php echo esc_html__('Informazioni', 'cignozen'); ?></h2>
      <div class="collapsable-content">
        <?php
          // Luogo di nascita – OHM con time slider (se coord presenti)
          $birth_map = $build_map_url($birth_place_name, $birth_place_lat, $birth_place_lng, 'google', 10);
          if ($birth_map) {
            // focus sull'anno di nascita; range nascita–morte
            $q_birth  = $ohm_time_query($by ?: null, $by ?: null, $dy ?: null);
            $birth_map = $ohm_with_time($birth_map, $q_birth);
          }
          if ($birth_place_name) {
            $bp_html = $birth_map
              ? sprintf('<a href="%s" target="_blank" rel="noopener nofollow">%s</a>',
                        esc_url($birth_map), esc_html($birth_place_name))
              : esc_html($birth_place_name);
            $meta_row(__('Luogo di nascita', 'cignozen'), $bp_html, 'meta-birth-place');
          }

          // Luogo del decesso – OHM con time slider (se coord presenti)
          $death_map = $build_map_url($death_place_name, $death_place_lat, $death_place_lng, 'google', 10);
          if ($death_map) {
            // focus sull'anno di morte; stesso range nascita–morte
            $q_death  = $ohm_time_query($dy ?: null, $by ?: null, $dy ?: null);
            $death_map = $ohm_with_time($death_map, $q_death);
          }
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

          // --- Sezione Maestri senza ridondanze ---
          if ($single_master_id) {
            if ($html = $link_for($single_master_id)) {
              $meta_row(__('Maestro','cignozen'), $html, 'meta-master-single');
            }
          } else {
            // Mostra "Maestri" se ci sono più maestri in totale o "altri" oltre a primary/heir
            $total_teachers = count($teachers_ids);
            if ($total_teachers > 1 || count($teachers_list) > 0) {
              $meta_row(__('Maestri','cignozen'), implode(', ', $teachers_list), 'meta-teachers');
            }

            // Primary Teacher (solo se diverso dall'Heir)
            if ($primary_id && $primary_id !== $heir_id) {
              if ($html = $link_for($primary_id)) {
                $meta_row(__('Maestro principale','cignozen'), $html, 'meta-primary-teacher');
              }
            }

            // Dharma Heir (solo se diverso dal Primary)
            if ($heir_id && $heir_id !== $primary_id) {
              if ($html = $link_for($heir_id)) {
                $meta_row(__('Erede del Dharma di','cignozen'), $html, 'meta-dharma-heir-of');
              }
            }
          }
        ?>
      </div>
    </section>
  <?php endif; ?>

  <section class="post-content">
    <?php
      the_content();

      // Paginazione multipagina classica
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
                          'longitude' => (float)$death_place_lng, // <- note: typo fix below
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

// FIX: longitude del birthPlace (copiaincolla) — corregge l'errore di campo
if (isset($person['birthPlace']['geo']['longitude'])) {
  $person['birthPlace']['geo']['longitude'] = (float)$birth_place_lng;
}

echo '<script type="application/ld+json">' .
     wp_json_encode($person, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
     '</script>';

endwhile; endif;
