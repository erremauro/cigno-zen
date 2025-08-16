<?php get_template_part('parts/header'); ?>

<div id="primary" class="content-area">
	<div class="tags-header-container">
		<?php get_template_part('parts/tags-header'); ?>
	</div>

	<main id="articles" class="site-main">
		<?php get_template_part('patterns/template-query-loop'); ?>
	</main>
</div>

<?php get_template_part('parts/footer'); ?>
