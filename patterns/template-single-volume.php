<?php
while ( have_posts() ) :
	the_post();

	global $wpdb;
	$table_name = $wpdb->prefix . 'cz_volume_items';

	$chapters_sql = $wpdb->prepare(
		"SELECT i.post_id, i.chapter_number, i.entry_type, i.section_label, i.position, p.post_title
		FROM {$table_name} i
		INNER JOIN {$wpdb->posts} p ON p.ID = i.post_id
		WHERE i.volume_id = %d
		AND p.post_type = %s
		AND p.post_status = %s
		ORDER BY i.position ASC, i.id ASC",
		get_the_ID(),
		'post',
		'publish'
	);
	$chapters = $wpdb->get_results( $chapters_sql );
	$front_sections = array();
	$numbered_chapters = array();
	$back_sections = array();
	if ( is_array( $chapters ) ) {
		foreach ( $chapters as $chapter ) {
			$entry_type = isset( $chapter->entry_type ) ? sanitize_key( (string) $chapter->entry_type ) : 'chapter';
			if ( 'front_matter' === $entry_type ) {
				$front_sections[] = $chapter;
			} elseif ( 'back_matter' === $entry_type ) {
				$back_sections[] = $chapter;
			} else {
				$numbered_chapters[] = $chapter;
			}
		}
	}

	$raw_content     = (string) get_the_content();
	$has_description = '' !== trim( wp_strip_all_tags( $raw_content ) );
	?>

	<div class="wp-group">
		<?php echo display_volume_author( get_the_ID(), false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<h1 class="volumes-title"><?php the_title(); ?></h1>

		<?php if ( $has_description ) : ?>
			<div class="post-content volumes-description">
				<?php echo apply_filters( 'the_content', $raw_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php endif; ?>
	</div>

	<div class="volumes-chapters">
		<?php if ( ! empty( $front_sections ) || ! empty( $numbered_chapters ) || ! empty( $back_sections ) ) : ?>
			<ul class="volumes-posts">
				<?php foreach ( array_merge( $front_sections, $numbered_chapters, $back_sections ) as $chapter ) : ?>
					<?php
					$chapter_post_id = isset( $chapter->post_id ) ? (int) $chapter->post_id : 0;
					$chapter_title   = isset( $chapter->post_title ) ? $chapter->post_title : '';
					if ( ! $chapter_post_id || '' === $chapter_title ) {
						continue;
					}
					?>
					<li>
						<h2 class="chapter-title"><a href="<?php echo esc_url( get_permalink( $chapter_post_id ) ); ?>"><?php echo esc_html( $chapter_title ); ?></a></h2>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p>Nessun capitolo disponibile per questo volume.</p>
		<?php endif; ?>
	</div>

<?php endwhile; ?>
