<section class="author-list">
	<h1>Tutti gli Autori</h1>
	<?php
	$authors = get_users([
		'role'    => 'author',
		'orderby' => 'display_name',
		'order'   => 'ASC',
	]);

	if ( ! empty( $authors ) ) :
		echo '<ul class="author-items">';
		foreach ( $authors as $author ) :
			$author_id   = $author->ID;
			$author_name = esc_html( $author->display_name );
			$author_url  = esc_url( get_author_posts_url( $author_id ) );
			$avatar      = get_avatar( $author_id, 48 );
			?>
			<li class="author-item">
				<h4><a href="<?php echo $author_url; ?>">
					<!-- <?php echo $avatar; ?> -->
					<span class="author-name"><?php echo $author_name; ?></span>
				</a></h4>
			</li>
			<?php
		endforeach;
		echo '</ul>';
	else :
		echo '<p>Nessun autore trovato.</p>';
	endif;
	?>
</section>
