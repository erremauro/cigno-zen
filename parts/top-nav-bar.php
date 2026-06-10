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
            <?php
            $current_path = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
            $login_url    = add_query_arg( 'redirect_to', $current_path, home_url( '/login' ) );
            ?>
            <a class="nav-login" href="<?php echo esc_url( $login_url ); ?>">Accedi</a>
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
                    <?php if ( current_user_can( 'manage_options' ) ) : ?>
                        <button type="button" role="menuitem" class="czap-trigger">
                            <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                            </svg>
                            <span>Dashboard</span>
                        </button>
                    <?php endif; ?>
                    <?php do_action( 'czh_nav_user_menu_items' ); ?>
                    <a role="menuitem" href="<?php echo esc_url( home_url( '/logout' ) ); ?>">
                        <?php get_template_part( 'parts/svg/logout' ); ?>
                        <span>Logout</span>
                    </a>
                    <a role="menuitem" href="<?php echo esc_url( home_url( '/utente/preferenze' ) ); ?>">
                        <?php get_template_part( 'parts/svg/settings' ); ?>
                        <span>Impostazioni</span>
                    </a>
                </div>
            </details>
        <?php endif; ?>
    </div>
</header>

<?php
if ( current_user_can( 'manage_options' ) ) :
    // Defaults
    $czap_nuovo_url       = admin_url( 'post-new.php' );
    $czap_modifica_url    = '';

    if ( is_singular( 'volume' ) ) {
        $czap_nuovo_url    = admin_url( 'post-new.php?post_type=volume' );
        $czap_modifica_url = admin_url( 'edit.php?post_type=volume&page=cz-volume-chapters&volume_id=' . get_the_ID() );
    } elseif ( is_post_type_archive( 'volume' ) ) {
        $czap_nuovo_url    = admin_url( 'post-new.php?post_type=volume' );
    } elseif ( is_singular( 'maestro' ) ) {
        $czap_nuovo_url    = admin_url( 'post-new.php?post_type=maestro' );
        $czap_modifica_url = admin_url( 'post.php?post=' . get_the_ID() . '&action=edit' );
    } elseif ( is_singular( 'post' ) ) {
        $czap_modifica_url = admin_url( 'post.php?post=' . get_the_ID() . '&action=edit' );
    } elseif ( is_tag() ) {
        $czap_term         = get_queried_object();
        $czap_nuovo_url    = admin_url( 'edit-tags.php?taxonomy=post_tag' );
        $czap_modifica_url = admin_url( 'edit-tags.php?action=edit&taxonomy=post_tag&tag_ID=' . $czap_term->term_id );
    } elseif ( is_page( 'dizionario' ) ) {
        $czap_nuovo_url    = admin_url( 'edit-tags.php?taxonomy=post_tag' );
    }
?>
<div id="cz-admin-panel" class="cz-admin-panel" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Pannello Admin">
    <div class="czap-backdrop"></div>
    <div class="czap-box">
        <div class="czap-header">
            <span class="czap-title">Admin</span>
            <button type="button" class="czap-close" aria-label="Chiudi pannello">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="czap-grid">
            <a class="czap-btn" href="<?php echo esc_url( admin_url() ); ?>">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>
                <span>Bacheca</span>
            </a>
            <a class="czap-btn" href="<?php echo esc_url( $czap_nuovo_url ); ?>">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                <span>Nuovo</span>
            </a>
            <?php if ( $czap_modifica_url ) : ?>
            <a class="czap-btn" href="<?php echo esc_url( $czap_modifica_url ); ?>">
            <?php else : ?>
            <span class="czap-btn czap-btn--disabled" aria-disabled="true">
            <?php endif; ?>
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                <span>Modifica</span>
            <?php echo $czap_modifica_url ? '</a>' : '</span>'; ?>
            <a class="czap-btn" href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=post_tag' ) ); ?>">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                <span>Tag</span>
            </a>
            <a class="czap-btn" href="<?php echo esc_url( admin_url( 'edit.php?post_type=volume' ) ); ?>">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                <span>Volumi</span>
            </a>
            <a class="czap-btn" href="<?php echo esc_url( admin_url( 'edit.php?post_type=cz_quote' ) ); ?>">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/></svg>
                <span>Citazioni</span>
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="nav-drawer-backdrop" data-nav-drawer-close></div>
<aside id="nav-drawer" class="nav-drawer" aria-hidden="true">
    <a class="nav-drawer-home-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">
        <?php get_template_part( 'parts/svg/home' ); ?>
        <span>Pagina Iniziale</span>
    </a>

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
