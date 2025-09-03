<?php
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

