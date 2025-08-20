
<section class="home-section <?php if ( ! is_user_logged_in() ) { echo 'czcr-guest-continue'; } ?>" id="readinglist">
	<?php
		get_template_part(
			'parts/cta-title-link',
			null,
			[
				"url"	=> "",
				"title"	=> "Continua a Leggere"
			]
		);

		echo do_shortcode('[readings limit="5"]');
	?>
</section>
