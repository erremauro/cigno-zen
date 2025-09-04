<?php
/**
 * Template part: CTA Link with animated chevron
 * Usage:
 *   get_template_part(
 *     'template-parts/components/cta-link',
 *     null,
 *     ['url' => '/blog', 'title' => 'Ultimi articoli']
 *   );
 *
 * Optional args: target, rel, id, class
 */

$args   = $args ?? [];
$url    = isset($args['url'])   ? $args['url']   : '#';
$title  = isset($args['title']) ? $args['title'] : '';
$target = isset($args['target']) ? $args['target'] : '';
$rel    = isset($args['rel'])    ? $args['rel']    : '';
$id     = isset($args['id'])     ? $args['id']     : '';
$class  = 'cta-link' . (isset($args['class']) ? ' ' . esc_attr($args['class']) : '');
?>
<a
	<?php if ($id) echo 'id="' . esc_attr($id) . '" '; ?>
	class="<?php echo esc_attr($class); ?>"
	href="<?php echo esc_url($url); ?>"
	<?php if ($target) echo 'target="' . esc_attr($target) . '" '; ?>
	<?php if ($rel) echo 'rel="' . esc_attr($rel) . '" '; ?>
>
	<span class="cta-link-label"><?php echo esc_html($title); ?></span>

	<svg class="cta-link-chevron" viewBox="0 0 24 24" width="48" height="48" aria-hidden="true"><path d="M6.23 8.97a1 1 0 0 1 1.41 0L12 13.34l4.36-4.37a1 1 0 1 1 1.41 1.42l-5.06 5.06a1 1 0 0 1-1.41 0L6.23 10.4a1 1 0 0 1 0-1.42z" fill="currentColor"></path></svg>
</a>
