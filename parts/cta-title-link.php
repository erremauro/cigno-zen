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

	<div class="cta-link-chevron">
		&rsaquo;
	</div>
</a>
