<?php
$term = get_queried_object();
if ( ! ( $term instanceof WP_Term ) ) {
	return;
}

$tag_name         = single_term_title( '', false );
$readings         = get_field( 'readings', $term );
$short_definition = get_field( 'short_definition', $term );
$full_description = get_field( 'description', $term );
$wp_description   = term_description( $term->term_id, $term->taxonomy );

// Build a stable unique ID for the collapsible panel
$panel_id = 'tag-full-description-' . $term->term_id;
?>

<div id="tag-hero" class="tag-header-container">
<header class="tag-header">
	<h1 class="tag-title"><?php echo esc_html( $tag_name ); ?></h1>

	<?php if ( $readings ) : ?>
		<div class="tag-readings">
			<?php echo wp_kses_post( $readings ); ?>
		</div>
	<?php endif; ?>

	<?php if ( $short_definition || $full_description || $wp_description ) : ?>
		<div class="tag-definition">
			<?php if ( $short_definition ) : ?>
				<div class="tag-short-definition">
					<?php echo wp_kses_post( wpautop( $short_definition ) ); ?>
				</div>
			<?php elseif ( $wp_description ) : ?>
				<div class="tag-short-definition">
					<?php echo wp_kses_post( $wp_description ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $full_description ) : ?>
				<?php
				// Toggle (MOSTRA TUTTO / CHIUDI)
				if ( function_exists( 'cz_render_more_link_toggle' ) ) {
					cz_render_more_link_toggle( $panel_id, 'MOSTRA TUTTO', 'CHIUDI', 'tag-more-toggle', 'tag-hero' );
				}
				?>
				<div id="<?php echo esc_attr( $panel_id ); ?>" class="tag-full-description" hidden>
					<?php echo wp_kses_post( apply_filters( 'the_content', $full_description ) ); ?>

					<?php
					// Bottom toggle mirrored (optional). Reuse the same helper for consistency.
					if ( function_exists( 'cz_render_more_link_toggle' ) ) {
						cz_render_more_link_toggle( $panel_id, 'MOSTRA TUTTO', 'CHIUDI', 'tag-more-toggle-bottom', 'tag-hero' );
					}
					?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</header>
</div>
<?php
if ( function_exists( 'cz_print_tag_jsonld_for_archive' ) ) {
  cz_print_tag_jsonld_for_archive();
}
