
<section class="home-section <?php if ( ! is_user_logged_in() ) { echo 'czcr-guest-continue'; } ?>" id="readinglist">
	<h4 class="section-title">Continua a Leggere</h4>
	<?php echo do_shortcode('[readings limit="5"]'); ?>
</section>
