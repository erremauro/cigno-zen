<?php
    $file_path = get_template_directory_uri() . '/assets/audio/bowl';
    $file_mp3 = esc_url( $file_path . '.mp3' );
    $file_ogg = esc_url( $file_path . '.ogg' );

    echo do_shortcode( '[separator]' );
?>

<section class="cz-bowl">
    <div class="cz-bowl-wrap">
        <button class="cz-bowl-btn" type="button" aria-label="Suona la campana">    <?php get_template_part( 'parts/svg/singing-bowl' ); ?>
         </button>
         <div class="cz-bowl-wave"></div>
         <audio class="cz-bowl-audio" preload="auto">
            <source src="<?php echo $file_mp3; ?>" type="audio/mpeg"> <source src="<?php echo $file_ogg; ?>" type="audio/ogg">
        </audio>
    </div>
</section>
