<?php

add_action('init', function () {
    global $wp_rewrite;
    $wp_rewrite->author_base = 'autore';
});

// Nasconde la admin bar nel frontend per tutti gli utenti
add_filter('show_admin_bar', '__return_false');

require_once get_template_directory() . '/inc/styles-and-scripts.php';
require_once get_template_directory() . '/inc/shortcodes.php';
require_once get_template_directory() . '/inc/jetpack.php';
require_once get_template_directory() . '/inc/authentication.php';
require_once get_template_directory() . '/inc/search.php';
require_once get_template_directory() . '/inc/posts.php';
require_once get_template_directory() . '/inc/volumes.php';
require_once get_template_directory() . '/inc/tags.php';
require_once get_template_directory() . '/inc/more-link.php';
require_once get_template_directory() . '/inc/cz-continue-reading.php';

require_once get_template_directory() . '/inc/masters.php';
require_once get_template_directory() . '/inc/broken-link.php';
require_once get_template_directory() . '/inc/link-index.php';
