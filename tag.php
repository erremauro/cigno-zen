<?php get_template_part('parts/header'); ?>

<div id="primary" class="content-area">
	<div class="tags-header-container">
		<h3 id="term" class="tag-header-title">Dal Dizionario</h3>
		<?php get_template_part('parts/tags-header'); ?>
	</div>

	<main id="articles" class="site-main">
		<?php get_template_part('patterns/template-query-loop'); ?>
	</main>
</div>

<?php get_template_part('parts/footer'); ?>
