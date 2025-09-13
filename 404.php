<?php get_template_part('parts/header'); ?>


<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <section class="error-404 not-found">
            <?php if (strpos($_SERVER['REQUEST_URI'], '/maestro') === 0): ?>
                <h1 class="ku">無</h1>
                <p><em>Maestro non trovato</em></p>
                <p class="info">La biografia del Maestro che stavi cercando non esiste ancora.</p>
                <p>
                    <a href="javascript:history.back()" class="btn-back">← Torna indietro</a>
                </p>
            <?php else: ?>
                <h1 class="ku">空</h1>
                <p><em>La forma è vuoto, il vuoto è forma</em></p>
                <p class="info">La pagina che stavi cercando non esiste.</p>
                <p><a href="/">Ritorna alla homepage</a></p>
            <?php endif ?>
        </section>
    </main>
</div>

<?php get_template_part('parts/footer'); ?>
