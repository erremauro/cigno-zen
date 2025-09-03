<?php
/**
 * Template Part: Single Maestro robust rendering
 * Assumes CPT "maestro". Safe-escapes, ACF-optional, taxonomy "school", and resilient fallbacks.
 */

if ( have_posts() ) : while ( have_posts() ) : the_post();

/** ------------------------------------------------------------------------
 * Helpers
 * --------------------------------------------------------------------- */

/** Get field safely with graceful fallback if ACF is missing. */
$acf_get = function (string $key, $default = '') {
  if ( function_exists('get_field') ) {
    $val = get_field($key);
    return (null !== $val && $val !== '') ? $val : $default;
  }
  // Fallback to post_meta for simple text fields
  $val = get_post_meta(get_the_ID(), $key, true);
  return (null !== $val && $val !== '') ? $val : $default;
};

/** Render a list of related people (post_object(s) as WP_Post[]|int[]|WP_Post|int). */
$render_people_list = function ($people): string {
  if (empty($people)) return '';
  $people = is_array($people) ? $people : [$people];
  $items  = [];
  foreach ($people as $p) {
    if ($p instanceof WP_Post) {
      $id = $p->ID;
    } elseif (is_numeric($p)) {
      $id = (int) $p;
    } else {
      continue;
    }
    $title = get_the_title($id);
    if ($title) {
      $items[] = sprintf(
        '<a href="%s">%s</a>',
        esc_url(get_permalink($id)),
        esc_html($title)
      );
    }
  }
  return implode(', ', $items);
};

/** Echo a meta row only if $value is not empty. */
$meta_row = function (string $label, $value, string $class = '') {
  if ($value === null || $value === '') return;
  printf(
    '<div class="meta-row %s"><label class="meta-label">%s</label> <span class="meta-data">%s</span></div>',
    esc_attr($class),
    esc_html($label),
    // value may contain anchors (teachers/successors), allow safe HTML tags
    wp_kses($value, ['a' => ['href' => [], 'title' => []]])
  );
};

/** Build linked term list HTML for a taxonomy. */
$render_term_list = function (int $post_id, string $tax): string {
  $terms = get_the_terms($post_id, $tax);
  if (!$terms || is_wp_error($terms)) return '';
  $links = [];
  foreach ($terms as $t) {
    $links[] = sprintf('<a href="%s">%s</a>',
      esc_url(get_term_link($t)),
      esc_html($t->name)
    );
  }
  return implode(', ', $links);
};

/** ------------------------------------------------------------------------
 * Data (with fallbacks)
 * --------------------------------------------------------------------- */

$portrait_id    = $acf_get('portrait', 0);

// Names / variants
$traditional_name  = (string) $acf_get('traditional_name', '');
$alt_pronounce     = (string) $acf_get('alt_pronounce', '');
$honorific_name    = (string) $acf_get('honorific_name', '');

// Dates / places
$birth_date     = (string) $acf_get('birth_date', '');
$birth_place    = (string) $acf_get('birth_place', '');
$death_date     = (string) $acf_get('death_date', '');
$death_place    = (string) $acf_get('death_place', '');

// Taxonomy "school"
$school_html   = $render_term_list(get_the_ID(), 'school');

// Relations (teacher/successor)
$teachers_raw   = $acf_get('teachers', []);
$successors_raw = $acf_get('successors', $acf_get('successors', []));

// Normalize relations for rendering
$teachers_html   = $render_people_list($teachers_raw);
$successors_html = $render_people_list($successors_raw);

// Header flags
$has_dates = ($birth_date !== '' || $death_date !== '');

// Determine portrait size (320w suggested) — use array(width, height=0) to constrain by width
$portrait_size = [320, 0];

?>
<article id="post-<?php the_ID(); ?>" <?php post_class('maestro-article'); ?> itemscope itemtype="https://schema.org/Person">

  <header class="master-info">
    <?php if ($alt_pronounce !== ''): ?>
      <div class="meta-alt-name">(<?php echo esc_html($alt_pronounce); ?>)</div>
    <?php endif; ?>

    <h1 class="master-title" itemprop="name"><?php echo esc_html(get_the_title()); ?></h1>

    <?php if ($portrait_id): ?>
    <div class="maestro-layout">
        <figure class="maestro-portrait">
          <?php
          echo wp_get_attachment_image(
            (int) $portrait_id,
            $portrait_size,
            false,
            [
              'class'    => 'portrait-img',
              'itemprop' => 'image',
              'alt'      => esc_attr(get_the_title() . ' – Ritratto'),
              'loading'  => 'lazy',
              'decoding' => 'async',
              'sizes'    => '(max-width: 480px) 50vw, 320px',
            ]
          );
          ?>
        </figure>
    </div>
    <?php endif; ?>

    <?php if ($traditional_name !== ''): ?>
      <div class="meta-original-name">
        <?php echo esc_html($traditional_name); ?>
      </div>
    <?php endif; ?>

    <?php if ($has_dates): ?>
      <div class="meta-master-dates">
        <?php if ($birth_date !== ''): ?>
          <span class="birth" itemprop="birthDate"><?php echo esc_html($birth_date); ?></span>
        <?php endif; ?>
        <?php if ($death_date !== ''): ?>
          <span class="sep" aria-hidden="true"> – </span>
          <span class="death" itemprop="deathDate"><?php echo esc_html($death_date); ?></span>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </header>

  <?php
  // Determine if there is anything to show in the meta section
  $has_meta =
    ($honorific_name !== '') ||
    ($birth_place !== '') ||
    ($death_place !== '') ||
    ($school_html !== '') ||
    ($teachers_html !== '') ||
    ($successors_html !== '');
  ?>

  <?php if ($has_meta): ?>
    <section class="master-meta collapsable-section" aria-labelledby="master-meta-title">
      <h2 id="master-meta-title" class="collapsable-toggle"><?php echo esc_html__('Informazioni', 'your-textdomain'); ?></h2>
      <div class="collapsable-content">
        <?php
        $meta_row(esc_html__('Titolo Onorifico', 'your-textdomain'), esc_html($honorific_name), 'meta-honorific');
        $meta_row(esc_html__('Luogo di Nascita', 'your-textdomain'), esc_html($birth_place), 'meta-birth-place');
        $meta_row(esc_html__('Luogo del Decesso', 'your-textdomain'), esc_html($death_place), 'meta-death-place');

        // Prefer taxonomy "school"; fallback to legacy ACF "school"
        if ($school_html !== '') {
          $meta_row(esc_html__('Scuola', 'your-textdomain'), $school_html, 'meta-school');
        } elseif ($school_legacy !== '') {
          $meta_row(esc_html__('Scuola', 'your-textdomain'), esc_html($school_legacy), 'meta-school-legacy');
        }

        if ($teachers_html !== '') {
          $meta_row(esc_html__('Maestro', 'your-textdomain'), $teachers_html, 'meta-teachers');
        }
        if ($successors_html !== '') {
          $meta_row(esc_html__('Successore', 'your-textdomain'), $successors_html, 'meta-successors');
        }
        ?>
      </div>
    </section>
  <?php endif; ?>

  <section class="post-content">
    <?php
    the_content();

    if (function_exists('custom_post_pagination')) {
      custom_post_pagination();
    }

    wp_link_pages([
      'before'         => '<div class="post-pagination"><h5>' . esc_html__('Pagine', 'your-textdomain') . '</h5><p class="page-links">',
      'next_or_number' => 'number',
      'after'          => '</p></div>',
    ]);
    ?>
  </section>

</article>

<?php
  /**
   * JSON-LD Person structured data
   */

  // Helpers
  $normalize_date = function (?string $date) {
    if (!$date) return null;
    $ts = strtotime($date);
    return $ts ? gmdate('Y-m-d', $ts) : trim($date);
  };

  $to_person_nodes = function ($items) {
    if (empty($items)) return [];
    $items = is_array($items) ? $items : [$items];
    $out = [];
    foreach ($items as $p) {
      $id = null;
      if ($p instanceof WP_Post)      { $id = $p->ID; }
      elseif (is_numeric($p))         { $id = (int) $p; }
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

  // Build affiliations from taxonomy "school" (with optional ACF logo on term)
  $build_affiliations = function (int $post_id): array {
    $terms = get_the_terms($post_id, 'school');
    if (!$terms || is_wp_error($terms)) return [];
    $orgs = [];
    foreach ($terms as $t) {
      $term_url = get_term_link($t);
      if (is_wp_error($term_url)) { $term_url = ''; }
      $org = [
        '@type' => 'Organization',
        '@id'   => $term_url ? $term_url . '#org' : null,
        'url'   => $term_url ?: null,
        'name'  => wp_strip_all_tags($t->name),
        'description' => $t->description ? wp_strip_all_tags($t->description) : null,
      ];

      // Optional: ACF term logo (field key "logo") -> full URL
      if (function_exists('get_field')) {
        // ACF term context uses 'taxonomy_term_{term_id}' OR '{tax}_{term_id}'
        $logo_id = get_field('logo', 'taxonomy_term_' . $t->term_id);
        if (!$logo_id) {
          $logo_id = get_field('logo', 'school_' . $t->term_id);
        }
        if ($logo_id) {
          $logo_url = wp_get_attachment_image_url((int)$logo_id, 'full');
          if ($logo_url) {
            $org['logo'] = [
              '@type' => 'ImageObject',
              'url'   => $logo_url,
            ];
          }
        }
      }

      // Remove nulls
      $orgs[] = array_filter($org, fn($v) => $v !== null && $v !== '');
    }
    return $orgs;
  };

  // Gather data
  $permalink     = get_permalink();
  $image_url     = $portrait_id ? wp_get_attachment_image_url((int)$portrait_id, 'full') : null;

  $birthDateISO  = $normalize_date($birth_date);
  $deathDateISO  = $normalize_date($death_date);

  $affiliations  = $build_affiliations(get_the_ID());

  // Fallback affiliation from legacy ACF "school" if no taxonomy terms
  if (!$affiliations && !empty($school_legacy)) {
    $affiliations = [[
      '@type' => 'Organization',
      'name'  => wp_strip_all_tags($school_legacy),
    ]];
  }

  // Teachers / successors mapped as "knows"
  $knows = array_values(array_filter(array_merge(
    $to_person_nodes($teachers_raw),
    $to_person_nodes($successors_raw)
  )));

  // Build Person node (omit empty properties)
  $person = array_filter([
    '@context'        => 'https://schema.org',
    '@type'           => 'Person',
    '@id'             => $permalink . '#person',
    'url'             => $permalink,
    'name'            => wp_strip_all_tags(get_the_title()),
    'alternateName'   => $alt_pronounce ?: null,          // e.g., romanization
    'honorificPrefix' => $honorific_name ?: null,          // e.g., "Chan Master", etc.
    'additionalName'  => $traditional_name ?: null,        // e.g., traditional/original name
    'image'           => $image_url ?: null,
    'birthDate'       => $birthDateISO ?: null,
    'deathDate'       => $deathDateISO ?: null,
    'birthPlace'      => $birth_place ? ['@type' => 'Place', 'name' => wp_strip_all_tags($birth_place)] : null,
    'deathPlace'      => $death_place ? ['@type' => 'Place', 'name' => wp_strip_all_tags($death_place)] : null,
    'affiliation'     => $affiliations ?: null,           // array of Organizations
    'knows'           => $knows ?: null,                  // related teachers/successors
    'description'     => has_excerpt() ? wp_strip_all_tags(get_the_excerpt()) : null,
  ], function($v){ return $v !== null && $v !== '' && $v !== []; });

  echo '<script type="application/ld+json">' .
       wp_json_encode($person, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) .
       '</script>';
?>


<?php endwhile; endif; ?>
