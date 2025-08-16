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

// Unique ID for collapsible bio
$bio_panel_id = 'author-full-bio-' . $author_id;
?>

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
						<!-- Top toggle (reusable helper) -->
						<?php
						if ( function_exists('cz_render_more_link_toggle') ) {
							cz_render_more_link_toggle( $bio_panel_id, 'LEGGI TUTTO', 'CHIUDI', '', 'author-hero' );
						}
						?>
					<?php endif; ?>
				</div>

				<?php if ($full_bio): ?>
					<div class="author-bio-full" id="<?php echo esc_attr($bio_panel_id); ?>" hidden>
						<?php echo wp_kses_post( apply_filters('the_content', $full_bio) ); ?>

						<!-- Bottom toggle (reusable helper) -->
						<?php
						if ( function_exists('cz_render_more_link_toggle') ) {
							cz_render_more_link_toggle( $bio_panel_id, 'LEGGI TUTTO', 'CHIUDI', 'more-link-bottom', 'author-hero' );
						}
						?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</header>
