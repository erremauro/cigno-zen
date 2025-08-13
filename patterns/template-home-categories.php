<section class="home-section" id="categories">
	<?php
		get_template_part(
			'parts/cta-title-link',
			null,
			[
				"url"	=> "/categorie",
				"title"	=> "Esplora le Categorie"
			]
		);
	?>

	<div class="categories-grid">
	  <a href="/categoria/approfondimento" class="category-card">Approfondimenti</a>
	  <a href="/categoria/sutra" class="category-card">Sutra</a>
	  <a href="/categoria/saggio" class="category-card">Saggi</a>
	  <a href="/categoria/poesia" class="category-card">Poesie</a>
	  <a href="/categoria/commentario" class="category-card">Commentari</a>
	  <a href="/categoria/estratto" class="category-card">Estratti</a>
	</div>
</section>
