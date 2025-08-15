<?php
/**
 * Plugin Name: Sentence Shortcode
 * Description: Adds [sentence source="..." reading="..." translation="..." class="..." order="..."] shortcode.
 * Version: 1.2.0
 */

// [sentence source="..." reading="..." translation="..." class="extra-class" order="1."]
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
