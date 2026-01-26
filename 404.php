<?php get_template_part('parts/header'); ?>


<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <section class="error-404 not-found">
            <?php $request_path = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; ?>
            <?php if ( strpos( $request_path, '/lemma/' ) === 0 ) : ?>
                <h1 class="ku">空</h1>
                <p><em>Non dovremmo rimanere intrappolati nell’abilità delle parole.</em></p>
                <p class="info">La definizione di questo vocabolo non è ancora disponibile.</p>
                <p><a href="<?php echo esc_url( home_url( '/dizionario/' ) ); ?>">Vai al dizionario</a></p>
            <?php elseif ( strpos( $request_path, '/maestro/' ) === 0 ) : ?>
                <h1 class="ku">無</h1>
                <p><em>La scomparsa è la virtù dei patriarchi.</em></p>
                <p class="info">La biografia di questo maestro non è ancora disponibile.</p>
                <p><a href="<?php echo esc_url( home_url( '/genealogia-dei-maestri/' ) ); ?>">Vai alla genealogia dei maestri</a></p>
            <?php else : ?>
                <h1 class="ku">空</h1>
                <p><em>La forma è vuoto, il vuoto è forma</em></p>
                <p class="info">La pagina che stavi cercando non esiste.</p>
                <p><a href="/">Ritorna alla homepage</a></p>
            <?php endif ?>
        </section>
    </main>
</div>

<?php get_template_part('parts/footer'); ?>
