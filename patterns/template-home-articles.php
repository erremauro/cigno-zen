<section class="home-section" id="latest-articles">
	<h2 class="section-title"><a class="no-color-link" href="/articoli" >Ultimi Articoli &rsaquo;</a></h2>
	<?php
		// Recupera l'ultimo articolo pubblicato
		$args = array(
		    'posts_per_page' => 1, // solo 1 articolo
		    'post_status'    => 'publish',
		);
		$latest_post = get_posts($args);

		if ($latest_post) :
	    	$post = $latest_post[0]; // prende il primo risultato
	    	setup_postdata($post);
    ?>
    	<a class="article-card" href="<?php echo get_permalink($post); ?>">
	    	<h2 class="article-title"><?php the_title(); ?></h2>
	    	<p class="author"><?php the_author(); ?></p>
		</a>
    <?php
	    wp_reset_postdata();
		endif;
	?>
</section>