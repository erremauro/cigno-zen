<style>
    .utility h1 {
        font-size: 2.5em;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 1em;
    }

    .utility p {
        text-indent: 0;
    }
    .utility-list {
        margin-top: 2em;
    }
    .utility-item {
        margin-top: 1em;
    }
    .utility-name {
        font-size: 1.68em;
    }
    .utility-description {
        color: var(--border);
    }
</style>

<article class="utility">
    <div class="post-content">
        <h1>Utilità</h1>

        <p>Questa sezione raccoglie risorse e strumenti pensati per accompagnarti nello studio del Dharma.</p>

        <div class="utility-list">
            <div class="utility-item">
                <div class="utility-name">
                    <a href="<?php echo esc_url(home_url('utilita/genealogia-dei-maestri')) ?>">Genealogia dei Maestri</a>
                </div>
                <div class="utility-description">
                    Utilizza questo tool per esplorare l'albero genealogico dei maestri zen.
                </div>
            </div>
            <div class="utility-item">
                <div class="utility-name">
                    <a href="<?php echo esc_url(home_url('utilita/studio-dei-sutra')) ?>">Studio dei Sutra</a>
                </div>
                <div class="utility-description">
                    Questo tool scompone i sutra in parti e ti aiuta a memorizzarli con il supporto dell'audio.
                </div>
            </div>
        </div>
    </div>
</article>