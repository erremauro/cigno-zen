
<?php
$show_continue_reading = true;
$current_user_id       = get_current_user_id();

if ($current_user_id) {
	$continue_reading_meta = get_user_meta($current_user_id, 'czup_continue_reading', true);

	$show_continue_reading = ! in_array($continue_reading_meta, array(false, 'false', 0, '0'), true);
}
?>
<?php if ($show_continue_reading) : ?>
	<section class="home-section czcr-readings-section" id="readinglist">
		<h4 class="section-title">Continua a Leggere</h4>
		<?php echo do_shortcode('[readings limit="5"]'); ?>
	</section>
<?php endif; ?>
