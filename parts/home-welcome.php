<?php
$show_quotes = true;
if ( is_user_logged_in() ) {
	$user_id = get_current_user_id();
	if ( metadata_exists( 'user', $user_id, 'czup_show_quotes' ) ) {
		$raw_value = get_user_meta( $user_id, 'czup_show_quotes', true );
		if ( is_bool( $raw_value ) ) {
			$show_quotes = $raw_value !== false;
		} else {
			$normalized = strtolower( trim( (string) $raw_value ) );
			$show_quotes = ! in_array( $normalized, array( 'false', '0', 'no', 'off' ), true );
		}
	}
}
?>
<section class="home-section" id="welcome">
<?php if ( ! is_user_logged_in() ) : ?>
<div class="post-content">
<h2 class="wp-heading">Benvenuti</h2>
<p>Questo sito ospita un'estesa collezione di traduzioni in italiano della tradizione buddhista Zen Sōtō e non solo.</p>
<?php else : ?>
<div class="post-content">
	<?php if ( $show_quotes ) : ?>
		<h2>Bentornato, <?php echo wp_get_current_user()->display_name; ?>.</h2>
	<?php endif ?>
<?php endif ?>
</div>

<?php if ( $show_quotes ) : ?>
<div class="quote-content">
<?php echo do_shortcode( '[zen_quotes frequency="random"]' ); ?>
</div>
<?php endif ?>
</section>
