<header class="top-nav-bar">
    <div class="nav-left">
        <button class="nav-toggle" type="button" aria-label="Apri menu" aria-controls="nav-drawer" aria-expanded="false">
            <?php get_template_part( 'parts/svg/hamburger' ); ?>
        </button>
        <a class="nav-icon nav-home nav-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Home">
            <?php get_template_part( 'parts/svg/site-logo' ); ?>
        </a>
        <button class="nav-search-close" type="button" aria-label="Chiudi ricerca">
            <?php get_template_part( 'parts/svg/chevron-left' ); ?>
        </button>
    </div>

    <div class="nav-center">
        <div id="top-nav-search" class="search-bar">
            <?php get_template_part( 'parts/search-bar'); ?>
        </div>
    </div>

    <div class="nav-right">
        <?php if ( ! is_user_logged_in() ) : ?>
            <a class="nav-login" href="<?php echo esc_url( home_url( '/login' ) ); ?>">Accedi</a>
        <?php endif; ?>
        <button class="nav-search-toggle" type="button" aria-label="Apri ricerca" aria-expanded="false" aria-controls="top-nav-search">
            <?php get_template_part( 'parts/svg/search-icon' ); ?>
        </button>
        <button id="theme-toggle" class="theme-toggle" aria-label="Cambia tema" aria-pressed="false" type="button">
            <?php get_template_part( 'parts/svg/sun' ); ?>
            <?php get_template_part( 'parts/svg/moon' ); ?>
        </button>
        <?php if ( is_user_logged_in() ) : ?>
            <details class="nav-user">
                <summary class="nav-user-toggle" aria-label="Apri menu utente">
                    <?php get_template_part( 'parts/svg/user' ); ?>
                </summary>
                <div class="nav-user-menu" role="menu">
                    <a role="menuitem" href="<?php echo esc_url( home_url( '/logout' ) ); ?>">Logout</a>
                </div>
            </details>
        <?php endif; ?>
    </div>
</header>

<div class="nav-drawer-backdrop" data-nav-drawer-close></div>
<aside id="nav-drawer" class="nav-drawer" aria-hidden="true">
    <div class="collapsable-section" data-initial="open">
        <h5 class="collapsable-toggle" role="button" tabindex="0" aria-controls="nav-drawer-contenuti" aria-expanded="true">
            Contenuti
        </h5>
        <div id="nav-drawer-contenuti" class="collapsable-content" aria-hidden="false">
            <ul class="menu">
                <li><a href="<?php echo esc_url( home_url( '/articoli' ) ); ?>">Articoli</a></li>
                <li><a href="<?php echo esc_url( home_url( '/autori' ) ); ?>">Autori</a></li>
                <li><a href="<?php echo esc_url( home_url( '/categorie' ) ); ?>">Categorie</a></li>
                <li><a href="<?php echo esc_url( home_url( '/volumi' ) ); ?>">Volumi</a></li>
            </ul>
        </div>
    </div>

    <div class="collapsable-section" data-initial="open">
        <h5 class="collapsable-toggle" role="button" tabindex="0" aria-controls="nav-drawer-utilita" aria-expanded="true">
            Utilità
        </h5>
        <div id="nav-drawer-utilita" class="collapsable-content" aria-hidden="false">
            <ul class="menu">
                <li><a href="<?php echo esc_url( home_url( '/dizionario' ) ); ?>">Dizionario</a></li>
                <li><a href="<?php echo esc_url( home_url( '/genealogia-dei-maestri' ) ); ?>">Genealogia dei Maestri</a></li>
                <li><a href="<?php echo esc_url( home_url( '/studio-dei-sutra' ) ); ?>">Studio dei Sutra</a></li>
            </ul>
        </div>
    </div>
</aside>
