<?php
/**
 * Render a reusable "more link" toggle block.
 *
 * @param string $target_id          The ID of the content panel to show/hide.
 * @param string $collapsed_label    Label when collapsed (top visible).
 * @param string $expanded_label     Label when expanded (bottom visible).
 * @param string $extra_class        Extra classes for wrapper.
 * @param string $scroll_target_id   (optional) Element ID to scroll to on collapse.
 */
function cz_render_more_link_toggle( string $target_id, string $collapsed_label = 'MOSTRA TUTTO', string $expanded_label = 'CHIUDI', string $extra_class = '', string $scroll_target_id = '' ): void {
	$chevron_url = esc_url( get_template_directory_uri() . '/assets/images/chevron-down.svg' );
	$data_scroll = $scroll_target_id ? ' data-scroll-target="#' . esc_attr( $scroll_target_id ) . '"' : '';
	?>
	<!-- MORE LINK CHEVRON TOGGLE -->
	<div class="more-link-toggle js-toggle <?php echo esc_attr( $extra_class ); ?>"
		role="button"
		tabindex="0"
		aria-controls="<?php echo esc_attr( $target_id ); ?>"
		aria-expanded="false"
		data-toggle-target="#<?php echo esc_attr( $target_id ); ?>"<?php echo $data_scroll; ?>>
		<label class="more-link-label more-link-lable-top"><?php echo esc_html( $collapsed_label ); ?></label>
		<img class="more-link-button" src="<?php echo $chevron_url; ?>" alt="" width="32" height="auto">
		<label class="more-link-label more-link-lable-bottom hidden"><?php echo esc_html( $expanded_label ); ?></label>
	</div>
	<?php
}
