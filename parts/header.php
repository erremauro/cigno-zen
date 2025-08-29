<?php
// Questo template permette di specificare
// dei parametri che ne condizionano il contenuto.
//
// Definizione dei parametri di default:
$array_defaults = array(
    'show_menu' => true,
);
$args = wp_parse_args($args, $array_defaults);
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<?php get_template_part('parts/head'); ?>

<body <?php body_class(); ?>>
    <header class="site-header">
        <!-- THEME TOGGLE -->
        <button id="theme-toggle" class="theme-toggle" aria-label="Cambia tema" aria-pressed="false">
            <!-- Sole -->
            <svg class="icon-sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <circle cx="12" cy="12" r="5"/>
              <line x1="12" y1="1" x2="12" y2="3"/>
              <line x1="12" y1="21" x2="12" y2="23"/>
              <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
              <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
              <line x1="1" y1="12" x2="3" y2="12"/>
              <line x1="21" y1="12" x2="23" y2="12"/>
              <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
              <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
            <!-- Luna -->
            <svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
        </button>
        <!-- SITE LOGO -->
        <div class="site-branding">
            <a href="<?php echo esc_url(home_url('/')) ?>" rel="home" class="custom-logo-link">
                <?php get_template_part( 'parts/svg/site-logo' ) ?>
            </a>
        </div>

        <?php if ( $args['show_menu'] ) : ?>
            <!-- SEARCH BAR -->
            <div id="search-bar" class="search-bar" style="<?php if (is_search()) echo 'display: block;'; else echo 'display: none;'; ?>">
                <?php get_template_part( 'parts/search-bar') ?>
                <!-- MENU -->
                <?php get_template_part( 'parts/menu-bar') ?>
            </div>
            <!-- MENU CHEVRON BUTTON -->
            <div id="site-menu-toggle" class="site-menu-toggle">
                <label class="menu-label <?php if (is_search()) echo 'hidden'; else echo '' ?>">MENU</label>
                <img id="menu-button" class="<?php if (is_search()) echo 'rotated'; else echo ''; ?>" src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/chevron-down.svg'); ?>" title="<?php echo bloginfo('description') ?>" alt="<?php echo bloginfo('name'); ?>" width="32" height="auto" style="cursor: pointer">
                <label class="menu-label <?php if (is_search()) echo ''; else echo 'hidden' ?>">MENU</label>
            </div>
        <?php endif ?>
    </header>
