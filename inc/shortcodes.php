<?php
// Strip shortcodes from search results
add_filter( 'ep_post_sync_args', function( array $post_args, int $post_id ) : array {
    $raw = get_post_field( 'post_content', $post_id );
    $no_sc = strip_shortcodes( $raw );
    $clean = wp_strip_all_tags( $no_sc, true );

    $post_args['post_content'] = $clean;

    if ( ! empty( $post_args['post_excerpt'] ) ) {
        $post_args['post_excerpt'] = wp_trim_words(
            wp_strip_all_tags( strip_shortcodes( $post_args['post_excerpt'] ), true ),
            55
        );
    }

    return $post_args;
}, 10, 2 );

add_shortcode('sentence', function($atts) {
    // Merge defaults
    $atts = shortcode_atts([
        'source'      => '',
        'reading'     => '',
        'translation' => '',
        'class'       => '',
        'order'       => '',
    ], $atts, 'sentence');

    // Sanitize input
    $source      = wp_kses_post($atts['source']);
    $reading     = wp_kses_post($atts['reading']);
    $translation = wp_kses_post($atts['translation']);

    // Allow multiple classes separated by spaces (each sanitized)
    $extra_classes = array_filter(array_map('sanitize_html_class', preg_split('/\s+/', (string)$atts['class'])));
    $wrapper_classes = trim('sentence-box' . (!empty($extra_classes) ? ' ' . implode(' ', $extra_classes) : ''));

    // "order" can be non-numeric like "1." or "(1)"
    $order = trim(wp_strip_all_tags((string)$atts['order']));
    $order_html = '';
    if ($order !== '') {
        // Ensure a trailing space for nice separation (e.g., "1. ")
        $order_display = rtrim($order) . (substr($order, -1) === ' ' ? '' : ' ');
        $order_html = '<span class="order">' . esc_html($order_display) . '</span>';
    }

    // Build HTML
    $html  = '<div class="' . esc_attr($wrapper_classes) . '">';
    $html .= '<div class="source">' . $order_html . $source . '</div>';
    $html .= '<div class="reading">' . $reading . '</div>';
    $html .= '<div class="translation">' . $translation . '</div>';
    $html .= '</div>';

    return $html;
});

add_shortcode('separator', function() {
    $html = '<p class="separator">＊&nbsp;&nbsp;&nbsp;＊&nbsp;&nbsp;&nbsp;＊<p>';
    return $html;
});

/** 
 * Shortcode: [autore id="3"]Mario Rossi[/autore]
 * Renders: <a href="/autore/mario-rossi" title="Visualizza la Pagina Autore di Mario Rossi">Mario Rossi</a>
 */
add_shortcode('autore', function ($atts, $content = null) {

    $atts = shortcode_atts([
        'id'     => null,
        'target' => '',
    ], $atts);

    if (!$atts['id']) {
        return '';
    }

    $url = get_author_posts_url((int) $atts['id']);
    $target = trim((string) $atts['target']);
    $target_attr = $target !== '' ? sprintf(' target="%s"', esc_attr($target)) : '';

    // fallback se il contenuto è vuoto
    $label = $content ?: get_the_author_meta('display_name', $atts['id']);

    return sprintf(
        '<a href="%s" title="Visualizza la Pagina Autore di %s"%s>%s</a>',
        esc_url($url),
        esc_html($label),
        $target_attr,
        esc_html($label)
    );
});

/**
 * Shortcode: [maestro slug="daisaku-ikeda"]Daisaku Ikeda[/maestro]
 * Renders: <a href="/maestro/daisaku-ikeda" title="Leggi la biografia del Maestro Daisaku Ikeda su Cigno Zen">Daisaku Ikeda</a>
 */
add_shortcode('maestro', function ($atts, $content = null) {
    $atts = shortcode_atts([
        'slug' => '',
    ], $atts, 'maestro');

    $label = trim(wp_strip_all_tags((string) $content));
    if ($label === '') {
        return '';
    }

    $slug = trim(wp_strip_all_tags((string) $atts['slug']));
    if ($slug === '') {
        $slug = strtolower(str_replace("'", '', $label));
        $slug = preg_replace('/\s+/', '-', trim($slug));
    }

    if ($slug === '') {
        return '';
    }

    $href = '/maestro/' . $slug;

    return sprintf(
        '<a href="%1$s" title="Leggi la biografia del Maestro %2$s su Cigno Zen">%3$s</a>',
        esc_url($href),
        esc_html($label),
        esc_html($label)
    );
});


/**
 * Shortcode: [LS id="1.68"]
 * Renders: <a href="/riferimenti-al-sutra-del-loto#ls-1-68" title="Consulta il riferimento al Sutra del Loto" target="_blank">LS 1.68</a>
 */
add_shortcode('LS', function($atts) {
    $atts = shortcode_atts([
        'id' => '',
    ], $atts, 'LS');

    $raw_id = trim(wp_strip_all_tags((string)$atts['id']));
    if ($raw_id === '') {
        return '';
    }

    $converted_id = str_replace('.', '-', $raw_id);
    $base_url = '/riferimenti-al-sutra-del-loto';
    $href = $base_url . '#ls-' . $converted_id;
    $href = add_query_arg('cr', 'disabled', $href);

    return sprintf(
        '<a href="%1$s" title="Consulta il riferimento al Sutra del Loto" target="_blank">LS %2$s</a>',
        esc_url($href),
        esc_html($raw_id)
    );
});

/**
 * [collapsable title="Biografia" initial="open" tag="h2" id="bio"]
 * Contenuto...
 * [/collapsable]
 */
add_shortcode('collapsable', function ($atts = [], $content = null, $tag = '') {
  // Defaults
  $atts = shortcode_atts([
    'title'   => 'Section',
    'initial' => 'open',     // "open" | "closed"
    'tag'     => 'h2',       // h2..h6
    'id'      => '',         // opzionale
    'class'   => '',         // classi extra sulla root
  ], $atts, $tag);

  // Sanitize
  $title   = wp_kses_post($atts['title']);
  $initial = strtolower(trim($atts['initial'])) === 'closed' ? 'closed' : 'open';
  $heading = in_array(strtolower($atts['tag']), ['h2','h3','h4','h5','h6'], true) ? strtolower($atts['tag']) : 'h2';
  $rootcls = trim('collapsable-section ' . sanitize_html_class($atts['class']));
  $panel_id = $atts['id'] !== '' ? sanitize_title($atts['id']) : 'collapsable-' . wp_generate_password(8, false, false);

  // ARIA state at render time (lo script corregge alla init)
  $is_open = ($initial === 'open');
  $aria_expanded = $is_open ? 'true' : 'false';
  $aria_hidden   = $is_open ? 'false' : 'true';

  // Inner content (shortcode & blocks)
  $inner = do_shortcode(shortcode_unautop($content ?? ''));

  ob_start(); ?>
  <div class="<?php echo esc_attr($rootcls); ?>" data-initial="<?php echo esc_attr($initial); ?>">
    <<?php echo $heading; ?>
      class="collapsable-toggle"
      role="button"
      tabindex="0"
      aria-controls="<?php echo esc_attr($panel_id); ?>"
      aria-expanded="<?php echo esc_attr($aria_expanded); ?>">
      <?php echo $title; ?>
    </<?php echo $heading; ?>>
    <div id="<?php echo esc_attr($panel_id); ?>" class="collapsable-content" aria-hidden="<?php echo esc_attr($aria_hidden); ?>">
      <?php echo $inner; ?>
    </div>
  </div>
  <?php
  return ob_get_clean();
});

// === Footnotes shortcodes: [footnotes], [fndef], [fn] =================
// Output target structure:
//
// <div class="footnotes">
//   <h2 id="footnotes-toggle">Note</h2>
//   <div class="footnotes-content">
//     <p class="footnote" id="fn1"><a class="fnref" href="#fnref1">1</a> Contenuto <a href="#fnref1" class="backlink">↩</a></p>
//   </div>
// </div>

if (!defined('ABSPATH')) { exit; }

/** Sanitize plain id into slug-ish but preserve digits for anchors */
function cz_fn_sanitize_id($id_raw) {
  $id_raw = (string) $id_raw;
  $id_raw = trim($id_raw);
  if ($id_raw === '') {
    static $auto = 0; $auto++;
    return (string) $auto;
  }
  // Allow [A-Za-z0-9_-], strip others
  $san = preg_replace('~[^A-Za-z0-9_-]+~', '', $id_raw);
  return $san !== '' ? $san : '1';
}

/** Inline reference: [fn id="1"] -> <sup><a id="fnref1" href="#fn1">1</a></sup> */
add_shortcode('fn', function($atts){
  $atts = shortcode_atts([
    'id' => '',
    'label' => '', // optional custom label shown instead of id
  ], $atts, 'fn');

  $id = cz_fn_sanitize_id($atts['id']);
  $label = $atts['label'] !== '' ? wp_kses_post($atts['label']) : esc_html($id);

  $ref_id  = 'fnref' . $id;
  $note_id = 'fn' . $id;

  return sprintf(
    '<sup class="fn"><a id="%1$s" href="#%2$s">%3$s</a></sup>',
    esc_attr($ref_id),
    esc_attr($note_id),
    $label
  );
});

/** Footnote definition to be used *inside* [footnotes]: [footnotedef id="1"]Content[/footnotedef] */
add_shortcode('fndef', function($atts = [], $content = null){
  $atts = shortcode_atts([
    'id' => '',
  ], $atts, 'fndef');

  $id = cz_fn_sanitize_id($atts['id']);
  $ref_id  = 'fnref' . $id;
  $note_id = 'fn' . $id;

  // Allow other shortcodes inside the note content
  $inner = do_shortcode(shortcode_unautop($content ?? ''));
  // Safe HTML (paragraph-like): keep links/emphasis/basic formatting
  $inner = wp_kses_post($inner);

  // Build: <p class="footnote" id="fn1"><a class="fnref" href="#fnref1">1</a> ... <a href="#fnref1" class="backlink">↩</a></p>
  return sprintf(
    '<p class="footnote" id="%1$s"><a class="fnref" href="#%2$s">%3$s</a> %4$s <a href="#%2$s" class="backlink">↩</a></p>',
    esc_attr($note_id),
    esc_attr($ref_id),
    esc_html($id),
    $inner
  );
});

/** Wrapper: [footnotes title="Note" heading="h2" toggle_id="footnotes-toggle"]...[/footnotes] */
add_shortcode('footnotes', function($atts = [], $content = null){
  $atts = shortcode_atts([
    'title'     => 'Note',
    'heading'   => 'h2',                 // h2..h6
    'toggle_id' => 'footnotes-toggle',   // id for the heading
    'class'     => '',                   // extra classes on root
  ], $atts, 'footnotes');

  $heading = in_array(strtolower($atts['heading']), ['h2','h3','h4','h5','h6'], true) ? strtolower($atts['heading']) : 'h2';
  $title   = wp_kses_post($atts['title']);
  $toggle_id = sanitize_html_class($atts['toggle_id']);
  $root_cls = trim('footnotes ' . sanitize_html_class($atts['class']));

  // Process inner content to expand [fndef] items (and any nested shortcodes)
  $items_html = do_shortcode(shortcode_unautop($content ?? ''));

  ob_start(); ?>
  <div class="<?php echo esc_attr($root_cls); ?>">
    <<?php echo $heading; ?> id="<?php echo esc_attr($toggle_id); ?>"><?php echo $title; ?></<?php echo $heading; ?>>
    <div class="footnotes-content">
      <?php echo $items_html; ?>
    </div>
  </div>
  <?php
  return ob_get_clean();
});

/**
 * Shortcode: [references title="Riferimenti" id="refs-mazu" class="mb-6" post_id="123"]
 * - Automatically wraps results inside your existing [collapsable] shortcode.
 * - If no references are found, returns an empty string (renders nothing).
 */

add_shortcode('references', function ($atts = [], $content = null, $tag = '') {
  // Shortcode attrs: wrapper title/id/class + optional target post_id
  $atts = shortcode_atts([
    'title'   => 'Riferimenti', // collapsable heading
    'id'      => '',            // collapsable id
    'class'   => '',            // extra classes on collapsable root
    'post_id' => '',            // optional: target post id (default current)
  ], $atts, $tag);

  // Target post ID
  $target_id = $atts['post_id'] !== '' ? intval($atts['post_id']) : get_the_ID();
  if ($target_id <= 0) return '';

  // Cache
  $cache_key = 'cz_refs_' . $target_id;
  $groups = get_transient($cache_key);
  if ($groups === false) {
    $groups = cz_find_referrers_grouped_by_post_type($target_id);
    set_transient($cache_key, $groups, MINUTE_IN_SECONDS * 10);
  }

  // Nothing to show => render nothing (no collapsable either)
  if (empty($groups)) return '';

  // Build inner HTML list (grouped by post type)
  $inner = '';
  foreach ($groups as $ptype => $ids) {
    $obj = get_post_type_object($ptype);
    if (!$obj) continue;

    $label = $obj->labels->name ?: $ptype;
    $inner .= '<div class="cz-references-group cz-references-group--' . esc_attr($ptype) . '">';
    $inner .= '<h3 class="cz-references-heading">' . esc_html($label) . '</h3>';
    $inner .= '<ul class="cz-references-list">';

    foreach ($ids as $id) {
      $title = get_the_title($id);
      $link  = get_permalink($id);
      if (!$title || !$link) continue;
      $inner .= '<li class="cz-references-item"><a href="' . esc_url($link) . '">' . esc_html($title) . '</a></li>';
    }

    $inner .= '</ul></div>';
  }

  // Sanitize inner HTML
  $inner = wp_kses($inner, [
    'div' => ['class' => true],
    'h3'  => ['class' => true],
    'ul'  => ['class' => true],
    'li'  => ['class' => true],
    'a'   => ['href' => true, 'class' => true, 'title' => true, 'target' => true, 'rel' => true],
  ]);

  // Build the [collapsable] wrapper automatically (initial=closed, tag=h3 by default)
  $title_attr = esc_attr($atts['title']);
  $id_attr    = $atts['id'] !== '' ? ' id="' . esc_attr($atts['id']) . '"' : '';
  $class_attr = $atts['class'] !== '' ? ' class="' . esc_attr(trim('references-collapsable ' . $atts['class'])) . '"' : ' class="references-collapsable"';

  // Compose collapsable shortcode string
  $wrapped = '[collapsable title="' . $title_attr . '" initial="closed" tag="h3"' . $id_attr . $class_attr . ']' . $inner . '[/collapsable]';

  // Render collapsable via do_shortcode
  return do_shortcode($wrapped);
});

/* ===== Helpers (same as previous message) ===== */

/** Build robust URL variants to match absolute/relative, www/non-www, scheme-less, with/without trailing slash. */
function cz_build_link_variants(int $post_id): array {
  $permalink = get_permalink($post_id);
  if (!$permalink) return [];

  $abs = rtrim($permalink, '/');
  $rel = rtrim(wp_make_link_relative($permalink), '/');
  $abs_no_scheme = preg_replace('#^https?://#i', '//', $abs);

  $swap_www = function ($url) {
    if (strpos($url, '//') === false) return $url;
    if (preg_match('#//www\.#i', $url)) return preg_replace('#//www\.#i', '//', $url, 1);
    return preg_replace('#//#', '//www.', $url, 1);
  };

  $variants = [];
  $add = function ($u) use (&$variants) {
    if (!$u) return;
    $variants[$u] = true;
    $variants[$u . '/'] = true;
    $variants[esc_url_raw($u)] = true;
    $variants[str_replace('&', '&amp;', $u)] = true;
    $variants[urlencode($u)] = true;
  };

  // Absolute
  $add($abs);
  $add($abs_no_scheme);
  $add($swap_www($abs));
  $add($swap_www($abs_no_scheme));

  // Relative
  $add($rel);
  $add('/' . ltrim($rel, '/'));

  // Path only
  $path = parse_url($abs, PHP_URL_PATH);
  if ($path) $add(rtrim($path, '/'));

  return array_keys($variants);
}

/** SQL finder (public, published only). */
function cz_find_referrers_group_by_sql(array $variants): array {
  global $wpdb;
  if (empty($variants)) return [];

  $public_types = get_post_types(['public' => true], 'names');
  if (empty($public_types)) return [];

  $in_types = implode("','", array_map('esc_sql', $public_types));

  $likes = [];
  $params = [];
  foreach ($variants as $v) {
    $likes[] = "post_content LIKE %s";
    $params[] = '%' . $wpdb->esc_like($v) . '%';
  }
  $likes_sql = implode(' OR ', $likes);

  $sql = "
    SELECT ID, post_type
    FROM {$wpdb->posts}
    WHERE post_status = 'publish'
      AND post_type IN ('$in_types')
      AND ( $likes_sql )
  ";

  return $wpdb->get_results($wpdb->prepare($sql, $params)) ?: [];
}

/** Order ids by date DESC. */
function cz_order_ids_by_date_desc(array $ids): array {
  $ids = array_values(array_filter(array_map('intval', $ids)));
  if (empty($ids)) return [];
  $q = new WP_Query([
    'post__in'       => $ids,
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
    'fields'         => 'ids',
  ]);
  return $q->posts ?: $ids;
}

/** Grouping + sorting + uniqueness + self-exclusion. */
function cz_find_referrers_grouped_by_post_type(int $target_post_id): array {
  $variants = cz_build_link_variants($target_post_id);
  if (empty($variants)) return [];

  $rows = cz_find_referrers_group_by_sql($variants);
  if (empty($rows)) return [];

  $groups = [];
  foreach ($rows as $row) {
    if ((int)$row->ID === $target_post_id) continue;
    $ptype = $row->post_type ?: 'post';
    $groups[$ptype][] = (int)$row->ID;
  }
  if (empty($groups)) return [];

  foreach ($groups as $ptype => $ids) {
    $groups[$ptype] = cz_order_ids_by_date_desc(array_unique($ids));
  }

  uksort($groups, function ($a, $b) {
    $oa = get_post_type_object($a);
    $ob = get_post_type_object($b);
    $la = ($oa && isset($oa->labels->name)) ? $oa->labels->name : $a;
    $lb = ($ob && isset($ob->labels->name)) ? $ob->labels->name : $b;
    return strcasecmp($la, $lb);
  });

  return $groups;
}

/* Cache busting on content changes */
add_action('save_post', function ($post_id) {
  if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) return;
  global $wpdb;
  $like = $wpdb->esc_like('cz_refs_') . '%';
  $wpdb->query($wpdb->prepare("
    DELETE FROM {$wpdb->options}
    WHERE option_name LIKE %s
  ", '_transient_' . $like));
}, 10, 1);

/**
 * CZ Tag Cloud – theme-integrated
 * Adds a template tag `cz_tag_cloud()` and a shortcode `[cz_tag_cloud]`.
 */

/** Core renderer (returns HTML string) */
if (!function_exists('cz_tag_cloud_render')) {
    function cz_tag_cloud_render(array $atts = []): string {
        $d = [
            // Data
            'taxonomy'      => 'post_tag',
            'number'        => 60,
            'hide_empty'    => true,
            'orderby'       => 'count',      // 'count' | 'name'
            'order'         => 'DESC',
            'min_count'     => 1,
            // Sizing
            'min_font'      => 0.85,
            'max_font'      => 2.00,
            'unit'          => 'rem',        // 'rem' | 'em' | 'px'
            'scale'         => 'log',        // 'linear' | 'log'
            // Display
            'show_count'    => true,
            'aria_label'    => 'Tag cloud',
            'class'         => '',
            // Cache
            'cache_minutes' => 30,
        ];
        $a = shortcode_atts($d, array_change_key_case($atts, CASE_LOWER), 'cz_tag_cloud');

        // Cache key depends on args + site/lang
        // This will cache the result and serve it faster, but for now
        // we disable it since we already use WP Cache. Will use it in the
        // future if the number of tags grows over a thosands.
        //
        // $cache_key = 'cz_tc_' . md5(serialize([$a, get_locale(), get_current_blog_id()]));
        // if ($html = get_transient($cache_key)) {
        //     wp_enqueue_style('cz-tag-cloud');
        //     return $html;
        // }

        $acf_term_get = function( string $field, WP_Term $term ) {
            if ( ! function_exists( 'get_field' ) ) return '';
            // Newer ACF accepts WP_Term or "term_{id}"
            $val = get_field( $field, $term );
            if ( $val === null || $val === '' ) {
                $val = get_field( $field, "{$term->taxonomy}_{$term->term_id}" );
            }
            if ( $val === null ) $val = '';
            return is_string($val) ? trim($val) : $val;
        };

        // Fetch terms
        $args  = [
            'taxonomy'   => $a['taxonomy'],
            'hide_empty' => (bool)$a['hide_empty'],
            'number'     => (int)$a['number'],
            'orderby'    => $a['orderby'],
            'order'      => $a['order'],
        ];

        $terms = get_terms($args);
        if (is_wp_error($terms) || empty($terms)) return '';

        // Filter by min_count
        $terms = array_values(array_filter($terms, fn($t)=> (int)$t->count >= (int)$a['min_count']));
        if (empty($terms)) return '';

        // Compute sizing
        $counts = array_map(fn($t)=> (int)$t->count, $terms);
        $minC = min($counts); $maxC = max($counts);
        $minF = (float)$a['min_font']; $maxF = (float)$a['max_font'];
        $unit = in_array($a['unit'], ['rem','em','px'], true) ? $a['unit'] : 'rem';

        $scaleFn = function (int $c) use ($minC,$maxC,$minF,$maxF,$a): float {
            if ($maxC === $minC) return ($minF + $maxF) / 2;
            if ($a['scale'] === 'linear') {
                $n = ($c - $minC) / ($maxC - $minC);
            } else {
                $n = (log($c) - log($minC)) / (log($maxC) - log($minC));
            }
            $n = max(0, min(1, $n));
            return $minF + ($maxF - $minF) * $n;
        };

        // Stable sort
        if ($a['orderby'] === 'name') {
            usort($terms, fn($x,$y)=> strcasecmp($x->name, $y->name));
            if (strtoupper($a['order']) === 'DESC') $terms = array_reverse($terms);
        } else { // count
            usort($terms, fn($x,$y)=> $y->count <=> $x->count);
            if (strtoupper($a['order']) === 'ASC') $terms = array_reverse($terms);
        }

        $classes = trim('cz-tag-cloud ' . sanitize_html_class($a['class']));
        $aria    = esc_attr($a['aria_label']);

        ob_start(); ?>
<nav class="<?php echo esc_attr($classes); ?>" aria-label="<?php echo $aria; ?>">
    <ul>
        <?php foreach ($terms as $t):
            $size = $scaleFn((int)$t->count);
            $url  = get_term_link($t);
            if (is_wp_error($url)) continue;
            $count = (int)$t->count;
            // Display name: ACF 'show_as' fallback to term name
            $show_as = $acf_term_get( 'show_as', $t );
            $display_name = $show_as !== '' ? $show_as : $t->name;
            $title = esc_attr(sprintf(_n('%s post', '%s posts', $count, 'default'), number_format_i18n($count)));
        ?>
        <li>
            <a href="<?php echo esc_url($url); ?>"
               rel="tag"
               style="font-size: <?php echo esc_attr($size . $unit); ?>;"
               aria-label="<?php echo esc_attr($name . ' – ' . $title); ?>">
                <span class="label"><?php echo $display_name; ?></span>
                <?php if ($a['show_count']): ?>
                    <span class="count" aria-hidden="true"><?php echo number_format_i18n($count); ?></span>
                <?php endif; ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</nav>
<?php
  $html = trim(ob_get_clean());
  set_transient($cache_key, $html, (int)$a['cache_minutes'] * MINUTE_IN_SECONDS);
  wp_enqueue_style('cz-tag-cloud');
  return $html;
    }
}

/** Template tag: echo directly */
if (!function_exists('cz_tag_cloud')) {
    function cz_tag_cloud(array $args = []): void {
        echo cz_tag_cloud_render($args);
    }
}

/** Optional shortcode: [cz_tag_cloud ...] */
add_shortcode('cz_tag_cloud', function ($atts) {
    return cz_tag_cloud_render((array)$atts);
});
