<?php get_template_part('parts/header'); ?>

<?php
// ===== Author context =====
$author     = get_queried_object();
$author_id  = $author->ID;

// ACF (with sensible fallbacks)
$portrait_id = get_field('author_portrait', 'user_' . $author_id);
$short_bio   = get_field('author_short_bio', 'user_' . $author_id) ?: get_the_author_meta('description', $author_id);
$full_bio    = get_field('author_full_bio', 'user_' . $author_id);
$birth       = get_field('author_birth', 'user_' . $author_id);
$death       = get_field('author_death', 'user_' . $author_id);
$sameas      = get_field('author_sameas', 'user_' . $author_id); // repeater of URLs (optional)

// Derived
$posts_count   = count_user_posts($author_id, 'post', true);
$portrait_html = $portrait_id ? wp_get_attachment_image($portrait_id, 'thumbnail', false, ['class' => 'author-portrait']) : '';
$chevron_url   = esc_url( get_template_directory_uri() . '/assets/images/chevron-down.svg' );
?>

<div id="primary" class="content-area">
	<header id="author-hero" class="author-hero">
		<div class="author-hero-media">
			<?php echo $portrait_html; ?>
		</div>

		<div class="author-hero-meta">
			<h1 class="author-name" id="author-title"><?php echo esc_html(get_the_author_meta('display_name', $author_id)); ?></h1>

			<?php if ($birth || $death): ?>
				<div class="author-dates">
					<?php if ($birth) echo '<span class="birth">'.esc_html($birth).'</span>'; ?>
					<?php if ($death) echo ' – <span class="death">'.esc_html($death).'</span>'; ?>
				</div>
			<?php endif; ?>

			<?php if ($short_bio || $full_bio): ?>
				<div class="author-bio-inline">
					<div class="author-bio-teaser">
						<?php echo wp_kses_post(wpautop($short_bio ?: '')); ?>

						<?php if ($full_bio): ?>
							<!-- Top toggle (label left, chevron right) -->
							<div class="more-link js-bio-toggle" role="button" tabindex="0"
								 aria-controls="author-full-bio" aria-expanded="false"
								 data-align="label-first">
								<div class="more-link-label">CONTINUA</div>
								<img class="more-link-chevron" src="<?php echo $chevron_url; ?>" width="32" height="auto" alt="Espandi/Riduci biografia">
							</div>
						<?php endif; ?>
					</div>

					<?php if ($full_bio): ?>
						<div class="author-bio-full" id="author-full-bio" hidden>
							<?php echo wp_kses_post( apply_filters('the_content', $full_bio) ); ?>

							<!-- Bottom toggle (chevron left, label right) -->
							<div class="more-link more-link-bottom js-bio-toggle" role="button" tabindex="0"
								 aria-controls="author-full-bio" aria-expanded="false"
								 data-align="chevron-first">
								<img class="more-link-chevron" src="<?php echo $chevron_url; ?>" width="32" height="auto" alt="Espandi/Riduci biografia">
								<div class="more-link-label">CONTINUA</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</header>

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
