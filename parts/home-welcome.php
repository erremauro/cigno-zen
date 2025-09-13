<section class="home-section" id="welcome">
<?php if ( ! is_user_logged_in() ) : ?>
<div class="post-content">
<h2 class="wp-heading">Benvenuti</h2>
<p>Questo sito ospita un'estesa collezione di traduzioni in italiano della tradizione buddhista Zen Sōtō e non solo.</p>
<?php else : ?>
<div class="post-content">
<h2>Bentornato, <?php echo wp_get_current_user()->display_name; ?>.</h2>
<?php endif ?>
</div>
<div class="quote-content">
<?php echo do_shortcode( '[zen_quotes frequency="random"]' ); ?>
</div>
</section>
