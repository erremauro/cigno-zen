<?php get_template_part('parts/header'); ?>

<div id="primary" class="content-area">
	<div class="author-header-container">
		<?php get_template_part('parts/author-header'); ?>
	</div>

	<main id="articles" class="site-main">
		<?php get_template_part('patterns/template-query-loop'); ?>
	</main>
</div>

<?php
// ===== JSON-LD Person =====
$person = [
	'@context' => 'https://schema.org',
	'@type'    => 'Person',
	'name'     => get_the_author_meta('display_name', $author_id),
];
if ($short_bio)   { $person['description'] = wp_strip_all_tags($short_bio); }
if ($portrait_id) { $person['image'] = wp_get_attachment_image_url($portrait_id, 'full'); }
if (!empty($sameas) && is_array($sameas)) {
	$urls = array_values(array_filter(array_map(static function($r){ return $r['url'] ?? ''; }, $sameas)));
	if ($urls) { $person['sameAs'] = $urls; }
}
?>
<script type="application/ld+json">
<?php echo wp_json_encode($person, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT); ?>
</script>

<?php get_template_part('parts/footer'); ?>
