<?php
/**
 * Template Part: Single Maestro robust rendering
 * Assumes CPT "maestro". Safe-escapes, ACF-optional, and resilient fallbacks.
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

/** Optionally add lang attr if we detect CJK chars (very naive). */
$cjk_lang_attr = function (string $text): string {
  if (preg_match('/[\x{3400}-\x{9FFF}\x{F900}-\x{FAFF}]/u', $text)) {
    // default to generic Chinese; adjust per your content if needed
    return ' lang="zh"';
  }
  return '';
};

/** ------------------------------------------------------------------------
 * Data (with fallbacks to alternate keys you might have used)
 * --------------------------------------------------------------------- */

$portrait_id    = $acf_get('portrait', 0);

// Names / variants
$original_name  = (string) $acf_get('original_name', '');
$alt_pronounce  = (string) ($acf_get('alt_pronounce', '') ?: $acf_get('pronounce', ''));
$honorific_name = (string) ($acf_get('onorific_name', '') ?: $acf_get('honorific_name', ''));

// Dates / places (support both your keys and the earlier ones)
$birth_date     = (string) $acf_get('birth_date', '');
$birth_place    = (string) ($acf_get('birth_place', '') ?: $acf_get('place_of_birth', ''));
$death_date     = (string) $acf_get('death_date', '');
$death_place    = (string) ($acf_get('death_place', '') ?: $acf_get('place_of_death', ''));

// School
$school         = (string) $acf_get('school', '');

// Relations (teacher/successor) supporting alt field names
$teachers_raw   = $acf_get('teacher', []);
$successors_raw = $acf_get('successor', $acf_get('successore', []));

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
      <div class="meta-alt-name"><?php echo esc_html($alt_pronounce); ?></div>
    <?php endif; ?>

    <h1 class="master-title" itemprop="name"><?php echo esc_html(get_the_title()); ?></h1>

    <div class="maestro-layout">
      <?php if ($portrait_id): ?>
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
      <?php endif; ?>
    </div>

    <?php if ($original_name !== ''): ?>
      <div class="meta-original-name"<?php echo $cjk_lang_attr($original_name); ?>>
        <?php echo esc_html($original_name); ?>
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
    ($school !== '') ||
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
        $meta_row(esc_html__('Scuola', 'your-textdomain'), esc_html($school), 'meta-school');

        if ($teachers_html !== '') {
          // Singular/plural label if needed (kept simple)
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
<?php endwhile; endif; ?>
